@php
    $isEdit = isset($model);
    $actionRoute = $isEdit
        ? route('admin.lens-types.update', [$model->id])
        : route('admin.lens-types.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<x-form :actionRoute="$actionRoute" :method="$method" :isEdit="$isEdit">

    <!-- Type Name -->
    <x-group-input-text
        label="Type Name"
        name="name"
        :value="$isEdit ? $model->name : (old('name') ?? '')"
        required
    />

</x-form>
