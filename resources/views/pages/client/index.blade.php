<x-app-layout>
    <x-dynamic-table
        table-id="clients_table"
        :columns="$columns"
        :filters="$filters"
        :create-url="auth()->user()->can('create-clients') ? route('admin.clients.create') : null"
        :show-checkbox="false"
    />
</x-app-layout>
