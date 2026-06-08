@extends('components.layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="page-header">
        <h1>Status Proses Dokumen</h1>
        <p class="text-slate-500">Ref: <strong>{{ $upload->reference_no }}</strong> | {{ $upload->template_type_label }}</p>
    </div>

    <div class="max-w-4xl mx-auto">
        {{-- Current Status Card --}}
        <div class="card mb-8 bg-gradient-to-r from-blue-50 to-blue-100 border-blue-200">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-blue-600 font-medium">STATUS SEMASA</p>
                        <p class="text-3xl font-bold text-blue-900 mt-2">{{ $upload->status_label }}</p>
                    </div>
                    <div class="text-6xl">
                        @if($upload->status === 'DRAFT')
                            📝
                        @elseif($upload->status === 'SEDANG_DISEMAK')
                            🔍
                        @elseif($upload->status === 'DILULUSKAN')
                            ✅
                        @elseif($upload->status === 'DITOLAK')
                            ❌
                        @elseif($upload->status === 'SELESAI')
                            🎉
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Workflow Stepper --}}
        <div class="card mb-8">
            <div class="card-body">
                <div class="space-y-6">
                    {{-- Step 1: Upload --}}
                    <div class="flex gap-4">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-slate-900">Dimuat Naik</h3>
                            <p class="text-sm text-slate-600 mt-1">Fail telah dimuat naik ke sistem</p>
                            @php
                                $uploadHistory = $workflowHistory->where('action', 'UPLOADED')->first();
                            @endphp
                            @if($uploadHistory)
                                <p class="text-xs text-slate-500 mt-2">
                                    {{ $uploadHistory->created_at->format('H:i, d M Y') }} - {{ $uploadHistory->performed_by_name ?? 'Sistem' }}
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Step 2: Extracted --}}
                    <div class="flex gap-4">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-slate-900">Data Diekstrak</h3>
                            <p class="text-sm text-slate-600 mt-1">Sistem telah mengekstrak data dari fail ({{ $upload->total_records }} rekod)</p>
                            @php
                                $extractHistory = $workflowHistory->where('action', 'EXTRACTED')->first();
                            @endphp
                            @if($extractHistory)
                                <p class="text-xs text-slate-500 mt-2">
                                    {{ $extractHistory->created_at->format('H:i, d M Y') }}
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Step 3: Verification --}}
                    <div class="flex gap-4">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-12 w-12 rounded-full {{ $upload->status !== 'DRAFT' ? 'bg-green-100' : 'bg-slate-200' }}">
                                @if($upload->status !== 'DRAFT')
                                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                @else
                                    <span class="text-slate-400">⏳</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold {{ $upload->status !== 'DRAFT' ? 'text-slate-900' : 'text-slate-500' }}">Sedang Disemak</h3>
                            <p class="text-sm {{ $upload->status !== 'DRAFT' ? 'text-slate-600' : 'text-slate-500' }} mt-1">Menunggu pengesahan data oleh pegawai</p>
                            @php
                                $verifyHistory = $workflowHistory->where('action', 'VERIFIED')->first();
                            @endphp
                            @if($verifyHistory)
                                <p class="text-xs text-slate-500 mt-2">
                                    {{ $verifyHistory->created_at->format('H:i, d M Y') }}
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Step 4: Approval --}}
                    <div class="flex gap-4">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-12 w-12 rounded-full {{ $upload->status === 'DILULUSKAN' || $upload->status === 'SELESAI' ? 'bg-green-100' : 'bg-slate-200' }}">
                                @if($upload->status === 'DILULUSKAN' || $upload->status === 'SELESAI')
                                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                @elseif($upload->status === 'DITOLAK')
                                    <span class="text-red-600">✕</span>
                                @else
                                    <span class="text-slate-400">⏳</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold {{ $upload->status === 'DILULUSKAN' || $upload->status === 'SELESAI' ? 'text-slate-900' : ($upload->status === 'DITOLAK' ? 'text-red-900' : 'text-slate-500') }}">
                                @if($upload->status === 'DITOLAK')
                                    Ditolak
                                @else
                                    Lulusan
                                @endif
                            </h3>
                            <p class="text-sm {{ $upload->status === 'DILULUSKAN' || $upload->status === 'SELESAI' ? 'text-slate-600' : ($upload->status === 'DITOLAK' ? 'text-red-600' : 'text-slate-500') }} mt-1">
                                @if($upload->status === 'DITOLAK')
                                    Dokumen telah ditolak
                                @else
                                    Dokumen telah diluluskan dan bersedia untuk diproses
                                @endif
                            </p>
                            @php
                                $approveHistory = $workflowHistory->where('action', 'APPROVED')->first();
                                $rejectHistory = $workflowHistory->where('action', 'REJECTED')->first();
                            @endphp
                            @if($approveHistory)
                                <p class="text-xs text-slate-500 mt-2">
                                    {{ $approveHistory->created_at->format('H:i, d M Y') }}
                                </p>
                            @elseif($rejectHistory)
                                <p class="text-xs text-red-500 mt-2">
                                    {{ $rejectHistory->created_at->format('H:i, d M Y') }} - {{ $rejectHistory->description }}
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Step 5: Completion --}}
                    <div class="flex gap-4">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-12 w-12 rounded-full {{ $upload->status === 'SELESAI' ? 'bg-green-100' : 'bg-slate-200' }}">
                                @if($upload->status === 'SELESAI')
                                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                @else
                                    <span class="text-slate-400">⏳</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold {{ $upload->status === 'SELESAI' ? 'text-slate-900' : 'text-slate-500' }}">Selesai</h3>
                            <p class="text-sm {{ $upload->status === 'SELESAI' ? 'text-slate-600' : 'text-slate-500' }} mt-1">Proses bagi dokumen ini telah selesai</p>
                            @php
                                $completeHistory = $workflowHistory->where('action', 'COMPLETED')->first();
                            @endphp
                            @if($completeHistory)
                                <p class="text-xs text-slate-500 mt-2">
                                    {{ $completeHistory->created_at->format('H:i, d M Y') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Timeline History --}}
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold">Sejarah Aktiviti Terperinci</h3>
            </div>
            <div class="card-body p-0">
                @if($workflowHistory->count() > 0)
                    <div class="divide-y">
                        @foreach($workflowHistory as $entry)
                            <div class="px-6 py-4 flex gap-4 hover:bg-slate-50">
                                <div class="flex-shrink-0 pt-1">
                                    @if($entry->action === 'UPLOADED')
                                        <div class="w-3 h-3 rounded-full bg-blue-500 ring-2 ring-blue-100"></div>
                                    @elseif($entry->action === 'EXTRACTED')
                                        <div class="w-3 h-3 rounded-full bg-purple-500 ring-2 ring-purple-100"></div>
                                    @elseif($entry->action === 'VERIFIED')
                                        <div class="w-3 h-3 rounded-full bg-yellow-500 ring-2 ring-yellow-100"></div>
                                    @elseif($entry->action === 'APPROVED')
                                        <div class="w-3 h-3 rounded-full bg-green-500 ring-2 ring-green-100"></div>
                                    @elseif($entry->action === 'REJECTED')
                                        <div class="w-3 h-3 rounded-full bg-red-500 ring-2 ring-red-100"></div>
                                    @else
                                        <div class="w-3 h-3 rounded-full bg-slate-400 ring-2 ring-slate-100"></div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900">{{ $entry->action_label }}</p>
                                            @if($entry->description)
                                                <p class="text-sm text-slate-600 mt-1">{{ $entry->description }}</p>
                                            @endif
                                        </div>
                                        <p class="text-xs text-slate-500 flex-shrink-0">
                                            {{ $entry->created_at->format('H:i') }}<br>
                                            {{ $entry->created_at->format('d M Y') }}
                                        </p>
                                    </div>
                                    @if($entry->performed_by_name)
                                        <p class="text-xs text-slate-500 mt-2">
                                            Oleh: <strong>{{ $entry->performed_by_name }}</strong>
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="px-6 py-12 text-center text-slate-500">
                        <p>Tiada sejarah aktiviti</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Actions --}}
        <div class="mt-8 flex gap-3">
            <a href="{{ url('/perancangan-perolehan/' . $upload->id . '/preview') }}" class="btn btn-secondary">
                ← Kembali ke Pratonton
            </a>
            <a href="{{ route('abm.repository') }}" class="btn btn-secondary">
                📁 Pusat Dokumen
            </a>
        </div>
    </div>
</div>

<style>
    .page-wrapper {
        padding: 2rem;
        background:
            radial-gradient(900px 480px at -10% -25%, rgba(37, 99, 235, 0.14), transparent 60%),
            radial-gradient(1000px 520px at 110% -20%, rgba(16, 185, 129, 0.12), transparent 60%),
            #f8fafc;
        min-height: calc(100vh - 120px);
    }

    .page-header {
        margin-bottom: 2rem;
    }

    .page-header h1 {
        font-size: 1.875rem;
        font-weight: bold;
        color: #111827;
        margin-bottom: 0.5rem;
    }

    .card {
        background: rgba(255,255,255,0.9);
        border-radius: 1rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 12px 30px rgba(15,23,42,0.06);
        backdrop-filter: blur(6px);
        overflow: hidden;
    }

    .card-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    .card-body {
        padding: 1.5rem;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.625rem 1.25rem;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        font-weight: 500;
        text-decoration: none;
        border: 1px solid transparent;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-secondary {
        background: #f1f5f9;
        color: #334155;
        border-color: #cbd5e1;
    }

    .btn-secondary:hover {
        background: #e2e8f0;
    }
</style>
@endsection
