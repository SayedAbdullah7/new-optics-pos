<x-app-layout>
    <x-dynamic-table
        table-id="range_powers_table"
        :columns="$columns"
        :filters="$filters"
        :create-url="route('admin.range-powers.create')"
        create-link-text="إضافة محفوظة جديدة"
        :show-checkbox="false"
    />
</x-app-layout>
