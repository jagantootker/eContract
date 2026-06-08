<?php

namespace App\Http\Controllers;

use App\Helpers\AuthHelper;
use App\Services\ApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditTrailController extends Controller
{
    public function __construct(protected ApiService $api) {}

    private const ACTION_LOGIN_FILTER = '__LOGIN__';
    private const ACTION_LOGOUT_FILTER = '__LOGOUT__';

    private function checkAccess(): void
    {
        if (! AuthHelper::isAdmin()) {
            abort(403, 'Akses tidak dibenarkan. Hanya Admin dibenarkan.');
        }
    }

    private function resolveApiParams(Request $request): array
    {
        $action = (string) ($request->action ?? '');
        $modelType = (string) ($request->model_type ?? '');

        if ($action === self::ACTION_LOGIN_FILTER) {
            $action = '';
            $modelType = 'login';
        } elseif ($action === self::ACTION_LOGOUT_FILTER) {
            $action = '';
            $modelType = 'logout';
        }

        return [
            'search' => $request->search ?? '',
            'action' => $action,
            'model_type' => $modelType,
            'date_from' => $request->date_from ?? '',
            'date_to' => $request->date_to ?? '',
            'page' => $request->page ?? 1,
            'per_page' => $request->per_page ?? 15,
        ];
    }

    public function index(Request $request): View
    {
        $this->checkAccess();

        $filters = [
            'search'     => $request->search ?? '',
            'action'     => $request->action ?? '',
            'model_type' => $request->model_type ?? '',
            'date_from'  => $request->date_from ?? '',
            'date_to'    => $request->date_to ?? '',
            'page'       => $request->page ?? 1,
            'per_page'   => $request->per_page ?? 15,
        ];

        $apiParams = $this->resolveApiParams($request);

        $result  = $this->api->withAuth()->get('/audit-log', array_filter($apiParams, fn ($v) => $v !== ''));
        $actions = $this->api->withAuth()->get('/audit-log/actions');

        return view('audit-trail.index', [
            'logs'       => $result['data'] ?? [],
            'actions'    => $actions['data'] ?? [],
            'filters'    => $filters,
        ]);
    }

    public function fetchAjax(Request $request): JsonResponse
    {
        $this->checkAccess();

        $params = array_filter($this->resolveApiParams($request), fn ($v) => $v !== null && $v !== '');

        $result = $this->api->withAuth()->get('/audit-log', $params);

        return response()->json($result);
    }
}
