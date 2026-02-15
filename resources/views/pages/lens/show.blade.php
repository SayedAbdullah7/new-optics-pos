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
                            {{ strtoupper(substr($lens->lens_code, 0, 1)) }}
                        </div>
                    </div>
                    <div>
                        <h3 class="mb-1 text-gray-900">{{ $lens->lens_code }}</h3>
                        <span class="text-muted fs-7">Lens #{{ $lens->id }} • Created {{ $lens->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <x-action-button
                        :action="route('admin.lenses.edit', $lens)"
                        type="edit"
                        variant="warning"
                        icon="pencil"
                        label="Edit Lens"
                    />
                    <a href="{{ route('admin.lenses.index') }}" class="btn btn-light">
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
        <!-- Left Column - Lens Details -->
        <div class="col-lg-8">
            <!-- Lens Information Card -->
            <div class="card mb-5">
                <div class="card-header bg-info">
                    <h5 class="card-title mb-0 text-white">
                        <i class="ki-duotone ki-eye fs-3 me-2 text-white">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Lens Details
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
                                Lens Code
                            </label>
                            <span class="text-gray-800 fs-4 fw-bold">{{ $lens->lens_code }}</span>
                        </div>

                        <div class="col-md-6 mb-5">
                            <label class="text-muted fs-5 fw-semibold d-block mb-3">
                                <i class="ki-duotone ki-abstract-29 fs-4 me-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                Range Power
                            </label>
                            <span class="text-gray-800 fs-4">
                                {{ $lens->rangePower ? $lens->rangePower->name : 'N/A' }}
                            </span>
                        </div>

                        <div class="col-md-6 mb-5">
                            <label class="text-muted fs-5 fw-semibold d-block mb-3">
                                <i class="ki-duotone ki-category fs-4 me-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                Type
                            </label>
                            <span class="text-gray-800 fs-4">
                                {{ $lens->type ? $lens->type->name : 'N/A' }}
                            </span>
                        </div>

                        <div class="col-md-6 mb-5">
                            <label class="text-muted fs-5 fw-semibold d-block mb-3">
                                <i class="ki-duotone ki-abstract-38 fs-4 me-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                Brand/Category
                            </label>
                            <span class="text-gray-800 fs-4">
                                {{ $lens->category ? ($lens->category->brand_name ?? $lens->category->name) : 'N/A' }}
                            </span>
                        </div>

                        <div class="col-md-6 mb-5">
                            <label class="text-muted fs-5 fw-semibold d-block mb-3">
                                <i class="ki-duotone ki-dollar fs-4 me-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                Sale Price
                            </label>
                            <span class="text-gray-800 fs-4 fw-bold text-success">
                                {{ number_format($lens->sale_price, 2) }} EGP
                            </span>
                        </div>

                        <div class="col-md-6 mb-5">
                            <label class="text-muted fs-5 fw-semibold d-block mb-3">
                                <i class="ki-duotone ki-basket fs-4 me-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                Purchase Price
                            </label>
                            <span class="text-gray-800 fs-4">
                                {{ $lens->purchase_price ? number_format($lens->purchase_price, 2) . ' EGP' : 'N/A' }}
                            </span>
                        </div>

                        <!-- Description -->
                        @if($lens->description)
                            <div class="col-12 mb-5">
                                <label class="text-muted fs-5 fw-semibold d-block mb-3">
                                    <i class="ki-duotone ki-document fs-4 me-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    Description
                                </label>
                                <span class="text-gray-800 fs-5">{{ $lens->description }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Statistics & Info -->
        <div class="col-lg-4">
            <!-- Stock Information Card -->
            <div class="card mb-5">
                <div class="card-header bg-light-primary">
                    <h5 class="card-title mb-0 text-primary">
                        <i class="ki-duotone ki-chart-simple fs-3 me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                        </i>
                        Stock Information
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Current Stock -->
                    <div class="mb-5">
                        <label class="text-muted fs-5 fw-semibold d-block mb-3">Current Stock</label>
                        @php
                            $stockClass = 'text-success';
                            if (($analytics['current_stock'] ?? 0) <= 0) $stockClass = 'text-danger';
                            elseif (($analytics['current_stock'] ?? 0) <= 10) $stockClass = 'text-warning';
                        @endphp
                        <span class="text-gray-800 fs-2 fw-bold {{ $stockClass }}">
                            {{ $analytics['current_stock'] ?? 0 }} units
                        </span>
                    </div>

                    <!-- Stock Value at Cost -->
                    <div class="mb-5">
                        <label class="text-muted fs-5 fw-semibold d-block mb-3">
                            <i class="ki-duotone ki-dollar fs-4 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Stock Value at Cost
                        </label>
                        <span class="text-gray-800 fs-4 fw-bold">
                            {{ number_format($analytics['stock_value_at_cost'] ?? 0, 2) }} EGP
                        </span>
                    </div>

                    <!-- Sold Quantity -->
                    <div class="mb-5">
                        <label class="text-muted fs-5 fw-semibold d-block mb-3">
                            <i class="ki-duotone ki-arrow-down fs-4 me-2 text-danger">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Total Sold Quantity
                        </label>
                        <span class="text-gray-800 fs-4 text-danger">{{ number_format($analytics['total_sold_qty'] ?? 0) }} units</span>
                    </div>

                    <!-- Bought Quantity -->
                    <div class="mb-5">
                        <label class="text-muted fs-5 fw-semibold d-block mb-3">
                            <i class="ki-duotone ki-arrow-up fs-4 me-2 text-success">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Total Bought Quantity
                        </label>
                        <span class="text-gray-800 fs-4 text-success">{{ number_format($analytics['total_bought_qty'] ?? 0) }} units</span>
                    </div>
                </div>
            </div>

            <!-- Financial Information Card -->
            <div class="card mb-5">
                <div class="card-header bg-light-success">
                    <h5 class="card-title mb-0 text-success">
                        <i class="ki-duotone ki-chart fs-3 me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Financial Information
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Profit Margin (Per Unit) -->
                    @if($lens->purchase_price > 0)
                        @php
                            $profit = $lens->sale_price - $lens->purchase_price;
                            $profitMargin = ($profit / $lens->sale_price) * 100;
                        @endphp
                        <div class="mb-5">
                            <label class="text-muted fs-5 fw-semibold d-block mb-3">Profit Margin (Per Unit)</label>
                            <span class="text-gray-800 fs-3 fw-bold text-success">
                                {{ number_format($profitMargin, 2) }}%
                            </span>
                            <div class="text-muted fs-6">({{ number_format($profit, 2) }} EGP)</div>
                        </div>
                    @endif

                    <!-- Expected Profit from Stock -->
                    <div class="mb-5">
                        <label class="text-muted fs-5 fw-semibold d-block mb-3">Expected Profit (from Stock)</label>
                        <span class="text-gray-800 fs-4 fw-bold text-success">
                            {{ number_format($analytics['expected_profit'] ?? 0, 2) }} EGP
                        </span>
                        <div class="text-muted fs-7">({{ $analytics['current_stock'] ?? 0 }} units × {{ number_format($profit ?? 0, 2) }} EGP)</div>
                    </div>

                    @if(($analytics['total_sold_qty'] ?? 0) > 0)
                        <hr class="my-5">

                        <!-- Total Revenue -->
                        <div class="mb-5">
                            <label class="text-muted fs-5 fw-semibold d-block mb-3">Total Revenue</label>
                            <span class="text-gray-800 fs-4 fw-bold text-success">
                                {{ number_format($analytics['total_revenue'] ?? 0, 2) }} EGP
                            </span>
                            <div class="text-muted fs-7">From {{ number_format($analytics['total_sold_qty'] ?? 0) }} units sold</div>
                        </div>

                        <!-- Realized Gross Profit -->
                        <div class="mb-5">
                            <label class="text-muted fs-5 fw-semibold d-block mb-3">Realized Gross Profit</label>
                            <span class="text-gray-800 fs-3 fw-bold text-success">
                                {{ number_format($analytics['realized_gross_profit'] ?? 0, 2) }} EGP
                            </span>
                        </div>
                    @endif

                    @if(($analytics['total_bought_qty'] ?? 0) > 0)
                        <hr class="my-5">
                        <!-- Total Purchase Spent -->
                        <div class="mb-5">
                            <label class="text-muted fs-5 fw-semibold d-block mb-3">Total Purchase Spent</label>
                            <span class="text-gray-800 fs-4">
                                {{ number_format($analytics['total_purchase_spent'] ?? 0, 2) }} EGP
                            </span>
                            <div class="text-muted fs-7">For {{ number_format($analytics['total_bought_qty'] ?? 0) }} units</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Date Information Card -->
            <div class="card mb-5">
                <div class="card-header bg-light-info">
                    <h5 class="card-title mb-0 text-info">
                        <i class="ki-duotone ki-calendar fs-3 me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Date Information
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Created At -->
                    <div class="mb-5">
                        <label class="text-muted fs-5 fw-semibold d-block mb-3">
                            <i class="ki-duotone ki-calendar fs-4 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Created Date
                        </label>
                        <span class="text-gray-800 fs-5">{{ $lens->created_at->format('M d, Y H:i') }}</span>
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
                        <span class="text-gray-800 fs-5">{{ $lens->updated_at->format('M d, Y H:i') }}</span>
                    </div>
                </div>
            </div>

            <!-- Full Name Display -->
            <div class="card">
                <div class="card-header bg-light-info">
                    <h5 class="card-title mb-0 text-info">
                        <i class="ki-duotone ki-information fs-3 me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Full Name
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-gray-800 fs-4 fw-bold mb-0">
                        {{ $lens->full_name }}
                    </p>
                </div>
            </div>
        </div>
    </div>
    </div>
</x-app-layout>
