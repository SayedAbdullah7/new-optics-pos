<div class="d-flex justify-content-end gap-2">
    <a href="#"
       class="btn btn-icon btn-light-warning btn-sm has_action"
       data-type="edit"
       data-action="{{ route('admin.lens-power-presets.edit', $model) }}"
       data-bs-toggle="tooltip"
       title="تعديل الاسم">
        <i class="ki-duotone ki-pencil fs-5">
            <span class="path1"></span>
            <span class="path2"></span>
        </i>
    </a>
    <a href="{{ route('admin.multi-select-table.index', ['preset' => $model->id]) }}"
       class="btn btn-icon btn-light-primary btn-sm"
       data-bs-toggle="tooltip"
       title="تحديث القيم">
        <i class="ki-duotone ki-grid fs-5">
            <span class="path1"></span>
            <span class="path2"></span>
        </i>
    </a>
    <a href="#"
       class="btn btn-icon btn-light-danger btn-sm delete_btn"
       data-action="{{ route('admin.multi-select-table.destroy', $model) }}"
       data-bs-toggle="tooltip"
       title="حذف">
        <i class="ki-duotone ki-trash fs-5">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
            <span class="path4"></span>
            <span class="path5"></span>
        </i>
    </a>
</div>
