<x-app-layout>
    <x-dynamic-table
        table-id="stock_table"
        :columns="$columns"
        :filters="$filters"
        :show-checkbox="false"
    />
</x-app-layout>