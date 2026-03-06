<div class="d-flex justify-content-end gap-2">
    <!-- Edit Button -->
    @can('update-roles')
    <a href="#"
       class="btn btn-icon btn-light-warning btn-sm has_action"
       data-type="edit"
       data-action="{{ route('admin.roles.edit', $model->id) }}"
       data-bs-toggle="tooltip"
       title="Edit role">
        <i class="ki-duotone ki-pencil fs-5">
            <span class="path1"></span>
            <span class="path2"></span>
        </i>
    </a>
    @endcan

    <!-- Delete Button -->
    @can('delete-roles')
    <a href="#"
       class="btn btn-icon btn-light-danger btn-sm delete_btn"
       data-action="{{ route('admin.roles.destroy', $model->id) }}"
       data-bs-toggle="tooltip"
       title="Delete role">
        <i class="ki-duotone ki-trash fs-5">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
            <span class="path4"></span>
            <span class="path5"></span>
        </i>
    </a>
    @endcan
</div>
