<div class="d-flex justify-content-end gap-2">
    @can('update-expenses')
    <a href="#" class="btn btn-icon btn-light-warning btn-sm has_action" data-type="edit" data-action="{{ route('admin.expenses.edit', $model->id) }}" title="Edit">
        <i class="fas fa-edit"></i>
    </a>
    @endcan
    @can('delete-expenses')
    <a href="#" class="btn btn-icon btn-light-danger btn-sm delete_btn" data-action="{{ route('admin.expenses.destroy', $model->id) }}" title="Delete">
        <i class="fas fa-trash"></i>
    </a>
    @endcan
</div>





