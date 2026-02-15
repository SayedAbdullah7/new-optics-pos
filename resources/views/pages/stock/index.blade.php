<x-app-layout>
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-xxl">

        <!-- Page Header -->
        <div class="card mb-5">
            <div class="card-body py-4">
                <h2 class="mb-0 text-gray-900">
                    <i class="ki-duotone ki-chart-simple fs-2 me-2 text-primary">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                        <span class="path4"></span>
                    </i>
                    Inventory Dashboard
                </h2>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
            <!-- Total Products -->
            <div class="col-sm-6 col-lg-3 mb-5 mb-lg-0">
                <div class="card card-flush" style="background-color: #7239EA; min-height: 140px;">
                    <div class="card-body d-flex flex-column justify-content-center py-6">
                        <div class="d-flex flex-column">
                            <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2 mb-2">{{ $kpis['total_products'] ?? 0 }}</span>
                            <span class="text-white opacity-75 fw-semibold fs-6">Total Products</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Stock Units -->
            <div class="col-sm-6 col-lg-3 mb-5 mb-lg-0">
                <div class="card card-flush" style="background-color: #50CD89; min-height: 140px;">
                    <div class="card-body d-flex flex-column justify-content-center py-6">
                        <div class="d-flex flex-column">
                            <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2 mb-2">{{ number_format($kpis['total_stock_units'] ?? 0) }}</span>
                            <span class="text-white opacity-75 fw-semibold fs-6">Total Stock Units</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventory Value at Cost -->
            <div class="col-sm-6 col-lg-3 mb-5 mb-lg-0">
                <div class="card card-flush" style="background-color: #F1416C; min-height: 140px;">
                    <div class="card-body d-flex flex-column justify-content-center py-6">
                        <div class="d-flex flex-column">
                            <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2 mb-2">{{ number_format($kpis['inventory_value_at_cost'] ?? 0, 2) }}</span>
                            <span class="text-white opacity-75 fw-semibold fs-6">Inventory Value (Cost)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Expected Profit -->
            <div class="col-sm-6 col-lg-3 mb-5 mb-lg-0">
                <div class="card card-flush" style="background-color: #FFC700; min-height: 140px;">
                    <div class="card-body d-flex flex-column justify-content-center py-6">
                        <div class="d-flex flex-column">
                            <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2 mb-2">{{ number_format($kpis['expected_profit'] ?? 0, 2) }}</span>
                            <span class="text-white opacity-75 fw-semibold fs-6">Expected Profit</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional KPI Row -->
        <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
            <!-- Inventory Value at Retail -->
            <div class="col-sm-6 col-lg-3 mb-5 mb-lg-0">
                <div class="card card-flush h-100 bg-light-primary">
                    <div class="card-body text-center py-10">
                        <span class="text-gray-600 fs-6 fw-semibold d-block mb-2">Inventory Value (Retail)</span>
                        <span class="text-primary fs-2x fw-bold">{{ number_format($kpis['inventory_value_at_retail'] ?? 0, 2) }} EGP</span>
                    </div>
                </div>
            </div>

            <!-- Expected Margin -->
            <div class="col-sm-6 col-lg-3 mb-5 mb-lg-0">
                <div class="card card-flush h-100 bg-light-success">
                    <div class="card-body text-center py-10">
                        <span class="text-gray-600 fs-6 fw-semibold d-block mb-2">Expected Margin</span>
                        <span class="text-success fs-2x fw-bold">{{ number_format($kpis['expected_margin_percent'] ?? 0, 2) }}%</span>
                    </div>
                </div>
            </div>

            <!-- Low Stock Count -->
            <div class="col-sm-6 col-lg-3 mb-5 mb-lg-0">
                <div class="card card-flush h-100 bg-light-warning">
                    <div class="card-body text-center py-10">
                        <span class="text-gray-600 fs-6 fw-semibold d-block mb-2">Low Stock Items</span>
                        <span class="text-warning fs-2x fw-bold">{{ $kpis['low_stock_count'] ?? 0 }}</span>
                    </div>
                </div>
            </div>

            <!-- Out of Stock Count -->
            <div class="col-sm-6 col-lg-3 mb-5 mb-lg-0">
                <div class="card card-flush h-100 bg-light-danger">
                    <div class="card-body text-center py-10">
                        <span class="text-gray-600 fs-6 fw-semibold d-block mb-2">Out of Stock</span>
                        <span class="text-danger fs-2x fw-bold">{{ $kpis['out_of_stock_count'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Analytics Table -->
        <div class="card mb-5">
            <div class="card-header bg-light-primary">
                <h5 class="card-title mb-0 text-primary">
                    <i class="ki-duotone ki-category fs-3 me-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    Category Analytics
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                        <thead>
                            <tr class="fw-bold text-muted">
                                <th>Category</th>
                                <th class="text-end">Current Stock</th>
                                <th class="text-end">Stock Value (Cost)</th>
                                <th class="text-end">Sold Qty</th>
                                <th class="text-end">Revenue</th>
                                <th class="text-end">COGS</th>
                                <th class="text-end">Realized Profit</th>
                                <th class="text-end">Bought Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categoryAnalytics ?? [] as $catAnalytic)
                                <tr>
                                    <td>
                                        <span class="text-gray-800 fw-bold">{{ $catAnalytic['category']->name ?? 'N/A' }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-gray-800">{{ number_format($catAnalytic['current_stock'] ?? 0) }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-gray-800">{{ number_format($catAnalytic['stock_value_at_cost'] ?? 0, 2) }} EGP</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-gray-800">{{ number_format($catAnalytic['total_sold_qty'] ?? 0) }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-success fw-bold">{{ number_format($catAnalytic['total_revenue'] ?? 0, 2) }} EGP</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-gray-800">{{ number_format($catAnalytic['cogs'] ?? 0, 2) }} EGP</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-success fw-bold">{{ number_format($catAnalytic['realized_profit'] ?? 0, 2) }} EGP</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-gray-800">{{ number_format($catAnalytic['total_bought_qty'] ?? 0) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-10">No category data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Product Analytics Table -->
        <div class="card mb-5">
            <div class="card-header bg-light-info">
                <h5 class="card-title mb-0 text-info">
                    <i class="ki-duotone ki-package fs-3 me-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>
                    Product Analytics
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                        <thead>
                            <tr class="fw-bold text-muted">
                                <th>Product</th>
                                <th>Category</th>
                                <th class="text-end">Current Stock</th>
                                <th class="text-end">Stock Value (Cost)</th>
                                <th class="text-end">Sold Qty</th>
                                <th class="text-end">Revenue</th>
                                <th class="text-end">COGS</th>
                                <th class="text-end">Realized Profit</th>
                                <th class="text-end">Avg Sale Price</th>
                                <th class="text-end">Bought Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($productAnalytics ?? [] as $prodAnalytic)
                                @php
                                    $product = $prodAnalytic['product'];
                                    $stockClass = 'text-success';
                                    if ($prodAnalytic['current_stock'] <= 0) $stockClass = 'text-danger';
                                    elseif ($prodAnalytic['current_stock'] <= 10) $stockClass = 'text-warning';
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.products.show', $product->id) }}" class="text-gray-800 text-hover-primary fw-bold">
                                            {{ $product->name }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="text-gray-600">{{ $product->category->name ?? 'N/A' }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="{{ $stockClass }} fw-bold">{{ number_format($prodAnalytic['current_stock'] ?? 0) }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-gray-800">{{ number_format($prodAnalytic['stock_value_at_cost'] ?? 0, 2) }} EGP</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-gray-800">{{ number_format($prodAnalytic['total_sold_qty'] ?? 0) }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-success fw-bold">{{ number_format($prodAnalytic['total_revenue'] ?? 0, 2) }} EGP</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-gray-800">{{ number_format($prodAnalytic['cogs'] ?? 0, 2) }} EGP</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-success fw-bold">{{ number_format($prodAnalytic['realized_profit'] ?? 0, 2) }} EGP</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-gray-600">{{ number_format($prodAnalytic['avg_sale_price'] ?? 0, 2) }} EGP</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-gray-800">{{ number_format($prodAnalytic['total_bought_qty'] ?? 0) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-10">No product data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Stock Mutations Table (Existing) -->
        <div class="card">
            <div class="card-header bg-light-secondary">
                <h5 class="card-title mb-0 text-secondary">
                    <i class="ki-duotone ki-abstract-26 fs-3 me-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    Stock Mutations History
                </h5>
            </div>
            <div class="card-body">
                <x-dynamic-table
                    table-id="stock_table"
                    :columns="$columns"
                    :filters="$filters"
                    :show-checkbox="false"
                />
            </div>
        </div>

    </div>
    <!--end::Content container-->
</x-app-layout>
