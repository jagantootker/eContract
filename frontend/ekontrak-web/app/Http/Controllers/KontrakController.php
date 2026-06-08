<?php

namespace App\Http\Controllers;

use App\Helpers\AuthHelper;
use App\Services\ApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class KontrakController extends Controller
{
    public function __construct(protected ApiService $api) {}

    // ── GET /kontrak ─────────────────────────────────────────────────────────
    public function index(Request $request): View
    {
        $params   = $request->only(['search', 'tahun_mula', 'tahun_tamat', 'page', 'per_page']);
        $result   = $this->api->withAuth()->get('/kontrak', $params);
        $jabatan  = $this->api->withAuth()->get('/ref/jabatan');
        $syarikat = $this->api->withAuth()->get('/syarikat', ['per_page' => 500]);

        $syarikatPayload = $syarikat['data'] ?? [];
        $syarikatList = is_array($syarikatPayload) && isset($syarikatPayload['data'])
            ? ($syarikatPayload['data'] ?? [])
            : $syarikatPayload;

        $pegawaiRef = $this->api->withAuth()->get('/ref/pegawai');
        $pegawaiPayload = $pegawaiRef['data'] ?? [];
        $pegawaiList = is_array($pegawaiPayload) && isset($pegawaiPayload['data'])
            ? ($pegawaiPayload['data'] ?? [])
            : $pegawaiPayload;
        $pegawaiLoadError = !($pegawaiRef['success'] ?? false);
        $pegawaiLoadMessage = $pegawaiRef['message'] ?? 'Gagal memuatkan senarai pegawai';

        if ($pegawaiLoadError) {
            Log::warning('KontrakController: gagal memuatkan rujukan pegawai', [
                'status' => $pegawaiRef['status'] ?? null,
                'message' => $pegawaiLoadMessage,
                'response' => $pegawaiRef,
            ]);
        }

        return view('components.kontrak.index', [
            'contracts' => $result['data'] ?? [],
            'jabatan'   => $jabatan['data'] ?? [],
            'syarikatList' => is_array($syarikatList) ? $syarikatList : [],
            'pegawaiList'  => is_array($pegawaiList) ? $pegawaiList : [],
            'pegawaiLoadError' => $pegawaiLoadError,
            'pegawaiLoadMessage' => $pegawaiLoadMessage,
            'filters'   => $params,
        ]);
    }

    // ── GET /kontrak/fetch (AJAX table reload) ───────────────────────────────
    public function fetchAjax(Request $request)
    {
        $result = $this->api->withAuth()->get('/kontrak', $request->all());

        if ($request->ajax() || $request->boolean('_partial')) {
            $payload = $result['data'] ?? [];
            return view('components.kontrak._table', [
                'data' => $payload['data'] ?? [],
                'meta' => $payload['meta'] ?? $payload,
                'canEdit' => AuthHelper::hasRole('pendaftar_kontrak') || AuthHelper::isAdmin(),
            ]);
        }

        return response()->json($result);
    }

    // ── GET /kontrak/{id} (AJAX for modal) ───────────────────────────────────
    public function show(int $id): JsonResponse
    {
        $result = $this->api->withAuth()->get("/kontrak/{$id}");
        return response()->json($result);
    }

    // ── POST /kontrak ────────────────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $result = $this->api->withAuth()->post('/kontrak', $request->all());
        return response()->json($result);
    }

    // ── PUT /kontrak/{id} ────────────────────────────────────────────────────
    public function update(Request $request, int $id): JsonResponse
    {
        $result = $this->api->withAuth()->put("/kontrak/{$id}", $request->all());
        return response()->json($result);
    }

    // ── GET /kontrak/{id}/catatan ────────────────────────────────────────────
    public function getCatatan(int $id): JsonResponse
    {
        $result = $this->api->withAuth()->get("/kontrak/{$id}/catatan");
        return response()->json($result);
    }

    // ── POST /kontrak/{id}/catatan ───────────────────────────────────────────
    public function storeCatatan(Request $request, int $id): JsonResponse
    {
        $result = $this->api->withAuth()->post("/kontrak/{$id}/catatan", $request->all());
        return response()->json($result);
    }

    // ── GET /kontrak/syarikat-search (AJAX typeahead) ────────────────────────
    public function searchSyarikat(Request $request): JsonResponse
    {
        $result = $this->api->withAuth()->get('/syarikat', [
            'search'   => $request->search ?? '',
            'per_page' => 10,
        ]);
        return response()->json($result);
    }

    // ── GET /kontrak/user-search (AJAX typeahead) ────────────────────────────
    public function searchUser(Request $request): JsonResponse
    {
        $result = $this->api->withAuth()->get('/users', [
            'search'   => $request->search ?? '',
            'per_page' => 10,
        ]);
        return response()->json($result);
    }
}
