{{-- شريط الحفظ الثابت --}}
<div id="stickyBar" class="sticky-save-bar">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <span class="badge badge-light-primary fs-7 px-3 py-2" id="stickyCount">0 خلية محددة</span>
            <div id="presetMessage" class="mb-0" style="display:none;"></div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.lens-power-presets.index') }}" class="btn btn-light btn-sm">إلغاء</a>
            <button type="button" class="btn btn-primary px-5" id="btnSave">
                @if(isset($preset) && $preset)
                    <i class="fas fa-save me-2"></i> تحديث المحفوظة
                @else
                    <i class="fas fa-save me-2"></i> حفظ المحفوظة
                @endif
            </button>
        </div>
    </div>
</div>
