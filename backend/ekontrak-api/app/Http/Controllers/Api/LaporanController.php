<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kontrak;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    // ── GET /api/v1/laporan/lampiran-a ───────────────────────────────────────
    // Pemantauan Status Kontrak Ditandatangani
    public function lampiranA(Request $request): JsonResponse
    {
        $query = Kontrak::with([
            'jabatan',
            'bahagianUnit',
            'syarikat',
            'pegawaiBertanggungjawab',
        ])
        ->when($request->tahun_mula, fn ($q, $y) =>
            $q->whereYear('ditandatangani_tarikh', '>=', $y)
        )
        ->when($request->tahun_tamat, fn ($q, $y) =>
            $q->whereYear('ditandatangani_tarikh', '<=', $y)
        )
        ->when($request->search, fn ($q, $s) =>
            $q->where('no_kontrak', 'like', "%{$s}%")
              ->orWhere('tajuk_kontrak', 'like', "%{$s}%")
              ->orWhereHas('syarikat', fn ($sq) =>
                  $sq->where('nama_syarikat', 'like', "%{$s}%")
              )
        )
        ->orderBy('jabatan_id')
        ->orderBy('ditandatangani_tarikh');

        return response()->json([
            'success' => true,
            'data'    => $query->get()->map(fn ($k) => [
                'id'                      => $k->id,
                'no_kontrak'              => $k->no_kontrak,
                'jabatan'                 => $k->jabatan?->nama,
                'bahagian_unit'           => $k->bahagianUnit?->nama,
                'tajuk_kontrak'           => $k->tajuk_kontrak,
                'kaedah_perolehan'        => $k->kaedah_perolehan,
                'tarikh_sst'              => $k->tarikh_sst?->format('d/m/Y'),
                'tarikh_sst_disetujui_terima' => $k->tarikh_draf_hantar_sistem?->format('d-m-Y'),
                'tarikh_akhir_kontrak_perlu_dimatikan_setem' => $this->datePlusMonths($k->tarikh_draf_hantar_sistem, 3),
                'nama_syarikat'           => $k->syarikat?->nama_syarikat,
                'status_draf_kompan'      => $k->status_draf_kompan,
                'tarikh_draf_hantar_sistem' => $k->tarikh_draf_hantar_sistem?->format('d-m-Y'),
                'ditandatangani_tarikh'   => $k->ditandatangani_tarikh?->format('d-m-Y'),
                'telah_tandatangan_tarikh_duti_setem' => $k->ditandatangani_tarikh?->format('d-m-Y'),
                'belum_tandatangan_status_tarikh_pergerakan' => $k->ditandatangani_tarikh
                    ? '-'
                    : $this->unsignStatusLabel($k->status_kontrak, $k->tarikh_draf_hantar_sistem),
                'sebab_lewat_tandatangan' => $this->lateSigningReason(
                    $k->tarikh_draf_hantar_sistem,
                    $k->ditandatangani_tarikh,
                    $k->catatan_kontrak
                ),
                'catatan_kontrak'         => $k->catatan_kontrak,
                'status_kontrak'          => $k->status_kontrak,
            ]),
            'message' => 'OK',
        ]);
    }

    private function datePlusMonths($date, int $months): ?string
    {
        if (! $date) {
            return null;
        }

        try {
            return $date->copy()->addMonths($months)->format('d-m-Y');
        } catch (\Throwable) {
            return null;
        }
    }

    private function unsignStatusLabel(?string $status, $movementDate): string
    {
        $statusText = trim((string) ($status ?? '-'));
        $dateText = $movementDate ? $movementDate->format('d-m-Y') : '-';

        return $statusText . ' (' . $dateText . ')';
    }

    private function lateSigningReason($approvedDate, $signedDate, ?string $note): string
    {
        if (! $approvedDate) {
            return $note ?: '-';
        }

        $limit = $approvedDate->copy()->addMonths(3);

        if ($signedDate) {
            if ($signedDate->greaterThan($limit)) {
                return $note ?: 'Kontrak ditandatangani melebihi 3 bulan.';
            }

            return '-';
        }

        if (now()->greaterThan($limit)) {
            return $note ?: 'Masih belum ditandatangani selepas 3 bulan.';
        }

        return '-';
    }

    // ── GET /api/v1/laporan/lampiran-b ───────────────────────────────────────
    // Pemantauan Tempoh Kontrak
    public function lampiranB(Request $request): JsonResponse
    {
        $query = Kontrak::with([
            'jabatan',
            'bahagianUnit',
            'syarikat',
        ])
        ->when($request->tahun_mula, fn ($q, $y) =>
            $q->whereYear('tarikh_draf_hantar_sistem', '>=', $y)
        )
        ->when($request->tahun_tamat, fn ($q, $y) =>
            $q->whereYear('tarikh_draf_hantar_sistem', '<=', $y)
        )
        ->when($request->search, fn ($q, $s) =>
            $q->where('no_kontrak', 'like', "%{$s}%")
              ->orWhere('tajuk_kontrak', 'like', "%{$s}%")
              ->orWhereHas('syarikat', fn ($sq) =>
                  $sq->where('nama_syarikat', 'like', "%{$s}%")
              )
        )
        ->orderBy('jabatan_id')
        ->orderBy('mula_tarikh');

        return response()->json([
            'success' => true,
            'data'    => $query->get()->map(fn ($k) => [
                'id'                         => $k->id,
                'jabatan'                    => $k->jabatan?->nama,
                'bahagian_unit'              => $k->bahagianUnit?->nama,
                'tajuk_kontrak'              => $k->tajuk_kontrak,
                'kaedah_perolehan'           => $k->kaedah_perolehan,
                'tarikh_sst_disetujui_terima' => $k->tarikh_draf_hantar_sistem?->format('d-m-Y'),
                'mula_tarikh'                => $k->mula_tarikh?->format('d-m-Y'),
                'tamat_tarikh'               => $k->tamat_tarikh?->format('d-m-Y'),
                'tempoh_bulan'               => $this->calcTempohBulan($k->mula_tarikh, $k->tamat_tarikh),
                'nama_syarikat'              => $k->syarikat?->nama_syarikat,
                'catatan_kontrak'            => $k->catatan_kontrak,
            ]),
            'message' => 'OK',
        ]);
    }

    private function calcTempohBulan($start, $end): int
    {
        if (! $start || ! $end) {
            return 0;
        }

        try {
            $interval = $start->diff($end);
            return ($interval->y * 12) + $interval->m + ($interval->d > 0 ? 1 : 0);
        } catch (\Throwable) {
            return 0;
        }
    }
}
