<x-app-layout>
    <x-dynamic-table
        table-id="invoices_table"
        :columns="$columns"
        :filters="$filters"
        :create-url="auth()->user()->can('create-invoices') ? route('admin.invoices.create') : null"
        :show-checkbox="false"
    />
</x-app-layout>
