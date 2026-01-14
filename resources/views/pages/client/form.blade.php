@php
    $isEdit = isset($model);
    $actionRoute = $isEdit
        ? route('admin.clients.update', [$model->id])
        : route('admin.clients.store');
    $method = $isEdit ? 'PUT' : 'POST';
    
    // Get the latest paper for editing
    $paper = $isEdit ? $model->papers()->latest()->first() : null;
@endphp

<x-form :actionRoute="$actionRoute" :method="$method" :isEdit="$isEdit">

    <div class="row">
        <!-- Client Information Section -->
        <div class="col-md-5">
            <h4 class="mb-4 text-primary">
                <i class="ki-duotone ki-user fs-2 me-2">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
                Client Information
            </h4>

            <!-- Client Name -->
            <x-group-input-text
                label="Client Name"
                name="name"
                :value="$isEdit ? $model->name : (old('name') ?? '')"
                required
            />

            <!-- Phone Numbers -->
            <div class="fv-row mb-7">
                <label class="fw-semibold fs-6 mb-2">Phone Numbers</label>
                <div id="phoneContainer">
                    @if($isEdit && is_array($model->phone) && count($model->phone) > 0)
                        @foreach($model->phone as $index => $phone)
                            <div class="input-group mb-2 phone-row">
                                <input type="text" name="phone[]" class="form-control form-control-solid" value="{{ $phone }}" placeholder="Enter phone number">
                                <button type="button" class="btn btn-icon btn-light-danger remove-phone" onclick="removePhone(this)">
                                    <i class="ki-duotone ki-cross fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                </button>
                            </div>
                        @endforeach
                    @else
                        <div class="input-group mb-2 phone-row">
                            <input type="text" name="phone[]" class="form-control form-control-solid" placeholder="Enter phone number">
                            <button type="button" class="btn btn-icon btn-light-danger remove-phone" onclick="removePhone(this)">
                                <i class="ki-duotone ki-cross fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </button>
                        </div>
                    @endif
                </div>
                <button type="button" class="btn btn-sm btn-light-primary" onclick="addPhone()">
                    <i class="ki-duotone ki-plus fs-2"></i>Add Phone
                </button>
            </div>

            <!-- Address -->
            <x-group-input-textarea
                label="Address"
                name="address"
                :value="$isEdit ? $model->address : (old('address') ?? '')"
                rows="3"
            />
        </div>

        <!-- Prescription (Paper) Section -->
        <div class="col-md-7">
            <h4 class="mb-4 text-primary">
                <i class="ki-duotone ki-eye fs-2 me-2">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                </i>
                Prescription (الروشتة)
            </h4>

            <div class="row" style="direction: ltr;">
                <!-- Right Eye -->
                <div class="col-md-6 border-end">
                    <h5 class="text-danger mb-3 text-center">Right Eye (OD)</h5>
                    <div class="row">
                        <div class="col-4">
                            <div class="fv-row mb-3">
                                <label class="fw-semibold fs-6 mb-2">SPH</label>
                                <input type="number" 
                                       name="R_sph" 
                                       step="0.25" 
                                       class="form-control form-control-solid" 
                                       value="{{ old('R_sph', $paper?->getRawRSph()) }}"
                                       autocomplete="off">
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="fv-row mb-3">
                                <label class="fw-semibold fs-6 mb-2">CYL</label>
                                <input type="number" 
                                       name="R_cyl" 
                                       step="0.25" 
                                       class="form-control form-control-solid" 
                                       value="{{ old('R_cyl', $paper?->getRawRCyl()) }}"
                                       autocomplete="off">
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="fv-row mb-3">
                                <label class="fw-semibold fs-6 mb-2">AXIS</label>
                                <input type="number" 
                                       name="R_axis" 
                                       min="0" 
                                       max="180" 
                                       class="form-control form-control-solid" 
                                       value="{{ old('R_axis', $paper?->R_axis) }}"
                                       autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="fv-row mb-3">
                        <label class="fw-semibold fs-6 mb-2">Addition (ADD)</label>
                        <input type="number" 
                               name="addtion" 
                               step="0.25" 
                               min="0" 
                               class="form-control form-control-solid" 
                               value="{{ old('addtion', $paper?->getRawAddtion()) }}"
                               autocomplete="off">
                    </div>
                </div>

                <!-- Left Eye -->
                <div class="col-md-6">
                    <h5 class="text-danger mb-3 text-center">Left Eye (OS)</h5>
                    <div class="row">
                        <div class="col-4">
                            <div class="fv-row mb-3">
                                <label class="fw-semibold fs-6 mb-2">SPH</label>
                                <input type="number" 
                                       name="L_sph" 
                                       step="0.25" 
                                       class="form-control form-control-solid" 
                                       value="{{ old('L_sph', $paper?->getRawLSph()) }}"
                                       autocomplete="off">
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="fv-row mb-3">
                                <label class="fw-semibold fs-6 mb-2">CYL</label>
                                <input type="number" 
                                       name="L_cyl" 
                                       step="0.25" 
                                       class="form-control form-control-solid" 
                                       value="{{ old('L_cyl', $paper?->getRawLCyl()) }}"
                                       autocomplete="off">
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="fv-row mb-3">
                                <label class="fw-semibold fs-6 mb-2">AXIS</label>
                                <input type="number" 
                                       name="L_axis" 
                                       min="0" 
                                       max="180" 
                                       class="form-control form-control-solid" 
                                       value="{{ old('L_axis', $paper?->L_axis) }}"
                                       autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="fv-row mb-3">
                        <label class="fw-semibold fs-6 mb-2">IPD (Pupillary Distance)</label>
                        <input type="number" 
                               name="ipd" 
                               min="0" 
                               step="0.5"
                               class="form-control form-control-solid" 
                               value="{{ old('ipd', $paper?->ipd) }}"
                               autocomplete="off">
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-form>

<script>
function addPhone() {
    const container = document.getElementById('phoneContainer');
    const row = document.createElement('div');
    row.className = 'input-group mb-2 phone-row';
    row.innerHTML = `
        <input type="text" name="phone[]" class="form-control form-control-solid" placeholder="Enter phone number">
        <button type="button" class="btn btn-icon btn-light-danger remove-phone" onclick="removePhone(this)">
            <i class="ki-duotone ki-cross fs-2">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
        </button>
    `;
    container.appendChild(row);
}

function removePhone(btn) {
    const container = document.getElementById('phoneContainer');
    const rows = container.querySelectorAll('.phone-row');
    if (rows.length > 1) {
        btn.closest('.phone-row').remove();
    }
}
</script>
