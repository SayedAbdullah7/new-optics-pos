@php
    $isEdit = isset($model);
    $actionRoute = $isEdit
        ? route('admin.transactions.update', [$model->id])
        : route('admin.transactions.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<x-form :actionRoute="$actionRoute" :method="$method" :isEdit="$isEdit">

    <div class="row">
        <div class="col-md-6">
            <x-group-input-select
                label="Type"
                name="type"
                :value="$isEdit ? $model->type : (old('type') ?? 'income')"
                :options="['income' => 'Income', 'expense' => 'Expense']"
                required
            />
        </div>
        <div class="col-md-6">
            <x-group-input-select
                label="Account"
                name="account_id"
                :value="$isEdit ? $model->account_id : (old('account_id') ?? '')"
                :options="$accounts"
                required
            />
        </div>
    </div>

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
                name="paid_at"
                :value="$isEdit ? ($model->paid_at ? $model->paid_at->format('Y-m-d') : '') : (old('paid_at') ?? date('Y-m-d'))"
                required
            />
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <x-group-input-text
                label="Payment Method"
                name="payment_method"
                :value="$isEdit ? $model->payment_method : (old('payment_method') ?? '')"
                placeholder="e.g., Cash, Bank Transfer, Card"
            />
        </div>
        <div class="col-md-6">
            <x-group-input-text
                label="Reference"
                name="reference"
                :value="$isEdit ? $model->reference : (old('reference') ?? '')"
            />
        </div>
    </div>

    <x-group-input-textarea
        label="Description"
        name="description"
        :value="$isEdit ? $model->description : (old('description') ?? '')"
        rows="3"
    />

</x-form>





