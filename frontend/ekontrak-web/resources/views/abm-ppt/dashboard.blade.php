@extends('components.layouts.app')

@section('content')
<div class="page-wrapper">
    @if(!empty($dbUnavailableMessage))
        <div class="mb-6 p-4 border border-amber-300 bg-amber-50 text-amber-800 rounded-lg">
            {{ $dbUnavailableMessage }}
        </div>
    @endif

    <div class="page-header">
        <div>
            <p class="eyebrow">Perancangan Perolehan</p>
            <h1>Dashboard ABM</h1>
            <p class="text-slate-500">Anggaran Belanjawan Menteri - ringkasan status dokumen dan aliran kerja semasa.</p>
        </div>
        <div class="dashboard-chip">Pemantauan Masa Nyata</div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 mb-8">
        <!-- Total Upload -->
        <div class="card stat-card hover:shadow-md transition-shadow">
            <div class="card-body">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-slate-600">Jumlah Muat Naik</span>
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                </div>
                <div class="text-3xl font-bold text-slate-900">{{ $totalUpload }}</div>
                <p class="text-xs text-slate-500 mt-1">Dokumen dimuat naik</p>
            </div>
        </div>

        <!-- Draft -->
        <div class="card stat-card hover:shadow-md transition-shadow">
            <div class="card-body">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-slate-600">Draf</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ $draft }}
                    </span>
                </div>
                <div class="text-3xl font-bold text-blue-600">{{ $draft }}</div>
            </div>
        </div>

        <!-- Sedang Disemak -->
        <div class="card stat-card hover:shadow-md transition-shadow">
            <div class="card-body">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-slate-600">Sedang Disemak</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        {{ $sedangDisemak }}
                    </span>
                </div>
                <div class="text-3xl font-bold text-yellow-600">{{ $sedangDisemak }}</div>
            </div>
        </div>

        <!-- Diluluskan -->
        <div class="card stat-card hover:shadow-md transition-shadow">
            <div class="card-body">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-slate-600">Diluluskan</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        {{ $diluluskan }}
                    </span>
                </div>
                <div class="text-3xl font-bold text-green-600">{{ $diluluskan }}</div>
            </div>
        </div>

        <!-- Ditolak -->
        <div class="card stat-card hover:shadow-md transition-shadow">
            <div class="card-body">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-slate-600">Ditolak</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        {{ $ditolak }}
                    </span>
                </div>
                <div class="text-3xl font-bold text-red-600">{{ $ditolak }}</div>
            </div>
        </div>

        <!-- Selesai -->
        <div class="card stat-card hover:shadow-md transition-shadow">
            <div class="card-body">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-slate-600">Selesai</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        {{ $selesai }}
                    </span>
                </div>
                <div class="text-3xl font-bold text-gray-600">{{ $selesai }}</div>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="action-strip mb-8">
        <a href="{{ url('/perancangan-perolehan/import') }}" class="btn btn-primary">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
            Muat Naik Dokumen Baru
        </a>
        <a href="{{ url('/perancangan-perolehan/pengurusan-abm') }}" class="btn btn-secondary">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Urus Dokumen
        </a>
        <a href="{{ url('/perancangan-perolehan/repository') }}" class="btn btn-secondary">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
            Pusat Dokumen
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Recent Activities --}}
        <div class="lg:col-span-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold">Aktiviti Terkini</h3>
                </div>
                <div class="card-body p-0 overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Rujukan</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Aktiviti</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Pengguna</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Masa</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($recentActivities as $activity)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-4 text-sm font-medium text-slate-900">{{ $activity->upload?->reference_no ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-700">{{ $activity->description }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-600">{{ $activity->performed_by_name ?? 'Sistem' }}</td>
                                    <td class="px-6 py-4 text-xs text-slate-500">{{ $activity->created_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-slate-500">Tiada aktiviti terkini</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Recent Uploads --}}
        <div>
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold">Muat Naik Terkini</h3>
                </div>
                <div class="card-body p-0">
                    @forelse($recentUploads as $upload)
                        <div class="px-6 py-4 border-b last:border-b-0 hover:bg-slate-50 transition">
                            <a href="{{ url('/perancangan-perolehan/' . $upload->id . '/preview') }}" class="block">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-slate-900">{{ $upload->reference_no }}</p>
                                        <p class="text-xs text-slate-500">{{ $upload->template_type_label }}</p>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ $upload->status_color }}">
                                        {{ $upload->status_label }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 mt-2">
                                    {{ $upload->created_at->format('d M Y H:i') }}
                                </p>
                            </a>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center text-slate-500">
                            <p>Tiada muat naik terkini</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="card mt-6">
                <div class="card-header">
                    <h3 class="text-lg font-semibold">Workflow Progress</h3>
                </div>
                <div class="card-body space-y-3">
                    @php
                        $maxCount = max(1, $totalUpload);
                        $progress = [
                            ['label' => 'Draf', 'value' => $draft, 'color' => '#2563eb'],
                            ['label' => 'Sedang Disemak', 'value' => $sedangDisemak, 'color' => '#d97706'],
                            ['label' => 'Diluluskan', 'value' => $diluluskan, 'color' => '#16a34a'],
                            ['label' => 'Ditolak', 'value' => $ditolak, 'color' => '#dc2626'],
                            ['label' => 'Selesai', 'value' => $selesai, 'color' => '#475569'],
                        ];
                    @endphp
                    @foreach($progress as $item)
                        @php
                            $percent = (int) round(($item['value'] / $maxCount) * 100);
                        @endphp
                        <div>
                            <div class="flex items-center justify-between text-xs text-slate-600 mb-1">
                                <span>{{ $item['label'] }}</span>
                                <span>{{ $item['value'] }} ({{ $percent }}%)</span>
                            </div>
                            <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-2 rounded-full" style="width: {{ $percent }}%; background: {{ $item['color'] }};"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    .page-wrapper {
        font-family: 'Plus Jakarta Sans', sans-serif;
        padding: 2rem;
        background:
            radial-gradient(1000px 500px at -20% -30%, rgba(14, 165, 233, 0.16), transparent 60%),
            radial-gradient(900px 500px at 120% -20%, rgba(16, 185, 129, 0.13), transparent 60%),
            linear-gradient(180deg, #f8fbff 0%, #f1f5f9 100%);
        min-height: calc(100vh - 120px);
    }
    
    .page-header {
        margin-bottom: 2rem;
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1rem;
    }

    .eyebrow {
        font-size: 0.75rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #0284c7;
        font-weight: 700;
        margin-bottom: 0.35rem;
    }
    
    .page-header h1 {
        font-size: 1.875rem;
        font-weight: 800;
        color: #111827;
        margin-bottom: 0.5rem;
    }

    .dashboard-chip {
        font-size: 0.78rem;
        font-weight: 700;
        color: #075985;
        background: #e0f2fe;
        border: 1px solid #bae6fd;
        border-radius: 999px;
        padding: 0.45rem 0.72rem;
    }
    
    .card {
        background: rgba(255,255,255,0.9);
        border-radius: 1rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.07);
        overflow: hidden;
    }

    .stat-card {
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    }
    
    .card-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
    }
    
    .card-body {
        padding: 1.5rem;
    }
    
    .action-strip {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        padding: 0.85rem;
        border: 1px solid #dbeafe;
        border-radius: 0.9rem;
        background: rgba(255, 255, 255, 0.75);
    }

    @media (max-width: 768px) {
        .page-wrapper {
            padding: 1rem;
        }

        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .card-header,
        .card-body {
            padding: 1rem;
        }
    }

    /* Compact modern overrides */
    .page-wrapper svg { width: 1rem !important; height: 1rem !important; }
    .card { box-shadow: 0 8px 18px rgba(15,23,42,0.05) !important; border-radius: 0.85rem !important; }
    .page-header h1 { font-weight: 700 !important; }
    .stat-card { padding: 1rem !important; }
</style>
@endsection
