@php
    $isEdit = isset($model);
    $actionRoute = $isEdit
        ? route('admin.invoices.update', [$model->id])
        : route('admin.invoices.store');
    $method = $isEdit ? 'PUT' : 'POST';

    // Get client's paper if available
    $clientPaper = null;
    if ($isEdit && $model->client) {
        $clientPaper = $model->paper ?? $model->client->papers()->latest()->first();
    } elseif (isset($client) && $client) {
        $clientPaper = $client->papers()->latest()->first();
    }
@endphp

<x-form :actionRoute="$actionRoute" :method="$method" :isEdit="$isEdit">
    <div class="row g-5">
        <!-- Main Content Area -->
        <div class="col-lg-8 col-xl-9">
            <!-- Invoice Header Section -->
            <div class="card card-flush shadow-sm mb-5 radius-lg">
                <div class="card-header bg-light-primary py-3">
                    <h5 class="card-title mb-0 d-flex align-items-center">
                        <i class="ki-duotone ki-notepad fs-1 me-3 text-primary">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                            <span class="path5"></span>
                        </i>
                        <span class="fw-bold text-gray-800">Invoice Information</span>
                    </h5>
                </div>
                <div class="card-body pt-5">
                    <div class="row g-4 flex-column flex-md-row">
                        <div class="col-12 col-md-6">
                            <x-group-input-select
                                label="Client"
                                name="client_id"
                                :value="$isEdit ? $model->client_id : (request('client_id') ?? old('client_id') ?? '')"
                                :options="$clients"
                                required
                                id="client_select"
                            />
                        </div>
                        <div class="col-12 col-md-6">
                            <x-group-input-text
                                label="Invoice Number"
                                name="invoice_number"
                                :value="$isEdit ? $model->invoice_number : ($invoiceNumber ?? old('invoice_number') ?? '')"
                                readonly
                            />
                        </div>
                        <div class="col-12 col-md-4">
                            <x-group-input-date
                                label="Invoice Date"
                                name="invoiced_at"
                                :value="$isEdit ? ($model->invoiced_at ? $model->invoiced_at->format('Y-m-d') : '') : (old('invoiced_at') ?? date('Y-m-d'))"
                                required
                            />
                        </div>
                        <div class="col-12 col-md-4">
                            <x-group-input-date
                                label="Due Date"
                                name="due_at"
                                :value="$isEdit ? ($model->due_at ? $model->due_at->format('Y-m-d') : '') : (old('due_at') ?? date('Y-m-d'))"
                                required
                            />
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="fv-row">
                                <label class="fw-semibold fs-6 mb-2 required">Account</label>
                                <select name="account_id" class="form-select form-select-solid" data-kt-select2="true" required>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}" {{ ($account->default ?? false) ? 'selected' : '' }}>
                                            {{ $account->translateOrNew(app()->getLocale())->name ?? $account->id }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                             <x-group-input-textarea
                                 label="Notes"
                                 name="notes"
                                 :value="$isEdit ? $model->notes : (old('notes') ?? '')"
                                 rows="2"
                                 placeholder="Optional invoice notes..."
                             />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Section -->
            <div class="card card-flush shadow-sm mb-5 radius-lg border-top border-primary border-3">
                <div class="card-header py-4">
                    <h5 class="card-title mb-0 d-flex align-items-center">
                        <div class="symbol symbol-40px bg-light-primary me-3">
                             <span class="symbol-label">
                                <i class="ki-duotone ki-package fs-2 text-primary">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                             </span>
                        </div>
                        <span class="fw-bold text-gray-800">Products List</span>
                    </h5>
                    <div class="card-toolbar">
                         <button type="button" class="btn btn-sm btn-light-primary fw-bold" id="add_product_row">
                             <i class="ki-duotone ki-plus fs-2"></i> Add Product
                         </button>
                    </div>
                </div>
                <div class="card-body py-0">
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4" id="products_table">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th class="ps-4 min-w-200px rounded-start">Product</th>
                                    <th class="min-w-100px">Qty</th>
                                    <th class="min-w-125px">Unit Price</th>
                                    <th class="min-w-125px">Total</th>
                                    <th class="min-w-50px text-end pe-4 rounded-end">Action</th>
                                </tr>
                            </thead>
                            <tbody id="products_body">
                                @if($isEdit && $model->items->count() > 0)
                                    @foreach($model->items as $index => $item)
                                        <tr class="product-row animate__animated animate__fadeIn">
                                            <td class="ps-4">
                                                <select name="product[]" class="form-select form-select-solid product-select" data-kt-select2="true" data-placeholder="Select a product">
                                                    <option value=""></option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->id }}&{{ $product->sale_price }}"
                                                                data-price="{{ $product->sale_price }}"
                                                                {{ $item->item_id == $product->id ? 'selected' : '' }}>
                                                            {{ $product->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-solid border">
                                                    <input type="number" name="quantity[]" class="form-control form-control-solid text-center quantity-input px-2" value="{{ $item->quantity }}" min="1">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-solid border">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" name="price[]" class="form-control form-control-solid price-input" value="{{ $item->price }}" step="0.01" min="0">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-solid border bg-light">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" class="form-control form-control-solid row-total bg-light" value="{{ $item->total }}" readonly tabindex="-1">
                                                </div>
                                            </td>
                                            <td class="text-end pe-4">
                                                <button type="button" class="btn btn-icon btn-light-danger btn-sm remove-product-row hover-elevate-up" title="Remove Product">
                                                    <i class="ki-duotone ki-trash fs-2">
                                                        <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span>
                                                    </i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr class="product-row animate__animated animate__fadeIn">
                                        <td class="ps-4">
                                            <select name="product[]" class="form-select form-select-solid product-select" data-kt-select2="true" data-placeholder="Choose product...">
                                                <option value=""></option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}&{{ $product->sale_price }}" data-price="{{ $product->sale_price }}">
                                                        {{ $product->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-solid border">
                                                <input type="number" name="quantity[]" class="form-control form-control-solid text-center quantity-input px-2" value="1" min="1">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-solid border">
                                                <span class="input-group-text">$</span>
                                                <input type="number" name="price[]" class="form-control form-control-solid price-input" value="0.00" step="0.01" min="0">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-solid border bg-light">
                                                <span class="input-group-text">$</span>
                                                <input type="number" class="form-control form-control-solid row-total bg-light" value="0.00" readonly tabindex="-1">
                                            </div>
                                        </td>
                                        <td class="text-end pe-4">
                                            <button type="button" class="btn btn-icon btn-light-danger btn-sm remove-product-row hover-elevate-up" title="Remove Product">
                                                <i class="ki-duotone ki-trash fs-2">
                                                    <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span>
                                                </i>
                                            </button>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Lenses Section -->
            <div class="card card-flush shadow-sm mb-5 radius-lg border-top border-info border-3">
                <div class="card-header py-4 align-items-center">
                    <h5 class="card-title mb-0 d-flex align-items-center">
                        <div class="symbol symbol-40px bg-light-info me-3">
                             <span class="symbol-label">
                                <i class="ki-duotone ki-eye fs-2 text-info">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                             </span>
                        </div>
                        <span class="fw-bold text-gray-800">Lenses Setup</span>
                    </h5>
                    <div class="card-toolbar d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-info fw-bold bg-opacity-90 hover-elevate-up" id="add_lens_pair_btn">
                            <i class="ki-duotone ki-plus fs-2"></i> Pair
                        </button>
                        <button type="button" class="btn btn-sm btn-light-info fw-bold hover-elevate-up" id="add_lens_row">
                            <i class="ki-duotone ki-plus fs-2"></i> Single
                        </button>
                    </div>
                </div>
                <div class="card-body py-0">
                    <div id="lens_pairs_container" class="mb-5">
                        @if($isEdit && isset($lensPairs) && count($lensPairs) > 0)
                            @foreach($lensPairs as $pair)
                                @php
                                    $invLens = $pair['invoice_lens'];
                                    $lens = $pair['lens'];
                                    if (!$lens) continue;
                                    $catId = $lens->category_id;
                                    $price = $invLens->price;
                                    $lensId = $lens->id;
                                    $brandVal = $catId . '&' . $price . '&' . $lensId;
                                    $rangeId = $lens->RangePower_id ?? '';
                                    $typeId = $lens->type_id ?? '';
                                    $typeName = $lens->type ? $lens->type->name : '';
                                    $brandName = $lens->category ? ($lens->category->brand_name ?? $lens->category->name) : '';
                                @endphp
                                <div class="card bg-light-info border-info border border-dashed rounded-3 mb-4 lens-pair-block animate__animated animate__fadeIn" data-lens-id-right="{{ $lensId }}" data-lens-id-left="{{ $lensId }}">
                                    <div class="card-header min-h-40px px-4 border-bottom border-info border-dashed">
                                        <div class="card-title align-items-center d-flex m-0">
                                            <i class="ki-duotone ki-glasses fs-3 text-info me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                            <span class="fw-bold fs-6 text-gray-800">Prescription Lens Pair</span>
                                        </div>
                                        <div class="card-toolbar">
                                            <button type="button" class="btn btn-icon btn-sm btn-active-light-danger remove-lens-pair-block" title="Remove Pair">
                                                <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body p-4">
                                        <!-- Shared Type & Brand -->
                                        <div class="row g-4 mb-4">
                                            <div class="col-md-6">
                                                <label class="form-label fs-7 fw-bold text-gray-700">Type</label>
                                                <select class="form-select form-select-solid form-select-sm pair-type-select border-info border-opacity-25" data-kt-select2="true" data-placeholder="Select Lens Type">
                                                    <option value=""></option>
                                                    @foreach($types as $t)
                                                        <option value="{{ $t->id }}" {{ $typeId == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fs-7 fw-bold text-gray-700">Brand</label>
                                                <select class="form-select form-select-solid form-select-sm pair-brand-select border-info border-opacity-25" data-kt-select2="true" data-placeholder="Select Brand">
                                                    <option value=""></option>
                                                    <option value="{{ $brandVal }}" selected>{{ $brandName }}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <!-- Per Eye Details -->
                                        <div class="row g-0 rounded-3 overflow-hidden border border-gray-300 bg-white" style="direction: ltr;">
                                            <!-- Right Eye -->
                                            <div class="col-md-6 border-end border-gray-300 p-4 position-relative">
                                                <div class="position-absolute top-0 start-0 w-100 h-100 bg-danger bg-opacity-5 pointer-events-none"></div>
                                                <div class="position-relative z-index-1">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <span class="badge badge-danger me-2">R</span>
                                                        <span class="fw-bold text-danger">Right Eye (OD)</span>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fs-8 text-gray-600 mb-1">Range Power</label>
                                                        <select class="form-select form-select-solid form-select-sm pair-range-right">
                                                            <option value="">Select Range</option>
                                                            @foreach($ranges as $range)
                                                                <option value="{{ $range->id }}" {{ $rangeId == $range->id ? 'selected' : '' }}>{{ $range->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="mb-1">
                                                        <label class="form-label fs-8 text-gray-600 mb-1">Unit Price</label>
                                                        <div class="input-group input-group-sm input-group-solid">
                                                            <span class="input-group-text">$</span>
                                                            <input type="number" class="form-control form-control-solid pair-price-right" value="{{ $price }}" step="0.01" min="0">
                                                        </div>
                                                    </div>
                                                    <div class="pair-status-right fs-8 fw-bold mt-2 h-20px text-end"></div>
                                                </div>
                                            </div>
                                            <!-- Left Eye -->
                                            <div class="col-md-6 p-4 position-relative">
                                                <div class="position-absolute top-0 start-0 w-100 h-100 bg-success bg-opacity-5 pointer-events-none"></div>
                                                <div class="position-relative z-index-1">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <span class="badge badge-success me-2">L</span>
                                                        <span class="fw-bold text-success">Left Eye (OS)</span>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fs-8 text-gray-600 mb-1">Range Power</label>
                                                        <select class="form-select form-select-solid form-select-sm pair-range-left">
                                                            <option value="">Select Range</option>
                                                            @foreach($ranges as $range)
                                                                <option value="{{ $range->id }}" {{ $rangeId == $range->id ? 'selected' : '' }}>{{ $range->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="mb-1">
                                                        <label class="form-label fs-8 text-gray-600 mb-1">Unit Price</label>
                                                        <div class="input-group input-group-sm input-group-solid">
                                                            <span class="input-group-text">$</span>
                                                            <input type="number" class="form-control form-control-solid pair-price-left" value="{{ $price }}" step="0.01" min="0">
                                                        </div>
                                                    </div>
                                                    <div class="pair-status-left fs-8 fw-bold mt-2 h-20px text-end"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-end align-items-center mt-3 pt-3 border-top border-info border-dashed">
                                            <span class="text-gray-600 me-3 fs-7 fw-semibold">Subtotal for Pair:</span>
                                            <span class="fs-4 fw-bolder text-info pair-total-display">${{ number_format($invLens->total, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div> <!-- End Lens Pairs Container -->

                    <!-- Single Lenses Table -->
                    <div class="table-responsive @if(!$isEdit || !isset($singleLenses) || count($singleLenses) == 0) d-none @endif" id="lenses_table_wrapper">
                        <h6 class="text-gray-700 fw-bold mb-3 d-flex align-items-center">
                            <i class="ki-duotone ki-element-11 fs-4 me-2 text-muted"><span class="path1"></span><span class="path2"></span></i>
                            Single Lenses
                        </h6>
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-3" id="lenses_table">
                            <thead>
                                <tr class="fw-bold text-muted bg-light fs-8 text-uppercase">
                                    <th class="ps-3 rounded-start min-w-100px">Code Search</th>
                                    <th class="min-w-120px">Range</th>
                                    <th class="min-w-120px">Type</th>
                                    <th class="min-w-120px">Brand</th>
                                    <th class="min-w-70px">Qty</th>
                                    <th class="min-w-100px">Price</th>
                                    <th class="min-w-100px">Total</th>
                                    <th class="pe-3 rounded-end w-40px text-end"></th>
                                </tr>
                            </thead>
                            <tbody id="lenses_body">
                                @if($isEdit && isset($singleLenses) && count($singleLenses) > 0)
                                    @foreach($singleLenses as $index => $lensItem)
                                        @php
                                            $lens = $lensItem->lens;
                                        @endphp
                                        <tr class="lens-row animate__animated animate__fadeIn">
                                            <td class="ps-3">
                                                <input type="text" name="lens_code[]" class="form-control form-control-sm form-control-solid lens-code-input border"
                                                       value="{{ $lens ? $lens->lens_code : '' }}" placeholder="Search code...">
                                                <div class="lens-code-help fs-9 fw-bold mt-1 h-15px"></div>
                                            </td>
                                            <td>
                                                <select name="lens_range[]" class="form-select form-select-sm form-select-solid lens-range-select border">
                                                    <option value="">Range</option>
                                                    @foreach($ranges as $range)
                                                        <option value="{{ $range->id }}" {{ $lens && $lens->RangePower_id == $range->id ? 'selected' : '' }}>
                                                            {{ $range->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <select name="lens_type[]" class="form-select form-select-sm form-select-solid lens-type-select border">
                                                    <option value="">Type</option>
                                                    @if($lens)
                                                        @foreach($types as $type)
                                                            <option value="{{ $type->id }}" {{ $lens && $lens->type_id == $type->id ? 'selected' : '' }}>
                                                                {{ $type->name }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </td>
                                            <td>
                                                <select name="lens_category[]" class="form-select form-select-sm form-select-solid lens-category-select border">
                                                    <option value="">Brand</option>
                                                    @if($lens)
                                                        @foreach($categories as $category)
                                                            <option value="{{ $category->id }}&{{ $lens ? $lens->sale_price : 0 }}&{{ $lens ? $lens->id : 0 }}"
                                                                    {{ $lens && $lens->category_id == $category->id ? 'selected' : '' }}>
                                                                {{ $category->brand_name ?? $category->name }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" name="lens_quantity[]" class="form-control form-control-sm form-control-solid lens-quantity border text-center px-1"
                                                       value="{{ $lensItem->quantity }}" min="1" step="1">
                                            </td>
                                            <td>
                                                <input type="number" name="lens_price[]" class="form-control form-control-sm form-control-solid lens-price border"
                                                       value="{{ $lensItem->price }}" step="0.01" min="0">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm form-control-solid lens-row-total bg-light"
                                                       value="{{ $lensItem->total }}" readonly tabindex="-1">
                                            </td>
                                            <td class="pe-3 text-end align-top pt-3">
                                                <button type="button" class="btn btn-icon btn-light-danger btn-sm remove-lens-row hover-elevate-up w-25px h-25px">
                                                    <i class="ki-duotone ki-trash fs-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar (Prescription & Totals) -->
        <div class="col-lg-4 col-xl-3">
            <div class="card card-flush shadow-sm mb-5 radius-lg" style="position: sticky; top: 80px; z-index: 10;">
                
                <!-- Section: Client Prescription -->
                <div class="card-header bg-light-warning py-3 border-bottom border-warning border-opacity-25 rounded-top">
                    <h5 class="card-title mb-0 d-flex align-items-center">
                        <i class="ki-duotone ki-eye fs-2 text-warning me-2">
                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                        </i>
                        <span class="fw-bold text-gray-800 fs-5">Prescription</span>
                    </h5>
                </div>
                <div class="card-body p-4 bg-light bg-opacity-50">
                    <div id="prescription_display" class="min-h-100px d-flex flex-column justify-content-center">
                        @if($clientPaper)
                            <input type="hidden" name="paper_id" value="{{ $clientPaper->id }}">
                            <div id="prescription_paper_data" class="d-none" data-addtion="{{ $clientPaper->getRawAddtion() }}" data-r-sph="{{ $clientPaper->getRawRSph() }}" data-r-cyl="{{ $clientPaper->getRawRCyl() }}" data-l-sph="{{ $clientPaper->getRawLSph() }}" data-l-cyl="{{ $clientPaper->getRawLCyl() }}"></div>
                            <x-prescription-display :paper="$clientPaper" :showDate="false" :compact="true" />
                        @else
                            <div class="text-center text-muted py-5">
                                <i class="ki-duotone ki-search-list fs-3x text-gray-400 mb-3 block">
                                    <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                </i>
                                <div class="fs-6 fw-semibold">No Prescription</div>
                                <div class="fs-8 text-gray-500 mt-1">Select a client to view</div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Section: Payment & Totals -->
                <div class="card-header bg-dark py-3 border-top border-gray-600">
                    <h5 class="card-title mb-0 d-flex align-items-center">
                        <i class="ki-duotone ki-wallet fs-2 text-white me-2">
                            <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span>
                        </i>
                        <span class="fw-bold text-white fs-5">Summary & Payment</span>
                    </h5>
                </div>
                <div class="card-body p-4 bg-light">
                    <!-- Subtotals -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-gray-600 fw-semibold fs-6">Products Total</span>
                        <div class="fw-bolder fs-6 text-gray-800">$<span id="products_total">0.00</span></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="text-gray-600 fw-semibold fs-6">Lenses Total</span>
                        <div class="fw-bolder fs-6 text-gray-800">$<span id="lenses_total">0.00</span></div>
                    </div>
                    
                    <div class="separator border-gray-300 mb-4"></div>
                    
                    <!-- Grand Total -->
                    <div class="d-flex justify-content-between align-items-center mb-5 bg-white p-3 rounded shadow-sm border border-primary border-opacity-25">
                        <span class="fw-bolder text-gray-800 fs-4">Grand Total</span>
                        <div class="fw-black fs-2x text-primary">$<span id="grand_total">0.00</span></div>
                        <input type="hidden" name="amount" id="amount_input" value="{{ $isEdit ? $model->amount : 0 }}">
                    </div>

                    <!-- Payment Input -->
                    <div class="mb-5 position-relative">
                        <label class="form-label fw-bold text-gray-800 fs-6 required">Payment Received</label>
                        <div class="input-group input-group-lg input-group-solid border border-success border-opacity-50 shadow-sm transition-all" id="payment_input_wrapper">
                            <span class="input-group-text bg-success bg-opacity-10 text-success fw-bolder">
                                <i class="ki-duotone ki-dollar fs-1 text-success"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                            </span>
                            <input type="number" name="paid" class="form-control form-control-solid fw-bolder fs-4 text-success"
                                   id="paid_amount" value="{{ $isEdit ? $model->paid : 0 }}"
                                   step="0.01" min="0" placeholder="0.00">
                        </div>
                    </div>

                    <!-- Remaining & Status -->
                    <div class="d-flex justify-content-between align-items-end rounded p-3 bg-white border border-gray-200" id="remaining_container">
                        <div>
                            <span class="text-gray-600 fw-semibold fs-7 d-block mb-1">Remaining Balance</span>
                            <div class="fw-bolder fs-3 text-danger" id="remaining_amount_display">$<span id="remaining_amount">0.00</span></div>
                        </div>
                        <div class="text-end">
                            <span class="text-gray-500 fw-semibold fs-8 d-block mb-1">Status</span>
                            <span class="badge badge-lg badge-light-danger fw-bold text-uppercase px-3" id="payment_status_badge">Unpaid</span>
                        </div>
                        <input type="hidden" name="status" id="status_input" value="{{ $isEdit ? $model->status : 'unpaid' }}">
                    </div>
                </div>
                <div class="card-footer p-4 border-top">
                    <button type="submit" class="btn btn-primary w-100 fw-bold fs-5 py-3 shadow-sm hover-elevate-up" id="submit_invoice_btn">
                        <i class="ki-duotone ki-check-circle fs-2 me-2"><span class="path1"></span><span class="path2"></span></i>
                        {{ $isEdit ? 'Update Invoice' : 'Create Invoice' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden input array container for specialized lens mapping -->
    <div id="lens_pair_submit_data" class="d-none" aria-hidden="true"></div>
</x-form>

<!-- Include JS via asset or keep script inline; switching to updated logic below -->
<script>
// JS logic updated for the new UI will be appended next
</script>
<script>
(function() {
    'use strict';

    console.log('Invoice form script loaded - Premium UI');

    function waitForJQuery(callback) {
        if (typeof $ !== 'undefined') {
            callback();
        } else {
            setTimeout(function() { waitForJQuery(callback); }, 100);
        }
    }

    waitForJQuery(function() {
        console.log('jQuery loaded, initializing invoice form');

        // Product row template
        const productRowTemplate = `
            <tr class="product-row animate__animated animate__fadeIn">
                <td class="ps-4">
                    <select name="product[]" class="form-select form-select-solid product-select" data-kt-select2="true" data-placeholder="Choose product...">
                        <option value=""></option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}&{{ $product->sale_price }}" data-price="{{ $product->sale_price }}">
                                {{ $product->translateOrNew(app()->getLocale())->name ?? 'Product #' . $product->id }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <div class="input-group input-group-solid border">
                        <input type="number" name="quantity[]" class="form-control form-control-solid text-center quantity-input px-2" value="1" min="1">
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-solid border">
                        <span class="input-group-text">$</span>
                        <input type="number" name="price[]" class="form-control form-control-solid price-input" value="0.00" step="0.01" min="0">
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-solid border bg-light">
                        <span class="input-group-text">$</span>
                        <input type="number" class="form-control form-control-solid row-total bg-light" value="0.00" readonly tabindex="-1">
                    </div>
                </td>
                <td class="text-end pe-4">
                    <button type="button" class="btn btn-icon btn-light-danger btn-sm remove-product-row hover-elevate-up" title="Remove Product">
                        <i class="ki-duotone ki-trash fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                    </button>
                </td>
            </tr>
        `;

        // Single Lens row template
        const lensRowTemplate = `
            <tr class="lens-row animate__animated animate__fadeIn">
                <td class="ps-3">
                    <input type="text" name="lens_code[]" class="form-control form-control-sm form-control-solid lens-code-input border"
                           value="" placeholder="Search...">
                    <div class="lens-code-help fs-9 fw-bold mt-1 h-15px"></div>
                </td>
                <td>
                    <select name="lens_range[]" class="form-select form-select-sm form-select-solid lens-range-select border" data-kt-select2="true" data-placeholder="Range">
                        <option value=""></option>
                        @foreach($ranges as $range)
                            <option value="{{ $range->id }}">{{ $range->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select name="lens_type[]" class="form-select form-select-sm form-select-solid lens-type-select border" data-kt-select2="true" data-placeholder="Type">
                        <option value=""></option>
                    </select>
                </td>
                <td>
                    <select name="lens_category[]" class="form-select form-select-sm form-select-solid lens-category-select border" data-kt-select2="true" data-placeholder="Brand">
                        <option value=""></option>
                    </select>
                </td>
                <td>
                    <input type="number" name="lens_quantity[]" class="form-control form-control-sm form-control-solid lens-quantity border text-center px-1" value="2" min="1" step="1">
                </td>
                <td>
                    <input type="number" name="lens_price[]" class="form-control form-control-sm form-control-solid lens-price border" value="0.00" step="0.01" min="0">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm form-control-solid lens-row-total bg-light" value="0.00" readonly tabindex="-1">
                </td>
                <td class="pe-3 text-end pt-3">
                    <button type="button" class="btn btn-icon btn-light-danger btn-sm remove-lens-row hover-elevate-up w-25px h-25px">
                        <i class="ki-duotone ki-trash fs-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                    </button>
                </td>
            </tr>
        `;

        // Lens Pair block template
        const lensPairBlockTemplate = `
            <div class="card bg-light-info border-info border border-dashed rounded-3 mb-4 lens-pair-block animate__animated animate__fadeIn">
                <div class="card-header min-h-40px px-4 border-bottom border-info border-dashed">
                    <div class="card-title align-items-center d-flex m-0">
                        <i class="ki-duotone ki-glasses fs-3 text-info me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                        <span class="fw-bold fs-6 text-gray-800">Prescription Lens Pair</span>
                    </div>
                    <div class="card-toolbar">
                        <button type="button" class="btn btn-icon btn-sm btn-active-light-danger remove-lens-pair-block">
                            <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4 flex-column flex-md-row mb-4">
                        <div class="col-12 col-md-4">
                            <label class="form-label fs-7 fw-bold text-gray-700">Usage (الاستخدام)</label>
                            <select class="form-select form-select-solid form-select-sm pair-usage-toggle border-info border-opacity-25" data-kt-select2="true" data-placeholder="Select Usage">
                                <option value=""></option>
                                <option value="distance">Distance (مسافات)</option>
                                <option value="reading">Reading (قراءة)</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fs-7 fw-bold text-gray-700">Type (النوع)</label>
                            <select class="form-select form-select-solid form-select-sm pair-type-select border-info border-opacity-25" data-kt-select2="true" data-placeholder="Select Type">
                                <option value=""></option>
                                @foreach($types as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fs-7 fw-bold text-gray-700">Brand (الماركة)</label>
                            <select class="form-select form-select-solid form-select-sm pair-brand-select border-info border-opacity-25" data-kt-select2="true" data-placeholder="Select Brand">
                                <option value=""></option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-0 flex-column flex-md-row rounded-3 overflow-hidden border border-gray-300 bg-white" style="direction: ltr;">
                        <div class="col-12 col-md-6 border-bottom border-md-bottom-0 border-md-end border-gray-300 p-4 position-relative">
                            <div class="position-absolute top-0 start-0 w-100 h-100 bg-danger bg-opacity-5 pointer-events-none"></div>
                            <div class="position-relative z-index-1">
                                <div class="d-flex align-items-center mb-3">
                                    <span class="badge badge-danger me-2">R</span>
                                    <span class="fw-bold text-danger">Right Eye (OD)</span>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fs-8 text-gray-600 mb-1">Target SPH & CYL</label>
                                    <div class="fs-7 fw-bolder pair-target-rx-right">-</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fs-8 text-gray-600 mb-1">Matched Range Power</label>
                                    <select class="form-select form-select-solid form-select-sm pair-range-right pointer-events-none bg-light" tabindex="-1">
                                        <option value="">Range Auto-Selected</option>
                                    </select>
                                </div>
                                <div class="mb-1">
                                    <label class="form-label fs-8 text-gray-600 mb-1">Unit Price</label>
                                    <div class="input-group input-group-sm input-group-solid">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control form-control-solid pair-price-right" value="0.00" step="0.01" min="0">
                                    </div>
                                </div>
                                <div class="pair-status-right fs-8 fw-bold mt-2 h-20px text-end"></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 p-4 position-relative">
                            <div class="position-absolute top-0 start-0 w-100 h-100 bg-success bg-opacity-5 pointer-events-none"></div>
                            <div class="position-relative z-index-1">
                                <div class="d-flex align-items-center mb-3">
                                    <span class="badge badge-success me-2">L</span>
                                    <span class="fw-bold text-success">Left Eye (OS)</span>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fs-8 text-gray-600 mb-1">Target SPH & CYL</label>
                                    <div class="fs-7 fw-bolder pair-target-rx-left">-</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fs-8 text-gray-600 mb-1">Matched Range Power</label>
                                    <select class="form-select form-select-solid form-select-sm pair-range-left pointer-events-none bg-light" tabindex="-1">
                                        <option value="">Range Auto-Selected</option>
                                    </select>
                                </div>
                                <div class="mb-1">
                                    <label class="form-label fs-8 text-gray-600 mb-1">Unit Price</label>
                                    <div class="input-group input-group-sm input-group-solid">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control form-control-solid pair-price-left" value="0.00" step="0.01" min="0">
                                    </div>
                                </div>
                                <div class="pair-status-left fs-8 fw-bold mt-2 h-20px text-end"></div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end align-items-center mt-3 pt-3 border-top border-info border-dashed">
                        <span class="text-gray-600 me-3 fs-7 fw-semibold">Subtotal for Pair:</span>
                        <span class="fs-4 fw-bolder text-info pair-total-display">$0.00</span>
                    </div>
                </div>
            </div>
        `;

        // Select2 Helpers
        function initSelect2(element) {
            const $select = $(element);
            if ($select.hasClass('select2-hidden-accessible')) {
                try {
                    const data = $select.data('select2');
                    if (data && typeof data === 'object') $select.select2('destroy');
                    else { $select.removeClass('select2-hidden-accessible'); $select.next('.select2-container').remove(); }
                } catch(e) { $select.removeClass('select2-hidden-accessible'); $select.next('.select2-container').remove(); }
            }
            try {
                const $form = $('#kt_modal_form');
                const dropdownParent = ($form.closest('.modal').length && $form.closest('.modal')[0]) || $form[0] || document.body;
                $select.select2({ dropdownParent: $(dropdownParent), width: '100%', allowClear: true });
            } catch(e) {}
        }

        function updateSelect2Options($select, options) {
            const isSelect2 = $select.hasClass('select2-hidden-accessible');
            const val = $select.val();
            if (isSelect2) {
                try {
                    const data = $select.data('select2');
                    if (data && typeof data === 'object') $select.select2('destroy');
                    else { $select.removeClass('select2-hidden-accessible'); $select.next('.select2-container').remove(); }
                } catch(e) { $select.removeClass('select2-hidden-accessible'); $select.next('.select2-container').remove(); }
            }
            $select.html(options);
            if (isSelect2) {
                initSelect2($select[0]);
                if (val) $select.val(val).trigger('change');
            }
        }

        // Action Handlers
        $('#add_product_row').on('click', function(e) {
            e.preventDefault();
            const $row = $(productRowTemplate);
            $('#products_body').append($row);
            setTimeout(() => initSelect2($row.find('.product-select')[0]), 50);
            calculateTotals();
        });

        $('#add_lens_row').on('click', function(e) {
            e.preventDefault();
            $('#lenses_table_wrapper').removeClass('d-none');
            const $row = $(lensRowTemplate);
            $('#lenses_body').append($row);
            setTimeout(() => $row.find('select[data-kt-select2="true"]').each(function() { initSelect2(this); }), 50);
            calculateTotals();
        });

        function addLensPairBlock() {
            const $block = $(lensPairBlockTemplate);
            $('#lens_pairs_container').append($block);
            
            const p = getPaperData();
            if(!p || p.add <= 0) {
                // No addition -> Force distance and disable reading
                $block.find('.pair-usage-toggle').val('distance');
                $block.find('.pair-usage-toggle option[value="reading"]').attr('disabled', 'disabled');
            } else {
                // Has addition -> leave unselected but enable both
                $block.find('.pair-usage-toggle').val('');
                $block.find('.pair-usage-toggle option[value="reading"]').removeAttr('disabled');
            }
            
            setTimeout(() => {
                $block.find('select[data-kt-select2="true"]').each(function() { initSelect2(this); });
                calculateTargetRx($block);
            }, 50);
        }

        $('#add_lens_pair_btn').on('click', function(e) {
            e.preventDefault(); addLensPairBlock();
        });

        $(document).on('click', '.remove-product-row', function() {
            if ($('.product-row').length > 1) {
                const $row = $(this).closest('.product-row');
                $row.removeClass('animate__fadeIn').addClass('animate__fadeOut');
                setTimeout(() => { $row.remove(); calculateTotals(); }, 300);
            } else {
                alert('At least one product is required.');
            }
        });

        $(document).on('click', '.remove-lens-row', function() {
            const $row = $(this).closest('.lens-row');
            $row.removeClass('animate__fadeIn').addClass('animate__fadeOut');
            setTimeout(() => {
                $row.remove();
                if ($('.lens-row').length === 0) $('#lenses_table_wrapper').addClass('d-none');
                calculateTotals();
            }, 300);
        });

        $(document).on('click', '.remove-lens-pair-block', function() {
            const $block = $(this).closest('.lens-pair-block');
            $block.removeClass('animate__fadeIn').addClass('animate__fadeOut');
            setTimeout(() => { $block.remove(); calculateTotals(); }, 300);
        });

        // Totals Calculation Engine
        function calculateRowTotal(row) {
            const $r = $(row);
            const qty = parseFloat($r.find('input[name="quantity[]"]').val()) || 0;
            const price = parseFloat($r.find('input[name="price[]"]').val()) || 0;
            $r.find('.row-total').val((qty * price).toFixed(2));
        }

        function calculateLensRowTotal(row) {
            const $r = $(row);
            const qty = parseFloat($r.find('input[name="lens_quantity[]"]').val()) || 0;
            const price = parseFloat($r.find('input[name="lens_price[]"]').val()) || 0;
            $r.find('.lens-row-total').val(((qty * price)/2).toFixed(2));
        }

        function updatePairBlockTotal($block) {
            const pr = parseFloat($block.find('.pair-price-right').val()) || 0;
            const pl = parseFloat($block.find('.pair-price-left').val()) || 0;
            $block.find('.pair-total-display').text('$' + ((pr + pl)/2).toFixed(2));
            calculateTotals();
        }

        function calculateTotals() {
            let pt = 0, lt = 0;
            $('.row-total').each(function() { pt += parseFloat($(this).val()) || 0; });
            $('.lens-row-total').each(function() { lt += parseFloat($(this).val()) || 0; });
            $('.pair-total-display').each(function() { lt += parseFloat($(this).text().replace('$','')) || 0; });
            
            const gt = pt + lt;
            const paid = parseFloat($('#paid_amount').val()) || 0;
            const rem = Math.max(0, gt - paid);
            
            $('#products_total').text(pt.toFixed(2));
            $('#lenses_total').text(lt.toFixed(2));
            $('#grand_total').text(gt.toFixed(2));
            $('#amount_input').val(gt.toFixed(2));
            $('#remaining_amount').text(rem.toFixed(2));
            
            // Status updating
            let st = 'unpaid';
            let bText = 'Unpaid', bClass = 'badge-light-danger', bTextColor='text-danger';
            if (paid >= gt && gt > 0) {
                st = 'paid'; bText = 'Paid in Full'; bClass = 'badge-light-success'; bTextColor='text-success';
            } else if (paid > 0) {
                st = 'partial'; bText = 'Partial Payment'; bClass = 'badge-light-warning'; bTextColor='text-warning';
            }
            $('#status_input').val(st);
            $('#payment_status_badge').removeClass('badge-light-danger badge-light-success badge-light-warning').addClass(bClass).text(bText);
            $('#remaining_amount_display').removeClass('text-danger text-success text-warning').addClass(bTextColor);

            // Polish payment wrapper dynamically
            if(paid > 0) {
                $('#payment_input_wrapper').addClass('border-active').addClass('shadow');
            } else {
                $('#payment_input_wrapper').removeClass('border-active').removeClass('shadow');
            }
        }

        // Live Event Listeners for Values
        $(document).on('change', '.product-select', function() {
            const v = $(this).val();
            if(v) $(this).closest('.product-row').find('.price-input').val((parseFloat(v.split('&')[1])||0).toFixed(2));
            calculateRowTotal($(this).closest('.product-row')[0]);
            calculateTotals();
        });
        
        $(document).on('input', '.quantity-input, .price-input', function() {
            calculateRowTotal($(this).closest('.product-row')[0]); calculateTotals();
        });

        $(document).on('input', '#paid_amount', calculateTotals);

        // Core AJAX Configuration and Helpers
        const lensAjaxUrl = '{{ route("admin.invoices.create") }}';
        const clientPaperUrlBase = '{{ route("admin.clients.paper", ["client" => 0]) }}';
        window.suggestedRanges = { right: [], left: [] };
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const basicAjaxArgs = { dataType: 'json', headers: { 'X-CSRF-TOKEN': csrfToken } };

        // [Prescription Handling Logic]
        function renderPrescriptionFromPaper(p) {
            const fV = v => (v == null || v === '') ? '-' : (Number(v) > 0 ? '+'+Number(v) : Number(v));
            return `<input type="hidden" name="paper_id" value="${p.id}">
                <div id="prescription_paper_data" class="d-none" data-addtion="${p.addtion??0}" data-r-sph="${p.raw?.R_sph??''}" data-r-cyl="${p.raw?.R_cyl??''}" data-l-sph="${p.raw?.L_sph??''}" data-l-cyl="${p.raw?.L_cyl??''}"></div>
                <div class="row g-0 flex-column flex-md-row rounded border border-gray-300 bg-white mb-3" style="direction: ltr;">
                    <div class="col-12 col-md-6 border-bottom border-md-bottom-0 border-md-end border-gray-300 p-2 position-relative bg-danger bg-opacity-5">
                       <h6 class="text-danger text-center fs-7 fw-bolder mb-2">Right (OD)</h6>
                       <div class="d-flex justify-content-between text-center px-1">
                           <div class="flex-column w-33">
                               <span class="fs-9 text-gray-500 d-block mb-1">SPH</span>
                               <span class="fs-7 fw-bold text-gray-800">${fV(p.R_sph)}</span>
                           </div>
                           <div class="flex-column w-33 px-1 border-start border-end border-gray-300">
                               <span class="fs-9 text-gray-500 d-block mb-1">CYL</span>
                               <span class="fs-7 fw-bold text-gray-800">${fV(p.R_cyl)}</span>
                           </div>
                           <div class="flex-column w-33">
                               <span class="fs-9 text-gray-500 d-block mb-1">AXIS</span>
                               <span class="fs-7 fw-bold text-gray-800">${p.R_axis||'-'}</span>
                           </div>
                       </div>
                       <div id="prescription_range_badge_right" class="mt-2 text-center fs-8"></div>
                    </div>
                    
                    <div class="col-12 col-md-6 p-2 position-relative bg-success bg-opacity-5">
                       <h6 class="text-success text-center fs-7 fw-bolder mb-2">Left (OS)</h6>
                       <div class="d-flex justify-content-between text-center px-1">
                           <div class="flex-column w-33">
                               <span class="fs-9 text-gray-500 d-block mb-1">SPH</span>
                               <span class="fs-7 fw-bold text-gray-800">${fV(p.L_sph)}</span>
                           </div>
                           <div class="flex-column w-33 px-1 border-start border-end border-gray-300">
                               <span class="fs-9 text-gray-500 d-block mb-1">CYL</span>
                               <span class="fs-7 fw-bold text-gray-800">${fV(p.L_cyl)}</span>
                           </div>
                           <div class="flex-column w-33">
                               <span class="fs-9 text-gray-500 d-block mb-1">AXIS</span>
                               <span class="fs-7 fw-bold text-gray-800">${p.L_axis||'-'}</span>
                           </div>
                       </div>
                       <div id="prescription_range_badge_left" class="mt-2 text-center fs-8"></div>
                    </div>
                </div>

                <div class="d-flex justify-content-around bg-light py-2 rounded border border-gray-300">
                    <div class="text-center">
                        <span class="fs-9 text-gray-500 fw-bold me-1">ADD:</span>
                        <span class="fs-8 fw-bolder text-gray-800">${fV(p.addtion)}</span>
                    </div>
                    <div class="text-center border-start border-gray-300 ps-4">
                        <span class="fs-9 text-gray-500 fw-bold me-1">IPD:</span>
                        <span class="fs-8 fw-bolder text-gray-800">${p.ipd||'-'}</span>
                    </div>
                </div>`;
        }
        
        function setEmptyPrescription(icon, title, desc) {
            $('#prescription_display').html(`
                <div class="text-center text-muted py-5 animate__animated animate__fadeIn">
                    <i class="ki-duotone ${icon} fs-3x text-gray-400 mb-3 block"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                    <div class="fs-6 fw-semibold">${title}</div><div class="fs-8 text-gray-500 mt-1">${desc}</div>
                </div>`);
            window.suggestedRanges = { right: [], left: [] };
        }

        $('#client_select').on('change', function() {
            const cid = $(this).val();
            if(!cid) return setEmptyPrescription('ki-search-list', 'No Client Selected', 'Select a client to view');
            $('#prescription_display').html('<div class="text-center py-5"><span class="spinner-border text-primary"></span></div>');
            $.ajax({ type:'GET', url:clientPaperUrlBase.replace(/\/0\//, '/'+cid+'/'), ...basicAjaxArgs })
             .done(r => {
                 if(!r||!r.paper) setEmptyPrescription('ki-eye-slash', 'No Prescription Found', 'Add lenses manually');
                 else { $('#prescription_display').html(renderPrescriptionFromPaper(r.paper)); loadPrescriptionRangeSuggestions(); }
             }).fail(() => setEmptyPrescription('ki-cross-circle','Error','Failed loading paper'));
        });

        function loadPrescriptionRangeSuggestions() {
            const $d = $('#prescription_paper_data'); if(!$d.length) return;
            const rS=$d.data('r-sph'), rC=$d.data('r-cyl'), lS=$d.data('l-sph'), lC=$d.data('l-cyl');
            
            const fetchSug = (sph,cyl,eye,bId) => {
                const $b = $('#'+bId);
                if(sph==null||sph===''||cyl==null||cyl==='') { $b.empty(); window.suggestedRanges[eye]=[]; return; }
                $b.html('<span class="spinner-border spinner-border-sm text-info"></span>');
                $.ajax({ type:'GET', url:lensAjaxUrl, data:{suggest_range:1,sph,cyl}, ...basicAjaxArgs })
                 .done(r => {
                     const lst = r?.data||[]; window.suggestedRanges[eye]=lst;
                     if(lst.length===0) $b.html('<span class="badge badge-light-danger fw-bold">No range match</span>');
                     else if(lst.length===1) $b.html('<span class="badge badge-light-success fw-bold">R: '+lst[0].name+'</span>');
                     else $b.html('<span class="badge badge-light-warning fw-bold" title="'+lst.map(x=>x.name).join(', ')+'">Multiple Matches</span>');
                 }).fail(()=>$b.html('<span class="badge badge-light-danger">Error</span>'));
            };
            fetchSug(rS,rC,'right','prescription_range_badge_right');
            fetchSug(lS,lC,'left','prescription_range_badge_left');
        }

        // [Lens Pair Dynamic Fetching]
        function getPaperData() {
            const $d = $('#prescription_paper_data');
            if(!$d.length) return null;
            return {
                rs: parseFloat($d.attr('data-r-sph'))||0, rc: parseFloat($d.attr('data-r-cyl'))||0,
                ls: parseFloat($d.attr('data-l-sph'))||0, lc: parseFloat($d.attr('data-l-cyl'))||0,
                add: parseFloat($d.attr('data-addtion'))||0
            };
        }

        function calculateTargetRx($block) {
            const p = getPaperData();
            const usage = $block.find('.pair-usage-toggle').val();
            let rx = { rs: null, rc: null, ls: null, lc: null, valid: false };
            
            if (p) {
                rx.valid = true;
                rx.rc = p.rc; rx.lc = p.lc;
                if (usage === 'reading' && p.add > 0) {
                    rx.rs = p.rs + p.add;
                    rx.ls = p.ls + p.add;
                } else {
                    rx.rs = p.rs;
                    rx.ls = p.ls;
                }
            }
            
            $block.data('target-rx', rx);
            const fV = v => v === null ? '-' : (v > 0 ? '+'+Math.abs(v).toFixed(2) : '-'+Math.abs(v).toFixed(2));
            $block.find('.pair-target-rx-right').text(rx.valid ? `SPH ${fV(rx.rs)} | CYL ${fV(rx.rc)}` : 'Manual');
            $block.find('.pair-target-rx-left').text(rx.valid ? `SPH ${fV(rx.ls)} | CYL ${fV(rx.lc)}` : 'Manual');
            
            if (rx.valid) {
                $.when(
                    $.ajax({ type:'GET', url:lensAjaxUrl, data:{suggest_range:1,sph:rx.rs,cyl:rx.rc}, ...basicAjaxArgs }),
                    $.ajax({ type:'GET', url:lensAjaxUrl, data:{suggest_range:1,sph:rx.ls,cyl:rx.lc}, ...basicAjaxArgs })
                ).done((rR, rL) => {
                    const sr = (rR&&rR[0]&&rR[0].data)||[];
                    const sl = (rL&&rL[0]&&rL[0].data)||[];
                    $block.data('valid-ranges-right', sr.map(x=>x.id));
                    $block.data('valid-ranges-left', sl.map(x=>x.id));
                    processBrandSelectionForBlock($block);
                });
            } else {
                $block.data('valid-ranges-right', []);
                $block.data('valid-ranges-left', []);
            }
        }

        function processBrandSelectionForBlock($block) {
            const t = $block.find('.pair-type-select').val();
            const c = $block.find('.pair-brand-select').val();
            const validR = $block.data('valid-ranges-right') || [];
            const validL = $block.data('valid-ranges-left') || [];
            
            const $pr = $block.find('.pair-price-right'); const $pl = $block.find('.pair-price-left');
            const $sR = $block.find('.pair-range-right'); const $sL = $block.find('.pair-range-left');
            const $stR = $block.find('.pair-status-right'); const $stL = $block.find('.pair-status-left');
            
            $pr.val('0.00'); $pl.val('0.00'); updatePairBlockTotal($block);
            $sR.html('<option value="">Range Auto-Selected</option>');
            $sL.html('<option value="">Range Auto-Selected</option>');
            $block.data('lens-id-right', ''); $block.data('lens-id-left', '');
            $stR.empty(); $stL.empty();

            if(!t || !c) return;
            
            $stR.html('<span class="spinner-border spinner-border-sm text-info"></span>');
            $stL.html('<span class="spinner-border spinner-border-sm text-info"></span>');

            $.ajax({ type:'GET', url:lensAjaxUrl, data:{get_ranges_by_type_brand:1, type:t, category:c}, ...basicAjaxArgs }).done(ranges => {
                let bestR = null; let bestL = null;
                
                if (validR.length > 0) bestR = ranges.find(x => validR.includes(parseInt(x.value)));
                if (validL.length > 0) bestL = ranges.find(x => validL.includes(parseInt(x.value)));
                
                if(bestR) {
                    $sR.html(`<option value="${bestR.value}" selected>${bestR.text}</option>`);
                    $pr.val(bestR.lens_price);
                    $block.data('lens-id-right', bestR.lens_id);
                    $stR.html('<i class="ki-duotone ki-check fs-4 text-success"><span class="path1"></span><span class="path2"></span></i> <span class="text-success">Found Right Rx</span>');
                } else {
                    $stR.html('<span class="text-danger fw-normal">Range match unavailable</span>');
                }
                
                if(bestL) {
                    $sL.html(`<option value="${bestL.value}" selected>${bestL.text}</option>`);
                    $pl.val(bestL.lens_price);
                    $block.data('lens-id-left', bestL.lens_id);
                    $stL.html('<i class="ki-duotone ki-check fs-4 text-success"><span class="path1"></span><span class="path2"></span></i> <span class="text-success">Found Left Rx</span>');
                } else {
                    $stL.html('<span class="text-danger fw-normal">Range match unavailable</span>');
                }
                
                updatePairBlockTotal($block);
            });
        }

        $(document).on('change', '.pair-usage-toggle', function() {
            const $block = $(this).closest('.lens-pair-block');
            $block.find('.pair-type-select').val('').trigger('change.select2');
            $block.find('.pair-brand-select').val('').html('<option value=""></option>').trigger('change.select2');
            calculateTargetRx($block);
        });
        
        $(document).on('change', '.pair-type-select', function() {
            const $block = $(this).closest('.lens-pair-block');
            const t = $(this).val();
            const $br = $block.find('.pair-brand-select');
            updateSelect2Options($br, '<option value=""></option>');
            if (t) {
                $.ajax({ type:'GET',url:lensAjaxUrl,data:{get_brands_by_type:1, type:t}, ...basicAjaxArgs }).done(d => {
                    let o = '<option value=""></option>';
                    d.forEach(i => o += `<option value="${i.value}">${i.text}</option>`);
                    updateSelect2Options($br, o);
                });
            }
            processBrandSelectionForBlock($block);
        });

        $(document).on('change', '.pair-brand-select', function() {
            processBrandSelectionForBlock($(this).closest('.lens-pair-block'));
        });

        $(document).on('input', '.pair-price-right, .pair-price-left', function() { updatePairBlockTotal($(this).closest('.lens-pair-block')); });

        // [Single Lenses Dynamics]
        function resetLensPrice($r) { $r.find('.lens-price').val('0.00'); calculateLensRowTotal($r[0]); calculateTotals(); }
        
        $(document).on('blur change', '.lens-code-input', function() {
            const $r = $(this).closest('.lens-row'), code = $(this).val().trim(), $h = $r.find('.lens-code-help');
            if(!code) { $h.empty(); return; }
            $h.html('<span class="spinner-border spinner-border-sm text-info align-middle me-1"></span><span class="text-info align-middle">Searching...</span>');
            
            $.ajax({ type:'GET',url:lensAjaxUrl,data:{lens_code:code},...basicAjaxArgs })
             .done(d => {
                 if(d.range) {
                     $r.find('.lens-range-select').val(d.range).trigger('change');
                     setTimeout(() => {
                        $.ajax({ type:'GET',url:lensAjaxUrl,data:{range:d.range},...basicAjaxArgs }).done(dt=>{
                            let oo='<option value=""></option>'; dt.forEach(i=>oo+=`<option value="${i.value}">${i.text}</option>`); updateSelect2Options($r.find('.lens-type-select'), oo);
                            setTimeout(()=>{
                                $r.find('.lens-type-select').val(d.type_id).trigger('change');
                                setTimeout(()=>{
                                    $.ajax({ type:'GET',url:lensAjaxUrl,data:{type:d.type_id,range:d.range},...basicAjaxArgs }).done(dc=>{
                                        let co='<option value=""></option>'; dc.forEach(i=>co+=`<option value="${i.value}">${i.text}</option>`); updateSelect2Options($r.find('.lens-category-select'), co);
                                        setTimeout(()=>{ $r.find('.lens-category-select').val(d.category_id).trigger('change'); $h.html('<i class="ki-duotone ki-check fs-7 text-success me-1"><span class="path1"></span><span class="path2"></span></i><span class="text-success">Found Lens</span>'); }, 100);
                                    });
                                }, 150);
                            }, 150);
                        });
                     }, 150);
                 }
             }).fail(()=>$h.html('<i class="ki-duotone ki-cross fs-7 text-danger me-1"><span class="path1"></span><span class="path2"></span></i><span class="text-danger">Not Found</span>'));
        });

        $(document).on('change', '.lens-range-select', function() {
            const $r=$(this).closest('.lens-row'), v=$(this).val(); resetLensPrice($r);
            updateSelect2Options($r.find('.lens-type-select'),'<option value=""></option>'); updateSelect2Options($r.find('.lens-category-select'),'<option value=""></option>');
            if(v) $.ajax({ type:'GET',url:lensAjaxUrl,data:{range:v},...basicAjaxArgs }).done(d=>{ let o='<option value=""></option>'; d.forEach(i=>o+=`<option value="${i.value}">${i.text}</option>`); updateSelect2Options($r.find('.lens-type-select'), o); });
        });

        $(document).on('change', '.lens-type-select', function() {
            const $r=$(this).closest('.lens-row'), v=$(this).val(), rg=$r.find('.lens-range-select').val(); resetLensPrice($r);
            updateSelect2Options($r.find('.lens-category-select'),'<option value=""></option>');
            if(v&&rg) $.ajax({ type:'GET',url:lensAjaxUrl,data:{type:v,range:rg},...basicAjaxArgs }).done(d=>{ let o='<option value=""></option>'; d.forEach(i=>o+=`<option value="${i.value}">${i.text}</option>`); updateSelect2Options($r.find('.lens-category-select'), o); });
        });

        $(document).on('change', '.lens-category-select', function() {
            const $r=$(this).closest('.lens-row'), v=$(this).val();
            if(v) $r.find('.lens-price').val((parseFloat(v.split('&')[1])||0).toFixed(2));
            else resetLensPrice($r);
            calculateLensRowTotal($r[0]); calculateTotals();
        });

        $(document).on('input', '.lens-quantity, .lens-price', function() { calculateLensRowTotal($(this).closest('.lens-row')[0]); calculateTotals(); });

        // [Form Submission Injection]
        function injectLensSubmissionData(e) {
            const $c = $('#lens_pair_submit_data'); $c.empty();
            let invalid = false;
            $('.lens-pair-block').each(function() {
                const bVal = $(this).find('.pair-brand-select').val(); if(!bVal) return;
                if(!$(this).data('lens-id-right') && !$(this).data('lens-id-left')) { invalid=true; return false; }
            });
            if(invalid) { e.preventDefault(); e.stopPropagation(); Swal.fire({text:'Please ensure all Lens Pairs have matching ranges for at least one eye.',icon:'error',buttonsStyling:!1,confirmButtonText:'Ok, got it!',customClass:{confirmButton:'btn btn-primary'}}); return; }

            $('.lens-pair-block').each(function() {
                const $b = $(this), bVal = $b.find('.pair-brand-select').val();
                if(!bVal) return;
                const cId = bVal;
                const rLId = $b.data('lens-id-right'), lLId = $b.data('lens-id-left');
                const pR = parseFloat($b.find('.pair-price-right').val())||0, pL = parseFloat($b.find('.pair-price-left').val())||0;
                
                if(rLId) { 
                    $c.append($('<input>').attr({type:'hidden',name:'lens_category[]',value:`${cId}&${pR}&${rLId}`})); 
                    $c.append($('<input>').attr({type:'hidden',name:'lens_quantity[]',value:'1'})); 
                    $c.append($('<input>').attr({type:'hidden',name:'lens_price[]',value:pR})); 
                    $c.append($('<input>').attr({type:'hidden',name:'lens_eye[]',value:'right'})); 
                }
                if(lLId) { 
                    $c.append($('<input>').attr({type:'hidden',name:'lens_category[]',value:`${cId}&${pL}&${lLId}`})); 
                    $c.append($('<input>').attr({type:'hidden',name:'lens_quantity[]',value:'1'})); 
                    $c.append($('<input>').attr({type:'hidden',name:'lens_price[]',value:pL})); 
                    $c.append($('<input>').attr({type:'hidden',name:'lens_eye[]',value:'left'})); 
                }
            });
        }

        const fm = document.getElementById('kt_modal_form');
        if(fm) fm.addEventListener('submit', injectLensSubmissionData, true);

        // Core Init
        function initializeInvoiceForm() {
            loadPrescriptionRangeSuggestions(); calculateTotals();
            $('#kt_modal_form').find('select[data-kt-select2="true"]').each(function() { if(!$(this).hasClass('select2-hidden-accessible')) initSelect2(this); });
            $('#lenses_body').find('.lens-range-select, .lens-type-select, .lens-category-select').each(function() { if(!$(this).hasClass('select2-hidden-accessible')) initSelect2(this); });
            
            // Re-load data for edit mode rows
            setTimeout(() => {
                $('#lenses_body').find('.lens-row').each(function() {
                    const $r=$(this), r=$r.find('.lens-range-select').val(), t=$r.find('.lens-type-select').val();
                    if(r && $r.find('.lens-type-select option').length <= 1) {
                        $.ajax({ type:'GET',url:lensAjaxUrl,data:{range:r},...basicAjaxArgs }).done(dt=>{
                            let oo='<option value=""></option>'; dt.forEach(i=>oo+=`<option value="${i.value}" ${t==i.value?'selected':''}>${i.text}</option>`); updateSelect2Options($r.find('.lens-type-select'), oo);
                            if(t) $.ajax({ type:'GET',url:lensAjaxUrl,data:{type:t,range:r},...basicAjaxArgs }).done(dc=>{
                                const ct=$r.find('.lens-category-select').val(); let co='<option value=""></option>';
                                dc.forEach(i=>co+=`<option value="${i.value}" ${ct==i.value?'selected':''}>${i.text}</option>`); updateSelect2Options($r.find('.lens-category-select'), co);
                            });
                        });
                    }
                });
            }, 200);
        }

        initializeInvoiceForm();
        $(document).on('shown.bs.modal', function(e) { if($(e.target).find('#kt_modal_form').length) setTimeout(initializeInvoiceForm,300); });
    });
})();
</script>
