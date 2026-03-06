<x-app-layout>
    <x-dynamic-table
        table-id="roles_table"
        :columns="$columns"
        :filters="$filters"
        :create-url="auth()->user()->can('create-roles') ? route('admin.roles.create') : null"
        :show-checkbox="false"
    />
</x-app-layout>
