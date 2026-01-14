<div class="d-flex justify-content-end gap-2">
    <a href="{{ route('admin.invoices.show', $model->id) }}" class="btn btn-icon btn-light-info btn-sm" title="View">
        <i class="fas fa-eye"></i>
    </a>
    <a href="#" class="btn btn-icon btn-light-warning btn-sm has_action" data-type="edit" data-action="{{ route('admin.invoices.edit', $model->id) }}" title="Edit">
        <i class="fas fa-edit"></i>
    </a>
    <a href="#" class="btn btn-icon btn-light-danger btn-sm delete_btn" data-action="{{ route('admin.invoices.destroy', $model->id) }}" title="Delete">
        <i class="fas fa-trash"></i>
    </a>
</div>





