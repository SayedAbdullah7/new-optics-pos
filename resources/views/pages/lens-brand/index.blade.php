<x-app-layout>
    <x-dynamic-table
        table-id="lens_brands_table"
        :columns="$columns"
        :filters="$filters"
        :create-url="auth()->user()->can('create-lens-brands') ? route('admin.lens-brands.create') : null"
        :show-checkbox="false"
    />
</x-app-layout>
