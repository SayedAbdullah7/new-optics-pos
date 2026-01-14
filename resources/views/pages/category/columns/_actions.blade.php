<div class="d-flex justify-content-end gap-2">
    <!-- Edit Button -->
    <a href="#"
       class="btn btn-icon btn-light-warning btn-sm has_action"
       data-type="edit"
       data-action="{{ route('admin.categories.edit', $model->id) }}"
       data-bs-toggle="tooltip"
       title="Edit category">
        <i class="fas fa-edit"></i>
    </a>

    <!-- Delete Button -->
    <a href="#"
       class="btn btn-icon btn-light-danger btn-sm delete_btn"
       data-action="{{ route('admin.categories.destroy', $model->id) }}"
       data-bs-toggle="tooltip"
       title="Delete category">
        <i class="fas fa-trash"></i>
    </a>
</div>





