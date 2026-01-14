@php
    $isEdit = isset($model);
    $actionRoute = $isEdit
        ? route('admin.categories.update', [$model->id])
        : route('admin.categories.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<x-form :actionRoute="$actionRoute" :method="$method" :isEdit="$isEdit">

    <!-- Category Name in Arabic and English -->
    <div class="row">
        <div class="col-md-6">
            <!-- Name in Arabic -->
            <x-group-input-text
                label="Name in Arabic"
                name="ar[name]"
                :value="$isEdit ? ($model->translate('ar')->name ?? '') : (old('ar.name') ?? '')"
                required
            />
        </div>
        <div class="col-md-6">
            <!-- Name in English -->
            <x-group-input-text
                label="Name in English"
                name="en[name]"
                :value="$isEdit ? ($model->translate('en')->name ?? '') : (old('en.name') ?? '')"
                required
            />
        </div>
    </div>

    {{-- <!-- Is Active -->
    <x-group-input-checkbox
        label="Is Active"
        name="is_active"
        :value="$isEdit ? $model->is_active : (old('is_active', true))"
    /> --}}

</x-form>





