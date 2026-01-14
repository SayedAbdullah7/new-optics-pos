<x-app-layout>
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-xxl">
    <!-- Page Header -->
    <div class="card mb-5">
        <div class="card-body py-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="symbol symbol-60px symbol-circle me-4">
                        @if($product->image && $product->image != 'default.png')
                            <img src="{{ $product->image_path }}" alt="{{ $product->name }}" class="symbol-label">
                        @else
                            <div class="symbol-label bg-light-primary text-primary fs-2 fw-bold">
                                {{ strtoupper(substr($product->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <div>
                        <h3 class="mb-1 text-gray-900">{{ $product->name }}</h3>
                        <span class="text-muted fs-7">Product #{{ $product->id }} • Item Code: {{ $product->item_code }} • Created {{ $product->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <x-action-button
                        :action="route('admin.products.edit', $product)"
                        type="edit"
                        variant="warning"
                        icon="pencil"
                        label="Edit Product"
                    />
                    <a href="{{ route('admin.products.index') }}" class="btn btn-light">
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
        <!-- Left Column - Product Details -->
        <div class="col-lg-8">
            <!-- Product Information Card -->
            <div class="card mb-5">
                <div class="card-header bg-primary">
                    <h5 class="card-title mb-0 text-white">
                        <i class="ki-duotone ki-box fs-3 me-2 text-white">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                        </i>
                        Product Details
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
                                Item Code
                            </label>
                            <span class="text-gray-800 fs-4 fw-bold">{{ $product->item_code }}</span>
                        </div>

                        <div class="col-md-6 mb-5">
                            <label class="text-muted fs-5 fw-semibold d-block mb-3">
                                <i class="ki-duotone ki-category fs-4 me-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                Category
                            </label>
                            <span class="text-gray-800 fs-4">
                                {{ $product->category ? $product->category->name : 'N/A' }}
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
                                {{ number_format($product->sale_price, 2) }} EGP
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
                                {{ number_format($product->purchase_price, 2) }} EGP
                            </span>
                        </div>

                        <!-- Product Names in different languages -->
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
                                    {{ $product->translate($locale)->name ?? 'N/A' }}
                                </span>
                            </div>
                        @endforeach

                        <!-- Descriptions in different languages -->
                        @foreach(config('translatable.locales') as $locale)
                            @if($product->translate($locale)->description ?? null)
                                <div class="col-12 mb-5">
                                    <label class="text-muted fs-5 fw-semibold d-block mb-3">
                                        <i class="ki-duotone ki-file-text fs-4 me-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        Description ({{ strtoupper($locale) }})
                                    </label>
                                    <span class="text-gray-800 fs-5">{{ $product->translate($locale)->description }}</span>
                                </div>
                            @endif
                        @endforeach

                        <!-- Product Image -->
                        @if($product->image && $product->image != 'default.png')
                            <div class="col-12 mb-5">
                                <label class="text-muted fs-5 fw-semibold d-block mb-3">
                                    <i class="ki-duotone ki-image fs-4 me-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    Product Image
                                </label>
                                <img src="{{ $product->image_path }}" alt="{{ $product->name }}" class="img-thumbnail" style="max-height: 300px;">
                            </div>
                        @endif
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
                    <!-- Current Stock -->
                    <div class="mb-5">
                        <label class="text-muted fs-5 fw-semibold d-block mb-3">Current Stock</label>
                        @php
                            $stockClass = 'text-success';
                            if ($product->stock <= 0) $stockClass = 'text-danger';
                            elseif ($product->stock <= 10) $stockClass = 'text-warning';
                        @endphp
                        <span class="text-gray-800 fs-2 fw-bold {{ $stockClass }}">
                            {{ $product->stock }} units
                        </span>
                    </div>

                    <!-- Sold Quantity -->
                    @php
                        $sold = $product->sold();
                    @endphp
                    <div class="mb-5">
                        <label class="text-muted fs-5 fw-semibold d-block mb-3">
                            <i class="ki-duotone ki-arrow-down fs-4 me-2 text-danger">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Sold Quantity
                        </label>
                        <span class="text-gray-800 fs-4 text-danger">{{ $sold }} units</span>
                    </div>

                    <!-- Bought Quantity -->
                    @php
                        $bought = $product->bought();
                    @endphp
                    <div class="mb-5">
                        <label class="text-muted fs-5 fw-semibold d-block mb-3">
                            <i class="ki-duotone ki-arrow-up fs-4 me-2 text-success">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Bought Quantity
                        </label>
                        <span class="text-gray-800 fs-4 text-success">{{ $bought }} units</span>
                    </div>

                    <!-- Actual Stock (stock2) -->
                    @php
                        $actualStock = $product->stock2();
                    @endphp
                    <div class="mb-5">
                        <label class="text-muted fs-5 fw-semibold d-block mb-3">Actual Stock</label>
                        <span class="text-gray-800 fs-4 fw-bold">
                            {{ $actualStock }} units
                        </span>
                        <div class="text-muted fs-7">(Base: {{ $product->attributes['stock'] ?? 0 }} - Sold: {{ $sold }} + Bought: {{ $bought }})</div>
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
                    <!-- Profit -->
                    <div class="mb-5">
                        <label class="text-muted fs-5 fw-semibold d-block mb-3">Profit</label>
                        <span class="text-gray-800 fs-3 fw-bold text-success">
                            {{ number_format($product->profit, 2) }} EGP
                        </span>
                        <div class="text-muted fs-6">({{ $product->profit_percent }}%)</div>
                    </div>

                    <!-- Total Sales Value (if sold) -->
                    @if($sold > 0)
                        <div class="mb-5">
                            <label class="text-muted fs-5 fw-semibold d-block mb-3">Total Sales Value</label>
                            <span class="text-gray-800 fs-4">
                                {{ number_format($sold * $product->sale_price, 2) }} EGP
                            </span>
                            <div class="text-muted fs-7">({{ $sold }} units × {{ number_format($product->sale_price, 2) }} EGP)</div>
                        </div>
                    @endif

                    <!-- Total Purchase Value (if bought) -->
                    @if($bought > 0)
                        <div class="mb-5">
                            <label class="text-muted fs-5 fw-semibold d-block mb-3">Total Purchase Value</label>
                            <span class="text-gray-800 fs-4">
                                {{ number_format($bought * $product->purchase_price, 2) }} EGP
                            </span>
                            <div class="text-muted fs-7">({{ $bought }} units × {{ number_format($product->purchase_price, 2) }} EGP)</div>
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
                        <span class="text-gray-800 fs-5">{{ $product->created_at->format('M d, Y H:i') }}</span>
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
                        <span class="text-gray-800 fs-5">{{ $product->updated_at->format('M d, Y H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</x-app-layout>
