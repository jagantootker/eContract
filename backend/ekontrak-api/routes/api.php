<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\KontrakController;
use App\Http\Controllers\Api\SyarikatController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\ReferenceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — eKontrak API v1
| All routes prefixed with /api/v1 (set in bootstrap/app.php or RouteServiceProvider)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ── Public ───────────────────────────────────────────────────────────────
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('auth/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('auth/register-check', [AuthController::class, 'registerCheck'])->middleware('throttle:10,1');
    Route::post('auth/password-reset/request', [AuthController::class, 'requestPasswordResetToken']);
    Route::post('auth/password-reset/verify', [AuthController::class, 'verifyPasswordResetToken']);
    Route::get('ref/jabatan',  [ReferenceController::class, 'jabatan']);
    Route::get('ref/bahagian-unit', [ReferenceController::class, 'bahagianUnit']);
    Route::get('ref/negeri',  [ReferenceController::class, 'negeri']);

    // ── Authenticated ────────────────────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('auth/logout',          [AuthController::class, 'logout']);
        Route::post('auth/change-password', [AuthController::class, 'changePassword']);
        Route::get('ref/pegawai',           [ReferenceController::class, 'pegawai']);

        // Dashboard
        Route::get('dashboard',                          [DashboardController::class, 'index']);
        Route::get('dashboard/maklumat-tidak-lengkap',   [DashboardController::class, 'maklumatTidakLengkap']);
        Route::get('dashboard/kontrak-selesai',          [DashboardController::class, 'kontrakSelesai']);
        Route::get('dashboard/alerts',                   [DashboardController::class, 'alerts']);

        // Users — admin, admin_sistem only
        Route::middleware('role:admin,admin_sistem')->group(function () {
            Route::get('users/roles',              [UserController::class, 'rolesIndex']);
            Route::get('users/permohonan',         [UserController::class, 'permohonanIndex']);
            Route::get('users/permohonan/{id}',    [UserController::class, 'permohonanShow']);
            Route::put('users/permohonan/{id}/keputusan', [UserController::class, 'permohonanKeputusan']);
            Route::get('users',                    [UserController::class, 'index']);
            Route::post('users',                   [UserController::class, 'store']);
            Route::put('users/{id}',               [UserController::class, 'update']);
            Route::put('users/{id}/toggle-block',  [UserController::class, 'toggleBlock']);
        });
        // Delete — admin only
        Route::middleware('role:admin')->delete('users/{id}', [UserController::class, 'destroy']);

        // Audit Log — admin only
        Route::middleware('role:admin')->group(function () {
            Route::get('audit-log',         [AuditLogController::class, 'index']);
            Route::get('audit-log/actions', [AuditLogController::class, 'actions']);
        });

        // Contracts
        Route::get('kontrak',              [KontrakController::class, 'index']);
        Route::get('kontrak/{id}',         [KontrakController::class, 'show']);
        Route::get('kontrak/{id}/catatan', [KontrakController::class, 'catatanIndex']);

        Route::middleware('role:pendaftar_kontrak')->group(function () {
            Route::post('kontrak',              [KontrakController::class, 'store']);
            Route::put('kontrak/{id}',          [KontrakController::class, 'update']);
            Route::post('kontrak/{id}/catatan', [KontrakController::class, 'catatanStore']);
        });

        // Companies
        Route::get('syarikat',       [SyarikatController::class, 'index']);
        Route::get('syarikat/{id}',  [SyarikatController::class, 'show']);

        Route::middleware('role:pendaftar_kontrak,admin')->group(function () {
            Route::post('syarikat',      [SyarikatController::class, 'store']);
            Route::put('syarikat/{id}',  [SyarikatController::class, 'update']);
        });

        // Reports
        Route::get('laporan/lampiran-a', [LaporanController::class, 'lampiranA']);
        Route::get('laporan/lampiran-b', [LaporanController::class, 'lampiranB']);
    });
});
