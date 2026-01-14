@php
    $isEdit = isset($model);
    $actionRoute = $isEdit
        ? route('admin.expenses.update', [$model->id])
        : route('admin.expenses.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<x-form :actionRoute="$actionRoute" :method="$method" :isEdit="$isEdit">

    <x-group-input-text
        label="Title"
        name="title"
        :value="$isEdit ? $model->title : (old('title') ?? '')"
        required
    />

    <div class="row">
        <div class="col-md-6">
            <x-group-input-number
                label="Amount"
                name="amount"
                :value="$isEdit ? $model->amount : (old('amount') ?? '')"
                step="0.01"
                min="0.01"
                required
            />
        </div>
        <div class="col-md-6">
            <x-group-input-date
                label="Date"
                name="date"
                :value="$isEdit ? ($model->date ? $model->date->format('Y-m-d') : '') : (old('date') ?? date('Y-m-d'))"
                required
            />
        </div>
    </div>

    <x-group-input-text
        label="Category"
        name="category"
        :value="$isEdit ? $model->category : (old('category') ?? '')"
        placeholder="e.g., Utilities, Rent, Supplies"
    />

    <x-group-input-textarea
        label="Description"
        name="description"
        :value="$isEdit ? $model->description : (old('description') ?? '')"
        rows="3"
    />

</x-form>





