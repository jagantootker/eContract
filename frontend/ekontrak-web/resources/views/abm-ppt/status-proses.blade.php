@extends('components.layouts.app')

@section('content')
<div class="page-wrapper">
    @if(!empty($dbUnavailableMessage))
        <div class="mb-6 p-4 border border-amber-300 bg-amber-50 text-amber-800 rounded-lg">
            {{ $dbUnavailableMessage }}
        </div>
    @endif

    <div class="page-header">
        <h1>Status Proses</h1>
        <p class="text-slate-500">Pemantauan status semua dokumen ABM/PPT</p>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 border-b">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Rujukan</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Template</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Langkah Semasa</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Dikemas Kini</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($uploads as $upload)
                            @php
                                $currentStep = match($upload->status) {
                                    'DRAFT' => 'Upload / Extraction',
                                    'SEDANG_DISEMAK' => 'Verification',
                                    'DILULUSKAN' => 'Approval',
                                    'DITOLAK' => 'Verification (Rejected)',
                                    'SELESAI' => 'Final',
                                    default => 'Unknown',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 text-sm font-medium text-slate-900">{{ $upload->reference_no }}</td>
                                <td class="px-6 py-4 text-sm text-slate-700">{{ $upload->template_type_label }}</td>
                                <td class="px-6 py-4 text-sm text-slate-700">{{ $currentStep }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ $upload->status_color }}">{{ $upload->status_label }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $upload->updated_at->format('d M Y H:i') }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <a href="{{ route('abm.workflow', $upload->id) }}" class="text-blue-600 hover:text-blue-900 font-medium">Lihat Stepper</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-slate-500">Tiada rekod proses.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t">{{ $uploads->links() }}</div>
        </div>
    </div>
</div>

<style>
.page-wrapper {
    padding: 2rem;
    background:
        radial-gradient(1000px 500px at -20% -20%, rgba(99, 102, 241, 0.14), transparent 60%),
        radial-gradient(800px 500px at 120% -20%, rgba(56, 189, 248, 0.12), transparent 60%),
        #f8fafc;
    min-height: calc(100vh - 120px);
}
.page-header { margin-bottom: 1rem; }
.page-header h1 { font-size: 1.875rem; font-weight: 700; }
.card {
    background: rgba(255,255,255,0.9);
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    box-shadow: 0 12px 30px rgba(15,23,42,0.06);
    backdrop-filter: blur(6px);
}
.card-body { padding: 1.5rem; }
</style>
@endsection
