<?php

namespace App\Http\Controllers;

use App\Exports\LampiranAExport;
use App\Exports\LampiranBExport;
use App\Services\ApiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    public function __construct(protected ApiService $apiService) {}

    public function index(): View
    {
        return view('laporan.index');
    }

    public function lampiranA(Request $request): View
    {
        $filters = $this->reportFilters($request);

        if (!empty($filters['tahun'])) {
            $filters['tahun_mula'] = $filters['tahun'];
            $filters['tahun_tamat'] = $filters['tahun'];
        }

        $result = $this->apiService->withAuth()->get('/laporan/lampiran-a', $filters);

        $records = $this->sortRecords((array) ($result['data'] ?? []), $filters['sort'] ?? null, $filters['order'] ?? null);

        $perPage = max(1, (int) ($filters['per_page'] ?? 10));
        $page = max(1, (int) ($filters['page'] ?? 1));
        $total = count($records);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min($page, $lastPage);

        $offset = ($page - 1) * $perPage;
        $pagedRecords = array_slice($records, $offset, $perPage);
        $from = $total > 0 ? $offset + 1 : 0;
        $to = $total > 0 ? min($offset + $perPage, $total) : 0;

        return view('laporan.lampiran_a', [
            'records' => $pagedRecords,
            'years' => $this->yearOptions(),
            'filters' => $filters,
            'pagination' => [
                'from' => $from,
                'to' => $to,
                'total' => $total,
                'current_page' => $page,
                'last_page' => $lastPage,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function lampiranB(Request $request): View
    {
        $filters = $this->reportFilters($request);

        if (!empty($filters['tahun'])) {
            $filters['tahun_mula'] = $filters['tahun'];
            $filters['tahun_tamat'] = $filters['tahun'];
        }

        $result = $this->apiService->withAuth()->get('/laporan/lampiran-b', $filters);

        $records = $this->sortRecords((array) ($result['data'] ?? []), $filters['sort'] ?? null, $filters['order'] ?? null);

        $perPage = max(1, (int) ($filters['per_page'] ?? 10));
        $page = max(1, (int) ($filters['page'] ?? 1));
        $total = count($records);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min($page, $lastPage);

        $offset = ($page - 1) * $perPage;
        $pagedRecords = array_slice($records, $offset, $perPage);
        $from = $total > 0 ? $offset + 1 : 0;
        $to = $total > 0 ? min($offset + $perPage, $total) : 0;

        return view('laporan.lampiran_b', [
            'records' => $pagedRecords,
            'years' => $this->yearOptions(),
            'filters' => $filters,
            'pagination' => [
                'from' => $from,
                'to' => $to,
                'total' => $total,
                'current_page' => $page,
                'last_page' => $lastPage,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function exportPdfA(Request $request)
    {
        $filters = $this->reportFilters($request);
        $result = $this->apiService->withAuth()->get('/laporan/lampiran-a', array_merge($filters, ['per_page' => 9999]));
        $records = $this->sortRecords((array) ($result['data'] ?? []), $filters['sort'] ?? null, $filters['order'] ?? null);

        return Pdf::loadView('laporan.pdf.lampiran_a', ['records' => $records])
            ->setPaper('a4', 'landscape')
            ->download('Lampiran_A_' . date('Ymd') . '.pdf');
    }

    public function exportPdfB(Request $request)
    {
        $filters = $this->reportFilters($request);
        $result = $this->apiService->withAuth()->get('/laporan/lampiran-b', array_merge($filters, ['per_page' => 9999]));
        $records = $this->sortRecords((array) ($result['data'] ?? []), $filters['sort'] ?? null, $filters['order'] ?? null);

        return Pdf::loadView('laporan.pdf.lampiran_b', ['records' => $records])
            ->setPaper('a4', 'landscape')
            ->download('Lampiran_B_' . date('Ymd') . '.pdf');
    }

    public function exportExcelA(Request $request)
    {
        return Excel::download(new LampiranAExport($this->apiService, $this->reportFilters($request)), 'Lampiran_A_' . date('Ymd') . '.xlsx');
    }

    public function exportExcelB(Request $request)
    {
        return Excel::download(new LampiranBExport($this->apiService, $this->reportFilters($request)), 'Lampiran_B_' . date('Ymd') . '.xlsx');
    }

    private function reportFilters(Request $request): array
    {
        return $request->only(['tahun', 'tahun_mula', 'tahun_tamat', 'search', 'sort', 'order', 'page', 'per_page']);
    }

    private function yearOptions(): array
    {
        $currentYear = (int) date('Y');
        $years = [];

        for ($i = 0; $i < 10; $i++) {
            $years[] = $currentYear - $i;
        }

        return $years;
    }

    private function sortRecords(array $records, ?string $sort, ?string $order): array
    {
        if (! $sort) {
            return $records;
        }

        $order = strtolower($order ?? 'asc') === 'desc' ? 'desc' : 'asc';

        usort($records, function ($a, $b) use ($sort, $order) {
            $valueA = $a[$sort] ?? '';
            $valueB = $b[$sort] ?? '';

            if (str_contains((string) $sort, 'tarikh')) {
                $valueA = $this->dateToTimestamp(is_string($valueA) ? $valueA : null);
                $valueB = $this->dateToTimestamp(is_string($valueB) ? $valueB : null);
            }

            if (is_bool($valueA)) {
                $valueA = $valueA ? 1 : 0;
            }
            if (is_bool($valueB)) {
                $valueB = $valueB ? 1 : 0;
            }

            $compare = is_numeric($valueA) && is_numeric($valueB)
                ? ((float) $valueA <=> (float) $valueB)
                : strcasecmp((string) $valueA, (string) $valueB);

            return $order === 'asc' ? $compare : -$compare;
        });

        return $records;
    }

    private function dateToTimestamp(?string $date): int
    {
        if (! $date) {
            return 0;
        }

        $parsed = \DateTime::createFromFormat('d/m/Y', $date)
            ?: \DateTime::createFromFormat('d-m-Y', $date);
        return $parsed ? $parsed->getTimestamp() : 0;
    }

    private function contractDurationMonths(?string $mulaTarikh, ?string $tamatTarikh): int
    {
        if (! $mulaTarikh || ! $tamatTarikh) {
            return 0;
        }

        $start = \DateTime::createFromFormat('d/m/Y', $mulaTarikh);
        $end = \DateTime::createFromFormat('d/m/Y', $tamatTarikh);

        if (! $start || ! $end) {
            return 0;
        }

        $interval = $start->diff($end);
        return ($interval->y * 12) + $interval->m + ($interval->d > 0 ? 1 : 0);
    }
}
