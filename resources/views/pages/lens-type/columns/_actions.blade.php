<div class="d-flex justify-content-end gap-2">
    <!-- View Button -->
    <a href="{{ route('admin.lens-types.show', $model->id) }}"
       class="btn btn-icon btn-light-info btn-sm"
       data-bs-toggle="tooltip"
       title="View type">
        <i class="ki-duotone ki-eye fs-5">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
        </i>
    </a>

    <!-- Edit Button -->
    <a href="#"
       class="btn btn-icon btn-light-warning btn-sm has_action"
       data-type="edit"
       data-action="{{ route('admin.lens-types.edit', $model->id) }}"
       data-bs-toggle="tooltip"
       title="Edit type">
        <i class="ki-duotone ki-pencil fs-5">
            <span class="path1"></span>
            <span class="path2"></span>
        </i>
    </a>

    <!-- Delete Button -->
    <a href="#"
       class="btn btn-icon btn-light-danger btn-sm delete_btn"
       data-action="{{ route('admin.lens-types.destroy', $model->id) }}"
       data-bs-toggle="tooltip"
       title="Delete type">
        <i class="ki-duotone ki-trash fs-5">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
            <span class="path4"></span>
            <span class="path5"></span>
        </i>
    </a>
</div>
