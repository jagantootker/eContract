<?php

namespace App\Http\Controllers;

use App\Helpers\AuthHelper;
use App\Services\ApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PenggunaController extends Controller
{
    public function __construct(protected ApiService $api) {}

    private function checkAccess(): void
    {
        if (! AuthHelper::hasRole('admin') && ! AuthHelper::hasRole('admin_sistem')) {
            abort(403, 'Akses tidak dibenarkan.');
        }
    }

    public function index(Request $request): View
    {
        $this->checkAccess();
        $params   = ['search' => $request->search ?? '', 'page' => $request->page ?? 1, 'per_page' => $request->per_page ?? 5];
        $response = $this->api->withAuth()->get('/users', $params);
        $jabatan  = $this->api->withAuth()->get('/ref/jabatan');
        $roles    = $this->api->withAuth()->get('/users/roles');

        if ($request->ajax() || $request->boolean('_partial')) {
            return view('components.pengguna._table', ['users' => $response['data'] ?? []]);
        }

        return view('components.pengguna.index', [
            'users' => $response['data'] ?? [],
            'jabatan' => $jabatan['data'] ?? [],
            'roles' => $roles['data'] ?? [],
            'search' => $params['search'],
        ]);
    }

    public function permohonan(Request $request): View
    {
        $this->checkAccess();

        $params = [
            'search' => $request->search ?? '',
            'status' => $request->status ?? '',
            'page' => $request->page ?? 1,
            'per_page' => $request->per_page ?? 10,
        ];

        $response = $this->api->withAuth()->get('/users/permohonan', $params);

        if ($request->ajax() || $request->boolean('_partial')) {
            return view('components.pengguna.permohonan._table', ['rows' => $response['data'] ?? []]);
        }

        return view('components.pengguna.permohonan.index', [
            'rows' => $response['data'] ?? [],
            'search' => $params['search'],
            'status' => $params['status'],
        ]);
    }

    public function permohonanShow(int $id): JsonResponse
    {
        $this->checkAccess();
        $response = $this->api->withAuth()->get("/users/permohonan/{$id}");

        if ($response['success'] ?? false) {
            $data = $response['data'] ?? [];
            $apiBase = (string) config('api.base_url', '');
            $apiOrigin = rtrim((string) preg_replace('#/api(?:/v\d+)?/?$#', '', $apiBase), '/');

            foreach (['borang_permohonan', 'kp_tentera', 'pas_pekerja'] as $key) {
                $url = $data['lampiran'][$key]['url'] ?? null;
                if (is_string($url) && $url !== '' && str_starts_with($url, '/') && $apiOrigin !== '') {
                    $data['lampiran'][$key]['url'] = $apiOrigin . $url;
                }
            }

            return response()->json(['success' => true, 'data' => $data]);
        }

        return response()->json([
            'success' => false,
            'message' => $response['message'] ?? 'Gagal mendapatkan maklumat permohonan.',
        ], $response['status'] ?? 422);
    }

    public function permohonanKeputusan(Request $request, int $id): JsonResponse
    {
        $this->checkAccess();
        $response = $this->api->withAuth()->put("/users/permohonan/{$id}/keputusan", [
            'status' => $request->input('status'),
            'peranan' => $request->input('peranan', []),
            'akses_scope' => $request->input('akses_scope'),
        ]);

        if ($response['success'] ?? false) {
            return response()->json([
                'success' => true,
                'message' => $response['message'] ?? 'Keputusan permohonan berjaya dikemas kini.',
                'data' => $response['data'] ?? [],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $response['message'] ?? 'Gagal mengemas kini keputusan permohonan.',
            'errors' => $response['errors'] ?? [],
        ], $response['status'] ?? 422);
    }

    public function store(Request $request): JsonResponse
    {
        $this->checkAccess();
        $response = $this->api->withAuth()->post('/users', $request->all());
        if ($response['success'] ?? false) {
            return response()->json(['success' => true, 'message' => 'Pengguna berjaya ditambah.']);
        }
        return response()->json(['success' => false, 'message' => $response['message'] ?? 'Gagal menambah pengguna.', 'errors' => $response['errors'] ?? []], 422);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $this->checkAccess();
        $response = $this->api->withAuth()->put("/users/{$id}", $request->all());
        if ($response['success'] ?? false) {
            return response()->json(['success' => true, 'message' => 'Pengguna berjaya dikemas kini.']);
        }
        return response()->json(['success' => false, 'message' => $response['message'] ?? 'Gagal mengemas kini pengguna.', 'errors' => $response['errors'] ?? []], 422);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->checkAccess();
        $response = $this->api->withAuth()->delete("/users/{$id}");
        if ($response['success'] ?? false) {
            return response()->json(['success' => true, 'message' => 'Pengguna berjaya dipadam.']);
        }
        return response()->json(['success' => false, 'message' => $response['message'] ?? 'Gagal memadam pengguna.'], 422);
    }

    public function toggleBlock(int $id): JsonResponse
    {
        $this->checkAccess();
        $response = $this->api->withAuth()->put("/users/{$id}/toggle-block", []);
        if ($response['success'] ?? false) {
            return response()->json(['success' => true, 'message' => $response['message'] ?? 'Status dikemas kini.', 'is_active' => $response['data']['is_active'] ?? null]);
        }
        return response()->json(['success' => false, 'message' => $response['message'] ?? 'Gagal.'], 422);
    }

    public function bahagianUnit(Request $request): JsonResponse
    {
        $response = $this->api->withAuth()->get('/ref/bahagian-unit', ['jabatan_id' => $request->jabatan_id]);
        return response()->json($response);
    }
}
