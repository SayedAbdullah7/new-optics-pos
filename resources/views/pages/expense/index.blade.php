<x-app-layout>
    <x-dynamic-table
        table-id="expenses_table"
        :columns="$columns"
        :filters="$filters"
        :create-url="auth()->user()->can('create-expenses') ? route('admin.expenses.create') : null"
        :show-checkbox="false"
    />
</x-app-layout>
