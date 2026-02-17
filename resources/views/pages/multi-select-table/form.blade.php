<x-app-layout>
    <div class="card shadow-sm">
        @include('pages.multi-select-table.parts._header')

        <div class="card-body pt-0">
            @include('pages.multi-select-table.parts._preset-name')

            {{-- الخطوة 2: أدوات التحديد --}}
            @include('pages.multi-select-table.parts._range-form')
            @include('pages.multi-select-table.parts._formula-panel')
            @include('pages.multi-select-table.parts._instructions')

            @include('pages.multi-select-table.parts._grid')
            @include('pages.multi-select-table.parts._selected-info')
        </div>
    </div>

    @include('pages.multi-select-table.parts._sticky-bar')

    @push('scripts')
        @include('pages.multi-select-table.parts._styles')
        @include('pages.multi-select-table.parts._scripts')
    @endpush
</x-app-layout>
