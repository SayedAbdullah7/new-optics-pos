<x-app-layout>
    <x-dynamic-table
        table-id="lens_types_table"
        :columns="$columns"
        :filters="$filters"
        :create-url="auth()->user()->can('create-lens-types') ? route('admin.lens-types.create') : null"
        :show-checkbox="false"
    />
</x-app-layout>
