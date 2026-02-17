{{-- Formula Selection: تحديد بالمعادلة --}}
<div class="card border border-primary border-dashed mb-5">
    <div class="card-header py-3 bg-light-primary cursor-pointer" data-bs-toggle="collapse" data-bs-target="#formulaPanel" aria-expanded="true">
        <h4 class="card-title m-0 fs-6 d-flex align-items-center">
            <i class="fas fa-calculator text-primary me-2"></i>
            تحديد بالمعادلة
            <i class="fas fa-chevron-down ms-auto text-muted fs-8 collapse-icon"></i>
        </h4>
    </div>
    <div class="collapse show" id="formulaPanel">
        <div class="card-body py-4">
            <div class="row g-3 align-items-end">
                {{-- SPH Range --}}
                <div class="col-12">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-success px-3">SPH</span>
                        <span class="text-muted fs-8">(كروية)</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <label class="text-gray-600 fs-7" style="min-width: 30px;">من</label>
                        <input type="number" id="fSphFrom" step="0.25" class="form-control form-control-sm" style="width: 100px;" placeholder="مثال: -5">
                        <label class="text-gray-600 fs-7" style="min-width: 30px;">إلى</label>
                        <input type="number" id="fSphTo" step="0.25" class="form-control form-control-sm" style="width: 100px;" placeholder="مثال: +5">
                    </div>
                </div>
                {{-- CYL Range --}}
                <div class="col-12">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-info px-3">CYL</span>
                        <span class="text-muted fs-8">(اسطوانة)</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <label class="text-gray-600 fs-7" style="min-width: 30px;">من</label>
                        <input type="number" id="fCylFrom" step="0.25" class="form-control form-control-sm" style="width: 100px;" placeholder="مثال: -4">
                        <label class="text-gray-600 fs-7" style="min-width: 30px;">إلى</label>
                        <input type="number" id="fCylTo" step="0.25" class="form-control form-control-sm" style="width: 100px;" placeholder="مثال: 0">
                    </div>
                </div>
                {{-- Total Range (optional) --}}
                <div class="col-12">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="fTotalEnabled">
                            <label class="form-check-label" for="fTotalEnabled">
                                <span class="badge bg-warning text-dark px-3">Total (SPH+CYL)</span>
                                <span class="text-muted fs-8">(اختياري)</span>
                            </label>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2" id="totalInputs">
                        <label class="text-gray-600 fs-7" style="min-width: 30px;">من</label>
                        <input type="number" id="fTotalFrom" step="0.25" class="form-control form-control-sm" style="width: 100px;" placeholder="مثال: -6" disabled>
                        <label class="text-gray-600 fs-7" style="min-width: 30px;">إلى</label>
                        <input type="number" id="fTotalTo" step="0.25" class="form-control form-control-sm" style="width: 100px;" placeholder="مثال: +6" disabled>
                    </div>
                </div>
                {{-- Action Buttons --}}
                <div class="col-12 pt-2">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-primary" id="applyFormula">
                            <i class="fas fa-check me-1"></i> تطبيق (استبدال التحديد)
                        </button>
                        <button type="button" class="btn btn-sm btn-light-primary" id="addFormula">
                            <i class="fas fa-plus me-1"></i> إضافة للتحديد الحالي
                        </button>
                        <button type="button" class="btn btn-sm btn-light-danger" id="subtractFormula">
                            <i class="fas fa-minus me-1"></i> حذف من التحديد
                        </button>
                    </div>
                    <div id="formulaResult" class="mt-2" style="display:none;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
