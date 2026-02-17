<x-app-layout>
    <x-dynamic-table
        table-id="lens_power_presets_table"
        :columns="$columns"
        :filters="$filters"
        :create-url="route('admin.lens-power-presets.create')"
        create-link-text="إضافة محفوظة جديدة"
        :show-checkbox="false"
    />
</x-app-layout>
