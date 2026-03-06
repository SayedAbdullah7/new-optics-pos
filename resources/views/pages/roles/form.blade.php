@php
    $isEdit = isset($model);
    $actionRoute = $isEdit
        ? route('admin.roles.update', [$model->id])
        : route('admin.roles.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<x-form :actionRoute="$actionRoute" :method="$method" :isEdit="$isEdit">
    <div class="row g-9 responsive-modal-row">
        <!-- Role Information -->
        <div class="col-12 responsive-column-left border-end-logic">
            <div class="mb-8">
                <h4 class="text-primary d-flex align-items-center mb-4">
                    <i class="ki-duotone ki-security-user fs-1 me-2 text-primary">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    Role Details
                </h4>
                <div class="separator separator-dashed mb-6"></div>
            </div>

            <x-group-input-text
                label="Role English Slug (e.g. admin)"
                name="name"
                placeholder="slug-name"
                :value="$isEdit ? $model->name : (old('name') ?? '')"
                required
            />
            
            <x-group-input-text
                label="Display Name"
                name="display_name"
                placeholder="Enter role display name"
                :value="$isEdit ? $model->display_name : (old('display_name') ?? '')"
                required
            />

            <x-group-input-textarea
                label="Role Description"
                name="description"
                rows="4"
                placeholder="Brief description of what this role can do"
                :value="$isEdit ? $model->description : (old('description') ?? '')"
            />
        </div>

        <!-- Permissions -->
        <div class="col-12 responsive-column-right ps-dynamic">
            <div class="mb-8">
                <h4 class="text-primary d-flex align-items-center mb-4">
                    <i class="ki-duotone ki-key fs-1 me-2 text-primary">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    Module Permissions
                </h4>
                <div class="separator separator-dashed mb-6"></div>
            </div>

            <div class="d-flex align-items-center justify-content-between mb-4 bg-light-primary p-3 rounded">
                <span class="fw-bold text-gray-700">Grant All Permissions</span>
                <div class="form-check form-check-custom form-check-solid">
                    <input class="form-check-input" type="checkbox" id="checkAllPermissions" />
                    <label class="form-check-label fw-bold" for="checkAllPermissions">Check All</label>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                    <thead>
                        <tr class="fw-bold text-muted">
                            <th class="min-w-150px">Module</th>
                            <th class="min-w-300px">Action Permissions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($permissions as $module => $modulePermissions)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="form-check form-check-custom form-check-solid me-3">
                                        <input class="form-check-input check-group" type="checkbox" data-group="{{ $module }}" />
                                    </div>
                                    <span class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6 text-capitalize">{{ $module }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-5">
                                    @foreach($modulePermissions as $permission)
                                    <div class="form-check form-check-custom form-check-solid">
                                        <input class="form-check-input permission-checkbox" type="checkbox" name="permissions[]" 
                                               value="{{ $permission->name }}" id="perm_{{ $permission->id }}"
                                               data-group="{{ $module }}"
                                               @if($isEdit && in_array($permission->name, $rolePermissions)) checked @endif />
                                        <label class="form-check-label text-gray-700 fw-semibold" for="perm_{{ $permission->id }}">
                                            @php
                                                $action = str_replace("-".$module, "", $permission->name);
                                            @endphp
                                            {{ str_replace('-', ' ', ucfirst($action)) }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="text-muted fs-7 mt-2">Manage the specific permissions for this role.</div>
        </div>
    </div>
</x-form>

<script>
    $(document).ready(function() {
        // Toggle all permissions
        $('#checkAllPermissions').on('change', function() {
            const isChecked = $(this).is(':checked');
            $('.permission-checkbox').prop('checked', isChecked);
            $('.check-group').prop('checked', isChecked);
        });

        // Toggle module group permissions
        $('.check-group').on('change', function() {
            const group = $(this).data('group');
            const isChecked = $(this).is(':checked');
            $(`.permission-checkbox[data-group="${group}"]`).prop('checked', isChecked);
            updateCheckAll();
        });

        // Update group checkbox when individual permissions changed
        $('.permission-checkbox').on('change', function() {
            const group = $(this).data('group');
            const total = $(`.permission-checkbox[data-group="${group}"]`).length;
            const checked = $(`.permission-checkbox[data-group="${group}"]:checked`).length;
            
            $(`.check-group[data-group="${group}"]`).prop('checked', total === checked);
            updateCheckAll();
        });

        function updateCheckAll() {
            const total = $('.permission-checkbox').length;
            const checked = $('.permission-checkbox:checked').length;
            $('#checkAllPermissions').prop('checked', total === checked);
        }

        // Initialize group checkboxes state
        $('.check-group').each(function() {
            const group = $(this).data('group');
            const total = $(`.permission-checkbox[data-group="${group}"]`).length;
            const checked = $(`.permission-checkbox[data-group="${group}"]:checked`).length;
            $(this).prop('checked', total === checked);
        });
        updateCheckAll();
    });
</script>

<style>
    /* Default behavior: Stacked (1 column) */
    .responsive-column-left, .responsive-column-right {
        width: 100%;
    }

    /* Behavior when modal is expanded */
    .modal-dialog-expanded .responsive-column-left {
        width: 41.66666667% !important; /* col-5 equivalent */
    }
    .modal-dialog-expanded .responsive-column-right {
        width: 58.33333333% !important; /* col-7 equivalent */
    }

    /* Padding for rights side when expanded */
    .modal-dialog-expanded .ps-dynamic {
        padding-left: 2.5rem !important;
    }

    /* Fixed Border handling */
    .modal-dialog-expanded .border-end-logic {
        border-right: 1px dashed #eff2f5 !important;
    }
    
    .responsive-modal-row {
        display: flex;
        flex-wrap: wrap;
    }
</style>
