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
    <!-- Invoice Header Section -->
    <div class="row mb-5">
        <div class="col-md-6">
            <h5 class="text-primary mb-3">
                <i class="ki-duotone ki-notepad fs-3 me-2">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
                Invoice Information
            </h5>

            <div class="row">
                <div class="col-md-6">
                    <x-group-input-select
                        label="Client"
                        name="client_id"
                        :value="$isEdit ? $model->client_id : (request('client_id') ?? old('client_id') ?? '')"
                        :options="$clients"
                        required
                        id="client_select"
                    />
                </div>
                <div class="col-md-6">
                    <x-group-input-text
                        label="Invoice Number"
                        name="invoice_number"
                        :value="$isEdit ? $model->invoice_number : ($invoiceNumber ?? old('invoice_number') ?? '')"
                        readonly
                    />
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <x-group-input-date
                        label="Invoice Date"
                        name="invoiced_at"
                        :value="$isEdit ? ($model->invoiced_at ? $model->invoiced_at->format('Y-m-d') : '') : (old('invoiced_at') ?? date('Y-m-d'))"
                        required
                    />
                </div>
                <div class="col-md-4">
                    <x-group-input-date
                        label="Due Date"
                        name="due_at"
                        :value="$isEdit ? ($model->due_at ? $model->due_at->format('Y-m-d') : '') : (old('due_at') ?? date('Y-m-d'))"
                        required
                    />
                </div>
                <div class="col-md-4">
                    <div class="fv-row mb-3">
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
            </div>
        </div>

        <!-- Prescription Display -->
        <div class="col-md-6">
            <h5 class="text-info mb-3">
                <i class="ki-duotone ki-eye fs-3 me-2">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                </i>
                Client Prescription
            </h5>
            <div id="prescription_display" class="border rounded p-3 bg-light-info">
                @if($clientPaper)
                    <input type="hidden" name="paper_id" value="{{ $clientPaper->id }}">
                    <div id="prescription_paper_data" class="d-none" data-r-sph="{{ $clientPaper->getRawRSph() }}" data-r-cyl="{{ $clientPaper->getRawRCyl() }}" data-l-sph="{{ $clientPaper->getRawLSph() }}" data-l-cyl="{{ $clientPaper->getRawLCyl() }}"></div>
                    <x-prescription-display :paper="$clientPaper" :showDate="false" :compact="true" />
                @else
                    <div class="text-center text-muted py-3">
                        <i class="ki-duotone ki-eye fs-3x opacity-25">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        <p class="mb-0 mt-2">Select a client to view prescription</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Products Section -->
    <div class="card mb-5">
        <div class="card-header bg-light-primary">
            <h5 class="card-title mb-0">
                <i class="ki-duotone ki-package fs-3 me-2 text-primary">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                </i>
                Products
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="products_table">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40%">Product</th>
                            <th style="width: 15%">Quantity</th>
                            <th style="width: 20%">Price</th>
                            <th style="width: 20%">Total</th>
                            <th style="width: 5%"></th>
                        </tr>
                    </thead>
                    <tbody id="products_body">
                        @if($isEdit && $model->items->count() > 0)
                            @foreach($model->items as $index => $item)
                                <tr class="product-row">
                                    <td>
                                        <select name="product[]" class="form-select form-select-solid product-select" data-kt-select2="true">
                                            <option value="">Select Product</option>
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
                                        <input type="number" name="quantity[]" class="form-control form-control-solid quantity-input"
                                               value="{{ $item->quantity }}" min="1">
                                    </td>
                                    <td>
                                        <input type="number" name="price[]" class="form-control form-control-solid price-input"
                                               value="{{ $item->price }}" step="0.01" min="0">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-solid row-total"
                                               value="{{ $item->total }}" readonly>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-icon btn-light-danger btn-sm remove-product-row">
                                            <i class="ki-duotone ki-cross fs-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr class="product-row">
                                <td>
                                    <select name="product[]" class="form-select form-select-solid product-select" data-kt-select2="true">
                                        <option value="">Select Product</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}&{{ $product->sale_price }}" data-price="{{ $product->sale_price }}">
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="quantity[]" class="form-control form-control-solid quantity-input" value="1" min="1">
                                </td>
                                <td>
                                    <input type="number" name="price[]" class="form-control form-control-solid price-input" value="0" step="0.01" min="0">
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-solid row-total" value="0" readonly>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-icon btn-light-danger btn-sm remove-product-row">
                                        <i class="ki-duotone ki-cross fs-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </button>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="8">
                                <button type="button" class="btn btn-sm btn-light-primary" id="add_product_row">
                                    <i class="ki-duotone ki-plus fs-2"></i> Add Product
                                </button>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Lenses Section -->
    <div class="card mb-5">
        <div class="card-header bg-light-info d-flex justify-content-between align-items-center flex-wrap">
            <h5 class="card-title mb-0">
                <i class="ki-duotone ki-eye fs-3 me-2 text-info">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                </i>
                Lenses
            </h5>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-info" id="add_lens_pair_btn">
                    <i class="ki-duotone ki-plus fs-2"></i> Add Lens Pair
                </button>
                <button type="button" class="btn btn-sm btn-light-info" id="add_lens_row">
                    <i class="ki-duotone ki-plus fs-2"></i> Add Single Lens
                </button>
            </div>
        </div>
        <div class="card-body">
            <div id="lens_pairs_container" class="mb-4"></div>
            <div class="table-responsive" id="lenses_table_wrapper">
                <table class="table table-bordered" id="lenses_table">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 10%">Code</th>
                            <th style="width: 12%">Range</th>
                            <th style="width: 12%">Type</th>
                            <th style="width: 12%">Brand</th>
                            <th style="width: 10%">Qty</th>
                            <th style="width: 12%">Price</th>
                            <th style="width: 12%">Total</th>
                            <th style="width: 5%"></th>
                        </tr>
                    </thead>
                    <tbody id="lenses_body">
                        @if($isEdit && $model->lenses->count() > 0)
                            @foreach($model->lenses as $index => $lensItem)
                                @php
                                    $lens = $lensItem->lens;
                                @endphp
                                <tr class="lens-row">
                                    <td>
                                        <input type="text" name="lens_code[]" class="form-control form-control-solid lens-code-input"
                                               value="{{ $lens ? $lens->lens_code : '' }}" placeholder="Code">
                                        <small class="lens-code-help form-text text-muted"></small>
                                    </td>
                                    <td>
                                        <select name="lens_range[]" class="form-select form-select-solid lens-range-select">
                                            <option value="">Range</option>
                                            @foreach($ranges as $range)
                                                <option value="{{ $range->id }}" {{ $lens && $lens->RangePower_id == $range->id ? 'selected' : '' }}>
                                                    {{ $range->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                <td>
                                    <select name="lens_type[]" class="form-select form-select-solid lens-type-select">
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
                                    <select name="lens_category[]" class="form-select form-select-solid lens-category-select">
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
                                        <input type="number" name="lens_quantity[]" class="form-control form-control-solid lens-quantity"
                                               value="{{ $lensItem->quantity }}" min="2" step="2">
                                    </td>
                                    <td>
                                        <input type="number" name="lens_price[]" class="form-control form-control-solid lens-price"
                                               value="{{ $lensItem->price }}" step="0.01" min="0">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-solid lens-row-total"
                                               value="{{ $lensItem->total }}" readonly>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-icon btn-light-danger btn-sm remove-lens-row">
                                            <i class="ki-duotone ki-cross fs-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
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

    <!-- Totals & Payment Section -->
    <div class="row">
        <div class="col-md-6">
            <x-group-input-textarea
                label="Notes"
                name="notes"
                :value="$isEdit ? $model->notes : (old('notes') ?? '')"
                rows="3"
            />
        </div>
        <div class="col-md-6">
            <div class="card bg-light">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fs-6">Products Total:</span>
                        <strong id="products_total">0.00</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fs-6">Lenses Total:</span>
                        <strong id="lenses_total">0.00</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fs-5 fw-bold">Grand Total:</span>
                        <strong class="fs-4 text-primary" id="grand_total">0.00</strong>
                        <input type="hidden" name="amount" id="amount_input" value="{{ $isEdit ? $model->amount : 0 }}">
                    </div>
                    <hr>
                    <div class="fv-row mb-3">
                        <label class="fw-semibold fs-6 mb-2">Payment Amount</label>
                        <input type="number" name="paid" class="form-control form-control-solid"
                               id="paid_amount" value="{{ $isEdit ? $model->paid : 0 }}"
                               step="0.01" min="0">
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="fs-6">Remaining:</span>
                        <strong class="text-danger" id="remaining_amount">0.00</strong>
                    </div>
                    <input type="hidden" name="status" id="status_input" value="{{ $isEdit ? $model->status : 'unpaid' }}">
                </div>
            </div>
        </div>
    </div>

    <div id="lens_pair_submit_data" class="d-none" aria-hidden="true"></div>
</x-form>

<script>
(function() {
    'use strict';

    console.log('Invoice form script loaded');

    // Wait for jQuery to be available
    function waitForJQuery(callback) {
        if (typeof $ !== 'undefined') {
            callback();
        } else {
            setTimeout(function() {
                waitForJQuery(callback);
            }, 100);
        }
    }

    waitForJQuery(function() {
        console.log('jQuery loaded, initializing invoice form');

        // Product row template
        const productRowTemplate = `
            <tr class="product-row">
                <td>
                    <select name="product[]" class="form-select form-select-solid product-select" data-kt-select2="true">
                        <option value="">Select Product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}&{{ $product->sale_price }}" data-price="{{ $product->sale_price }}">
                                {{ $product->translateOrNew(app()->getLocale())->name ?? 'Product #' . $product->id }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="number" name="quantity[]" class="form-control form-control-solid quantity-input" value="1" min="1">
                </td>
                <td>
                    <input type="number" name="price[]" class="form-control form-control-solid price-input" value="0" step="0.01" min="0">
                </td>
                <td>
                    <input type="number" class="form-control form-control-solid row-total" value="0" readonly>
                </td>
                <td>
                    <button type="button" class="btn btn-icon btn-light-danger btn-sm remove-product-row">
                        <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                    </button>
                </td>
            </tr>
        `;

        // Lens row template
        const lensRowTemplate = `
            <tr class="lens-row">
                <td>
                    <input type="text" name="lens_code[]" class="form-control form-control-solid lens-code-input"
                           value="" placeholder="Code">
                    <small class="lens-code-help form-text text-muted"></small>
                </td>
                <td>
                    <select name="lens_range[]" class="form-select form-select-solid lens-range-select">
                        <option value="">Range</option>
                        @foreach($ranges as $range)
                            <option value="{{ $range->id }}">{{ $range->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select name="lens_type[]" class="form-select form-select-solid lens-type-select">
                        <option value="">Type</option>
                    </select>
                </td>
                <td>
                    <select name="lens_category[]" class="form-select form-select-solid lens-category-select">
                        <option value="">Brand</option>
                    </select>
                </td>
                <td>
                    <input type="number" name="lens_quantity[]" class="form-control form-control-solid lens-quantity" value="2" min="2" step="2">
                </td>
                <td>
                    <input type="number" name="lens_price[]" class="form-control form-control-solid lens-price" value="0" step="0.01" min="0">
                </td>
                <td>
                    <input type="number" class="form-control form-control-solid lens-row-total" value="0" readonly>
                </td>
                <td>
                    <button type="button" class="btn btn-icon btn-light-danger btn-sm remove-lens-row">
                        <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                    </button>
                </td>
            </tr>
        `;

        // Lens pair block template (shared Type+Brand, per-eye Range+Price)
        const lensPairBlockTemplate = `
            <div class="card lens-pair-block mb-3 border border-info">
                <div class="card-header py-2 d-flex justify-content-between align-items-center bg-light-info">
                    <span class="fw-semibold">Lens Pair</span>
                    <button type="button" class="btn btn-icon btn-sm btn-light-danger remove-lens-pair-block">
                        <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                    </button>
                </div>
                <div class="card-body py-3">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Type</label>
                            <select class="form-select form-select-solid pair-type-select" data-kt-select2="true">
                                <option value="">Type</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Brand</label>
                            <select class="form-select form-select-solid pair-brand-select" data-kt-select2="true">
                                <option value="">Brand</option>
                            </select>
                        </div>
                    </div>
                    <div class="row" style="direction: ltr;">
                        <div class="col-md-6 border-end">
                            <label class="form-label small fw-semibold text-danger">Right Eye (OD)</label>
                            <div class="mb-2">
                                <label class="form-label small text-muted mb-0">Range</label>
                                <select class="form-select form-select-solid pair-range-right">
                                    <option value="">Range</option>
                                    @foreach($ranges as $range)
                                        <option value="{{ $range->id }}">{{ $range->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small text-muted mb-0">Price</label>
                                <input type="number" class="form-control form-control-solid pair-price-right" value="0" step="0.01" min="0">
                            </div>
                            <div class="pair-status-right small"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-danger">Left Eye (OS)</label>
                            <div class="mb-2">
                                <label class="form-label small text-muted mb-0">Range</label>
                                <select class="form-select form-select-solid pair-range-left">
                                    <option value="">Range</option>
                                    @foreach($ranges as $range)
                                        <option value="{{ $range->id }}">{{ $range->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small text-muted mb-0">Price</label>
                                <input type="number" class="form-control form-control-solid pair-price-left" value="0" step="0.01" min="0">
                            </div>
                            <div class="pair-status-left small"></div>
                        </div>
                    </div>
                    <div class="row mt-2 pt-2 border-top">
                        <div class="col text-end">
                            <span class="text-muted me-2">Pair Total:</span>
                            <strong class="pair-total-display">0.00</strong>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Initialize Select2 for a select element
        function initSelect2(selectElement) {
            const $select = $(selectElement);
            if ($select.hasClass('select2-hidden-accessible')) {
                try {
                    const select2Data = $select.data('select2');
                    // Check if select2Data is an object (not boolean or null)
                    if (select2Data && typeof select2Data === 'object' && select2Data.constructor === Object) {
                        $select.select2('destroy');
                    } else {
                        $select.removeClass('select2-hidden-accessible');
                        $select.next('.select2-container').remove();
                    }
                } catch (e) {
                    $select.removeClass('select2-hidden-accessible');
                    $select.next('.select2-container').remove();
                }
            }
            try {
                $select.select2({
                    dropdownParent: $('#modal-form'),
                    width: '100%',
                    placeholder: 'Select...'
                });
            } catch (e) {
                // Silently fail if Select2 initialization fails
            }
        }

        // Update Select2 options and refresh
        function updateSelect2Options($select, options) {
            const isSelect2 = $select.hasClass('select2-hidden-accessible');
            const currentValue = $select.val();

            if (isSelect2) {
                try {
                    const select2Data = $select.data('select2');
                    // Check if select2Data is an object (not boolean or null)
                    if (select2Data && typeof select2Data === 'object' && select2Data.constructor === Object) {
                        $select.select2('destroy');
                    } else {
                        $select.removeClass('select2-hidden-accessible');
                        $select.next('.select2-container').remove();
                    }
                } catch (e) {
                    $select.removeClass('select2-hidden-accessible');
                    $select.next('.select2-container').remove();
                }
            }

            $select.html(options);

            if (isSelect2) {
                initSelect2($select[0]);
                if (currentValue) {
                    $select.val(currentValue).trigger('change');
                }
            }
        }

        // Add product row
        $(document).on('click', '#add_product_row', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Add product row clicked');

            const $newRow = $(productRowTemplate);
            $('#products_body').append($newRow);

            // Initialize Select2 for the new row
            setTimeout(function() {
                const $select = $newRow.find('.product-select');
                if ($select.length) {
                    initSelect2($select[0]);
                }
            }, 100);

            calculateTotals();
        });

        // Add lens row (single)
        $(document).on('click', '#add_lens_row', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Add lens row clicked');

            const $newRow = $(lensRowTemplate);
            $('#lenses_body').append($newRow);

            // Initialize Select2 for lens selects in the new row
            setTimeout(function() {
                $newRow.find('.lens-range-select, .lens-type-select, .lens-category-select').each(function() {
                    initSelect2(this);
                });
            }, 100);

            calculateTotals();
        });

        // --- Lens Pair Block logic ---
        function addLensPairBlock() {
            const $block = $(lensPairBlockTemplate);
            $('#lens_pairs_container').append($block);

            const $rangeRight = $block.find('.pair-range-right');
            const $rangeLeft = $block.find('.pair-range-left');
            const suggestedRight = window.suggestedRanges && window.suggestedRanges.right;
            const suggestedLeft = window.suggestedRanges && window.suggestedRanges.left;

            if (Array.isArray(suggestedRight) && suggestedRight.length === 1) {
                $rangeRight.val(suggestedRight[0].id);
            } else if (Array.isArray(suggestedRight) && suggestedRight.length > 1) {
                $rangeRight.val(suggestedRight[0].id);
            }
            if (Array.isArray(suggestedLeft) && suggestedLeft.length === 1) {
                $rangeLeft.val(suggestedLeft[0].id);
            } else if (Array.isArray(suggestedLeft) && suggestedLeft.length > 1) {
                $rangeLeft.val(suggestedLeft[0].id);
            }

            setTimeout(function() {
                $block.find('.pair-type-select, .pair-brand-select').each(function() {
                    initSelect2(this);
                });
            }, 50);

            loadPairBlockTypes($block);
        }

        function loadPairBlockTypes($block) {
            const rangeR = $block.find('.pair-range-right').val();
            const rangeL = $block.find('.pair-range-left').val();
            const $typeSelect = $block.find('.pair-type-select');
            const $brandSelect = $block.find('.pair-brand-select');
            $brandSelect.html('<option value="">Brand</option>');
            $block.find('.pair-price-right, .pair-price-left').val(0);
            $block.find('.pair-status-right, .pair-status-left').html('');
            updatePairBlockTotal($block);

            if (!rangeR || !rangeL) {
                $typeSelect.html('<option value="">Type</option>');
                updateSelect2Options($typeSelect, '<option value="">Type</option>');
                return;
            }

            $.when(
                $.ajax({ type: 'GET', url: lensAjaxUrl, data: { range: rangeR }, dataType: 'json', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') } }),
                $.ajax({ type: 'GET', url: lensAjaxUrl, data: { range: rangeL }, dataType: 'json', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') } })
            ).done(function(resR, resL) {
                const typesR = (resR && resR[0] && Array.isArray(resR[0])) ? resR[0] : [];
                const typesL = (resL && resL[0] && Array.isArray(resL[0])) ? resL[0] : [];
                const idsR = {};
                typesR.forEach(function(t) { idsR[t.value] = t; });
                const intersection = [];
                typesL.forEach(function(t) {
                    if (idsR[t.value]) intersection.push(t);
                });
                let opts = '<option value="">Type</option>';
                intersection.forEach(function(t) {
                    opts += '<option value="' + t.value + '">' + t.text + '</option>';
                });
                updateSelect2Options($typeSelect, opts);
            });
        }

        function loadPairBlockBrands($block) {
            const type = $block.find('.pair-type-select').val();
            const rangeR = $block.find('.pair-range-right').val();
            const rangeL = $block.find('.pair-range-left').val();
            const $brandSelect = $block.find('.pair-brand-select');
            $block.find('.pair-price-right, .pair-price-left').val(0);
            $block.find('.pair-status-right, .pair-status-left').html('');
            updatePairBlockTotal($block);

            if (!type || !rangeR || !rangeL) {
                $brandSelect.html('<option value="">Brand</option>');
                updateSelect2Options($brandSelect, '<option value="">Brand</option>');
                return;
            }

            $.when(
                $.ajax({ type: 'GET', url: lensAjaxUrl, data: { type: type, range: rangeR }, dataType: 'json', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') } }),
                $.ajax({ type: 'GET', url: lensAjaxUrl, data: { type: type, range: rangeL }, dataType: 'json', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') } })
            ).done(function(resR, resL) {
                const catsR = (resR && resR[0] && Array.isArray(resR[0])) ? resR[0] : [];
                const catsL = (resL && resL[0] && Array.isArray(resL[0])) ? resL[0] : [];
                const byCatR = {};
                catsR.forEach(function(c) {
                    const catId = c.value.split('&')[0];
                    byCatR[catId] = c;
                });
                const intersection = [];
                catsL.forEach(function(c) {
                    const catId = c.value.split('&')[0];
                    if (byCatR[catId]) intersection.push(c);
                });
                let opts = '<option value="">Brand</option>';
                intersection.forEach(function(c) {
                    opts += '<option value="' + c.value + '">' + c.text + '</option>';
                });
                updateSelect2Options($brandSelect, opts);
            });
        }

        function updatePairBlockPricesFromBrand($block) {
            const brandValR = $block.find('.pair-brand-select').val();
            const type = $block.find('.pair-type-select').val();
            const rangeR = $block.find('.pair-range-right').val();
            const rangeL = $block.find('.pair-range-left').val();
            if (!type || !rangeR || !rangeL) return;

            const $statusR = $block.find('.pair-status-right');
            const $statusL = $block.find('.pair-status-left');
            const $priceR = $block.find('.pair-price-right');
            const $priceL = $block.find('.pair-price-left');

            $.when(
                $.ajax({ type: 'GET', url: lensAjaxUrl, data: { type: type, range: rangeR }, dataType: 'json', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') } }),
                $.ajax({ type: 'GET', url: lensAjaxUrl, data: { type: type, range: rangeL }, dataType: 'json', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') } })
            ).done(function(resR, resL) {
                const catsR = (resR && resR[0] && Array.isArray(resR[0])) ? resR[0] : [];
                const catsL = (resL && resL[0] && Array.isArray(resL[0])) ? resL[0] : [];
                const selectedBrandVal = $block.find('.pair-brand-select').val();
                if (!selectedBrandVal) {
                    $priceR.val(0);
                    $priceL.val(0);
                    $statusR.html('');
                    $statusL.html('');
                } else {
                    const catId = selectedBrandVal.split('&')[0];
                    const matchR = catsR.find(function(c) { return c.value.split('&')[0] === catId; });
                    const matchL = catsL.find(function(c) { return c.value.split('&')[0] === catId; });
                    if (matchR) {
                        const parts = matchR.value.split('&');
                        $priceR.val(parts[1] || 0);
                        $block.data('lens-id-right', parts[2] || '');
                        $statusR.html('<span class="text-success">Found</span>');
                    } else {
                        $priceR.val(0);
                        $block.data('lens-id-right', '');
                        $statusR.html('<span class="text-warning">Not found for this range</span>');
                    }
                    if (matchL) {
                        const parts = matchL.value.split('&');
                        $priceL.val(parts[1] || 0);
                        $block.data('lens-id-left', parts[2] || '');
                        $statusL.html('<span class="text-success">Found</span>');
                    } else {
                        $priceL.val(0);
                        $block.data('lens-id-left', '');
                        $statusL.html('<span class="text-warning">Not found for this range</span>');
                    }
                }
                updatePairBlockTotal($block);
                calculateTotals();
            });
        }

        function updatePairBlockTotal($block) {
            const pR = parseFloat($block.find('.pair-price-right').val()) || 0;
            const pL = parseFloat($block.find('.pair-price-left').val()) || 0;
            const total = (pR * 1) / 2 + (pL * 1) / 2;
            $block.find('.pair-total-display').text(total.toFixed(2));
            calculateTotals();
        }

        $(document).on('click', '#add_lens_pair_btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            addLensPairBlock();
        });

        $(document).on('click', '.remove-lens-pair-block', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const $block = $(this).closest('.lens-pair-block');
            $block.find('.pair-type-select, .pair-brand-select').each(function() {
                if ($(this).hasClass('select2-hidden-accessible')) {
                    try { $(this).select2('destroy'); } catch (err) {}
                }
            });
            $block.remove();
            calculateTotals();
        });

        $(document).on('change', '.pair-range-right, .pair-range-left', function() {
            const $block = $(this).closest('.lens-pair-block');
            loadPairBlockTypes($block);
        });

        $(document).on('change', '.pair-type-select', function() {
            const $block = $(this).closest('.lens-pair-block');
            loadPairBlockBrands($block);
        });

        $(document).on('change', '.pair-brand-select', function() {
            const $block = $(this).closest('.lens-pair-block');
            updatePairBlockPricesFromBrand($block);
        });

        $(document).on('input', '.pair-price-right, .pair-price-left', function() {
            const $block = $(this).closest('.lens-pair-block');
            updatePairBlockTotal($block);
        });

        // Event delegation for product select change
        $(document).on('change', '.product-select', function() {
            const $row = $(this).closest('.product-row');
            const value = $(this).val();
            if (value) {
                const parts = value.split('&');
                const price = parseFloat(parts[1]) || 0;
                $row.find('.price-input').val(price);
            }
            calculateRowTotal($row[0]);
            calculateTotals();
        });

        // Event delegation for product quantity/price input
        $(document).on('input', '.quantity-input, .price-input', function() {
            const $row = $(this).closest('.product-row');
            calculateRowTotal($row[0]);
            calculateTotals();
        });

        // Event delegation for remove product row
        $(document).on('click', '.remove-product-row', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Remove product row clicked');

            const $rows = $('.product-row');
            if ($rows.length > 1) {
                const $row = $(this).closest('.product-row');
                // Destroy Select2 if exists
                const $select = $row.find('.product-select');
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('destroy');
                }
                $row.remove();
                calculateTotals();
            } else {
                alert('At least one product row is required.');
            }
        });

        // AJAX URL for lens filtering
        const lensAjaxUrl = '{{ route("admin.invoices.create") }}';

        // Suggested ranges from prescription (for pair block auto-fill)
        window.suggestedRanges = { right: [], left: [] };

        // Load range suggestions for prescription and show badges
        function loadPrescriptionRangeSuggestions() {
            const $data = $('#prescription_paper_data');
            if (!$data.length) return;

            const rSph = $data.data('r-sph');
            const rCyl = $data.data('r-cyl');
            const lSph = $data.data('l-sph');
            const lCyl = $data.data('l-cyl');

            function suggestOne(sph, cyl, eye, badgeId) {
                const $badge = $('#' + badgeId);
                if (sph === '' || sph === null || sph === undefined || cyl === '' || cyl === null || cyl === undefined) {
                    $badge.html('').removeClass('text-success text-warning text-danger');
                    if (eye === 'right') window.suggestedRanges.right = [];
                    else window.suggestedRanges.left = [];
                    return;
                }
                $badge.html('<span class="text-muted">...</span>').removeClass('text-success text-warning text-danger');
                $.ajax({
                    type: 'GET',
                    url: lensAjaxUrl,
                    data: { suggest_range: 1, sph: sph, cyl: cyl },
                    dataType: 'json',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') },
                    success: function(res) {
                        const list = (res && res.data) ? res.data : [];
                        if (eye === 'right') window.suggestedRanges.right = list;
                        else window.suggestedRanges.left = list;

                        if (list.length === 0) {
                            $badge.html('<span class="badge badge-light-danger">No range match</span>').addClass('text-danger');
                        } else if (list.length === 1) {
                            $badge.html('<span class="badge badge-light-success">Range: ' + list[0].name + '</span>').addClass('text-success');
                        } else {
                            const names = list.map(function(r) { return r.name; }).join(', ');
                            $badge.html('<span class="badge badge-light-warning">Ranges: ' + names + '</span>').addClass('text-warning');
                        }
                    },
                    error: function() {
                        $badge.html('<span class="badge badge-light-danger">Error</span>').addClass('text-danger');
                        if (eye === 'right') window.suggestedRanges.right = [];
                        else window.suggestedRanges.left = [];
                    }
                });
            }

            suggestOne(rSph, rCyl, 'right', 'prescription_range_badge_right');
            suggestOne(lSph, lCyl, 'left', 'prescription_range_badge_left');
        }

        // Event delegation for lens code input (search by code)
        $(document).on('blur change', '.lens-code-input', function() {
            const $row = $(this).closest('.lens-row');
            const $help = $row.find('.lens-code-help');
            const lensCode = $(this).val().trim();

            if (!lensCode) {
                $help.text('').removeClass('text-success text-danger');
                return;
            }

            $help.text('Searching...').removeClass('text-success text-danger').addClass('text-info');

            $.ajax({
                type: 'GET',
                url: lensAjaxUrl,
                data: { lens_code: lensCode },
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                success: function(data) {
                    if (data.range) {
                        const $rangeSelect = $row.find('.lens-range-select');
                        const $typeSelect = $row.find('.lens-type-select');
                        const $categorySelect = $row.find('.lens-category-select');

                        // Set range and trigger change to load types
                        $rangeSelect.val(data.range).trigger('change');

                        // Wait for types to load, then set type and load categories
                        setTimeout(function() {
                            // Load types first
                            $.ajax({
                                type: 'GET',
                                url: lensAjaxUrl,
                                data: { range: data.range },
                                dataType: 'json',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                                },
                                success: function(typesData) {
                                    if (Array.isArray(typesData) && typesData.length > 0) {
                                        let typeOptions = '<option value="">Type</option>';
                                        typesData.forEach(function(item) {
                                            typeOptions += '<option value="' + item.value + '">' + item.text + '</option>';
                                        });
                                        updateSelect2Options($typeSelect, typeOptions);

                                        // Set type value and trigger change to load categories
                                        setTimeout(function() {
                                            $typeSelect.val(data.type_id).trigger('change');

                                            // Load categories
                                            setTimeout(function() {
                                                $.ajax({
                                                    type: 'GET',
                                                    url: lensAjaxUrl,
                                                    data: { type: data.type_id, range: data.range },
                                                    dataType: 'json',
                                                    headers: {
                                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                                                    },
                                                    success: function(categoriesData) {
                                                        if (Array.isArray(categoriesData) && categoriesData.length > 0) {
                                                            let categoryOptions = '<option value="">Brand</option>';
                                                            categoriesData.forEach(function(item) {
                                                                categoryOptions += '<option value="' + item.value + '">' + item.text + '</option>';
                                                            });
                                                            updateSelect2Options($categorySelect, categoryOptions);

                                                            // Set category value
                                                            setTimeout(function() {
                                                                $categorySelect.val(data.category_id).trigger('change');
                                                                $help.text('Found').removeClass('text-info text-danger').addClass('text-success');
                                                            }, 100);
                                                        }
                                                    }
                                                });
                                            }, 200);
                                        }, 200);
                                    }
                                }
                            });
                        }, 300);

                        $help.text('Found').removeClass('text-info text-danger').addClass('text-success');
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 404) {
                        $help.text('Not found').removeClass('text-info text-success').addClass('text-danger');
                    } else {
                        $help.text('Error').removeClass('text-info text-success').addClass('text-danger');
                    }
                }
            });
        });

        // Event delegation for lens range change (filter types)
        $(document).on('change', '.lens-range-select', function() {
            const $row = $(this).closest('.lens-row');
            const range = $(this).val();
            const $typeSelect = $row.find('.lens-type-select');
            const $categorySelect = $row.find('.lens-category-select');

            // Clear type and category
            updateSelect2Options($typeSelect, '<option value="">Type</option>');
            updateSelect2Options($categorySelect, '<option value="">Brand</option>');

            // Reset price when range changes (or is cleared)
            resetLensPrice($row);

            if (!range) {
                return;
            }

            $.ajax({
                type: 'GET',
                url: lensAjaxUrl,
                data: { range: range },
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                success: function(data) {
                    if (Array.isArray(data) && data.length > 0) {
                        let options = '<option value="">Type</option>';
                        data.forEach(function(item) {
                            options += '<option value="' + item.value + '">' + item.text + '</option>';
                        });
                        updateSelect2Options($typeSelect, options);
                    } else {
                        updateSelect2Options($typeSelect, '<option value="">Type</option>');
                    }
                },
                error: function() {
                    console.error('Failed to load types for range:', range);
                    updateSelect2Options($typeSelect, '<option value="">Type</option>');
                }
            });
        });

        // Event delegation for lens type change (filter categories)
        $(document).on('change', '.lens-type-select', function() {
            const $row = $(this).closest('.lens-row');
            const type = $(this).val();
            const range = $row.find('.lens-range-select').val();
            const $categorySelect = $row.find('.lens-category-select');

            // Clear category
            updateSelect2Options($categorySelect, '<option value="">Brand</option>');

            // Reset price when type changes (or is cleared)
            resetLensPrice($row);

            // If type changed but no range selected, clear category only
            if (!type) {
                return;
            }

            // If no range selected, try to get it or return
            if (!range) {
                return;
            }

            $.ajax({
                type: 'GET',
                url: lensAjaxUrl,
                data: { type: type, range: range },
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                success: function(data) {
                    if (Array.isArray(data) && data.length > 0) {
                        let options = '<option value="">Brand</option>';
                        data.forEach(function(item) {
                            options += '<option value="' + item.value + '">' + item.text + '</option>';
                        });
                        updateSelect2Options($categorySelect, options);
                    } else {
                        updateSelect2Options($categorySelect, '<option value="">Brand</option>');
                    }
                },
                error: function() {
                    console.error('Failed to load categories for type:', type, 'and range:', range);
                    updateSelect2Options($categorySelect, '<option value="">Brand</option>');
                }
            });
        });

        // Event delegation for lens category change
        $(document).on('change', '.lens-category-select', function() {
            const $row = $(this).closest('.lens-row');
            const value = $(this).val();
            if (value) {
                const parts = value.split('&');
                const price = parseFloat(parts[1]) || 0;
                $row.find('.lens-price').val(price);
            } else {
                // Reset price if category is cleared
                resetLensPrice($row);
                return;
            }
            calculateLensRowTotal($row[0]);
            calculateTotals();
        });

        // Event delegation for lens quantity/price input
        $(document).on('input', '.lens-quantity, .lens-price', function() {
            const $row = $(this).closest('.lens-row');
            calculateLensRowTotal($row[0]);
            calculateTotals();
        });

        // Event delegation for remove lens row
        $(document).on('click', '.remove-lens-row', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Remove lens row clicked');

            const $rows = $('.lens-row');
            const $pairs = $('.lens-pair-block');
            if ($rows.length > 1 || $pairs.length > 0) {
                const $row = $(this).closest('.lens-row');
                // Destroy Select2 before removing
                $row.find('.lens-range-select, .lens-type-select, .lens-category-select').each(function() {
                    if ($(this).hasClass('select2-hidden-accessible')) {
                        $(this).select2('destroy');
                    }
                });
                $row.remove();
                calculateTotals();
            } else {
                alert('Add at least one lens (pair or single) before removing.');
            }
        });

        // Calculate product row total
        function calculateRowTotal(row) {
            const $row = $(row);
            const quantity = parseFloat($row.find('.quantity-input').val()) || 0;
            const price = parseFloat($row.find('.price-input').val()) || 0;
            const total = quantity * price;
            $row.find('.row-total').val(total.toFixed(2));
        }

        // Calculate lens row total
        function calculateLensRowTotal(row) {
            const $row = $(row);
            const quantity = parseFloat($row.find('.lens-quantity').val()) || 0;
            const price = parseFloat($row.find('.lens-price').val()) || 0;
            const total = (quantity * price) / 2;
            $row.find('.lens-row-total').val(total.toFixed(2));
        }

        // Reset lens price and recalculate totals
        function resetLensPrice($row) {
            $row.find('.lens-price').val(0);
            calculateLensRowTotal($row[0]);
            calculateTotals();
        }

        // Calculate all totals
        function calculateTotals() {
            let productsTotal = 0;
            let lensesTotal = 0;

            $('.row-total').each(function() {
                productsTotal += parseFloat($(this).val()) || 0;
            });

            $('.lens-row-total').each(function() {
                lensesTotal += parseFloat($(this).val()) || 0;
            });

            $('.pair-total-display').each(function() {
                lensesTotal += parseFloat($(this).text()) || 0;
            });

            const grandTotal = productsTotal + lensesTotal;
            const paidAmount = parseFloat($('#paid_amount').val()) || 0;
            const remaining = grandTotal - paidAmount;

            $('#products_total').text(productsTotal.toFixed(2));
            $('#lenses_total').text(lensesTotal.toFixed(2));
            $('#grand_total').text(grandTotal.toFixed(2));
            $('#amount_input').val(grandTotal.toFixed(2));
            $('#remaining_amount').text(remaining.toFixed(2));

            // Update status
            let status = 'unpaid';
            if (paidAmount >= grandTotal && grandTotal > 0) {
                status = 'paid';
            } else if (paidAmount > 0) {
                status = 'partial';
            }
            $('#status_input').val(status);
        }

        // Payment amount change
        $(document).on('input', '#paid_amount', calculateTotals);

        // On form submit: serialize lens pair blocks into hidden inputs
        $(document).on('submit', 'form', function() {
            const $container = $('#lens_pair_submit_data');
            $container.empty();

            $('.lens-pair-block').each(function() {
                const $block = $(this);
                const brandVal = $block.find('.pair-brand-select').val();
                if (!brandVal) return;
                const catId = brandVal.split('&')[0];
                const lensIdR = $block.data('lens-id-right');
                const lensIdL = $block.data('lens-id-left');
                const priceR = parseFloat($block.find('.pair-price-right').val()) || 0;
                const priceL = parseFloat($block.find('.pair-price-left').val()) || 0;
                const rangeR = $block.find('.pair-range-right').val();
                const rangeL = $block.find('.pair-range-left').val();

                if (rangeR === rangeL && lensIdR && lensIdR === lensIdL) {
                    $container.append($('<input>').attr({ type: 'hidden', name: 'lens_category[]', value: catId + '&' + priceR + '&' + lensIdR }));
                    $container.append($('<input>').attr({ type: 'hidden', name: 'lens_quantity[]', value: '2' }));
                    $container.append($('<input>').attr({ type: 'hidden', name: 'lens_price[]', value: priceR }));
                } else {
                    if (lensIdR) {
                        $container.append($('<input>').attr({ type: 'hidden', name: 'lens_category[]', value: catId + '&' + priceR + '&' + lensIdR }));
                        $container.append($('<input>').attr({ type: 'hidden', name: 'lens_quantity[]', value: '1' }));
                        $container.append($('<input>').attr({ type: 'hidden', name: 'lens_price[]', value: priceR }));
                    }
                    if (lensIdL) {
                        $container.append($('<input>').attr({ type: 'hidden', name: 'lens_category[]', value: catId + '&' + priceL + '&' + lensIdL }));
                        $container.append($('<input>').attr({ type: 'hidden', name: 'lens_quantity[]', value: '1' }));
                        $container.append($('<input>').attr({ type: 'hidden', name: 'lens_price[]', value: priceL }));
                    }
                }
            });
        });

        // Load types for existing lens rows that have range selected
        function loadTypesForExistingRows() {
            $('#lenses_body').find('.lens-row').each(function() {
                const $row = $(this);
                const $rangeSelect = $row.find('.lens-range-select');
                const $typeSelect = $row.find('.lens-type-select');
                const $categorySelect = $row.find('.lens-category-select');
                const range = $rangeSelect.val();
                const type = $typeSelect.val();

                // If range is selected but type select is empty (only has default option), load types
                if (range && $typeSelect.find('option').length <= 1) {
                    $.ajax({
                        type: 'GET',
                        url: lensAjaxUrl,
                        data: { range: range },
                        dataType: 'json',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                        },
                        success: function(data) {
                            if (Array.isArray(data) && data.length > 0) {
                                let options = '<option value="">Type</option>';
                                data.forEach(function(item) {
                                    const selected = type == item.value ? 'selected' : '';
                                    options += '<option value="' + item.value + '" ' + selected + '>' + item.text + '</option>';
                                });
                                updateSelect2Options($typeSelect, options);

                                // If type was already selected, load categories
                                if (type) {
                                    loadCategoriesForRow($row, type, range);
                                }
                            }
                        }
                    });
                }
            });
        }

        // Load categories for a row
        function loadCategoriesForRow($row, type, range) {
            const $categorySelect = $row.find('.lens-category-select');
            const currentCategory = $categorySelect.val();

            if (!type || !range) {
                return;
            }

            $.ajax({
                type: 'GET',
                url: lensAjaxUrl,
                data: { type: type, range: range },
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                success: function(data) {
                    if (Array.isArray(data) && data.length > 0) {
                        let options = '<option value="">Brand</option>';
                        data.forEach(function(item) {
                            const selected = currentCategory == item.value ? 'selected' : '';
                            options += '<option value="' + item.value + '" ' + selected + '>' + item.text + '</option>';
                        });
                        updateSelect2Options($categorySelect, options);
                    }
                }
            });
        }

        // Initialize when modal content is loaded
        function initializeInvoiceForm() {
            console.log('Initializing invoice form');

            loadPrescriptionRangeSuggestions();

            // Calculate initial totals
            if ($('#products_body').length && $('#lenses_body').length) {
                // If no lens rows exist, add one
                if ($('#lenses_body').find('.lens-row').length === 0) {
                    const $newRow = $(lensRowTemplate);
                    $('#lenses_body').append($newRow);

                    // Initialize Select2 for the new row
                    setTimeout(function() {
                        $newRow.find('.lens-range-select, .lens-type-select, .lens-category-select').each(function() {
                            initSelect2(this);
                        });
                    }, 100);
                }

                calculateTotals();

                // Initialize Select2 for existing selects with data-kt-select2
                $('#modal-form').find('select[data-kt-select2="true"]').each(function() {
                    if (!$(this).hasClass('select2-hidden-accessible')) {
                        initSelect2(this);
                    }
                });

                // Initialize Select2 for lens selects (even if they don't have data-kt-select2)
                $('#lenses_body').find('.lens-range-select, .lens-type-select, .lens-category-select').each(function() {
                    if (!$(this).hasClass('select2-hidden-accessible')) {
                        initSelect2(this);
                    }
                });

                // Load types and categories for existing rows
                setTimeout(function() {
                    loadTypesForExistingRows();
                }, 200);
            }
        }

        // Initialize when modal is shown
        $(document).on('shown.bs.modal', '#modal-form', function() {
            console.log('Modal shown, initializing form');
            setTimeout(initializeInvoiceForm, 300);
        });

        // Also check if modal is already open
        $(document).ready(function() {
            console.log('Document ready, checking for modal');
            loadPrescriptionRangeSuggestions();
            setTimeout(function() {
                if ($('#modal-form').length && $('#modal-form').hasClass('show')) {
                    console.log('Modal already open, initializing');
                    initializeInvoiceForm();
                }
            }, 500);
        });
    });
})();
</script>
