<x-app-layout>
    <x-dynamic-table
        table-id="categories_table"
        :columns="$columns"
        :filters="$filters"
        :create-url="auth()->user()->can('create-categories') ? route('admin.categories.create') : null"
        :show-checkbox="false"
    />
</x-app-layout>
