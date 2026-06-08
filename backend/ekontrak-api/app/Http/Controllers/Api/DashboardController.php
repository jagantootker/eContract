<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use App\Models\Kontrak;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    // ── GET /api/v1/dashboard ────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $user      = $request->user();
        $userRoles = $user->roles->pluck('name')->toArray();

        // Pegawai Undang-Undang gets department breakdown table
        if (in_array('pegawai_undang_undang', $userRoles)) {
            return $this->undangUndangDashboard();
        }

        return $this->generalDashboard();
    }

    // ── GET /api/v1/dashboard/maklumat-tidak-lengkap ─────────────────────────
    public function maklumatTidakLengkap(Request $request): JsonResponse
    {
        $query = Kontrak::with(['syarikat', 'jabatan', 'bahagianUnit'])
            ->where(function ($q) {
                $q->whereNull('tajuk_kontrak')
                  ->orWhereNull('syarikat_id')
                  ->orWhereNull('nilai_kontrak')
                  ->orWhereNull('mula_tarikh')
                  ->orWhereNull('tamat_tarikh');
            })
            ->when($request->tahun, fn ($q, $y) =>
                $q->whereYear('created_at', $y)
            )
            ->when($request->search, fn ($q, $s) =>
                $q->where('no_kontrak', 'like', "%{$s}%")
                  ->orWhere('tajuk_kontrak', 'like', "%{$s}%")
            );

        $perPage = (int) $request->get('per_page', 5);

        return response()->json([
            'success' => true,
            'data'    => $query->latest()->paginate($perPage),
            'message' => 'OK',
        ]);
    }

    // ── GET /api/v1/dashboard/kontrak-selesai ────────────────────────────────
    public function kontrakSelesai(Request $request): JsonResponse
    {
        $query = Kontrak::with(['syarikat', 'jabatan'])
            ->where('status_kontrak', 'KONTRAK_SELESAI')
            ->when($request->tahun, fn ($q, $y) =>
                $q->whereYear('tamat_tarikh', $y)
            )
            ->when($request->search, fn ($q, $s) =>
                $q->where('no_kontrak', 'like', "%{$s}%")
                  ->orWhere('tajuk_kontrak', 'like', "%{$s}%")
            );

        $perPage = (int) $request->get('per_page', 5);

        return response()->json([
            'success' => true,
            'data'    => $query->latest()->paginate($perPage),
            'message' => 'OK',
        ]);
    }

    // ── GET /api/v1/dashboard/alerts ─────────────────────────────────────────
    public function alerts(Request $request): JsonResponse
    {
        $now        = Carbon::now();
        $twoWeeks   = $now->copy()->addWeeks(2);
        $sixMonths  = $now->copy()->subMonths(6);

        // Contracts past expiry (no active EOT)
        $tamat = Kontrak::with(['syarikat', 'jabatan', 'pegawaiBertanggungjawab'])
            ->where('status_kontrak', 'DALAM_PELAKSANAAN')
            ->whereNotNull('tamat_tarikh')
            ->where('tamat_tarikh', '<', $now)
            ->get();

        // Expiring within 2 weeks
        $hampirTamat = Kontrak::with(['syarikat', 'jabatan', 'pegawaiBertanggungjawab'])
            ->where('status_kontrak', 'DALAM_PELAKSANAAN')
            ->whereBetween('tamat_tarikh', [$now, $twoWeeks])
            ->get();

        // Active within last 6 months
        $aktifEnamBulan = Kontrak::with(['syarikat', 'jabatan', 'pegawaiBertanggungjawab'])
            ->where('status_kontrak', 'DALAM_PELAKSANAAN')
            ->where('mula_tarikh', '>=', $sixMonths)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => [
                'tempoh_tamat_telah_tamat'   => $tamat,
                'tempoh_tamat_dalam_2_minggu' => $hampirTamat,
                'tempoh_aktif_6_bulan'        => $aktifEnamBulan,
            ],
            'message' => 'OK',
        ]);
    }

    // ── Private: General Dashboard Summary ───────────────────────────────────

    private function generalDashboard(): JsonResponse
    {
        $tidakLengkap = Kontrak::where(function ($q) {
            $q->whereNull('tajuk_kontrak')
              ->orWhereNull('syarikat_id')
              ->orWhereNull('nilai_kontrak')
              ->orWhereNull('mula_tarikh')
              ->orWhereNull('tamat_tarikh');
        })->count();

        $summary = [
            'maklumat_tidak_lengkap' => $tidakLengkap,
            'draf_kontrak'           => Kontrak::where('status_kontrak', 'DRAF')->count(),
            'dalam_pelaksanaan'      => Kontrak::where('status_kontrak', 'DALAM_PELAKSANAAN')->count(),
            'eot'                    => Kontrak::where('status_kontrak', 'EOT')->count(),
            'kontrak_selesai'        => Kontrak::where('status_kontrak', 'KONTRAK_SELESAI')->count(),
            'jumlah_keseluruhan'     => Kontrak::count(),
        ];

        $now      = Carbon::now();
        $twoWeeks = $now->copy()->addWeeks(2);

        $alerts = [
            'tempoh_tamat_telah_tamat'    => Kontrak::where('status_kontrak', 'DALAM_PELAKSANAAN')
                ->where('tamat_tarikh', '<', $now)->pluck('no_kontrak'),
            'tempoh_tamat_dalam_2_minggu' => Kontrak::where('status_kontrak', 'DALAM_PELAKSANAAN')
                ->whereBetween('tamat_tarikh', [$now, $twoWeeks])->pluck('no_kontrak'),
            'tempoh_aktif_6_bulan'        => Kontrak::where('status_kontrak', 'DALAM_PELAKSANAAN')
                ->where('mula_tarikh', '>=', $now->copy()->subMonths(6))->pluck('no_kontrak'),
        ];

        return response()->json([
            'success' => true,
            'data'    => compact('summary', 'alerts'),
            'message' => 'OK',
        ]);
    }

    // ── Private: Undang-Undang Department Breakdown ───────────────────────────

    private function undangUndangDashboard(): JsonResponse
    {
        $now = Carbon::now();
        $twoWeeks = $now->copy()->addWeeks(2);

        $contracts = Kontrak::query()
            ->with('jabatan:id,kod')
            ->get(['id', 'status_kontrak', 'tamat_tarikh', 'jabatan_id', 'no_kontrak']);

        $masterAgencies = Jabatan::query()->orderBy('kod')->pluck('kod')->all();
        $fromContracts = [];

        foreach ($contracts as $contract) {
            $fromContracts[] = $this->resolveAgencyCode($contract->jabatan?->kod, $contract->no_kontrak, $masterAgencies);
        }

        $agencyCodes = array_values(array_unique(array_merge($masterAgencies, array_filter($fromContracts))));
        if (! in_array('LAIN-LAIN', $agencyCodes, true)) {
            $agencyCodes[] = 'LAIN-LAIN';
        }

        $template = [
            'draf_kontrak' => 0,
            'eot_kontrak' => 0,
            'eot_kontrak_dalam_tempoh' => 0,
            'eot_kontrak_akan_tamat' => 0,
            'kontrak_telah_tamat' => 0,
            'jumlah_keseluruhan' => 0,
        ];

        $byAgency = [];
        foreach ($agencyCodes as $code) {
            $byAgency[$code] = $template;
        }

        $totals = $template;

        foreach ($contracts as $contract) {
            $agency = $this->resolveAgencyCode($contract->jabatan?->kod, $contract->no_kontrak, $masterAgencies);
            if (! isset($byAgency[$agency])) {
                $byAgency[$agency] = $template;
            }

            $status = strtoupper((string) $contract->status_kontrak);
            $isExpired = $contract->tamat_tarikh && Carbon::parse($contract->tamat_tarikh)->lt($now);
            $isDueSoon = $contract->tamat_tarikh && Carbon::parse($contract->tamat_tarikh)->betweenIncluded($now, $twoWeeks);

            $byAgency[$agency]['jumlah_keseluruhan']++;
            $totals['jumlah_keseluruhan']++;

            if ($status === 'DRAF') {
                $byAgency[$agency]['draf_kontrak']++;
                $totals['draf_kontrak']++;
            }

            if ($status === 'EOT') {
                $byAgency[$agency]['eot_kontrak']++;
                $totals['eot_kontrak']++;

                if ($isExpired) {
                    $byAgency[$agency]['kontrak_telah_tamat']++;
                    $totals['kontrak_telah_tamat']++;
                } elseif ($isDueSoon) {
                    $byAgency[$agency]['eot_kontrak_akan_tamat']++;
                    $totals['eot_kontrak_akan_tamat']++;
                } else {
                    $byAgency[$agency]['eot_kontrak_dalam_tempoh']++;
                    $totals['eot_kontrak_dalam_tempoh']++;
                }
            }

            if ($status === 'DALAM_PELAKSANAAN' && $isExpired) {
                $byAgency[$agency]['kontrak_telah_tamat']++;
                $totals['kontrak_telah_tamat']++;
            }
        }

        $agencyRows = [];
        foreach ($agencyCodes as $code) {
            $row = $byAgency[$code] ?? $template;
            if ($code === 'LAIN-LAIN' && $row['jumlah_keseluruhan'] === 0) {
                continue;
            }

            $agencyRows[] = ['kod' => $code] + $row;
        }

        $agencyRows[] = ['kod' => 'JUMLAH KESELURUHAN'] + $totals;

        $matrixRows = [
            [
                'label' => 'Draf Kontrak',
                'counts' => collect($agencyCodes)->mapWithKeys(fn ($code) => [$code => $byAgency[$code]['draf_kontrak'] ?? 0])->all(),
                'jumlah' => $totals['draf_kontrak'],
            ],
            [
                'label' => 'Extension Of Time (EOT) Kontrak',
                'counts' => collect($agencyCodes)->mapWithKeys(fn ($code) => [$code => $byAgency[$code]['eot_kontrak'] ?? 0])->all(),
                'jumlah' => $totals['eot_kontrak'],
            ],
            [
                'label' => 'JUMLAH KESELURUHAN',
                'counts' => collect($agencyCodes)->mapWithKeys(fn ($code) => [$code => $byAgency[$code]['jumlah_keseluruhan'] ?? 0])->all(),
                'jumlah' => $totals['jumlah_keseluruhan'],
            ],
        ];

        return response()->json([
            'success' => true,
            'data'    => [
                'summary' => [
                    'draf_kontrak' => $totals['draf_kontrak'],
                    'eot_kontrak' => $totals['eot_kontrak'],
                    'eot_akan_tamat' => $totals['eot_kontrak_akan_tamat'],
                    'kontrak_telah_tamat' => $totals['kontrak_telah_tamat'],
                    'jumlah_keseluruhan' => $totals['jumlah_keseluruhan'],
                ],
                'matrix' => [
                    'agencies' => $agencyCodes,
                    'rows' => $matrixRows,
                ],
                'agency_rows' => $agencyRows,
            ],
            'message' => 'OK',
        ]);
    }

    private function resolveAgencyCode(?string $jabatanKod, ?string $noKontrak, array $knownCodes = []): string
    {
        if (is_string($jabatanKod) && trim($jabatanKod) !== '') {
            return strtoupper(trim($jabatanKod));
        }

        if (is_string($noKontrak) && trim($noKontrak) !== '') {
            $normalized = strtoupper(trim($noKontrak));
            $token = '';

            if (str_contains($normalized, '/')) {
                $parts = explode('/', $normalized);
                $token = trim($parts[0] ?? '');
            } elseif (preg_match('/^[A-Z]+/', $normalized, $matches) === 1) {
                $token = trim($matches[0]);
            }

            if ($token !== '') {
                if (in_array($token, $knownCodes, true)) {
                    return $token;
                }

                $prefixMatches = array_values(array_filter(
                    $knownCodes,
                    fn ($code) => is_string($code) && $code !== '' && str_starts_with($token, $code)
                ));

                if (! empty($prefixMatches)) {
                    usort($prefixMatches, fn ($a, $b) => strlen($b) <=> strlen($a));
                    return $prefixMatches[0];
                }

                return $token;
            }
        }

        return 'LAIN-LAIN';
    }
}
