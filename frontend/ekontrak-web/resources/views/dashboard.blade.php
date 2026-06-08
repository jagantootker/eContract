@extends('components.layouts.app')

@section('title', 'Laman Utama')
@section('breadcrumb', 'Laman Utama')

@section('content')
<div class="card" style="max-width:920px;">
    <div class="card-header">
        <div>
            <div class="page-title">Laman Utama</div>
            <div class="page-subtitle">Selamat datang ke sistem eKONTRAK.</div>
        </div>
    </div>
    <div class="card-body">
        <div class="section-hdr">Akses Pantas</div>
        <div style="display:flex;gap:0.75rem;flex-wrap:wrap;">
            <a href="{{ url('/kontrak') }}" class="btn btn-primary">Senarai Kontrak</a>
            <a href="{{ url('/syarikat') }}" class="btn btn-outline">Maklumat Syarikat</a>
            <a href="{{ url('/laporan') }}" class="btn btn-outline">Laporan</a>
            <a href="{{ route('change-password') }}" class="btn btn-outline">Tukar Kata Laluan</a>
        </div>
    </div>
</div>
@endsection
