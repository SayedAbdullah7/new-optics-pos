@php
    $isEdit = isset($model);
    $actionRoute = $isEdit
        ? route('admin.lens-brands.update', ['lens_category' => $model->id])
        : route('admin.lens-brands.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<x-form :actionRoute="$actionRoute" :method="$method" :isEdit="$isEdit">

    <div class="row">
        <div class="col-md-6">
            <!-- Brand Name -->
            <x-group-input-text
                label="Brand Name"
                name="brand_name"
                :value="$isEdit ? $model->brand_name : (old('brand_name') ?? '')"
                required
            />
        </div>
        <div class="col-md-6">
            <!-- Country Name -->
            <x-group-input-text
                label="Country Name"
                name="country_name"
                :value="$isEdit ? $model->country_name : (old('country_name') ?? '')"
            />
        </div>
    </div>

</x-form>
