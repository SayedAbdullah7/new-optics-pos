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

        // Initialize Select2 for a select element
        function initSelect2(selectElement) {
            const $select = $(selectElement);
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }
            $select.select2({
                dropdownParent: $('#modal-form'),
                width: '100%',
                placeholder: 'Select...'
            });
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

        // Calculate product row total
        function calculateRowTotal(row) {
            const $row = $(row);
            const quantity = parseFloat($row.find('.quantity-input').val()) || 0;
            const price = parseFloat($row.find('.price-input').val()) || 0;
            const total = quantity * price;
            $row.find('.row-total').val(total.toFixed(2));
        }

        // Calculate all totals
        function calculateTotals() {
            let productsTotal = 0;

            $('.row-total').each(function() {
                productsTotal += parseFloat($(this).val()) || 0;
            });

            const grandTotal = productsTotal;
            const paidAmount = parseFloat($('#paid_amount').val()) || 0;
            const remaining = grandTotal - paidAmount;

            $('#products_total').text(productsTotal.toFixed(2));
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

        // Initialize when modal content is loaded
        function initializeBillForm() {
            console.log('Initializing bill form');

            // Calculate initial totals
            if ($('#products_body').length) {
                calculateTotals();

                // Initialize Select2 for existing selects with data-kt-select2
                $('#modal-form').find('select[data-kt-select2="true"]').each(function() {
                    if (!$(this).hasClass('select2-hidden-accessible')) {
                        initSelect2(this);
                    }
                });
            }
        }

        // Initialize when modal is shown
        $(document).on('shown.bs.modal', '#modal-form', function() {
            console.log('Modal shown, initializing form');
            setTimeout(initializeBillForm, 300);
        });

        // Also check if modal is already open
        $(document).ready(function() {
            console.log('Document ready, checking for modal');
            setTimeout(function() {
                if ($('#modal-form').length && $('#modal-form').hasClass('show')) {
                    console.log('Modal already open, initializing');
                    initializeBillForm();
                }
            }, 500);
        });
    });
})();
</script>