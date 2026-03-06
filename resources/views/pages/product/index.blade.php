<x-app-layout>
    <x-dynamic-table
        table-id="products_table"
        :columns="$columns"
        :filters="$filters"
        :create-url="auth()->user()->can('create-products') ? route('admin.products.create') : null"
        :show-checkbox="false"
    />
</x-app-layout>
