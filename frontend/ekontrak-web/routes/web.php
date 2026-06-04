<?php

use App\Http\Controllers\AbmPptController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuditTrailController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KontrakController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\SyarikatController;
use Illuminate\Support\Facades\Route;

// ── Guest Only ────────────────────────────────────────────────────────────────
Route::middleware('guest.session')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/tukar-kata-laluan', [AuthController::class, 'showForgotPasswordRequest'])->name('password.reset.request');
    Route::post('/tukar-kata-laluan/hantar-token', [AuthController::class, 'requestPasswordResetToken'])->name('password.reset.send');
    Route::get('/tukar-kata-laluan/pengesahan', [AuthController::class, 'showForgotPasswordVerification'])->name('password.reset.verify.form');
    Route::post('/tukar-kata-laluan/pengesahan', [AuthController::class, 'verifyPasswordResetToken'])->name('password.reset.verify');
    Route::get('/daftar', [AuthController::class, 'showRegister'])->name('register');
    Route::get('/daftar/permohonan', [AuthController::class, 'showRegisterForm'])->name('register.form');
    Route::get('/daftar/bahagian-unit', [AuthController::class, 'registerBahagianUnit'])->name('register.bahagian-unit');
    Route::post('/daftar', [AuthController::class, 'register'])->name('register.submit');
    Route::post('/daftar/semakan', [AuthController::class, 'checkRegistrationStatus'])->name('register.check');
});

