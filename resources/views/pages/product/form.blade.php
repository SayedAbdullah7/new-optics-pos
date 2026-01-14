@php
    $isEdit = isset($model);
    $actionRoute = $isEdit
        ? route('admin.products.update', [$model->id])
        : route('admin.products.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<x-form :actionRoute="$actionRoute" :method="$method" :isEdit="$isEdit">

    <div class="row">
        <div class="col-md-6">
            <!-- Item Code -->
            <x-group-input-number
                label="Item Code"
                name="item_code"
                :value="$isEdit ? $model->item_code : (old('item_code') ?? '')"
                required
            />
        </div>
        <div class="col-md-6">
            <!-- Category -->
            <x-group-input-select
                label="Category"
                name="category_id"
                :value="$isEdit ? $model->category_id : (old('category_id') ?? '')"
                :options="$categories"
                required
            />
        </div>
    </div>

    <!-- Product Name and Description for each locale -->
    @foreach(config('translatable.locales') as $locale)
        <div class="row">
            <div class="col-md-12">
                <h5 class="mb-3">{{ strtoupper($locale) }}</h5>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <!-- Name in {{ strtoupper($locale) }} -->
                <x-group-input-text
                    label="Name ({{ strtoupper($locale) }})"
                    name="{{ $locale }}[name]"
                    :value="$isEdit ? ($model->translate($locale)->name ?? '') : (old($locale.'.name') ?? '')"
                    required
                />
            </div>
            <div class="col-md-6">
                <!-- Description in {{ strtoupper($locale) }} -->
                <x-group-input-textarea
                    label="Description ({{ strtoupper($locale) }})"
                    name="{{ $locale }}[description]"
                    :value="$isEdit ? ($model->translate($locale)->description ?? '') : (old($locale.'.description') ?? '')"
                    rows="3"
                />
            </div>
        </div>
    @endforeach

    <div class="row">
        <div class="col-md-4">
            <!-- Purchase Price -->
            <x-group-input-number
                label="Purchase Price"
                name="purchase_price"
                :value="$isEdit ? $model->purchase_price : (old('purchase_price') ?? '')"
                step="0.01"
                min="0"
                required
            />
        </div>
        <div class="col-md-4">
            <!-- Sale Price -->
            <x-group-input-number
                label="Sale Price"
                name="sale_price"
                :value="$isEdit ? $model->sale_price : (old('sale_price') ?? '')"
                step="0.01"
                min="0"
                required
            />
        </div>
        <div class="col-md-4">
            <!-- Stock -->
            <x-group-input-number
                label="Stock"
                name="stock"
                :value="$isEdit ? $model->stock : (old('stock') ?? '0')"
                min="0"
                required
            />
        </div>
    </div>

    <!-- Image -->
    <div class="mb-3">
        <label class="form-label fw-semibold">Product Image</label>
        @if($isEdit && $model->image)
            <div class="mb-2">
                <img src="{{ $model->image_path }}" alt="Current Image" class="img-thumbnail" style="max-height: 100px;">
            </div>
        @endif
        <input type="file" name="image" class="form-control form-control-solid" accept="image/*">
        <small class="text-muted">Allowed: JPEG, PNG, JPG, GIF. Max: 2MB</small>
    </div>

</x-form>





