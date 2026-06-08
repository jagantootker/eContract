<?php

namespace App\Http\Controllers;

use App\Helpers\AuthHelper;
use App\Models\AbmPptUpload;
use App\Models\AbmPptWorkflowHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AbmPptController extends Controller
{
    private const DB_FALLBACK_MESSAGE = 'Pangkalan data tidak tersedia (PDO SQLite driver tidak dijumpai). Paparan demo kosong dipaparkan.';
    private const OBJECT_MAP = [
        '10000' => 'Emolumen',
        '20000' => 'Perkhidmatan & Bekalan',
        '30000' => 'Aset',
        '40000' => 'Pemberian & Kenaan Tetap',
        '50000' => 'Lain-Lain',
    ];

    /**
     * Check if user has access to ABM/PPT module (admin or admin_sistem only)
     */
    private function checkAccess(): void
    {
        $roles = AuthHelper::roles();
        $hasAccess = in_array('admin', $roles, true) || in_array('admin_sistem', $roles, true);

        if (!$hasAccess) {
            abort(403, 'Anda tidak mempunyai akses kepada modul ini.');
        }
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // DASHBOARD
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Dashboard ABM/PPT with summary cards
     */
    public function dashboard(): View
    {
        $this->checkAccess();

        return $this->dashboardV2();
    }

    public function dashboardV2(): View
    {
        $this->checkAccess();

        if (! $this->isDatabaseReady()) {
            $totalUpload = 0;
            $totalAmount = 0;
            $totalPrograms = 0;
            $totalActivities = 0;
            $pendingReview = 0;
            $approved = 0;
            $draft = 0;
            $sedangDisemak = 0;
            $diluluskan = 0;
            $ditolak = 0;
            $selesai = 0;
            $budgetBreakdown = collect();
            $departmentComparison = collect();
            $trend = collect();
            $recentUploads = collect();
            $recentActivities = collect();
            $dbUnavailableMessage = self::DB_FALLBACK_MESSAGE;

            return view('abm-ppt.dashboard', compact(
                'totalUpload',
                'totalAmount',
                'totalPrograms',
                'totalActivities',
                'pendingReview',
                'approved',
                'draft',
                'sedangDisemak',
                'diluluskan',
                'ditolak',
                'selesai',
                'budgetBreakdown',
                'departmentComparison',
                'trend',
                'recentActivities',
                'recentUploads',
                'dbUnavailableMessage'
            ));
        }

        $uploads = AbmPptUpload::with('workflowHistory')->orderBy('created_at', 'desc')->get();
        $totalUpload = $uploads->count();
        $draft = $uploads->where('status', 'DRAFT')->count();
        $sedangDisemak = $uploads->where('status', 'SEDANG_DISEMAK')->count();
        $diluluskan = $uploads->where('status', 'DILULUSKAN')->count();
        $ditolak = $uploads->where('status', 'DITOLAK')->count();
        $selesai = $uploads->where('status', 'SELESAI')->count();
        $pendingReview = $uploads->whereIn('status', ['DRAFT', 'SEDANG_DISEMAK'])->count();
        $approved = $uploads->whereIn('status', ['DILULUSKAN', 'SELESAI'])->count();

        $records = $this->flattenAbmRows($uploads);
        $totalAmount = (int) $records->sum(fn (array $row) => $this->normalizeAmount($row['jumlah_dicadang'] ?? $row['jumlah'] ?? 0));
        $totalPrograms = $records->pluck('program_name')->filter()->unique()->count();
        $totalActivities = $records->pluck('aktiviti_name')->filter()->count();

        $budgetBreakdown = $records->groupBy('objek_am_code')->map(function (Collection $group, string $code) {
            return [
                'code' => $code,
                'name' => self::OBJECT_MAP[$code] ?? $code,
                'amount' => (int) $group->sum(fn (array $row) => $this->normalizeAmount($row['jumlah_dicadang'] ?? $row['jumlah'] ?? 0)),
                'programs' => $group->pluck('program_name')->filter()->unique()->count(),
            ];
        })->sortByDesc('amount')->values();

        $departmentComparison = $records->groupBy(fn (array $row) => $row['department'] ?? $row['bahagian'] ?? 'Tidak Dinyatakan')
            ->map(function (Collection $group, string $department) {
                return [
                    'department' => $department,
                    'amount' => (int) $group->sum(fn (array $row) => $this->normalizeAmount($row['jumlah_dicadang'] ?? $row['jumlah'] ?? 0)),
                    'activities' => $group->pluck('aktiviti_name')->filter()->unique()->count(),
                ];
            })->sortByDesc('amount')->take(4)->values();

        $trend = $uploads->groupBy(fn ($upload) => $upload->created_at?->format('Y') ?? date('Y'))
            ->map(function (Collection $group, string $year) {
                $amount = $group->flatMap(fn ($upload) => collect($upload->extraction_data ?? []))
                    ->sum(fn (array $row) => $this->normalizeAmount($row['jumlah_dicadang'] ?? $row['jumlah'] ?? 0));

                return [
                    'year' => $year,
                    'amount' => (int) $amount,
                    'uploads' => $group->count(),
                ];
            })->sortBy('year')->values();

        $recentActivities = AbmPptWorkflowHistory::with('upload')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $recentUploads = AbmPptUpload::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('abm-ppt.dashboard', compact(
            'totalUpload',
            'totalAmount',
            'totalPrograms',
            'totalActivities',
            'pendingReview',
            'approved',
            'draft',
            'sedangDisemak',
            'diluluskan',
            'ditolak',
            'selesai',
            'budgetBreakdown',
            'departmentComparison',
            'trend',
            'recentActivities',
            'recentUploads'
        ));
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // UPLOAD/IMPORT
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Show upload page
     */
    public function uploadPage(): View
    {
        $this->checkAccess();
        return view('abm-v2.import');
    }

    public function importV2(): View
    {
        return $this->uploadPage();
    }

    /**
     * Handle file upload
     */
    public function handleUpload(Request $request): JsonResponse
    {
        $this->checkAccess();

        if (! $this->isDatabaseReady()) {
            return response()->json([
                'success' => false,
                'message' => self::DB_FALLBACK_MESSAGE,
            ], 503);
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,pdf|max:10240',
            'template_type' => 'required|in:ABM1,ABM2,ABM3,ABM4,ABM5,ABM6,ABM7,ABM7A,ABM7B,ABM8,PPT_BARU,PPT_KEMAS_KINI',
        ]);

        try {
            $file = $request->file('file');
            $templateType = $request->input('template_type');
            $fileType = strtoupper($file->getClientOriginalExtension()) === 'PDF' ? 'PDF' : 'EXCEL';

            // Generate reference number
            $refNo = 'ABM-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            // Store file
            $filename = $refNo . '_' . time() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('abm-ppt-uploads', $filename, 'public');

            // Create upload record
            $upload = AbmPptUpload::create([
                'reference_no' => $refNo,
                'filename' => $filename,
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'template_type' => $templateType,
                'file_type' => $fileType,
                'uploaded_by' => AuthHelper::user()['id'] ?? null,
                'uploaded_by_name' => AuthHelper::userName(),
                'status' => 'DRAFT',
            ]);

            // Log workflow
            $this->logWorkflow($upload->id, 'UPLOADED', 'Fail telah dimuat naik');

            // Simulate extraction
            $extractedData = $this->simulateExtraction($upload, $file);
            $upload->update([
                'extraction_data' => $extractedData,
                'total_records' => count($extractedData),
            ]);

            $this->logWorkflow($upload->id, 'EXTRACTED', 'Data telah diekstrak');

            return response()->json([
                'success' => true,
                'message' => 'Fail telah dimuat naik dengan berjaya',
                'data' => [
                    'id' => $upload->id,
                    'reference_no' => $upload->reference_no,
                    'template_type' => $upload->template_type,
                    'file_type' => $upload->file_type,
                    'total_records' => $upload->total_records,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ralat: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // PREVIEW & VERIFICATION
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Show preview page for uploaded file
     */
    public function preview(AbmPptUpload $upload): View
    {
        $this->checkAccess();

        $workflowHistory = AbmPptWorkflowHistory::where('upload_id', $upload->id)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('abm-ppt.preview', compact('upload', 'workflowHistory'));
    }

    /**
     * Get extracted data for preview (AJAX)
     */
    public function getExtractedData(AbmPptUpload $upload): JsonResponse
    {
        $this->checkAccess();

        return response()->json([
            'success' => true,
            'data' => $upload->extraction_data ?? [],
            'total_records' => $upload->total_records,
        ]);
    }

    /**
     * Save as draft
     */
    public function saveDraft(Request $request, AbmPptUpload $upload): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $this->checkAccess();

        if (! $this->isDatabaseReady()) {
            return response()->json([
                'success' => false,
                'message' => self::DB_FALLBACK_MESSAGE,
            ], 503);
        }

        $extractionData = $this->parseExtractionData($request->input('extraction_data'));

        $payload = [
            'notes' => $request->input('notes'),
            'status' => 'DRAFT',
        ];

        if ($extractionData !== null) {
            $payload['extraction_data'] = $extractionData;
            $payload['total_records'] = count($extractionData);
        }

        $upload->update($payload);

        $this->logWorkflow($upload->id, 'VERIFIED', 'Draf telah disimpan');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Draf telah disimpan',
            ]);
        }

        return redirect()->route('abm.preview', $upload->id)->with('success', 'Draf telah disimpan.');
    }

    /**
     * Submit for verification
     */
    public function submitForVerification(Request $request, AbmPptUpload $upload): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $this->checkAccess();

        if (! $this->isDatabaseReady()) {
            return response()->json([
                'success' => false,
                'message' => self::DB_FALLBACK_MESSAGE,
            ], 503);
        }

        $extractionData = $this->parseExtractionData($request->input('extraction_data'));

        $payload = [
            'status' => 'SEDANG_DISEMAK',
        ];

        if ($request->filled('notes')) {
            $payload['notes'] = $request->input('notes');
        }

        if ($extractionData !== null) {
            $payload['extraction_data'] = $extractionData;
            $payload['total_records'] = count($extractionData);
        }

        $upload->update($payload);

        $this->logWorkflow($upload->id, 'SUBMITTED', 'Diserahkan untuk pengesahan');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Telah diserahkan untuk pengesahan',
            ]);
        }

        return redirect()->route('abm.preview', $upload->id)->with('success', 'Telah diserahkan untuk pengesahan.');
    }

    /**
     * Approve upload
     */
    public function approve(Request $request, AbmPptUpload $upload): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $this->checkAccess();

        if (! $this->isDatabaseReady()) {
            return response()->json([
                'success' => false,
                'message' => self::DB_FALLBACK_MESSAGE,
            ], 503);
        }

        $upload->update([
            'status' => 'DILULUSKAN',
        ]);

        $this->logWorkflow($upload->id, 'APPROVED', 'Telah diluluskan');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Telah diluluskan',
            ]);
        }

        return redirect()->route('abm.preview', $upload->id)->with('success', 'Dokumen telah diluluskan.');
    }

    /**
     * Reject upload
     */
    public function reject(Request $request, AbmPptUpload $upload): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $this->checkAccess();

        if (! $this->isDatabaseReady()) {
            return response()->json([
                'success' => false,
                'message' => self::DB_FALLBACK_MESSAGE,
            ], 503);
        }

        $request->validate([
            'reason' => 'required|string|min:5',
        ]);

        $upload->update([
            'status' => 'DITOLAK',
            'rejection_reason' => $request->input('reason'),
        ]);

        $this->logWorkflow($upload->id, 'REJECTED', 'Ditolak: ' . $request->input('reason'));

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Telah ditolak',
            ]);
        }

        return redirect()->route('abm.preview', $upload->id)->with('success', 'Dokumen telah ditolak.');
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // DOCUMENT MANAGEMENT
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Document repository (list all documents)
     */
    public function repository(): View
    {
        $this->checkAccess();

        if (! $this->isDatabaseReady()) {
            $documents = $this->emptyPaginator(15);
            $dbUnavailableMessage = self::DB_FALLBACK_MESSAGE;
            return view('abm-v2.repository', compact('documents', 'dbUnavailableMessage'));
        }

        $documents = AbmPptUpload::orderBy('created_at', 'desc')->paginate(15);

        return view('abm-v2.repository', compact('documents'));
    }

    public function repositoryV2(): View
    {
        return $this->repository();
    }

    public function summaryV2(): View
    {
        $this->checkAccess();

        $uploads = $this->isDatabaseReady()
            ? AbmPptUpload::orderBy('created_at', 'desc')->get()
            : collect();

        $tree = $this->buildSummaryTree($uploads);
        $totals = [
            'files' => $uploads->count(),
            'objects' => $tree->count(),
            'programs' => $tree->sum(fn (array $object) => $object['program_count']),
            'activities' => $tree->sum(fn (array $object) => $object['activity_count']),
            'amount' => $tree->sum(fn (array $object) => $object['amount']),
        ];

        return view('abm-v2.summary', compact('tree', 'totals'));
    }

    public function reviewV2(): View
    {
        $this->checkAccess();

        $uploads = $this->isDatabaseReady()
            ? AbmPptUpload::whereIn('status', ['DRAFT', 'SEDANG_DISEMAK'])->orderBy('updated_at', 'desc')->get()
            : collect();

        return view('abm-v2.review', [
            'items' => $uploads,
        ]);
    }

    public function approvalV2(): View
    {
        $this->checkAccess();

        $uploads = $this->isDatabaseReady()
            ? AbmPptUpload::whereIn('status', ['DILULUSKAN', 'DITOLAK', 'SELESAI'])->orderBy('updated_at', 'desc')->paginate(12)
            : $this->emptyPaginator(12);

        return view('abm-v2.approval', [
            'uploads' => $uploads,
        ]);
    }

    public function auditTrailV2(): View
    {
        $this->checkAccess();

        $histories = $this->isDatabaseReady()
            ? AbmPptWorkflowHistory::with('upload')->orderBy('created_at', 'desc')->paginate(20)
            : $this->emptyPaginator(20);

        return view('abm-v2.audit-trail', [
            'histories' => $histories,
        ]);
    }

    /**
     * Dedicated viewer page (PDF/Excel)
     */
    public function viewer(AbmPptUpload $upload): View
    {
        $this->checkAccess();

        return view('abm-ppt.viewer', compact('upload'));
    }

    /**
     * Fetch documents (AJAX with filtering)
     */
    public function fetchDocuments(Request $request): JsonResponse
    {
        $this->checkAccess();

        if (! $this->isDatabaseReady()) {
            return response()->json([
                'success' => true,
                'data' => [],
                'pagination' => [
                    'total' => 0,
                    'per_page' => (int) $request->input('per_page', 15),
                    'current_page' => (int) $request->input('page', 1),
                    'last_page' => 1,
                ],
                'warning' => self::DB_FALLBACK_MESSAGE,
            ]);
        }

        $query = AbmPptUpload::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('reference_no', 'like', "%$search%")
                  ->orWhere('template_type', 'like', "%$search%")
                  ->orWhere('department', 'like', "%$search%");
        }

        if ($request->filled('template_type')) {
            $query->where('template_type', $request->input('template_type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('year')) {
            $query->where('year', $request->input('year'));
        }

        $documents = $query->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $documents->items(),
            'pagination' => [
                'total' => $documents->total(),
                'per_page' => $documents->perPage(),
                'current_page' => $documents->currentPage(),
                'last_page' => $documents->lastPage(),
            ]
        ]);
    }

    /**
     * View document (get file info)
     */
    public function viewDocument(AbmPptUpload $upload): JsonResponse
    {
        $this->checkAccess();

        $workflowHistory = AbmPptWorkflowHistory::where('upload_id', $upload->id)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'document' => [
                'id' => $upload->id,
                'reference_no' => $upload->reference_no,
                'filename' => $upload->filename,
                'original_filename' => $upload->original_filename,
                'file_path' => asset('storage/' . $upload->file_path),
                'template_type' => $upload->template_type,
                'file_type' => $upload->file_type,
                'status' => $upload->status,
                'uploaded_by_name' => $upload->uploaded_by_name,
                'created_at' => $upload->created_at->format('d M Y H:i'),
                'total_records' => $upload->total_records,
            ],
            'workflow' => $workflowHistory->map(fn($h) => [
                'action' => $h->action_label,
                'description' => $h->description,
                'performed_by' => $h->performed_by_name,
                'created_at' => $h->created_at->format('H:i d M Y'),
            ]),
        ]);
    }

    /**
     * Download document
     */
    public function downloadDocument(AbmPptUpload $upload)
    {
        $this->checkAccess();

        $filePath = storage_path('app/public/' . $upload->file_path);

        if (!file_exists($filePath)) {
            abort(404, 'Fail tidak dijumpai');
        }

        return response()->download($filePath, $upload->original_filename);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // WORKFLOW STATUS
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Show workflow status page
     */
    public function workflowStatus(AbmPptUpload $upload): View
    {
        $this->checkAccess();

        $workflowHistory = AbmPptWorkflowHistory::where('upload_id', $upload->id)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('abm-ppt.workflow-status', compact('upload', 'workflowHistory'));
    }

    /**
     * Workflow status list page
     */
    public function statusProses(): View
    {
        $this->checkAccess();

        if (! $this->isDatabaseReady()) {
            $uploads = $this->emptyPaginator(20);
            $dbUnavailableMessage = self::DB_FALLBACK_MESSAGE;
            return view('abm-ppt.status-proses', compact('uploads', 'dbUnavailableMessage'));
        }

        $uploads = AbmPptUpload::orderBy('updated_at', 'desc')->paginate(20);
        return view('abm-ppt.status-proses', compact('uploads'));
    }

    /**
     * Get workflow progress (AJAX)
     */
    public function getWorkflowProgress(AbmPptUpload $upload): JsonResponse
    {
        $this->checkAccess();

        $workflow = [
            ['step' => 1, 'name' => 'Dimuat Naik', 'status' => 'UPLOADED', 'completed' => true],
            ['step' => 2, 'name' => 'Diekstrak', 'status' => 'EXTRACTED', 'completed' => true],
            ['step' => 3, 'name' => 'Disahkan', 'status' => 'VERIFIED', 'completed' => $upload->status !== 'DRAFT'],
            ['step' => 4, 'name' => 'Diluluskan', 'status' => 'APPROVED', 'completed' => $upload->status === 'DILULUSKAN' || $upload->status === 'SELESAI'],
            ['step' => 5, 'name' => 'Selesai', 'status' => 'COMPLETED', 'completed' => $upload->status === 'SELESAI'],
        ];

        if ($upload->status === 'DITOLAK') {
            $workflow[3]['rejected'] = true;
        }

        return response()->json([
            'success' => true,
            'workflow' => $workflow,
            'current_status' => $upload->status_label,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // HELPER METHODS
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Log workflow action
     */
    private function logWorkflow(int $uploadId, string $action, string $description, ?int $performedBy = null): void
    {
        AbmPptWorkflowHistory::create([
            'upload_id' => $uploadId,
            'action' => $action,
            'description' => $description,
            'performed_by' => $performedBy ?? (AuthHelper::user()['id'] ?? null),
            'performed_by_name' => AuthHelper::userName(),
        ]);
    }

    /**
     * Simulate file extraction (demo data parser)
     * In production, this would use PhpSpreadsheet or PDF parser
     */
    private function simulateExtraction(AbmPptUpload $upload, $file): array
    {
        $recordCount = rand(18, 42);
        $departments = ['Bahagian ICT', 'Bahagian Kewangan', 'Bahagian Perolehan', 'Bahagian Pembangunan', 'Bahagian Undang-Undang'];
        $ministries = ['Kementerian A', 'Kementerian B', 'Kementerian C'];
        $objectCodes = array_keys(self::OBJECT_MAP);
        $programNames = ['Pembangunan Sistem', 'Naik Taraf Infrastruktur', 'Pengurusan Aset', 'Latihan & Kompetensi', 'Audit & Pematuhan'];
        $activityNames = ['Server Upgrade', 'Security Audit', 'Cloud Migration', 'Perolehan Lesen', 'Penyelenggaraan Tahunan', 'Integrasi Data'];
        $officers = ['Pn. Siti', 'Encik Ahmad', 'Pn. Fatimah', 'Encik Zainal', 'Pn. Aina', 'Encik Hakim'];

        $demoData = [];

        for ($i = 1; $i <= $recordCount; $i++) {
            $objectCode = $objectCodes[array_rand($objectCodes)];
            $program = $programNames[array_rand($programNames)];
            $activity = $activityNames[array_rand($activityNames)];
            $amount = rand(50000, 2500000);

            $demoData[] = [
                'tahun' => (string) date('Y'),
                'pegawai_pengawal' => 'Pegawai Pengawal ' . rand(1, 5),
                'kementerian' => $ministries[array_rand($ministries)],
                'department' => $departments[array_rand($departments)],
                'objek_am_code' => $objectCode,
                'objek_am_name' => self::OBJECT_MAP[$objectCode],
                'program_name' => $program,
                'aktiviti_name' => $activity,
                'butiran' => 'Butiran ' . $i,
                'kod_perkara' => 'P' . str_pad((string) rand(1, 999), 3, '0', STR_PAD_LEFT),
                'butiran_keterangan' => $upload->template_type_label . ' - ' . $activity,
                'pegawai' => $officers[array_rand($officers)],
                'jumlah_dicadang' => $amount,
                'jumlah_disyorkan' => (int) round($amount * 0.9),
                'jumlah_diluluskan' => (int) round($amount * 0.8),
                'status' => 'Dicadangkan',
            ];
        }

        return $demoData;
    }

    private function normalizeAmount(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            return (int) round($value);
        }

        if (! is_string($value)) {
            return 0;
        }

        return (int) preg_replace('/[^0-9]/', '', $value);
    }

    private function flattenAbmRows(Collection $uploads): Collection
    {
        return $uploads->flatMap(function ($upload) {
            return collect($upload->extraction_data ?? [])->map(function (array $row) use ($upload) {
                return array_merge($row, [
                    'upload_id' => $upload->id,
                    'reference_no' => $upload->reference_no,
                    'department' => $row['department'] ?? ($upload->department ?? null),
                    'template_type' => $upload->template_type,
                ]);
            });
        })->values();
    }

    private function buildSummaryTree(Collection $uploads): Collection
    {
        $rows = $this->flattenAbmRows($uploads);

        return $rows->groupBy('objek_am_code')->map(function (Collection $objectRows, string $objectCode) {
            $programs = $objectRows->groupBy('program_name')->map(function (Collection $programRows, string $programName) {
                $activities = $programRows->groupBy('aktiviti_name')->map(function (Collection $activityRows, string $activityName) {
                    return [
                        'name' => $activityName,
                        'count' => $activityRows->count(),
                        'amount' => (int) $activityRows->sum(fn (array $row) => $this->normalizeAmount($row['jumlah_dicadang'] ?? $row['jumlah'] ?? 0)),
                        'items' => $activityRows->map(fn (array $row) => [
                            'butiran' => $row['butiran'] ?? $row['butiran_keterangan'] ?? '-',
                            'amount' => $this->normalizeAmount($row['jumlah_dicadang'] ?? $row['jumlah'] ?? 0),
                        ])->values(),
                    ];
                })->values();

                return [
                    'name' => $programName,
                    'activity_count' => $activities->count(),
                    'amount' => (int) $programRows->sum(fn (array $row) => $this->normalizeAmount($row['jumlah_dicadang'] ?? $row['jumlah'] ?? 0)),
                    'activities' => $activities,
                ];
            })->values();

            return [
                'code' => $objectCode,
                'name' => self::OBJECT_MAP[$objectCode] ?? $objectCode,
                'program_count' => $programs->count(),
                'activity_count' => $programs->sum(fn (array $program) => $program['activity_count']),
                'amount' => (int) $objectRows->sum(fn (array $row) => $this->normalizeAmount($row['jumlah_dicadang'] ?? $row['jumlah'] ?? 0)),
                'programs' => $programs,
            ];
        })->values();
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // MANAGEMENT
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Management page - Pengurusan ABM/PPT
     */
    public function management(): View
    {
        $this->checkAccess();

        if (! $this->isDatabaseReady()) {
            $uploads = $this->emptyPaginator(20);
            $stats = [
                'total' => 0,
                'draft' => 0,
                'sedang_disemak' => 0,
                'diluluskan' => 0,
                'ditolak' => 0,
                'selesai' => 0,
            ];
            $dbUnavailableMessage = self::DB_FALLBACK_MESSAGE;
            return view('abm-ppt.management', compact('uploads', 'stats', 'dbUnavailableMessage'));
        }

        $uploads = AbmPptUpload::orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total' => AbmPptUpload::count(),
            'draft' => AbmPptUpload::where('status', 'DRAFT')->count(),
            'sedang_disemak' => AbmPptUpload::where('status', 'SEDANG_DISEMAK')->count(),
            'diluluskan' => AbmPptUpload::where('status', 'DILULUSKAN')->count(),
            'ditolak' => AbmPptUpload::where('status', 'DITOLAK')->count(),
            'selesai' => AbmPptUpload::where('status', 'SELESAI')->count(),
        ];

        return view('abm-ppt.management', compact('uploads', 'stats'));
    }

    /**
     * Management page filtered for PPT templates
     */
    public function managementPpt(): View
    {
        $this->checkAccess();

        if (! $this->isDatabaseReady()) {
            $uploads = $this->emptyPaginator(20);
            $stats = [
                'total' => 0,
                'draft' => 0,
                'sedang_disemak' => 0,
                'diluluskan' => 0,
                'ditolak' => 0,
                'selesai' => 0,
            ];
            $dbUnavailableMessage = self::DB_FALLBACK_MESSAGE;

            return view('abm-ppt.management', [
                'uploads' => $uploads,
                'stats' => $stats,
                'title' => 'Pengurusan PPT',
                'dbUnavailableMessage' => $dbUnavailableMessage,
            ]);
        }

        $uploads = AbmPptUpload::whereIn('template_type', ['PPT_BARU', 'PPT_KEMAS_KINI'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total' => AbmPptUpload::whereIn('template_type', ['PPT_BARU', 'PPT_KEMAS_KINI'])->count(),
            'draft' => AbmPptUpload::whereIn('template_type', ['PPT_BARU', 'PPT_KEMAS_KINI'])->where('status', 'DRAFT')->count(),
            'sedang_disemak' => AbmPptUpload::whereIn('template_type', ['PPT_BARU', 'PPT_KEMAS_KINI'])->where('status', 'SEDANG_DISEMAK')->count(),
            'diluluskan' => AbmPptUpload::whereIn('template_type', ['PPT_BARU', 'PPT_KEMAS_KINI'])->where('status', 'DILULUSKAN')->count(),
            'ditolak' => AbmPptUpload::whereIn('template_type', ['PPT_BARU', 'PPT_KEMAS_KINI'])->where('status', 'DITOLAK')->count(),
            'selesai' => AbmPptUpload::whereIn('template_type', ['PPT_BARU', 'PPT_KEMAS_KINI'])->where('status', 'SELESAI')->count(),
        ];

        return view('abm-ppt.management', [
            'uploads' => $uploads,
            'stats' => $stats,
            'title' => 'Pengurusan PPT',
        ]);
    }

    /**
     * Check DB connectivity and driver readiness.
     */
    private function isDatabaseReady(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Return an empty paginator for fallback screens.
     */
    private function emptyPaginator(int $perPage): LengthAwarePaginator
    {
        $currentPage = max((int) request()->get('page', 1), 1);
        return new LengthAwarePaginator(new Collection(), 0, $perPage, $currentPage, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);
    }

    /**
     * Parse extraction data payload from hidden form input.
     */
    private function parseExtractionData(mixed $raw): ?array
    {
        if (! is_string($raw) || trim($raw) === '') {
            return null;
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
            return is_array($decoded) ? array_values($decoded) : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
