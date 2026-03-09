@props([
    'paper',
    'showDate' => true,
    'compact' => false,
])

@if($paper)
    <div class="row g-0 flex-column flex-md-row rounded border border-gray-300 bg-white mb-3" style="direction: ltr;">
        <!-- Right Eye (OD) -->
        <div class="col-12 col-md-6 border-bottom border-md-bottom-0 border-md-end border-gray-300 p-2 position-relative bg-danger bg-opacity-5">
           <h6 class="text-danger text-center fs-7 fw-bolder mb-2">Right (OD)</h6>
           <div class="d-flex justify-content-between text-center px-1">
               <div class="flex-column w-33">
                   <span class="fs-9 text-gray-500 d-block mb-1">SPH</span>
                   <span class="fs-7 fw-bold text-gray-800">{{ $paper->R_sph ?: '-' }}</span>
               </div>
               <div class="flex-column w-33 px-1 border-start border-end border-gray-300">
                   <span class="fs-9 text-gray-500 d-block mb-1">CYL</span>
                   <span class="fs-7 fw-bold text-gray-800">{{ $paper->R_cyl ?: '-' }}</span>
               </div>
               <div class="flex-column w-33">
                   <span class="fs-9 text-gray-500 d-block mb-1">AXIS</span>
                   <span class="fs-7 fw-bold text-gray-800">{{ $paper->R_axis ?: '-' }}</span>
               </div>
           </div>
           @if(!$compact)
               <div class="text-center mt-3">
                   <span class="fs-9 text-gray-500 d-block mb-1">Addition (ADD)</span>
                   <span class="fs-7 fw-bolder text-gray-800">{{ $paper->addtion ?: '-' }}</span>
               </div>
           @endif
           <div id="prescription_range_badge_right" class="mt-2 text-center fs-8"></div>
        </div>
        
        <!-- Left Eye (OS) -->
        <div class="col-12 col-md-6 p-2 position-relative bg-success bg-opacity-5">
           <h6 class="text-success text-center fs-7 fw-bolder mb-2">Left (OS)</h6>
           <div class="d-flex justify-content-between text-center px-1">
               <div class="flex-column w-33">
                   <span class="fs-9 text-gray-500 d-block mb-1">SPH</span>
                   <span class="fs-7 fw-bold text-gray-800">{{ $paper->L_sph ?: '-' }}</span>
               </div>
               <div class="flex-column w-33 px-1 border-start border-end border-gray-300">
                   <span class="fs-9 text-gray-500 d-block mb-1">CYL</span>
                   <span class="fs-7 fw-bold text-gray-800">{{ $paper->L_cyl ?: '-' }}</span>
               </div>
               <div class="flex-column w-33">
                   <span class="fs-9 text-gray-500 d-block mb-1">AXIS</span>
                   <span class="fs-7 fw-bold text-gray-800">{{ $paper->L_axis ?: '-' }}</span>
               </div>
           </div>
           @if(!$compact)
               <div class="text-center mt-3">
                   <span class="fs-9 text-gray-500 d-block mb-1">IPD</span>
                   <span class="fs-7 fw-bolder text-gray-800">{{ $paper->ipd ?: '-' }}</span>
               </div>
           @endif
           <div id="prescription_range_badge_left" class="mt-2 text-center fs-8"></div>
        </div>
    </div>

    @if($compact)
        <div class="d-flex justify-content-around bg-light py-2 rounded border border-gray-300">
            <div class="text-center">
                <span class="fs-9 text-gray-500 fw-bold me-1">ADD:</span>
                <span class="fs-8 fw-bolder text-gray-800">{{ $paper->addtion ?: '-' }}</span>
            </div>
            <div class="text-center border-start border-gray-300 ps-4">
                <span class="fs-9 text-gray-500 fw-bold me-1">IPD:</span>
                <span class="fs-8 fw-bolder text-gray-800">{{ $paper->ipd ?: '-' }}</span>
            </div>
        </div>
    @endif

    @if($showDate)
        <div class="text-center mt-3 pt-2 w-100">
            <span class="fs-9 text-gray-500 fw-bold me-1">DATE:</span>
            <span class="fs-8 fw-bolder text-gray-800">{{ $paper->created_at->format('M d, Y H:i') }}</span>
        </div>
    @endif
@else
    <div class="text-center text-muted py-5 animate__animated animate__fadeIn">
        <i class="ki-duotone ki-search-list fs-3x text-gray-400 mb-3 block"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
        <div class="fs-6 fw-semibold">No Prescription found</div>
        <div class="fs-8 text-gray-500 mt-1">Select a client to view</div>
    </div>
@endif
