<div class="d-flex justify-content-end gap-2">
    <!-- View Button -->
    <a href="{{ route('admin.products.show', $model->id) }}"
       class="btn btn-icon btn-light-info btn-sm"
       data-bs-toggle="tooltip"
       title="View product">
        <i class="fas fa-eye"></i>
    </a>

    <!-- Edit Button -->
    <a href="#"
       class="btn btn-icon btn-light-warning btn-sm has_action"
       data-type="edit"
       data-action="{{ route('admin.products.edit', $model->id) }}"
       data-bs-toggle="tooltip"
       title="Edit product">
        <i class="fas fa-edit"></i>
    </a>

    <!-- Delete Button -->
    <a href="#"
       class="btn btn-icon btn-light-danger btn-sm delete_btn"
       data-action="{{ route('admin.products.destroy', $model->id) }}"
       data-bs-toggle="tooltip"
       title="Delete product">
        <i class="fas fa-trash"></i>
    </a>
</div>





