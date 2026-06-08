@extends('components.layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="page-header">
        <h1>Viewer Dokumen</h1>
        <p class="text-slate-500">{{ $upload->reference_no }} • {{ $upload->template_type_label }}</p>
    </div>

    <div class="card">
        <div class="card-body">
            @if($upload->file_type === 'PDF')
                <div class="mb-4 text-sm text-slate-600">Pratonton PDF terbenam</div>
                <iframe src="{{ asset('storage/' . $upload->file_path) }}" style="width:100%; height:75vh; border:1px solid #e2e8f0; border-radius:8px;"></iframe>
            @else
                <div class="mb-4 text-sm text-slate-600">Pratonton Excel (100 baris pertama)</div>
                <div class="overflow-x-auto border rounded-lg">
                    @php
                        $rows = is_array($upload->extraction_data) ? array_slice($upload->extraction_data, 0, 100) : [];
                        $keys = count($rows) > 0 ? array_keys((array)$rows[0]) : [];
                    @endphp
                    <x-table
                        :headers="$keys"
                        wrap-class="table-scroll"
                        table-class="w-full"
                    >
                        @forelse($rows as $row)
                            <tr class="hover:bg-slate-50">
                                @foreach($keys as $key)
                                    <td class="px-4 py-2 text-sm text-slate-800">{{ $row[$key] ?? '-' }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ max(count($keys), 1) }}" class="px-4 py-8 text-center text-slate-500">Tiada data dipratonton.</td>
                            </tr>
                        @endforelse
                    </x-table>
                </div>
            @endif

            <div class="mt-6 flex gap-3">
                <a class="btn btn-secondary" href="{{ route('abm.repository') }}">Kembali ke Pusat Dokumen</a>
                <a class="btn btn-primary" href="{{ route('abm.download', $upload->id) }}">Muat Turun</a>
            </div>
        </div>
    </div>
</div>

<style>
.page-wrapper {
    padding: 2rem;
    background:
        radial-gradient(1000px 500px at -20% -30%, rgba(14, 165, 233, 0.14), transparent 60%),
        radial-gradient(900px 500px at 120% -20%, rgba(16, 185, 129, 0.12), transparent 60%),
        #f8fafc;
    min-height: calc(100vh - 120px);
}
.page-header { margin-bottom: 1rem; }
.page-header h1 { font-size: 1.875rem; font-weight: 700; }
.card {
    background: rgba(255,255,255,0.9);
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
    backdrop-filter: blur(6px);
}
.card-body { padding: 1.5rem; }
.btn { display:inline-flex; align-items:center; justify-content:center; padding:.625rem 1.25rem; border-radius:.375rem; text-decoration:none; }
.btn-primary { background:linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color:#fff; }
.btn-secondary { background:#f1f5f9; color:#334155; border:1px solid #cbd5e1; }
</style>
@endsection
