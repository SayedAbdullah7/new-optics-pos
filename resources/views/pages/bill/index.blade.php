<x-app-layout>
    <x-dynamic-table
        table-id="bills_table"
        :columns="$columns"
        :filters="$filters"
        :create-url="auth()->user()->can('create-bills') ? route('admin.bills.create') : null"
        :show-checkbox="false"
    />
</x-app-layout>
