@php
    $isEdit = isset($model);
    $actionRoute = $isEdit
        ? route('admin.vendors.update', [$model->id])
        : route('admin.vendors.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<x-form :actionRoute="$actionRoute" :method="$method" :isEdit="$isEdit">

    <x-group-input-text
        label="Vendor Name"
        name="name"
        :value="$isEdit ? $model->name : (old('name') ?? '')"
        required
    />

    <div class="mb-3">
        <label class="form-label fw-semibold">Phone Numbers</label>
        <div id="phoneContainer">
            @if($isEdit && is_array($model->phone) && count($model->phone) > 0)
                @foreach($model->phone as $phone)
                    <div class="input-group mb-2 phone-row">
                        <input type="text" name="phone[]" class="form-control form-control-solid" value="{{ $phone }}">
                        <button type="button" class="btn btn-light-danger" onclick="removePhone(this)">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endforeach
            @else
                <div class="input-group mb-2 phone-row">
                    <input type="text" name="phone[]" class="form-control form-control-solid" placeholder="Enter phone">
                    <button type="button" class="btn btn-light-danger" onclick="removePhone(this)">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif
        </div>
        <button type="button" class="btn btn-sm btn-light-primary" onclick="addPhone()">
            <i class="fas fa-plus me-1"></i>Add Phone
        </button>
    </div>

    <x-group-input-textarea
        label="Address"
        name="address"
        :value="$isEdit ? $model->address : (old('address') ?? '')"
        rows="3"
    />

</x-form>

<script>
function addPhone() {
    const container = document.getElementById('phoneContainer');
    const row = document.createElement('div');
    row.className = 'input-group mb-2 phone-row';
    row.innerHTML = `
        <input type="text" name="phone[]" class="form-control form-control-solid" placeholder="Enter phone">
        <button type="button" class="btn btn-light-danger" onclick="removePhone(this)">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(row);
}

function removePhone(btn) {
    const container = document.getElementById('phoneContainer');
    if (container.querySelectorAll('.phone-row').length > 1) {
        btn.closest('.phone-row').remove();
    }
}
</script>





