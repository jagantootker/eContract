<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditLogMiddleware
{
    /**
     * Automatically logs every POST, PUT, PATCH, DELETE request to audit_log.
     * Runs AFTER the response is generated so we can capture the outcome.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            // Only log if request was successful (2xx)
            if ($response->getStatusCode() < 400) {
                $this->writeLog($request, $response);
            }
        }

        return $response;
    }

    private function writeLog(Request $request, Response $response): void
    {
        try {
            // Derive action from method + route
            $method = $request->method();
            $path   = $request->path();

            $action = match ($method) {
                'POST'             => 'CREATE',
                'PUT', 'PATCH'     => 'UPDATE',
                'DELETE'           => 'DELETE',
                default            => $method,
            };

            // Sanitize payload — remove sensitive fields
            $payload = $request->except(['password', 'current_password', 'new_password', 'new_password_confirmation', 'mfa_secret']);

            // Try to extract model_type and model_id from route URI
            // e.g. /api/v1/kontrak/5 → model_type=kontrak, model_id=5
            $segments  = explode('/', $path);
            $modelType = null;
            $modelId   = null;

            foreach ($segments as $i => $segment) {
                if (is_numeric($segment) && isset($segments[$i - 1])) {
                    $modelType = $segments[$i - 1];
                    $modelId   = (int) $segment;
                    break;
                }
            }

            if (! $modelType) {
                // Use last non-numeric segment as model type
                $modelType = collect($segments)->last(fn ($s) => ! is_numeric($s));
            }

            AuditLog::create([
                'user_id'    => $request->user()?->id,
                'action'     => $action,
                'model_type' => $modelType ?? $path,
                'model_id'   => $modelId,
                'payload'    => $payload,
                'ip_address' => $request->ip(),
            ]);
        } catch (\Throwable $e) {
            // Never let audit logging break the main request
            logger()->error('AuditLogMiddleware error: ' . $e->getMessage());
        }
    }
}
