@props([
    'headers' => [],
    'loading' => false,
    'emptyText' => 'Tiada rekod dijumpai.',
    'stickyHeader' => true,
    'empty' => false,
    'colspan' => null,
    'wrapClass' => 'ds-table-wrap shadow-sm',
    'tableClass' => 'ds-table',
    'tableStyle' => '',
    'tableId' => '',
    'theadClass' => '',
    'tbodyClass' => '',
    'tbodyId' => '',
    'emptyCellClass' => 'ds-empty-cell',
])

<div {{ $attributes->merge(['class' => $wrapClass]) }}>
    @isset($toolbar)
        <div class="ds-table-toolbar">
            {{ $toolbar }}
        </div>
    @endisset
    <table class="{{ $tableClass }}" @if($tableId !== '') id="{{ $tableId }}" @endif @if($tableStyle !== '') style="{{ $tableStyle }}" @endif>
        @if(count($headers))
            <thead class="{{ trim(($stickyHeader ? 'ds-table-sticky ' : '') . $theadClass) }}">
                <tr>
                    @foreach($headers as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody class="{{ $tbodyClass }}" @if($tbodyId !== '') id="{{ $tbodyId }}" @endif>
            @if($loading)
                @for($i = 0; $i < 5; $i++)
                    <tr>
                        <td colspan="{{ max(count($headers), 1) }}">
                            <div class="ds-skeleton"></div>
                        </td>
                    </tr>
                @endfor
            @elseif($empty)
                <tr>
                    <td colspan="{{ $colspan ?: max(count($headers), 1) }}" class="{{ $emptyCellClass }}">
                        <div class="ds-empty-state">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>{{ $emptyText }}</span>
                        </div>
                    </td>
                </tr>
            @else
                {{ $slot }}
            @endif
        </tbody>
        @isset($tableFooter)
            <tfoot>
                {{ $tableFooter }}
            </tfoot>
        @endisset
    </table>
    @isset($footer)
        <div class="ds-table-footer">
            {{ $footer }}
        </div>
    @endisset
</div>