// ── Authenticated ─────────────────────────────────────────────────────────────
Route::middleware('auth.session')->group(function () {

    Route::get('/', fn() => redirect()->route('dashboard'));
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/tukar-kata-laluan/baharu',  [AuthController::class, 'showChangePassword'])->name('change-password');
    Route::post('/tukar-kata-laluan/baharu', [AuthController::class, 'changePassword'])->name('change-password.update');

    // ── Dashboard ─────────────────────────────────────────────────────────────
    Route::get('/dashboard',                        [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/maklumat-tidak-lengkap', [DashboardController::class, 'getMaklumatTidakLengkap']);
    Route::get('/dashboard/kontrak-selesai',        [DashboardController::class, 'getKontrakSelesai']);
    Route::get('/dashboard/status/{type}',          [DashboardController::class, 'getStatusList']);

    // ── Kontrak ───────────────────────────────────────────────────────────────
    Route::prefix('kontrak')->group(function () {
        Route::get('/',                [KontrakController::class, 'index'])->name('kontrak.index');
        Route::get('/fetch',           [KontrakController::class, 'fetchAjax']);
        Route::get('/syarikat-search', [KontrakController::class, 'searchSyarikat']);
        Route::get('/user-search',     [KontrakController::class, 'searchUser']);
        Route::post('/',               [KontrakController::class, 'store'])->name('kontrak.store');
        Route::get('/{id}',            [KontrakController::class, 'show'])->name('kontrak.show');
        Route::put('/{id}',            [KontrakController::class, 'update'])->name('kontrak.update');
        Route::get('/{id}/catatan',    [KontrakController::class, 'getCatatan']);
        Route::post('/{id}/catatan',   [KontrakController::class, 'storeCatatan']);
    });

    // ── Syarikat ──────────────────────────────────────────────────────────────
    Route::prefix('syarikat')->group(function () {
        Route::get('/',      [SyarikatController::class, 'index'])->name('syarikat.index');
        Route::get('/table', [SyarikatController::class, 'table']);
        Route::get('/fetch', [SyarikatController::class, 'fetchAjax']);
        Route::post('/',     [SyarikatController::class, 'store']);
        Route::get('/{id}',  [SyarikatController::class, 'show']);
        Route::put('/{id}',  [SyarikatController::class, 'update']);
    });

    // ── Laporan ───────────────────────────────────────────────────────────────
    Route::prefix('laporan')->group(function () {
        Route::get('/',                       [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/lampiran-a',            [LaporanController::class, 'lampiranA'])->name('laporan.a');
        Route::get('/lampiran-b',            [LaporanController::class, 'lampiranB'])->name('laporan.b');
        Route::get('/lampiran-a/export-pdf', [LaporanController::class, 'exportPdfA'])->name('laporan.a.pdf');
        Route::get('/lampiran-b/export-pdf', [LaporanController::class, 'exportPdfB'])->name('laporan.b.pdf');
        Route::get('/lampiran-a/export-excel', [LaporanController::class, 'exportExcelA'])->name('laporan.a.excel');
        Route::get('/lampiran-b/export-excel', [LaporanController::class, 'exportExcelB'])->name('laporan.b.excel');
    });

    // ── Urus Pengguna ─────────────────────────────────────────────────────────
    Route::prefix('pengguna')->group(function () {        Route::get('/',                  [PenggunaController::class, 'index'])->name('pengguna.index');
        Route::get('/permohonan',        [PenggunaController::class, 'permohonan'])->name('pengguna.permohonan.index');
        Route::get('/permohonan/{id}',   [PenggunaController::class, 'permohonanShow'])->name('pengguna.permohonan.show');
        Route::put('/permohonan/{id}/keputusan', [PenggunaController::class, 'permohonanKeputusan'])->name('pengguna.permohonan.keputusan');
        Route::post('/',                 [PenggunaController::class, 'store'])->name('pengguna.store');
        Route::put('/{id}',              [PenggunaController::class, 'update'])->name('pengguna.update');
        Route::delete('/{id}',           [PenggunaController::class, 'destroy'])->name('pengguna.destroy');
        Route::put('/{id}/toggle-block', [PenggunaController::class, 'toggleBlock']);
        Route::get('/bahagian-unit',     [PenggunaController::class, 'bahagianUnit']);
    });

    // ── Audit Trail (Admin only) ───────────────────────────────────────────────
    Route::prefix('audit-trail')->group(function () {
        Route::get('/',       [AuditTrailController::class, 'index'])->name('audit-trail.index');
        Route::get('/fetch',  [AuditTrailController::class, 'fetchAjax']);
    });

    // ── Perancangan Perolehan (ABM/PPT) ────────────────────────────────────────
    Route::prefix('perancangan-perolehan')->group(function () {
        // Dashboard
        Route::get('/dashboard-abm',      [AbmPptController::class, 'dashboard'])->name('abm.dashboard');
        
        // Management
        Route::get('/pengurusan-abm',     [AbmPptController::class, 'management'])->name('abm.management');
        Route::get('/pengurusan-ppt',     [AbmPptController::class, 'managementPpt'])->name('abm.management.ppt');
        
        // Upload
        Route::get('/import',             [AbmPptController::class, 'uploadPage'])->name('abm.upload');
        Route::post('/upload',            [AbmPptController::class, 'handleUpload'])->name('abm.upload.handle');
        
        // Preview & Verification
        Route::get('/{upload}/preview',   [AbmPptController::class, 'preview'])->name('abm.preview');
        Route::get('/{upload}/extracted-data', [AbmPptController::class, 'getExtractedData']);
        Route::post('/{upload}/draft',    [AbmPptController::class, 'saveDraft']);
        Route::post('/{upload}/submit',   [AbmPptController::class, 'submitForVerification']);
        Route::post('/{upload}/approve',  [AbmPptController::class, 'approve']);
        Route::post('/{upload}/reject',   [AbmPptController::class, 'reject']);
        
        // Repository
        Route::get('/repository',         [AbmPptController::class, 'repository'])->name('abm.repository');
        Route::get('/fetch-documents',    [AbmPptController::class, 'fetchDocuments']);
        Route::get('/{upload}/view',      [AbmPptController::class, 'viewDocument']);
        Route::get('/{upload}/viewer',    [AbmPptController::class, 'viewer'])->name('abm.viewer');
        Route::get('/{upload}/download',  [AbmPptController::class, 'downloadDocument'])->name('abm.download');
        
        // Workflow
        Route::get('/status-proses',      [AbmPptController::class, 'statusProses'])->name('abm.status-proses');
        Route::get('/{upload}/workflow',  [AbmPptController::class, 'workflowStatus'])->name('abm.workflow');
        Route::get('/{upload}/progress',  [AbmPptController::class, 'getWorkflowProgress']);
    });

});
