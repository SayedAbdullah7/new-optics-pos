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
                            {{ strtoupper(substr($category->name, 0, 1)) }}
                        </div>
                    </div>
                    <div>
                        <h3 class="mb-1 text-gray-900">{{ $category->name }}</h3>
                        <span class="text-muted fs-7">Category #{{ $category->id }} • Created {{ $category->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <x-action-button
                        :action="route('admin.categories.edit', $category)"
                        type="edit"
                        variant="warning"
                        icon="pencil"
                        label="Edit Category"
                    />
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-light">
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
        <!-- Left Column - Category Details & Products -->
        <div class="col-lg-8">
            <!-- Category Information Card -->
            <div class="card mb-5">
                <div class="card-header bg-primary">
                    <h5 class="card-title mb-0 text-white">
                        <i class="ki-duotone ki-category fs-3 me-2 text-white">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Category Information
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
                                Category Name
                            </label>
                            <span class="text-gray-800 fs-4 fw-bold">{{ $category->name }}</span>
                        </div>

                        <div class="col-md-6 mb-5">
                            <label class="text-muted fs-5 fw-semibold d-block mb-3">
                                <i class="ki-duotone ki-package fs-4 me-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                                Total Products
                            </label>
                            <span class="text-gray-800 fs-4 fw-bold text-primary">
                                {{ $analytics['total_products'] ?? 0 }} products
                            </span>
                        </div>

                        <!-- Category Names in different languages -->
                        @foreach(config('translatable.locales') as $locale)
                            <div class="col-md-6 mb-5">
                                <label class="text-muted fs-5 fw-semibold d-block mb-3">
                                    <i class="ki-duotone ki-document fs-4 me-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    Name ({{ strtoupper($locale) }})
                                </label>
                                <span class="text-gray-800 fs-5">
                                    {{ $category->translate($locale)->name ?? 'N/A' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Products in Category -->
            <div class="card">
                <div class="card-header bg-light-info">
                    <h5 class="card-title mb-0 text-info">
                        <i class="ki-duotone ki-package fs-3 me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Products in This Category
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                            <thead>
                                <tr class="fw-bold text-muted">
                                    <th>Product</th>
                                    <th class="text-end">Stock</th>
                                    <th class="text-end">Sale Price</th>
                                    <th class="text-end">Purchase Price</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products ?? [] as $product)
                                    @php
                                        $stockClass = 'text-success';
                                        if ($product->stock <= 0) $stockClass = 'text-danger';
                                        elseif ($product->stock <= 10) $stockClass = 'text-warning';
                                    @endphp
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.products.show', $product->id) }}" class="text-gray-800 text-hover-primary fw-bold">
                                                {{ $product->name }}
                                            </a>
                                        </td>
                                        <td class="text-end">
                                            <span class="{{ $stockClass }} fw-bold">{{ $product->stock }}</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-gray-800">{{ number_format($product->sale_price, 2) }} EGP</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-gray-600">{{ number_format($product->purchase_price, 2) }} EGP</span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-sm btn-light-primary">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-10">No products in this category</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Statistics & Info -->
        <div class="col-lg-4">
            <!-- Stock Statistics Card -->
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
                    <!-- Total Stock -->
                    <div class="mb-5">
                        <label class="text-muted fs-5 fw-semibold d-block mb-3">Total Stock</label>
                        <span class="text-gray-800 fs-2 fw-bold text-primary">
                            {{ number_format($analytics['total_stock'] ?? 0) }} units
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

            <!-- Financial Statistics Card -->
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
                    <!-- Expected Revenue from Stock -->
                    <div class="mb-5">
                        <label class="text-muted fs-5 fw-semibold d-block mb-3">Expected Revenue (from Stock)</label>
                        <span class="text-gray-800 fs-4 fw-bold text-primary">
                            {{ number_format($analytics['expected_revenue'] ?? 0, 2) }} EGP
                        </span>
                    </div>

                    <!-- Expected Profit from Stock -->
                    <div class="mb-5">
                        <label class="text-muted fs-5 fw-semibold d-block mb-3">Expected Profit (from Stock)</label>
                        <span class="text-gray-800 fs-4 fw-bold text-success">
                            {{ number_format($analytics['expected_profit'] ?? 0, 2) }} EGP
                        </span>
                    </div>

                    @if(($analytics['total_sold_qty'] ?? 0) > 0)
                        <hr class="my-5">

                        <!-- Total Revenue (Actual) -->
                        <div class="mb-5">
                            <label class="text-muted fs-5 fw-semibold d-block mb-3">Total Revenue (Actual)</label>
                            <span class="text-gray-800 fs-4 fw-bold text-success">
                                {{ number_format($analytics['total_revenue'] ?? 0, 2) }} EGP
                            </span>
                            <div class="text-muted fs-7">From {{ number_format($analytics['total_sold_qty'] ?? 0) }} units sold</div>
                        </div>

                        <!-- COGS -->
                        <div class="mb-5">
                            <label class="text-muted fs-5 fw-semibold d-block mb-3">Cost of Goods Sold (COGS)</label>
                            <span class="text-gray-800 fs-4">
                                {{ number_format($analytics['total_cogs'] ?? 0, 2) }} EGP
                            </span>
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
            <div class="card">
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
                        <span class="text-gray-800 fs-5">{{ $category->created_at->format('M d, Y H:i') }}</span>
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
                        <span class="text-gray-800 fs-5">{{ $category->updated_at->format('M d, Y H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</x-app-layout>
