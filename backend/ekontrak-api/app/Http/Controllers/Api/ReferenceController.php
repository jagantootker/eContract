<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BahagianUnit;
use App\Models\Jabatan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReferenceController extends Controller
{
    // ── GET /api/v1/ref/jabatan ──────────────────────────────────────────────
    public function jabatan(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => Jabatan::query()
                ->where('is_visible_in_registration', true)
                ->orderBy('kod')
                ->get(['id', 'kod', 'nama']),
            'message' => 'OK',
        ]);
    }

    // ── GET /api/v1/ref/bahagian-unit?jabatan_id={id} ────────────────────────
    public function bahagianUnit(Request $request): JsonResponse
    {
        $query = BahagianUnit::query()
            ->when($request->jabatan_id, fn ($q, $jid) =>
                $q->where('jabatan_id', $jid)
            )
            ->orderBy('nama');

        return response()->json([
            'success' => true,
            'data'    => $query->get(['id', 'jabatan_id', 'kod', 'nama']),
            'message' => 'OK',
        ]);
    }

    // ── GET /api/v1/ref/negeri (public) ──────────────────────────────────────
    public function negeri(): JsonResponse
    {
        $negeri = [
            'JOHOR', 'KEDAH', 'KELANTAN', 'MELAKA',
            'NEGERI SEMBILAN', 'PAHANG', 'PERAK', 'PERLIS',
            'PULAU PINANG', 'SABAH', 'SARAWAK', 'SELANGOR',
            'TERENGGANU', 'WILAYAH PERSEKUTUAN KUALA LUMPUR',
            'WILAYAH PERSEKUTUAN LABUAN', 'WILAYAH PERSEKUTUAN PUTRAJAYA',
        ];

        return response()->json([
            'success' => true,
            'data'    => $negeri,
            'message' => 'OK',
        ]);
    }

    // ── GET /api/v1/ref/pegawai (authenticated) ────────────────────────────
    public function pegawai(): JsonResponse
    {
        $pegawai = User::query()
            ->where('is_active', true)
            ->where('permohonan_status', 'diluluskan')
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return response()->json([
            'success' => true,
            'data'    => $pegawai,
            'message' => 'OK',
        ]);
    }
}
