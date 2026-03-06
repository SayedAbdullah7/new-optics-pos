<x-app-layout>
    <x-dynamic-table
        table-id="users_table"
        :columns="$columns"
        :filters="$filters"
        :create-url="auth()->user()->can('create-users') ? route('admin.users.create') : null"
        :show-checkbox="false"
    />
</x-app-layout>
