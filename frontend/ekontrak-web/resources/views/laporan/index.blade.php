@extends('components.layouts.app')

@section('title', 'Laporan')
@section('breadcrumb', 'Laporan')

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <div class="page-title">Laporan</div>
            <div class="page-subtitle">Senarai laporan pemantauan kontrak yang tersedia.</div>
        </div>
    </div>

    <div class="card-body">
        <div style="display: flex; flex-wrap: wrap; gap: 1.5rem; margin-top: 1.25rem;">
            <a href="{{ route('laporan.a') }}" class="report-card" style="flex:1 1 320px; min-width:300px; max-width:420px;">
                <div class="report-card-icon blue">
                    <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="4" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 8h8M8 12h8M8 16h4"/></svg>
                </div>
                <div class="report-card-body">
                    <div class="title">Pemantauan Status Kontrak Ditandatangani</div>
                    <div class="desc">Lampiran A &mdash; Senarai kontrak yang telah ditandatangani dan status pelaksanaan.</div>
                </div>
            </a>
            <a href="{{ route('laporan.b') }}" class="report-card" style="flex:1 1 320px; min-width:300px; max-width:420px;">
                <div class="report-card-icon emerald">
                    <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18h18"/><rect x="7" y="7" width="7" height="7" rx="2" stroke-width="2"/></svg>
                </div>
                <div class="report-card-body">
                    <div class="title">Pemantauan Tempoh Kontrak</div>
                    <div class="desc">Lampiran B &mdash; Pantau tempoh kontrak dan status tamat/lanjut tempoh.</div>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
