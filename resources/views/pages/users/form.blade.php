@php
    $isEdit = isset($model);
    $actionRoute = $isEdit
        ? route('admin.users.update', [$model->id])
        : route('admin.users.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<x-form :actionRoute="$actionRoute" :method="$method" :isEdit="$isEdit">
    <div class="row g-9 responsive-modal-row">
        <!-- Account Information -->
        <div class="col-12 responsive-column border-end-logic">
            <div class="mb-8">
                <h4 class="text-primary d-flex align-items-center mb-4">
                    <i class="ki-duotone ki-user fs-1 me-2 text-primary">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    Account Information
                </h4>
                <div class="separator separator-dashed mb-6"></div>
            </div>

            <div class="row g-9 mb-7">
                <div class="col-md-6 fv-row">
                    <x-group-input-text
                        label="First Name"
                        name="first_name"
                        :value="$isEdit ? $model->first_name : (old('first_name') ?? '')"
                        required
                    />
                </div>
                <div class="col-md-6 fv-row">
                    <x-group-input-text
                        label="Last Name"
                        name="last_name"
                        :value="$isEdit ? $model->last_name : (old('last_name') ?? '')"
                        required
                    />
                </div>
            </div>

            <x-group-input-text
                label="Email Address"
                name="email"
                type="email"
                placeholder="example@domain.com"
                :value="$isEdit ? $model->email : (old('email') ?? '')"
                required
            />
        </div>

        <!-- Security & Roles -->
        <div class="col-12 responsive-column">
            <div class="mb-8">
                <h4 class="text-primary d-flex align-items-center mb-4">
                    <i class="ki-duotone ki-shield-tick fs-1 me-2 text-primary">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    Security & Access
                </h4>
                <div class="separator separator-dashed mb-6"></div>
            </div>

            <div class="row g-9 mb-7">
                <div class="col-md-6 fv-row">
                    <x-group-input-text
                        label="Password"
                        name="password"
                        type="password"
                        :required="!$isEdit"
                    />
                </div>
                <div class="col-md-6 fv-row">
                    <x-group-input-text
                        label="Confirm Password"
                        name="password_confirmation"
                        type="password"
                        :required="!$isEdit"
                    />
                </div>
            </div>
            
            @if($isEdit)
                <div class="text-muted fs-7 mb-7 mt-n5">Leave blank to keep current password.</div>
            @endif

            <div class="fv-row mb-7">
                <label class="fw-semibold fs-6 mb-2">Assign Roles</label>
                <select name="roles[]" class="form-select form-select-solid" data-control="select2" data-placeholder="Select roles" multiple="multiple">
                    <option></option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" 
                            @if($isEdit && in_array($role->id, $userRoles)) selected @endif>
                            {{ $role->display_name }} ({{ $role->name }})
                        </option>
                    @endforeach
                </select>
                <div class="text-muted fs-7 mt-2">Pick one or more roles for this user.</div>
            </div>
        </div>
    </div>
</x-form>

<script>
    $(document).ready(function() {
        if (typeof $.fn.select2 !== 'undefined') {
            $('[data-control="select2"]').select2();
        }
    });
</script>

<style>
    /* Default behavior: Stacked (1 column) */
    .responsive-column {
        width: 100%;
    }

    /* Behavior when modal is expanded: 2 columns */
    .modal-dialog-expanded .responsive-column {
        width: 50% !important;
    }

    /* Fixed Border handling */
    .modal-dialog-expanded .border-end-logic {
        border-right: 1px dashed #eff2f5 !important;
    }
    
    /* Ensure rows inside columns still use grid */
    .responsive-modal-row {
        display: flex;
        flex-wrap: wrap;
    }
</style>
