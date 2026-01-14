<x-app-layout>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ isset($transaction) ? 'Edit' : 'Create' }} Payment for Invoice</h3>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ isset($transaction) ? route('admin.invoices.transactions.update', $transaction->id) : route('admin.invoices.transactions.store', $invoice->id) }}">
                @csrf
                @if(isset($transaction))
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-6 mb-5">
                        <label class="form-label">Invoiced At</label>
                        <input type="text" class="form-control-plaintext" value="{{ isset($transaction) ? $transaction->invoice->invoiced_at->format('Y-m-d') : $invoice->invoiced_at->format('Y-m-d') }}" readonly>
                    </div>
                    <div class="col-md-6 mb-5">
                        <label class="form-label">Invoice Number</label>
                        <input type="text" class="form-control-plaintext" value="{{ isset($transaction) ? $transaction->invoice->invoice_number : $invoice->invoice_number }}" readonly>
                    </div>
                </div>

                <div class="mb-5">
                    <label class="form-label required">Paid At</label>
                    <input type="date" class="form-control" name="paid_at" value="{{ isset($transaction) ? $transaction->paid_at->format('Y-m-d') : \Carbon\Carbon::now()->format('Y-m-d') }}" required>
                </div>

                <div class="mb-5">
                    <label class="form-label">Remaining</label>
                    <input type="text" class="form-control-plaintext" value="{{ isset($transaction) ? number_format($transaction->invoice->remaining + $transaction->amount, 2) : number_format($invoice->remaining, 2) }}" readonly>
                </div>

                <div class="mb-5">
                    <label class="form-label required">Amount</label>
                    <input type="number" step="0.01" min="0" max="{{ isset($transaction) ? ($transaction->amount + $transaction->invoice->remaining) : $invoice->remaining }}" class="form-control" name="amount" value="{{ isset($transaction) ? $transaction->amount : 0 }}" required>
                </div>

                <div class="mb-5">
                    <label class="form-label required">Account</label>
                    <select name="account_id" class="form-select" required>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}" {{ (isset($transaction) && $transaction->account_id == $account->id) || (!isset($transaction) && $account->default) ? 'selected' : '' }}>
                                {{ $account->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-5">
                    <label class="form-label">Payment Method</label>
                    <input type="text" class="form-control" name="payment_method" value="{{ isset($transaction) ? $transaction->payment_method : '' }}">
                </div>

                <div class="mb-5">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="3">{{ isset($transaction) ? $transaction->description : '' }}</textarea>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('admin.transactions.index') }}" class="btn btn-light me-3">Cancel</a>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
