@php
    $isEdit = isset($model);
    $actionRoute = $isEdit
        ? route('admin.lens-power-presets.update', $model)
        : route('admin.lens-power-presets.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<x-form :actionRoute="$actionRoute" :method="$method" :isEdit="$isEdit">

    <x-group-input-text
        label="اسم المحفوظة"
        name="name"
        :value="$isEdit ? $model->name : (old('name') ?? '')"
        required
    />

</x-form>
