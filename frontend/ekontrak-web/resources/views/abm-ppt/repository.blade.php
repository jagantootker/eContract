@extends('components.layouts.app')

@section('content')
<div class="page-wrapper">
    @if(!empty($dbUnavailableMessage))
        <div class="mb-6 p-4 border border-amber-300 bg-amber-50 text-amber-800 rounded-lg">
            {{ $dbUnavailableMessage }}
        </div>
    @endif

    <div class="page-header">
        <h1>Pusat Dokumen</h1>
        <p class="text-slate-500">Repositori terpusat untuk semua dokumen ABM dan PPT</p>
    </div>

    <div class="max-w-7xl mx-auto">
        {{-- Filters --}}
        <div class="card mb-6">
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Cari</label>
                        <input type="text" id="searchInput" placeholder="Carian..." class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Template</label>
                        <select id="templateFilter" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Template</option>
                            <option value="ABM1">ABM 1</option>
                            <option value="ABM2">ABM 2</option>
                            <option value="ABM7">ABM 7</option>
                            <option value="PPT_BARU">PPT Baru</option>
                            <option value="PPT_KEMAS_KINI">PPT Kemas Kini</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Status</label>
                        <select id="statusFilter" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Status</option>
                            <option value="DRAFT">Draf</option>
                            <option value="SEDANG_DISEMAK">Sedang Disemak</option>
                            <option value="DILULUSKAN">Diluluskan</option>
                            <option value="DITOLAK">Ditolak</option>
                            <option value="SELESAI">Selesai</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Tahun</label>
                        <select id="yearFilter" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Tahun</option>
                            @for($year = date('Y'); $year >= 2020; $year--)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Documents Table --}}
        <div class="card">
            <div class="card-body p-0">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase">Ref No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase">Template</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase">Bahagian</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase">Dimuat Naik Oleh</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase">Tarikh</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody id="documentsTableBody" class="divide-y">
                            @forelse($documents as $doc)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 text-sm font-medium text-slate-900">
                                        {{ $doc->reference_no }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        {{ $doc->template_type_label }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        {{ $doc->department ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        {{ $doc->uploaded_by_name }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ $doc->status_color }}">
                                            {{ $doc->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        {{ $doc->created_at->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm space-x-2">
                                        <a href="{{ route('abm.viewer', $doc->id) }}" class="text-blue-600 hover:text-blue-900 font-medium">Lihat</a>
                                        <a href="{{ route('abm.download', $doc->id) }}" class="text-slate-600 hover:text-slate-900 font-medium">Muat</a>
                                        <a href="{{ url('/perancangan-perolehan/' . $doc->id . '/workflow') }}" class="text-purple-600 hover:text-purple-900 font-medium">History</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                        Tiada dokumen ditemui
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($documents->hasPages())
                    <div class="px-6 py-4 border-t">
                        {{ $documents->links() }}
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
            radial-gradient(1000px 500px at -20% -30%, rgba(14, 165, 233, 0.14), transparent 60%),
            radial-gradient(900px 500px at 120% -20%, rgba(59, 130, 246, 0.12), transparent 60%),
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
    }
    
    .card-body {
        padding: 1.5rem;
    }
</style>

<script>
    // Simple filtering (in production, use AJAX)
    document.getElementById('searchInput')?.addEventListener('change', applyFilters);
    document.getElementById('templateFilter')?.addEventListener('change', applyFilters);
    document.getElementById('statusFilter')?.addEventListener('change', applyFilters);
    document.getElementById('yearFilter')?.addEventListener('change', applyFilters);
    
    function applyFilters() {
        // For demo, reload with filter params
        const params = new URLSearchParams();
        const search = document.getElementById('searchInput')?.value;
        const template = document.getElementById('templateFilter')?.value;
        const status = document.getElementById('statusFilter')?.value;
        const year = document.getElementById('yearFilter')?.value;
        
        if (search) params.append('search', search);
        if (template) params.append('template_type', template);
        if (status) params.append('status', status);
        if (year) params.append('year', year);
        
        if (params.toString()) {
            window.location.href = '{{ url("/perancangan-perolehan/repository") }}?' + params.toString();
        }
    }
</script>
@endsection
