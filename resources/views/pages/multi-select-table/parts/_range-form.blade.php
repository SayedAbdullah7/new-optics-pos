{{-- Range Form: نطاق SPH/CYL --}}
@php $presetId = isset($preset) && $preset ? $preset->id : null; @endphp
<div class="bg-light rounded p-4 mb-5">
    <form method="GET" action="{{ route('admin.multi-select-table.index') }}" class="row g-3 align-items-end">
        @if($presetId)<input type="hidden" name="preset" value="{{ $presetId }}">@endif
        <div class="col-auto">
            <label class="form-label fw-semibold text-gray-700 fs-7">SPH من</label>
            <input type="number" name="min_sph" step="0.25" class="form-control form-control-sm" style="width: 100px;" value="{{ $minSph }}">
        </div>
        <div class="col-auto">
            <label class="form-label fw-semibold text-gray-700 fs-7">SPH إلى</label>
            <input type="number" name="max_sph" step="0.25" class="form-control form-control-sm" style="width: 100px;" value="{{ $maxSph }}">
        </div>
        <div class="col-auto">
            <label class="form-label fw-semibold text-gray-700 fs-7">CYL من</label>
            <input type="number" name="min_cyl" step="0.25" class="form-control form-control-sm" style="width: 100px;" value="{{ $minCyl }}">
        </div>
        <div class="col-auto">
            <label class="form-label fw-semibold text-gray-700 fs-7">CYL إلى</label>
            <input type="number" name="max_cyl" step="0.25" class="form-control form-control-sm" style="width: 100px;" value="{{ $maxCyl }}">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="fas fa-sync-alt me-1"></i> تحديث
            </button>
        </div>
        <div class="col-auto ms-3">
            <span class="text-muted fs-8">اختيار سريع:</span>
            <a href="{{ route('admin.multi-select-table.index', array_filter(['min_sph' => -3, 'max_sph' => 3, 'min_cyl' => -3, 'max_cyl' => 0, 'preset' => $presetId])) }}" class="btn btn-sm btn-outline-secondary ms-1">±3</a>
            <a href="{{ route('admin.multi-select-table.index', array_filter(['min_sph' => -6, 'max_sph' => 6, 'min_cyl' => -4, 'max_cyl' => 0, 'preset' => $presetId])) }}" class="btn btn-sm btn-outline-secondary ms-1">±6</a>
            <a href="{{ route('admin.multi-select-table.index', array_filter(['min_sph' => -8, 'max_sph' => 8, 'min_cyl' => -6, 'max_cyl' => 0, 'preset' => $presetId])) }}" class="btn btn-sm btn-outline-secondary ms-1">±8</a>
        </div>
    </form>
</div>
