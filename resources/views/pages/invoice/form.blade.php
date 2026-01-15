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
        <div class="card-header bg-light-info">
            <h5 class="card-title mb-0">
                <i class="ki-duotone ki-eye fs-3 me-2 text-info">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                </i>
                Lenses
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
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
                    <tfoot>
                        <tr>
                            <td colspan="8">
                                <button type="button" class="btn btn-sm btn-light-info" id="add_lens_row">
                                    <i class="ki-duotone ki-plus fs-2"></i> Add Lens
                                </button>
                            </td>
                        </tr>
                    </tfoot>
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

        // Add lens row
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
            if ($rows.length > 1) {
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
                alert('At least one lens row is required.');
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
