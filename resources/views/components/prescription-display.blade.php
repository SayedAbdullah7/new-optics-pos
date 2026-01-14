@props([
    'paper',
    'showDate' => true,
    'compact' => false,
])

@if($paper)
    <div class="row" style="direction: ltr;">
        <!-- Right Eye (OD) -->
        <div class="col-md-6 {{ !$compact ? 'border-end' : '' }}">
            <h6 class="text-danger text-center mb-3">
                <i class="ki-duotone ki-eye fs-5 me-1">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                </i>
                Right Eye (OD)
            </h6>
            <div class="row text-center">
                <div class="col-4">
                    <label class="text-muted small d-block">SPH</label>
                    <div class="fw-bold {{ $compact ? 'fs-6' : 'fs-5' }}">{{ $paper->R_sph ?: '-' }}</div>
                </div>
                <div class="col-4">
                    <label class="text-muted small d-block">CYL</label>
                    <div class="fw-bold {{ $compact ? 'fs-6' : 'fs-5' }}">{{ $paper->R_cyl ?: '-' }}</div>
                </div>
                <div class="col-4">
                    <label class="text-muted small d-block">AXIS</label>
                    <div class="fw-bold {{ $compact ? 'fs-6' : 'fs-5' }}">{{ $paper->R_axis ?: '-' }}</div>
                </div>
            </div>
            @if(!$compact)
                <div class="text-center mt-3">
                    <label class="text-muted small d-block">Addition (ADD)</label>
                    <div class="fw-bold fs-5">{{ $paper->addtion ?: '-' }}</div>
                </div>
            @endif
        </div>

        <!-- Left Eye (OS) -->
        <div class="col-md-6">
            <h6 class="text-danger text-center mb-3">
                <i class="ki-duotone ki-eye fs-5 me-1">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                </i>
                Left Eye (OS)
            </h6>
            <div class="row text-center">
                <div class="col-4">
                    <label class="text-muted small d-block">SPH</label>
                    <div class="fw-bold {{ $compact ? 'fs-6' : 'fs-5' }}">{{ $paper->L_sph ?: '-' }}</div>
                </div>
                <div class="col-4">
                    <label class="text-muted small d-block">CYL</label>
                    <div class="fw-bold {{ $compact ? 'fs-6' : 'fs-5' }}">{{ $paper->L_cyl ?: '-' }}</div>
                </div>
                <div class="col-4">
                    <label class="text-muted small d-block">AXIS</label>
                    <div class="fw-bold {{ $compact ? 'fs-6' : 'fs-5' }}">{{ $paper->L_axis ?: '-' }}</div>
                </div>
            </div>
            @if(!$compact)
                <div class="text-center mt-3">
                    <label class="text-muted small d-block">IPD</label>
                    <div class="fw-bold fs-5">{{ $paper->ipd ?: '-' }}</div>
                </div>
            @endif
        </div>
    </div>

    @if($compact)
        <div class="row mt-2 text-center">
            <div class="col-6">
                <label class="text-muted small d-block">ADD</label>
                <span class="fw-bold">{{ $paper->addtion ?: '-' }}</span>
            </div>
            <div class="col-6">
                <label class="text-muted small d-block">IPD</label>
                <span class="fw-bold">{{ $paper->ipd ?: '-' }}</span>
            </div>
        </div>
    @endif

    @if($showDate)
        <div class="text-muted text-center mt-3 small border-top pt-2">
            <i class="ki-duotone ki-calendar fs-6 me-1">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
            {{ $paper->created_at->format('M d, Y H:i') }}
        </div>
    @endif
@else
    <div class="text-center text-muted py-4">
        <i class="ki-duotone ki-eye fs-3x mb-3 opacity-50">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
        </i>
        <p class="mb-0">No prescription recorded</p>
    </div>
@endif




