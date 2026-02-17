{{-- Card Header --}}
<div class="card-header border-0 pt-6">
    <div class="card-title">
        <a href="{{ route('admin.lens-power-presets.index') }}" class="btn btn-sm btn-icon btn-light me-3" title="رجوع">
            <i class="fas fa-arrow-right"></i>
        </a>
        <h3 class="fw-bold m-0">
            @if(isset($preset) && $preset)
                تعديل محفوظة
            @else
                إنشاء محفوظة جديدة
            @endif
        </h3>
    </div>
    <div class="card-toolbar gap-2">
        <span class="badge badge-primary fs-7 px-4 py-2" id="selectedCount" style="display:none;">
            <i class="fas fa-check-circle me-1"></i>
            محدد: <span id="countNum">0</span>
        </span>
        <button type="button" class="btn btn-sm btn-light-primary" id="selectAll" title="تحديد الكل">
            <i class="fas fa-check-double me-1"></i> تحديد الكل
        </button>
        <button type="button" class="btn btn-sm btn-light-danger" id="clearSelection" title="مسح التحديد">
            <i class="fas fa-times me-1"></i> مسح
        </button>
        <button type="button" class="btn btn-sm btn-light-success" id="exportSelected" title="تصدير القيم المحددة">
            <i class="fas fa-file-export me-1"></i> تصدير
        </button>
    </div>
</div>
