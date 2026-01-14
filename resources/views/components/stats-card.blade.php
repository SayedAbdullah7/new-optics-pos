@props([
    'value',
    'label',
    'variant' => 'primary',  // primary, success, warning, danger, info
    'icon' => 'chart-simple', // ki-duotone icon name
])

<div class="card bg-{{ $variant }} text-white h-100">
    <div class="card-body py-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="mb-0 text-white">{{ $value }}</h3>
                <small class="text-white opacity-75">{{ $label }}</small>
            </div>
            <i class="ki-duotone ki-{{ $icon }} fs-2x opacity-50">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
                <span class="path4"></span>
                <span class="path5"></span>
            </i>
        </div>
    </div>
</div>




