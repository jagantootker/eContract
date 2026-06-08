<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kontrak\StoreKontrakRequest;
use App\Http\Requests\Kontrak\UpdateKontrakRequest;
use App\Http\Requests\Kontrak\StoreCatatanRequest;
use App\Models\CatatanKontrak;
use App\Models\Kontrak;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KontrakController extends Controller
{
    // ── GET /api/v1/kontrak ──────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $query = Kontrak::with(['syarikat', 'jabatan', 'bahagianUnit', 'pegawaiBertanggungjawab'])
            ->when($request->search, function ($q, $s) {
                $q->where(function ($q) use ($s) {
                    $q->where('no_kontrak', 'like', "%{$s}%")
                      ->orWhere('tajuk_kontrak', 'like', "%{$s}%")
                      ->orWhere('status_kontrak', 'like', "%{$s}%")
                      ->orWhereHas('syarikat', fn ($sq) =>
                          $sq->where('nama_syarikat', 'like', "%{$s}%")
                      );
                });
            })
            ->when($request->tahun_mula, fn ($q, $y) =>
                $q->whereYear('mula_tarikh', $y)
            )
            ->when($request->tahun_tamat, fn ($q, $y) =>
                $q->whereYear('tamat_tarikh', $y)
            )
            ->when($request->status, fn ($q, $s) =>
                $q->where('status_kontrak', $s)
            );

        $perPage = (int) $request->get('per_page', 10);

        return response()->json([
            'success' => true,
            'data'    => $query->latest()->paginate($perPage),
            'message' => 'OK',
        ]);
    }

    // ── POST /api/v1/kontrak ─────────────────────────────────────────────────
    public function store(StoreKontrakRequest $request): JsonResponse
    {
        $data               = $request->validated();
        $data['created_by'] = $request->user()->id;

        $kontrak = Kontrak::create($data);
        $kontrak->load(['syarikat', 'jabatan', 'bahagianUnit', 'pegawaiBertanggungjawab']);

        return response()->json([
            'success' => true,
            'data'    => $kontrak,
            'message' => 'Kontrak berjaya didaftarkan.',
        ], 201);
    }

    // ── GET /api/v1/kontrak/{id} ─────────────────────────────────────────────
    public function show(int $id): JsonResponse
    {
        $kontrak = Kontrak::with([
            'syarikat',
            'jabatan',
            'bahagianUnit',
            'pegawaiBertanggungjawab',
            'pegawaiPerhubungan1',
            'pegawaiPerhubungan2',
            'catatan.user',
            'eot',
            'createdBy',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $kontrak,
            'message' => 'OK',
        ]);
    }

    // ── PUT /api/v1/kontrak/{id} ─────────────────────────────────────────────
    public function update(UpdateKontrakRequest $request, int $id): JsonResponse
    {
        $kontrak = Kontrak::findOrFail($id);

        if ($kontrak->status_kontrak === 'KONTRAK_SELESAI') {
            return response()->json([
                'success' => false,
                'message' => 'Kontrak yang telah selesai tidak boleh dikemas kini.',
            ], 422);
        }

        $kontrak->update($request->validated());
        $kontrak->load(['syarikat', 'jabatan', 'bahagianUnit', 'pegawaiBertanggungjawab']);

        return response()->json([
            'success' => true,
            'data'    => $kontrak,
            'message' => 'Kontrak berjaya dikemas kini.',
        ]);
    }

    // ── GET /api/v1/kontrak/{id}/catatan ─────────────────────────────────────
    public function catatanIndex(int $id): JsonResponse
    {
        $kontrak = Kontrak::findOrFail($id);

        $catatan = CatatanKontrak::with('user')
            ->where('kontrak_id', $kontrak->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $catatan,
            'message' => 'OK',
        ]);
    }

    // ── POST /api/v1/kontrak/{id}/catatan ────────────────────────────────────
    public function catatanStore(StoreCatatanRequest $request, int $id): JsonResponse
    {
        $kontrak = Kontrak::findOrFail($id);

        $catatan = CatatanKontrak::create([
            'kontrak_id' => $kontrak->id,
            'user_id'    => $request->user()->id,
            'status'     => $request->status,
            'tahap'      => $request->tahap,
            'catatan'    => $request->catatan,
        ]);

        $catatan->load('user');

        return response()->json([
            'success' => true,
            'data'    => $catatan,
            'message' => 'Catatan berjaya disimpan.',
        ], 201);
    }
}
