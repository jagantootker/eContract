@extends('components.layouts.app')

@section('content')
<div class="page-wrapper">
    @if(!empty($dbUnavailableMessage))
        <div class="mb-6 p-4 border border-amber-300 bg-amber-50 text-amber-800 rounded-lg">
            {{ $dbUnavailableMessage }}
        </div>
    @endif

    <div class="page-header">
        <h1>{{ $title ?? 'Pengurusan ABM & PPT' }}</h1>
        <p class="text-slate-500">Kelola semua dokumen Perancangan Perolehan</p>
    </div>

    <div class="max-w-7xl mx-auto">
        {{-- Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-8">
            <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                <p class="text-xs text-blue-600 font-medium">JUMLAH</p>
                <p class="text-2xl font-bold text-blue-900 mt-1">{{ $stats['total'] }}</p>
            </div>
            <div class="p-4 bg-indigo-50 rounded-lg border border-indigo-200">
                <p class="text-xs text-indigo-600 font-medium">DRAF</p>
                <p class="text-2xl font-bold text-indigo-900 mt-1">{{ $stats['draft'] }}</p>
            </div>
            <div class="p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                <p class="text-xs text-yellow-600 font-medium">SEDANG DISEMAK</p>
                <p class="text-2xl font-bold text-yellow-900 mt-1">{{ $stats['sedang_disemak'] }}</p>
            </div>
            <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                <p class="text-xs text-green-600 font-medium">DILULUSKAN</p>
                <p class="text-2xl font-bold text-green-900 mt-1">{{ $stats['diluluskan'] }}</p>
            </div>
            <div class="p-4 bg-red-50 rounded-lg border border-red-200">
                <p class="text-xs text-red-600 font-medium">DITOLAK</p>
                <p class="text-2xl font-bold text-red-900 mt-1">{{ $stats['ditolak'] }}</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                <p class="text-xs text-gray-600 font-medium">SELESAI</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['selesai'] }}</p>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="flex flex-wrap gap-3 mb-8">
            <a href="{{ url('/perancangan-perolehan/import') }}" class="btn btn-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                Muat Naik Baru
            </a>
            <a href="{{ route('abm.dashboard') }}" class="btn btn-secondary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
            <a href="{{ route('abm.repository') }}" class="btn btn-secondary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                Pusat Dokumen
            </a>
        </div>

        {{-- Documents Table --}}
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold">Senarai Dokumen</h3>
            </div>
            <div class="card-body p-0">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase">Ref No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase">Template</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase">Jenis Fail</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase">Rekod</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase">Dimuat Naik Oleh</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase">Tarikh</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($uploads as $upload)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 text-sm font-medium text-slate-900">
                                        <a href="{{ url('/perancangan-perolehan/' . $upload->id . '/preview') }}" class="text-blue-600 hover:text-blue-900">
                                            {{ $upload->reference_no }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        {{ $upload->template_type_label }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        {{ $upload->file_type }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                            {{ $upload->total_records }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ $upload->status_color }}">
                                            {{ $upload->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        {{ $upload->uploaded_by_name }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        {{ $upload->created_at->format('d M Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm space-x-3">
                                        <a href="{{ url('/perancangan-perolehan/' . $upload->id . '/preview') }}" class="text-blue-600 hover:text-blue-900 font-medium">
                                            Lihat
                                        </a>
                                        <a href="{{ url('/perancangan-perolehan/' . $upload->id . '/workflow') }}" class="text-purple-600 hover:text-purple-900 font-medium">
                                            Proses
                                        </a>
                                        <a href="{{ route('abm.download', $upload->id) }}" class="text-green-600 hover:text-green-900 font-medium">
                                            Muat
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-slate-500">
                                        <p>Tiada dokumen ditemui</p>
                                        <a href="{{ url('/perancangan-perolehan/import') }}" class="text-blue-600 hover:text-blue-900 text-sm mt-2 inline-block">
                                            Muat naik dokumen baru →
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($uploads->hasPages())
                    <div class="px-6 py-4 border-t">
                        {{ $uploads->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .page-wrapper {
        padding: 2rem;
        background:
            radial-gradient(1000px 520px at -10% -20%, rgba(16, 185, 129, 0.14), transparent 60%),
            radial-gradient(900px 500px at 115% -10%, rgba(59, 130, 246, 0.12), transparent 60%),
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
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
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
    
</style>
@endsection
