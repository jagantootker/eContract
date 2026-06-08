<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Syarikat\StoreSyarikatRequest;
use App\Http\Requests\Syarikat\UpdateSyarikatRequest;
use App\Models\Syarikat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SyarikatController extends Controller
{
    // ── GET /api/v1/syarikat ─────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $query = Syarikat::with('createdBy')
            ->when($request->search, fn ($q, $s) =>
                $q->where('nama_syarikat', 'like', "%{$s}%")
                  ->orWhere('negeri', 'like', "%{$s}%")
            );

        $perPage = (int) $request->get('per_page', 10);

        return response()->json([
            'success' => true,
            'data'    => $query->latest()->paginate($perPage),
            'message' => 'OK',
        ]);
    }

    // ── POST /api/v1/syarikat ────────────────────────────────────────────────
    public function store(StoreSyarikatRequest $request): JsonResponse
    {
        $data               = $request->validated();
        $data['created_by'] = $request->user()->id;

        $syarikat = Syarikat::create($data);

        return response()->json([
            'success' => true,
            'data'    => $syarikat,
            'message' => 'Syarikat berjaya didaftarkan.',
        ], 201);
    }

    // ── GET /api/v1/syarikat/{id} ────────────────────────────────────────────
    public function show(int $id): JsonResponse
    {
        $syarikat = Syarikat::with(['createdBy', 'kontrak'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $syarikat,
            'message' => 'OK',
        ]);
    }

    // ── PUT /api/v1/syarikat/{id} ────────────────────────────────────────────
    public function update(UpdateSyarikatRequest $request, int $id): JsonResponse
    {
        $syarikat = Syarikat::findOrFail($id);
        $syarikat->update($request->validated());

        return response()->json([
            'success' => true,
            'data'    => $syarikat,
            'message' => 'Maklumat syarikat berjaya dikemas kini.',
        ]);
    }
}
