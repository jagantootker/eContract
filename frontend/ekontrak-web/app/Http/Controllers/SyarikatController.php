<?php

namespace App\Http\Controllers;

use App\Helpers\AuthHelper;
use App\Services\ApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SyarikatController extends Controller
{
    public function __construct(protected ApiService $apiService) {}

    private function checkAccess(): void
    {
        if (! AuthHelper::hasRole('pendaftar_kontrak') && ! AuthHelper::isAdmin()) {
            abort(403, 'Akses tidak dibenarkan.');
        }
    }

    public function index(Request $request): View
    {
        $this->checkAccess();

        $params = [
            'search'   => $request->search ?? '',
            'page'     => $request->page ?? 1,
            'per_page' => $request->per_page ?? 10,
        ];

        $result = $this->apiService->withAuth()->get('/syarikat', $params);

        return view('syarikat.index', [
            'companies' => $result['data'] ?? [],
            'search'    => $params['search'],
        ]);
    }

    public function fetchAjax(Request $request): JsonResponse
    {
        $this->checkAccess();
        $result = $this->apiService->withAuth()->get('/syarikat', $request->all());
        return response()->json($result);
    }

    public function table(Request $request): View
    {
        $this->checkAccess();

        $params = [
            'search' => $request->search ?? '',
            'page' => $request->page ?? 1,
            'per_page' => $request->per_page ?? 10,
        ];

        $result = $this->apiService->withAuth()->get('/syarikat', $params);

        return view('syarikat._table', [
            'companies' => $result['data'] ?? [],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $this->checkAccess();
        $result = $this->apiService->withAuth()->get("/syarikat/{$id}");
        return response()->json($result);
    }

    public function store(Request $request): JsonResponse
    {
        $this->checkAccess();
        $result = $this->apiService->withAuth()->post('/syarikat', $request->all());
        return response()->json($result, $result['status'] ?? 200);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $this->checkAccess();
        $result = $this->apiService->withAuth()->put("/syarikat/{$id}", $request->all());
        return response()->json($result, $result['status'] ?? 200);
    }
}
