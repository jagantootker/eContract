@extends('components.layouts.app')

@section('content')
<div class="page-wrapper">
    @if(!empty($dbUnavailableMessage))
                    <x-table
                        :headers="['Ref No', 'Template', 'Jenis Fail', 'Rekod', 'Status', 'Dimuat Naik Oleh', 'Tarikh', 'Tindakan']"
                        wrap-class="table-scroll"
                        table-class="w-full"
                    >
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
                    </x-table>
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
                    <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/80">
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
