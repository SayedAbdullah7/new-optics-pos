<x-app-layout>
    <x-dynamic-table
        table-id="lenses_table"
        :columns="$columns"
        :filters="$filters"
        :create-url="auth()->user()->can('create-lenses') ? route('admin.lenses.create') : null"
        :show-checkbox="false"
    />
</x-app-layout>
