<x-app-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="fas fa-file-invoice me-2"></i>Bill Details
        </h4>
        <div>
            <a href="{{ route('admin.bills.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back
            </a>
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Print
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div>
                        <h5 class="mb-1">{{ $bill->bill_number }}</h5>
                        <span class="badge bg-{{ $bill->status === 'paid' ? 'success' : ($bill->status === 'partial' ? 'warning' : 'danger') }} fs-6">
                            {{ ucfirst($bill->status ?? 'unpaid') }}
                        </span>
                    </div>
                    <div class="text-end">
                        <p class="mb-0 text-muted">Bill Date</p>
                        <strong>{{ $bill->billed_at?->format('M d, Y') }}</strong>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted">Vendor:</h6>
                            <h5>{{ $bill->vendor?->name ?? 'N/A' }}</h5>
                            @if($bill->vendor?->phone)
                                <p class="mb-0">{{ is_array($bill->vendor->phone) ? implode(', ', $bill->vendor->phone) : $bill->vendor->phone }}</p>
                            @endif
                            <p>{{ $bill->vendor?->address ?? '' }}</p>
                        </div>
                        <div class="col-md-6 text-end">
                            <h6 class="text-muted">Due Date:</h6>
                            <strong>{{ $bill->due_at?->format('M d, Y') ?? 'N/A' }}</strong>
                        </div>
                    </div>

                    @if($bill->items && $bill->items->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Item</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bill->items as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item->name ?? $item->product?->name ?? 'N/A' }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>{{ number_format($item->price, 2) }}</td>
                                            <td>{{ number_format($item->total, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            @if($bill->notes)
                                <h6 class="text-muted">Notes:</h6>
                                <p>{{ $bill->notes }}</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light p-3 rounded">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Total Amount:</span>
                                    <strong>{{ number_format($bill->amount, 2) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2 text-success">
                                    <span>Paid:</span>
                                    <strong>{{ number_format($bill->paid, 2) }}</strong>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between text-danger">
                                    <span><strong>Balance:</strong></span>
                                    <strong>{{ number_format($bill->balance, 2) }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Payment History</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($bill->transactions as $transaction)
                            <li class="list-group-item d-flex justify-content-between">
                                <div>
                                    <strong class="text-danger">-{{ number_format($transaction->amount, 2) }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $transaction->paid_at?->format('M d, Y') }}</small>
                                </div>
                                <span class="badge bg-light text-dark">{{ $transaction->payment_method ?? 'Cash' }}</span>
                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted">No payments yet</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>





