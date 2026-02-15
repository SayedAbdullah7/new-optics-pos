@php
    $isEdit = isset($model);
    $actionRoute = $isEdit
        ? route('admin.bills.update', [$model->id])
        : route('admin.bills.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<x-form :actionRoute="$actionRoute" :method="$method" :isEdit="$isEdit">
    <!-- Bill Header Section -->
    <div class="row mb-5">
        <div class="col-md-6">
            <x-group-input-select
                label="Vendor"
                name="vendor_id"
                :value="$isEdit ? $model->vendor_id : (old('vendor_id') ?? ($vendor->id ?? ''))"
                :options="$vendors"
                required
            />
        </div>
        <div class="col-md-6">
            <x-group-input-text
                label="Bill Number"
                name="bill_number"
                :value="$isEdit ? $model->bill_number : (old('bill_number') ?? '')"
                placeholder="Auto-generated if empty"
            />
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-md-4">
            <x-group-input-date
                label="Bill Date"
                name="billed_at"
                :value="$isEdit ? ($model->billed_at ? $model->billed_at->format('Y-m-d') : '') : (old('billed_at') ?? date('Y-m-d'))"
                required
            />
        </div>
        <div class="col-md-4">
            <x-group-input-date
                label="Due Date"
                name="due_at"
                :value="$isEdit ? ($model->due_at ? $model->due_at->format('Y-m-d') : '') : (old('due_at') ?? '')"
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
                        @if($isEdit && $model->items && $model->items->count() > 0)
                            @foreach($model->items as $index => $item)
                                <tr class="product-row">
                                    <td>
                                        <select name="product[]" class="form-select form-select-solid product-select" data-kt-select2="true">
                                            <option value="">Select Product</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}&{{ $product->purchase_price }}"
                                                        data-price="{{ $product->purchase_price }}"
                                                        {{ $item->item_id == $product->id ? 'selected' : '' }}>
                                                    {{ $product->translateOrNew(app()->getLocale())->name ?? 'Product #' . $product->id }}
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
                                            <option value="{{ $product->id }}&{{ $product->purchase_price }}" data-price="{{ $product->purchase_price }}">
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
                            <td colspan="5">
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
                        @if($isEdit && $model->lenses && $model->lenses->count() > 0)
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
                                                    <option value="{{ $category->id }}&{{ $lens ? $lens->purchase_price : 0 }}&{{ $lens ? $lens->id : 0 }}"
                                                            {{ $lens && $lens->category_id == $category->id ? 'selected' : '' }}>
                                                        {{ $category->brand_name ?? $category->name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="lens_quantity[]" class="form-control form-control-solid lens-quantity"
                                               value="{{ $lensItem->quantity }}" min="1" step="1">
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

    console.log('Bill form script loaded');

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
        console.log('jQuery loaded, initializing bill form');

        // Product row template
        const productRowTemplate = `
            <tr class="product-row">
                <td>
                    <select name="product[]" class="form-select form-select-solid product-select" data-kt-select2="true">
                        <option value="">Select Product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}&{{ $product->purchase_price }}" data-price="{{ $product->purchase_price }}">
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
                    <input type="number" name="lens_quantity[]" class="form-control form-control-solid lens-quantity" value="1" min="1" step="1">
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
                // Silently fail
            }
        }

        // Update Select2 options and refresh
        function updateSelect2Options($select, options) {
            const isSelect2 = $select.hasClass('select2-hidden-accessible');
            const currentValue = $select.val();

            if (isSelect2) {
                try {
                    $select.select2('destroy');
                } catch (e) {
                    $select.removeClass('select2-hidden-accessible');
                    $select.next('.select2-container').remove();
                }
            }

            $select.html(options);

            initSelect2($select[0]);
            if (currentValue) {
                $select.val(currentValue).trigger('change');
            }
        }

        // Add product row
        $(document).on('click', '#add_product_row', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const $newRow = $(productRowTemplate);
            $('#products_body').append($newRow);

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

            const $newRow = $(lensRowTemplate);
            $('#lenses_body').append($newRow);

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

            const $rows = $('.product-row');
            if ($rows.length > 1) {
                const $row = $(this).closest('.product-row');
                // Destroy Select2
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

        // AJAX URL for lens filtering - Pointing to Bill Create
        const lensAjaxUrl = '{{ route("admin.bills.create") }}';

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

                        $rangeSelect.val(data.range).trigger('change');

                        setTimeout(function() {
                            // Load types
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

                                        setTimeout(function() {
                                            $typeSelect.val(data.type_id).trigger('change');

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
                    }
                },
                error: function(xhr) {
                   $help.text('Not found').removeClass('text-info text-success').addClass('text-danger');
                }
            });
        });

        // Event delegation for lens range change
        $(document).on('change', '.lens-range-select', function() {
            const $row = $(this).closest('.lens-row');
            const range = $(this).val();
            const $typeSelect = $row.find('.lens-type-select');
            const $categorySelect = $row.find('.lens-category-select');

            updateSelect2Options($typeSelect, '<option value="">Type</option>');
            updateSelect2Options($categorySelect, '<option value="">Brand</option>');
            resetLensPrice($row);

            if (!range) return;

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
                    }
                }
            });
        });

        // Event delegation for lens type change
        $(document).on('change', '.lens-type-select', function() {
            const $row = $(this).closest('.lens-row');
            const type = $(this).val();
            const range = $row.find('.lens-range-select').val();
            const $categorySelect = $row.find('.lens-category-select');

            updateSelect2Options($categorySelect, '<option value="">Brand</option>');
            resetLensPrice($row);

            if (!type || !range) return;

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
                    }
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

            const $rows = $('.lens-row');
            if ($rows.length > 1) {
                const $row = $(this).closest('.lens-row');
                // Destroy Select2
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
            // For bills, assume price is per unit
            const total = quantity * price; 
            $row.find('.lens-row-total').val(total.toFixed(2));
        }

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
            // Assuming there's an element for lens total if I added it? 
            // I didn't add #lenses_total in HTML but Invoice form has it.
            // I'll check if I added it in previous step. I didn't. 
            // But I can leave it or ignore.

            $('#grand_total').text(grandTotal.toFixed(2));
            $('#amount_input').val(grandTotal.toFixed(2));
            $('#remaining_amount').text(remaining.toFixed(2));

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

        // Initialize when modal content is loaded
        function initializeBillForm() {
            console.log('Initializing bill form');

            if ($('#products_body').length) {
                // Initialize Select2 for existing selects
                $('#modal-form').find('select[data-kt-select2="true"]').each(function() {
                    if (!$(this).hasClass('select2-hidden-accessible')) {
                        initSelect2(this);
                    }
                });
                
                // Initialize Lens selects
                $('#lenses_body').find('.lens-range-select, .lens-type-select, .lens-category-select').each(function() {
                     if (!$(this).hasClass('select2-hidden-accessible')) {
                        initSelect2(this);
                    }
                });

                calculateTotals();
            }
        }

        $(document).on('shown.bs.modal', '#modal-form', function() {
            setTimeout(initializeBillForm, 300);
        });

        $(document).ready(function() {
            setTimeout(function() {
                if ($('#modal-form').length && $('#modal-form').hasClass('show')) {
                    initializeBillForm();
                }
            }, 500);
        });
    });
})();
</script>
