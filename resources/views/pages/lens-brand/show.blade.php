<x-app-layout>
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-xxl">
    <!-- Page Header -->
    <div class="card mb-5">
        <div class="card-body py-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="symbol symbol-60px symbol-circle me-4">
                        <div class="symbol-label bg-light-primary text-primary fs-2 fw-bold">
                            {{ strtoupper(substr($lensCategory->brand_name, 0, 1)) }}
                        </div>
                    </div>
                    <div>
                        <h3 class="mb-1 text-gray-900">{{ $lensCategory->brand_name }}</h3>
                        <span class="text-muted fs-7">Brand #{{ $lensCategory->id }} • Created {{ $lensCategory->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <x-action-button
                        :action="route('admin.lens-brands.edit', ['lens_category' => $lensCategory->id])"
                        type="edit"
                        variant="warning"
                        icon="pencil"
                        label="Edit Brand"
                    />
                    <a href="{{ route('admin.lens-brands.index') }}" class="btn btn-light">
                        <i class="ki-duotone ki-arrow-left fs-4 me-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-5">
        <!-- Left Column - Brand Details -->
        <div class="col-lg-8">
            <!-- Brand Information Card -->
            <div class="card mb-5">
                <div class="card-header bg-primary">
                    <h5 class="card-title mb-0 text-white">
                        <i class="ki-duotone ki-abstract-38 fs-3 me-2 text-white">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Brand Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-7">
                        <div class="col-md-6 mb-5">
                            <label class="text-muted fs-5 fw-semibold d-block mb-3">
                                <i class="ki-duotone ki-tag fs-4 me-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                Brand Name
                            </label>
                            <span class="text-gray-800 fs-4 fw-bold">{{ $lensCategory->brand_name }}</span>
                        </div>

                        <div class="col-md-6 mb-5">
                            <label class="text-muted fs-5 fw-semibold d-block mb-3">
                                <i class="ki-duotone ki-geolocation fs-4 me-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                Country
                            </label>
                            <span class="text-gray-800 fs-4">
                                {{ $lensCategory->country_name ?: 'N/A' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Statistics -->
        <div class="col-lg-4">
            <!-- Statistics Card -->
            <div class="card mb-5">
                <div class="card-header bg-light-primary">
                    <h5 class="card-title mb-0 text-primary">
                        <i class="ki-duotone ki-chart-simple fs-3 me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                        </i>
                        Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Lenses Count -->
                    <div class="mb-5">
                        <label class="text-muted fs-5 fw-semibold d-block mb-3">Total Lenses</label>
                        <div class="d-flex align-items-center">
                            <i class="ki-duotone ki-eye fs-3 text-primary me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                            <span class="text-gray-800 fs-3 fw-bold text-primary">
                                {{ $lensCategory->lenses_count ?? 0 }}
                            </span>
                        </div>
                    </div>

                    <!-- Created At -->
                    <div class="mb-5">
                        <label class="text-muted fs-5 fw-semibold d-block mb-3">
                            <i class="ki-duotone ki-calendar fs-4 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Created Date
                        </label>
                        <span class="text-gray-800 fs-5">{{ $lensCategory->created_at->format('M d, Y H:i') }}</span>
                    </div>

                    <!-- Updated At -->
                    <div>
                        <label class="text-muted fs-5 fw-semibold d-block mb-3">
                            <i class="ki-duotone ki-calendar-tick fs-4 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Last Updated
                        </label>
                        <span class="text-gray-800 fs-5">{{ $lensCategory->updated_at->format('M d, Y H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</x-app-layout>
