@php
    $actionRoute = route('admin.invoices.addPayment', $invoice);
@endphp

<form id="kt_modal_form" class="form" action="{{ $actionRoute }}" method="post" data-method="POST">
    @csrf
    
    <div class="d-flex flex-column scroll-y px-3" id="kt_modal_scroll">
        <!-- Invoice Info -->
        <div class="alert alert-info mb-4">
            <div class="d-flex justify-content-between">
                <span>Invoice: <strong>{{ $invoice->invoice_number }}</strong></span>
                <span>Client: <strong>{{ $invoice->client?->name }}</strong></span>
            </div>
        </div>

        <!-- Payment Summary -->
        <div class="row mb-4">
            <div class="col-md-4 text-center">
                <div class="border rounded p-3">
                    <small class="text-muted d-block">Total Amount</small>
                    <strong class="fs-4">{{ number_format($invoice->amount, 2) }}</strong>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <div class="border rounded p-3 bg-light-success">
                    <small class="text-muted d-block">Paid</small>
                    <strong class="fs-4 text-success">{{ number_format($invoice->paid, 2) }}</strong>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <div class="border rounded p-3 bg-light-danger">
                    <small class="text-muted d-block">Remaining</small>
                    <strong class="fs-4 text-danger">{{ number_format($invoice->remaining, 2) }}</strong>
                </div>
            </div>
        </div>

        <!-- Payment Amount -->
        <div class="fv-row mb-4">
            <label class="required fw-semibold fs-6 mb-2">Payment Amount</label>
            <input type="number" 
                   name="amount" 
                   class="form-control form-control-solid" 
                   placeholder="Enter payment amount"
                   step="0.01" 
                   min="0.01" 
                   max="{{ $invoice->remaining }}"
                   value="{{ $invoice->remaining }}"
                   required>
            <div class="form-text">Maximum: {{ number_format($invoice->remaining, 2) }}</div>
        </div>

        <!-- Account Selection -->
        <div class="fv-row mb-4">
            <label class="required fw-semibold fs-6 mb-2">Account</label>
            <select name="account_id" class="form-select form-select-solid" data-kt-select2="true" required>
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}" {{ $account->default ? 'selected' : '' }}>
                        {{ $account->translateOrNew(app()->getLocale())->name ?? $account->id }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Payment Date -->
        <div class="fv-row mb-4">
            <label class="fw-semibold fs-6 mb-2">Payment Date</label>
            <input type="date" 
                   name="paid_at" 
                   class="form-control form-control-solid" 
                   value="{{ date('Y-m-d') }}">
        </div>
    </div>

    <div class="text-center pt-4 border-top mt-4">
        <button type="reset" class="btn btn-secondary me-3" data-bs-dismiss="modal">
            <i class="fas fa-times me-2"></i>Cancel
        </button>
        <button type="submit" class="btn btn-success">
            <span class="indicator-label"><i class="fas fa-check me-2"></i>Add Payment</span>
            <span class="indicator-progress d-none">Please wait...
                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
            </span>
        </button>
    </div>
</form>

