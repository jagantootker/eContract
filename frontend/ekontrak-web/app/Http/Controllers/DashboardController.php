<?php

namespace App\Http\Controllers;

use App\Helpers\AuthHelper;
use App\Services\ApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(protected ApiService $api) {}

    // ── GET /dashboard ───────────────────────────────────────────────────────
    public function index(): View
    {
        $roles   = AuthHelper::roles();
        $data    = $this->api->withAuth()->get('/dashboard');
        $alerts  = $this->api->withAuth()->get('/dashboard/alerts');

        if (in_array('pegawai_undang_undang', $roles, true)) {
            return view('components.dashboard.undang_undang', compact('data', 'alerts'));
        }

        return view('components.dashboard.pegawai', compact('data', 'alerts'));
    }

    // ── AJAX: Maklumat Tidak Lengkap ─────────────────────────────────────────
    public function getMaklumatTidakLengkap(Request $request): JsonResponse
    {
        $result = $this->api->withAuth()->get('/dashboard/maklumat-tidak-lengkap', $request->all());
        return response()->json($result);
    }

    // ── AJAX: Kontrak Selesai ────────────────────────────────────────────────
    public function getKontrakSelesai(Request $request): JsonResponse
    {
        $result = $this->api->withAuth()->get('/dashboard/kontrak-selesai', $request->all());
        return response()->json($result);
    }

    // ── AJAX: Generic Status List ───────────────────────────────────────────
    public function getStatusList(Request $request, string $type): JsonResponse
    {
        $statusMap = [
            'draf-kontrak' => 'DRAF',
            'dalam-pelaksanaan' => 'DALAM_PELAKSANAAN',
            'eot' => 'EOT',
            'kontrak-selesai' => 'KONTRAK_SELESAI',
        ];

        if (! isset($statusMap[$type])) {
            return response()->json([
                'success' => false,
                'message' => 'Jenis status tidak sah.',
            ], 422);
        }

        $params = [
            'page' => $request->get('page', 1),
            'per_page' => $request->get('per_page', 10),
            'search' => $request->get('search', ''),
            'status' => $statusMap[$type],
        ];

        if ($request->filled('tahun')) {
            $tahun = (string) $request->get('tahun');
            $params['tahun_mula'] = $tahun;
            $params['tahun_tamat'] = $tahun;
        }

        $result = $this->api->withAuth()->get('/kontrak', $params);
        return response()->json($result);
    }
}
