<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    // GET /api/v1/audit-log
    public function index(Request $request): JsonResponse
    {
        $query = AuditLog::with('user:id,name,ic_number')
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('action', 'like', "%{$search}%")
                      ->orWhere('model_type', 'like', "%{$search}%")
                      ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->action, fn ($q, $v) => $q->where('action', $v))
            ->when($request->model_type, fn ($q, $v) => $q->where('model_type', $v))
            ->when($request->date_from, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($request->date_to, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest();

        $perPage = min((int) $request->get('per_page', 15), 100);
        $logs    = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $logs,
            'message' => 'OK',
        ]);
    }

    // GET /api/v1/audit-log/actions  — distinct action names for filter dropdown
    public function actions(): JsonResponse
    {
        $actions = AuditLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return response()->json([
            'success' => true,
            'data'    => $actions,
        ]);
    }
}
