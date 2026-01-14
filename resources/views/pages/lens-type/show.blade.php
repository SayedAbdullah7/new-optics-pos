<x-app-layout>
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-xxl">
    <!-- Page Header -->
    <div class="card mb-5">
        <div class="card-body py-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="symbol symbol-60px symbol-circle me-4">
                        <div class="symbol-label bg-light-info text-info fs-2 fw-bold">
                            {{ strtoupper(substr($lensType->name, 0, 1)) }}
                        </div>
                    </div>
                    <div>
                        <h3 class="mb-1 text-gray-900">{{ $lensType->name }}</h3>
                        <span class="text-muted fs-7">Type #{{ $lensType->id }} • Created {{ $lensType->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <x-action-button
                        :action="route('admin.lens-types.edit', $lensType)"
                        type="edit"
                        variant="warning"
                        icon="pencil"
                        label="Edit Type"
                    />
                    <a href="{{ route('admin.lens-types.index') }}" class="btn btn-light">
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
        <!-- Left Column - Type Details -->
        <div class="col-lg-8">
            <!-- Type Information Card -->
            <div class="card mb-5">
                <div class="card-header bg-info">
                    <h5 class="card-title mb-0 text-white">
                        <i class="ki-duotone ki-category fs-3 me-2 text-white">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                        </i>
                        Type Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-5">
                        <label class="text-muted fs-5 fw-semibold d-block mb-3">
                            <i class="ki-duotone ki-tag fs-4 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Type Name
                        </label>
                        <span class="text-gray-800 fs-4 fw-bold">{{ $lensType->name }}</span>
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
                                {{ $lensType->lenses_count ?? 0 }}
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
                        <span class="text-gray-800 fs-5">{{ $lensType->created_at->format('M d, Y H:i') }}</span>
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
                        <span class="text-gray-800 fs-5">{{ $lensType->updated_at->format('M d, Y H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</x-app-layout>
