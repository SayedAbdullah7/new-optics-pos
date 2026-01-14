<x-app-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="fas fa-exchange-alt me-2"></i>Transaction Details
        </h4>
        <a href="{{ route('admin.transactions.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to List
        </a>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Transaction Information</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="bg-{{ $transaction->type === 'income' ? 'success' : 'danger' }} text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-{{ $transaction->type === 'income' ? 'arrow-down' : 'arrow-up' }} fa-2x"></i>
                        </div>
                        <h3 class="mt-3 mb-1 text-{{ $transaction->type === 'income' ? 'success' : 'danger' }}">
                            {{ $transaction->type === 'income' ? '+' : '-' }}{{ number_format($transaction->amount, 2) }}
                        </h3>
                        <span class="badge bg-{{ $transaction->type === 'income' ? 'success' : 'danger' }} fs-6">
                            {{ ucfirst($transaction->type ?? 'N/A') }}
                        </span>
                    </div>
                    <hr>
                    <table class="table table-borderless">
                        <tr>
                            <td class="text-muted">Transaction ID</td>
                            <td class="text-end"><strong>#{{ $transaction->id }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Account</td>
                            <td class="text-end">{{ $transaction->account?->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Payment Method</td>
                            <td class="text-end">{{ ucfirst($transaction->payment_method ?? 'Cash') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Reference</td>
                            <td class="text-end">{{ $transaction->reference ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Date</td>
                            <td class="text-end">{{ $transaction->paid_at?->format('M d, Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created By</td>
                            <td class="text-end">{{ $transaction->user?->name ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            @if($transaction->description)
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Description</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $transaction->description }}</p>
                    </div>
                </div>
            @endif

            @if($transaction->invoice)
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Related Invoice</h5>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('admin.invoices.show', $transaction->invoice->id) }}" class="text-decoration-none">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $transaction->invoice->invoice_number }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $transaction->invoice->client?->name }}</small>
                                </div>
                                <i class="fas fa-chevron-right text-muted"></i>
                            </div>
                        </a>
                    </div>
                </div>
            @endif

            @if($transaction->bill)
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Related Bill</h5>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('admin.bills.show', $transaction->bill->id) }}" class="text-decoration-none">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $transaction->bill->bill_number }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $transaction->bill->vendor?->name }}</small>
                                </div>
                                <i class="fas fa-chevron-right text-muted"></i>
                            </div>
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>





