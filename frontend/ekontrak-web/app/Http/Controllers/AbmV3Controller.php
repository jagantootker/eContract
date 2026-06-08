<?php

namespace App\Http\Controllers;

use App\Helpers\AuthHelper;
use App\Models\AbmV3Upload;
use App\Models\AbmV3WorkflowHistory;
use App\Services\AbmV3WorkbookParser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AbmV3Controller extends Controller
{
    private const DB_FALLBACK_MESSAGE = 'Pangkalan data tidak tersedia. Paparan demo kosong dipaparkan.';

    public function __construct(private readonly AbmV3WorkbookParser $parser)
    {
    }

    private function checkAccess(): void
    {
        $roles = AuthHelper::roles();
        if (! in_array('admin', $roles, true) && ! in_array('admin_sistem', $roles, true)) {
            abort(403, 'Anda tidak mempunyai akses kepada modul ini.');
        }
    }

    private function renderPptPlaceholder(string $pageTitle): View
    {
        $this->checkAccess();

        return view('abm-v3.ppt-placeholder', [
            'pageTitle' => $pageTitle,
        ]);
    }

    public function pptDashboard(): View
    {
        return $this->renderPptPlaceholder('Dashboard PPT');
    }

    public function pptImport(): View
    {
        return $this->renderPptPlaceholder('Muat Naik PPT');
    }

    public function pptSummary(): View
    {
        return $this->renderPptPlaceholder('Ringkasan PPT');
    }

    public function pptRepository(): View
    {
        return $this->renderPptPlaceholder('Repositori PPT');
    }

    public function pptStatus(): View
    {
        return $this->renderPptPlaceholder('Status Proses PPT');
    }

    public function pptAudit(): View
    {
        return $this->renderPptPlaceholder('Audit Trail PPT');
    }

    private function isDatabaseReady(): bool
    {
        try {
            DB::connection()->getPdo();
            return Schema::hasTable('abm_v3_uploads') && Schema::hasTable('abm_v3_workflow_history');
        } catch (\Throwable) {
            return false;
        }
    }

    public function dashboard(Request $request): View
    {
        $this->checkAccess();

        if (! $this->isDatabaseReady()) {
            return view('abm-v3.dashboard', [
                'stats' => $this->zeroStats(),
                'selectedYear' => (int) date('Y'),
                'previousYear' => (int) date('Y') - 1,
                'yearOptions' => [(int) date('Y')],
                'sectionBreakdown' => collect(),
                'sectorBreakdown' => collect(),
                'programBreakdown' => collect(),
                'activityBreakdown' => collect(),
                'yearComparison' => collect(),
                'monthlyCalendar' => collect(),
                'headerSummary' => [],
                'recentUploads' => collect(),
                'recentActivities' => collect(),
                'topSections' => collect(),
                'dbUnavailableMessage' => self::DB_FALLBACK_MESSAGE,
            ]);
        }

        $uploads = AbmV3Upload::with('workflowHistory')->latest()->get();
        $yearOptions = $uploads
            ->map(fn (AbmV3Upload $upload) => $this->resolveWorkbookYear($upload))
            ->filter(fn ($year) => $year !== null)
            ->map(fn ($year) => (int) $year)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        $selectedYear = $this->normalizeYearValue($request->input('year'))
            ?? ($yearOptions[0] ?? (int) date('Y'));

        if (! in_array($selectedYear, $yearOptions, true)) {
            $yearOptions[] = $selectedYear;
            rsort($yearOptions);
        }

        $previousYear = $selectedYear - 1;

        $selectedUploads = $uploads->filter(function (AbmV3Upload $upload) use ($selectedYear) {
            return $this->resolveWorkbookYear($upload) === $selectedYear;
        })->values();

        $selectedRows = $this->gatherRows($selectedUploads);
        $selectedSections = $this->gatherSections($selectedUploads);
        $headerSummary = $this->gatherHeaderSummary($selectedUploads);
        $selectedField = $this->selectedYearColumnFor($selectedYear);
        $previousField = $this->previousYearColumnFor($selectedYear);

        $stats = [
            'uploads' => $selectedUploads->count(),
            'rows' => count($selectedRows),
            'sheets' => $selectedUploads->sum('total_sheets'),
            'sections' => count($selectedSections),
            'amount' => (float) $selectedUploads->sum('total_amount'),
            'categories' => count($selectedSections),
            'programs' => count($selectedRows),
            'activities' => 0,
            'header_fields' => collect($headerSummary)->filter(fn ($value) => trim((string) $value) !== '')->count(),
        ];

        $sectionBreakdown = $this->buildObjectAmSummary($selectedUploads, $selectedField, $previousField);
        $sectorBreakdown = $this->buildPivotSummary($selectedUploads, 'sektor');
        $programBreakdown = $this->buildPivotSummary($selectedUploads, 'program');
        $activityBreakdown = $this->buildPivotSummary($selectedUploads, 'aktiviti');
        $yearComparison = $this->buildYearComparisonSummary($uploads, $yearOptions);
        $monthlyCalendar = $this->buildMonthlyCalendarSummary($selectedUploads, $selectedYear);

        $topSections = collect($sectionBreakdown)
            ->sortByDesc('selected_year')
            ->take(6)
            ->values();

        $recentUploads = $selectedUploads->take(8);
        $recentActivities = AbmV3WorkflowHistory::with('upload')->latest()->limit(12)->get();

        return view('abm-v3.dashboard', compact(
            'stats',
            'selectedYear',
            'previousYear',
            'yearOptions',
            'sectionBreakdown',
            'sectorBreakdown',
            'programBreakdown',
            'activityBreakdown',
            'yearComparison',
            'monthlyCalendar',
            'headerSummary',
            'recentUploads',
            'recentActivities',
            'topSections'
        ));
    }

    public function import(): View
    {
        $this->checkAccess();
        return view('abm-v3.import');
    }

    public function upload(Request $request): JsonResponse
    {
        $this->checkAccess();

        if (! $this->isDatabaseReady()) {
            return response()->json(['success' => false, 'message' => self::DB_FALLBACK_MESSAGE], 503);
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:15360',
            'template_type' => 'nullable|string|max:40',
        ]);

        $file = $request->file('file');
        $templateType = 'ABM_TEMPLATE';
        $referenceNo = 'V3-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        $filename = $referenceNo . '_' . time() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('abm-v3-uploads', $filename, 'public');

        $parsed = $this->parser->parsePath(storage_path('app/public/' . $filePath), $templateType);

        $upload = AbmV3Upload::create([
            'reference_no' => $referenceNo,
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'template_type' => $templateType,
            'file_type' => 'EXCEL',
            'uploaded_by' => AuthHelper::user()['id'] ?? null,
            'uploaded_by_name' => AuthHelper::userName(),
            'status' => 'DIEXTRACT',
            'workbook_data' => [
                'header_information' => $parsed['header_information'],
                'budget_rows' => $parsed['budget_rows'],
                'budget_sections' => $parsed['budget_sections'],
                'hierarchy' => $parsed['hierarchy'],
                'sheets' => $parsed['sheets'],
            ],
            'summary_data' => $parsed['budget_sections'],
            'total_rows' => $parsed['totals']['rows'],
            'total_sections' => $parsed['totals']['sections'],
            'total_sheets' => $parsed['totals']['sheets'],
            'total_amount' => $parsed['totals']['amount'],
            'year' => (int) date('Y'),
        ]);

        $this->logWorkflow($upload->id, 'UPLOADED', 'Fail berjaya dimuat naik');
        $this->logWorkflow($upload->id, 'EXTRACTED', 'Data berjaya diekstrak', [
            'rows' => $upload->total_rows,
            'sections' => $upload->total_sections,
            'sheets' => $upload->total_sheets,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Fail berjaya dimuat naik dan diekstrak.',
            'data' => [
                'id' => $upload->id,
                'reference_no' => $upload->reference_no,
                'preview_url' => route('abm.v3.preview', $upload->id),
            ],
        ]);
    }

    public function preview(AbmV3Upload $upload): View
    {
        $this->checkAccess();

        return view('abm-v3.preview', [
            'upload' => $upload,
            'workflowHistory' => $upload->workflowHistory()->latest()->get(),
            'headerInformation' => $upload->workbook_data['header_information'] ?? [],
            'budgetSections' => $upload->workbook_data['budget_sections'] ?? [],
        ]);
    }

    public function summary(Request $request): View
    {
        $this->checkAccess();

        if (! $this->isDatabaseReady()) {
            return view('abm-v3.summary', [
                'selectedYear' => (int) date('Y'),
                'previousYear' => (int) date('Y') - 1,
                'yearOptions' => [(int) date('Y')],
                'objectAmSummary' => collect(),
                'objectAmGrandTotal' => [
                    'selected_year' => 0,
                    'previous_year' => 0,
                ],
                'totals' => $this->zeroStats(),
                'headerInformation' => [],
                'dbUnavailableMessage' => self::DB_FALLBACK_MESSAGE,
            ]);
        }

        $uploads = AbmV3Upload::query()->latest()->get();
        $uploadYears = $uploads
            ->map(fn (AbmV3Upload $upload) => $this->resolveWorkbookYear($upload))
            ->filter(fn ($year) => $year !== null)
            ->map(fn ($year) => (int) $year)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        $selectedYear = $this->normalizeYearValue($request->input('year'))
            ?? ($uploadYears[0] ?? (int) date('Y'));

        if (! in_array($selectedYear, $uploadYears, true)) {
            $uploadYears[] = $selectedYear;
            rsort($uploadYears);
        }

        $selectedUploads = $uploads->filter(function (AbmV3Upload $upload) use ($selectedYear) {
            return $this->resolveWorkbookYear($upload) === $selectedYear;
        })->values();

        $previousYear = $selectedYear - 1;
        $selectedField = $this->selectedYearColumnFor($selectedYear);
        $previousField = $this->previousYearColumnFor($selectedYear);

        $objectAmSummary = $this->buildObjectAmSummary($selectedUploads, $selectedField, $previousField);
        $objectAmGrandTotal = [
            'selected_year' => $objectAmSummary->sum('selected_year'),
            'previous_year' => $objectAmSummary->sum('previous_year'),
        ];

        return view('abm-v3.summary', [
            'selectedYear' => $selectedYear,
            'previousYear' => $previousYear,
            'yearOptions' => $uploadYears,
            'objectAmSummary' => $objectAmSummary,
            'objectAmGrandTotal' => $objectAmGrandTotal,
            'totals' => [
                'uploads' => $selectedUploads->count(),
                'rows' => count($this->gatherRows($selectedUploads)),
                'amount' => (float) $selectedUploads->sum('total_amount'),
                'categories' => count($objectAmSummary),
                'programs' => count($this->gatherRows($selectedUploads)),
                'activities' => 0,
            ],
            'headerInformation' => $this->gatherHeaderSummary($selectedUploads),
        ]);
    }

    public function repository(): View
    {
        $this->checkAccess();

        if (! $this->isDatabaseReady()) {
            return view('abm-v3.repository', [
                'uploads' => $this->emptyPaginator(15),
                'dbUnavailableMessage' => self::DB_FALLBACK_MESSAGE,
            ]);
        }

        return view('abm-v3.repository', [
            'uploads' => AbmV3Upload::latest()->paginate(15),
        ]);
    }

    public function auditTrail(): View
    {
        $this->checkAccess();

        if (! $this->isDatabaseReady()) {
            return view('abm-v3.audit-trail', [
                'histories' => $this->emptyPaginator(20),
                'dbUnavailableMessage' => self::DB_FALLBACK_MESSAGE,
            ]);
        }

        return view('abm-v3.audit-trail', [
            'histories' => AbmV3WorkflowHistory::with('upload')->latest()->paginate(20),
        ]);
    }

    public function getExtractedData(AbmV3Upload $upload): JsonResponse
    {
        $this->checkAccess();

        return response()->json([
            'success' => true,
            'data' => $upload->workbook_data ?? [],
            'summary' => $upload->summary_data ?? [],
        ]);
    }

    private function logWorkflow(int $uploadId, string $action, string $description, array $metadata = []): void
    {
        AbmV3WorkflowHistory::create([
            'upload_id' => $uploadId,
            'action' => $action,
            'description' => $description,
            'performed_by' => AuthHelper::user()['id'] ?? null,
            'performed_by_name' => AuthHelper::userName(),
            'metadata' => $metadata ?: null,
        ]);
    }

    private function gatherRows(Collection $uploads): array
    {
        return $uploads->flatMap(function (AbmV3Upload $upload) {
            return collect($upload->workbook_data['budget_rows'] ?? [])->map(function (array $row) use ($upload) {
                $row['reference_no'] = $upload->reference_no;
                $row['upload_id'] = $upload->id;

                return $row;
            });
        })->values()->all();
    }

    private function gatherSections(Collection $uploads): array
    {
        return $uploads->flatMap(function (AbmV3Upload $upload) {
            return collect($upload->workbook_data['budget_sections'] ?? [])->map(function (array $section) use ($upload) {
                $section['reference_no'] = $upload->reference_no;
                $section['upload_id'] = $upload->id;

                return $section;
            });
        })->values()->all();
    }

    private function gatherHeaderSummary(Collection $uploads): array
    {
        $summary = [
            'sektor' => null,
            'maksud' => null,
            'program' => null,
            'aktiviti' => null,
            'jenis_aktiviti' => null,
            'dasar' => null,
            'tahun' => null,
            'tajuk' => null,
        ];

        foreach ($uploads as $upload) {
            $header = $upload->workbook_data['header_information'] ?? [];
            foreach ($summary as $key => $value) {
                if (trim((string) $value) === '' && ! empty($header[$key])) {
                    $summary[$key] = $header[$key];
                }
            }
        }

        return $summary;
    }

    private function resolveWorkbookYear(AbmV3Upload $upload): ?int
    {
        $headerYear = $this->normalizeYearValue(data_get($upload->workbook_data, 'header_information.tahun'));

        if ($headerYear !== null) {
            return $headerYear;
        }

        return $this->normalizeYearValue($upload->year);
    }

    private function normalizeYearValue(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        if (is_string($value) && preg_match('/\b(19|20)\d{2}\b/', $value, $matches)) {
            return (int) $matches[0];
        }

        return null;
    }

    private function selectedYearColumnFor(int $year): string
    {
        return match ($year) {
            2024 => 'peruntukan_2024',
            2025 => 'peruntukan_asal_2025',
            2026 => 'anggaran_disyorkan_2026',
            2027 => 'anggaran_disyorkan_2027',
            default => 'anggaran_disyorkan_' . $year,
        };
    }

    private function previousYearColumnFor(int $year): string
    {
        return match ($year) {
            2024 => 'perbelanjaan_sebenar_2024',
            2025 => 'peruntukan_2024',
            2026 => 'peruntukan_asal_2025',
            2027 => 'anggaran_disyorkan_2026',
            default => 'anggaran_disyorkan_' . ($year - 1),
        };
    }

    private function buildObjectAmSummary(Collection $uploads, string $selectedField, string $previousField): Collection
    {
        return $uploads
            ->flatMap(function (AbmV3Upload $upload) use ($selectedField, $previousField) {
                $rows = collect($upload->workbook_data['budget_rows'] ?? []);
                $detailRows = $rows->filter(function (array $row) {
                    return strtoupper(trim((string) ($row['row_type'] ?? ''))) === 'DETAIL';
                });

                $detailTotal = $detailRows->sum(fn (array $row) => (float) ($row[$selectedField] ?? 0));
                $useAllRows = abs($detailTotal - (float) $upload->total_amount) > 0.01;

                $sourceRows = $useAllRows
                    ? $rows->filter(function (array $row) {
                        $rowType = strtoupper(trim((string) ($row['row_type'] ?? '')));

                        return $rowType !== 'TOTAL';
                    })
                    : $detailRows;

                return $sourceRows
                    ->filter(function (array $row) {
                        $code = strtoupper(trim((string) ($row['section_code'] ?? '')));

                        return $code !== '' && $code !== 'UNCLASSIFIED';
                    })
                    ->groupBy(fn (array $row) => trim((string) ($row['section_code'] ?? '')))
                    ->map(function ($rows, string $code) use ($upload, $selectedField, $previousField) {
                        return [
                            'code' => $code,
                            'name' => $this->objectAmNameForCode($code),
                            'selected_year' => $rows->sum(fn (array $row) => (float) ($row[$selectedField] ?? 0)),
                            'previous_year' => $rows->sum(fn (array $row) => (float) ($row[$previousField] ?? 0)),
                            'upload_year' => $upload->year,
                        ];
                    });
            })
            ->filter(function (array $section) {
                $code = strtoupper(trim((string) ($section['code'] ?? '')));

                return $code !== '' && $code !== 'UNCLASSIFIED';
            })
            ->groupBy(fn (array $section) => trim((string) ($section['code'] ?? '')))
            ->map(function ($sections, string $code) use ($selectedField, $previousField) {
                return [
                    'code' => $code,
                    'name' => $this->objectAmNameForCode($code),
                    'selected_year' => $sections->sum(fn (array $section) => (float) ($section['selected_year'] ?? 0)),
                    'previous_year' => $sections->sum(fn (array $section) => (float) ($section['previous_year'] ?? 0)),
                ];
            })
            ->sortBy('code')
            ->values();
    }

    private function objectAmNameForCode(string $code): string
    {
        return match (trim($code)) {
            '10000' => 'EMOLUMEN',
            '20000' => 'PERKHIDMATAN DAN BEKALAN',
            '30000' => 'ASET',
            '40000' => 'PEMBERIAN DAN KENAIKAN BAYARAN TETAP',
            '50000' => 'PERBELANJAAN-PERBELANJAAN LAIN',
            default => 'TIDAK DINYATAKAN',
        };
    }

    private function buildPivotSummary(Collection $uploads, string $field): Collection
    {
        return $uploads
            ->groupBy(function (AbmV3Upload $upload) use ($field) {
                $label = trim((string) data_get($upload->workbook_data, 'header_information.' . $field, ''));

                return $label !== '' ? $label : 'TIDAK DINYATAKAN';
            })
            ->map(function (Collection $group, string $label) {
                return [
                    'label' => $label,
                    'count' => $group->count(),
                    'rows' => (int) $group->sum('total_rows'),
                    'amount' => (float) $group->sum('total_amount'),
                ];
            })
            ->sortByDesc('amount')
            ->values();
    }

    private function buildYearComparisonSummary(Collection $uploads, array $yearOptions): Collection
    {
        return collect($yearOptions)
            ->map(function (int $year) use ($uploads) {
                $group = $uploads->filter(fn (AbmV3Upload $upload) => $this->resolveWorkbookYear($upload) === $year);

                return [
                    'year' => $year,
                    'count' => $group->count(),
                    'rows' => (int) $group->sum('total_rows'),
                    'amount' => (float) $group->sum('total_amount'),
                ];
            })
            ->sortByDesc('year')
            ->values();
    }

    private function buildMonthlyCalendarSummary(Collection $uploads, int $selectedYear): Collection
    {
        $months = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mac',
            4 => 'Apr',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Ogo',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Dis',
        ];

        $selectedUploads = $uploads->filter(fn (AbmV3Upload $upload) => $this->resolveWorkbookYear($upload) === $selectedYear);

        return collect(range(1, 12))->map(function (int $month) use ($selectedUploads, $months) {
            $group = $selectedUploads->filter(function (AbmV3Upload $upload) use ($month) {
                return optional($upload->created_at)->month === $month;
            });

            return [
                'month' => $month,
                'label' => $months[$month],
                'count' => $group->count(),
                'amount' => (float) $group->sum('total_amount'),
            ];
        })->values();
    }

    private function yearColorForIndex(int $index): string
    {
        $palette = [
            '#2563eb',
            '#0ea5e9',
            '#14b8a6',
            '#8b5cf6',
            '#f59e0b',
            '#ec4899',
        ];

        return $palette[$index % count($palette)];
    }

    private function buildYearTiles(Collection $yearComparison): Collection
    {
        return $yearComparison->values()->map(function (array $row, int $index) {
            return [
                'year' => $row['year'],
                'count' => $row['count'],
                'rows' => $row['rows'],
                'amount' => $row['amount'],
                'color' => $this->yearColorForIndex($index),
            ];
        });
    }

    private function zeroStats(): array
    {
        return [
            'uploads' => 0,
            'rows' => 0,
            'sheets' => 0,
            'sections' => 0,
            'amount' => 0,
            'categories' => 0,
            'programs' => 0,
            'activities' => 0,
        ];
    }

    private function emptyPaginator(int $perPage): LengthAwarePaginator
    {
        return new LengthAwarePaginator([], 0, $perPage, 1, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);
    }
}