{{-- الخطوة 1: اسم المحفوظة --}}
@if(isset($preset) && $preset)
    <div class="fv-row mb-5">
        <label class="form-label fw-semibold fs-6 text-gray-700">اسم المحفوظة</label>
        <input type="text" class="form-control form-control-solid bg-light" value="{{ $preset->name }}" disabled readonly>
    </div>
@else
    <div class="fv-row mb-5">
        <label class="form-label required fw-semibold fs-6 text-gray-700">اسم المحفوظة</label>
        <input type="text" id="presetName" class="form-control" placeholder="أدخل اسم المحفوظة" maxlength="255">
    </div>
@endif

<div class="separator separator-dashed mb-5"></div>
