@php
    $isEdit = isset($model);
    $actionRoute = $isEdit
        ? route('admin.lenses.update', [$model->id])
        : route('admin.lenses.store');
    $method = $isEdit ? 'PUT' : 'POST';

    // Prepare options for selects
    $rangeOptions = $ranges->pluck('name', 'id')->toArray();
    $typeOptions = $types->pluck('name', 'id')->toArray();
    $categoryOptions = $categories->pluck('brand_name', 'id')->toArray();
@endphp

<x-form :actionRoute="$actionRoute" :method="$method" :isEdit="$isEdit">

    <div class="row">
        <div class="col-md-6">
            <!-- Lens Code -->
            <x-group-input-text
                label="Lens Code"
                name="lens_code"
                :value="$isEdit ? $model->lens_code : (old('lens_code') ?? '')"
                required
            />
        </div>
        <div class="col-md-6">
            <!-- Range Power -->
            <x-group-input-select
                label="Range Power"
                name="RangePower_id"
                :value="$isEdit ? $model->RangePower_id : (old('RangePower_id') ?? '')"
                :options="$rangeOptions"
                required
            />
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <!-- Lens Type -->
            <x-group-input-select
                label="Lens Type"
                name="type_id"
                :value="$isEdit ? $model->type_id : (old('type_id') ?? '')"
                :options="$typeOptions"
                required
            />
        </div>
        <div class="col-md-6">
            <!-- Category/Brand -->
            <x-group-input-select
                label="Category/Brand"
                name="category_id"
                :value="$isEdit ? $model->category_id : (old('category_id') ?? '')"
                :options="$categoryOptions"
                required
            />
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <!-- Sale Price -->
            <x-group-input-number
                label="Sale Price"
                name="sale_price"
                :value="$isEdit ? $model->sale_price : (old('sale_price') ?? '0')"
                step="0.01"
                min="0"
                required
            />
        </div>
        <div class="col-md-6">
            <!-- Purchase Price -->
            <x-group-input-number
                label="Purchase Price"
                name="purchase_price"
                :value="$isEdit ? $model->purchase_price : (old('purchase_price') ?? '0')"
                step="0.01"
                min="0"
            />
        </div>
    </div>

    <!-- Description -->
    <x-group-input-textarea
        label="Description"
        name="description"
        :value="$isEdit ? $model->description : (old('description') ?? '')"
        rows="3"
    />

</x-form>
