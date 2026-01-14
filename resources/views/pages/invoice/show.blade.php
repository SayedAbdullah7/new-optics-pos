<x-app-layout>
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-xxl">
        <!-- Page Header -->
        <div class="card mb-5">
            <div class="card-body py-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-60px symbol-circle me-4">
                            @php
                                $statusColors = [
                                    'paid' => 'success',
                                    'partial' => 'warning',
                                    'unpaid' => 'danger',
                                    'canceled' => 'secondary',
                                    'cancelled' => 'secondary',
                                ];
                                $statusColor = $statusColors[$invoice->status] ?? 'primary';
                            @endphp
                            <div class="symbol-label bg-light-{{ $statusColor }} text-{{ $statusColor }} fs-2 fw-bold">
                                <i class="ki-duotone ki-notepad fs-2x text-{{ $statusColor }}">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                    <span class="path5"></span>
                                </i>
                            </div>
                        </div>
                        <div>
                            <h3 class="mb-1 text-gray-900">{{ $invoice->invoice_number }}</h3>
                            <span class="badge badge-light-{{ $statusColor }} fs-7">{{ ucfirst($invoice->status) }}</span>
                            <span class="text-muted fs-7 ms-2">{{ $invoice->invoiced_at?->format('M d, Y') }}</span>
                        </div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        @if(!$invoice->isCancelled() && $invoice->remaining > 0)
                            <x-action-button 
                                :action="route('admin.invoices.paymentForm', $invoice)"
                                type="create"
                                variant="success"
                                icon="plus"
                                label="Add Payment"
                            />
                        @endif
                        <a href="{{ route('admin.invoices.print', $invoice) }}" class="btn btn-light-primary" target="_blank">
                            <i class="ki-duotone ki-printer fs-4 me-1">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                                <span class="path4"></span>
                                <span class="path5"></span>
                            </i>
                            Print
                        </a>
                        @if(!$invoice->isCancelled())
                            <x-action-button 
                                :action="route('admin.invoices.edit', $invoice)"
                                type="edit"
                                variant="warning"
                                icon="pencil"
                                label="Edit"
                            />
                        @endif
                        <a href="{{ route('admin.invoices.index') }}" class="btn btn-light">
                            <i class="ki-duotone ki-arrow-left fs-4 me-1">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-5">
            <!-- Left Column - Invoice Details -->
            <div class="col-lg-8">
                <!-- Client & Invoice Info -->
                <div class="card mb-5">
                    <div class="card-header bg-primary">
                        <h5 class="card-title mb-0 text-white">
                            <i class="ki-duotone ki-user fs-3 me-2 text-white">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Client & Invoice Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="text-muted fs-5 fw-semibold d-block mb-3">BILL TO</label>
                                <h5 class="mb-3 text-gray-800">{{ $invoice->client?->name ?? 'N/A' }}</h5>
                                @if($invoice->client?->phone && is_array($invoice->client->phone))
                                    @foreach(array_filter($invoice->client->phone) as $phone)
                                        <div class="mb-2">
                                            <i class="ki-duotone ki-phone fs-5 me-2 text-muted">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            <span class="text-gray-700">{{ $phone }}</span>
                                        </div>
                                    @endforeach
                                @endif
                                @if($invoice->client?->address)
                                    <div class="mb-2">
                                        <i class="ki-duotone ki-geolocation fs-5 me-2 text-muted">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        <span class="text-gray-700">{{ $invoice->client->address }}</span>
                                    </div>
                                @endif
                                @if($invoice->client)
                                    <div class="mt-4">
                                        <a href="{{ route('admin.clients.show', $invoice->client) }}" class="btn btn-sm btn-light-info">
                                            <i class="ki-duotone ki-eye fs-5 me-1">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                            </i>
                                            View Client Profile
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted fs-5 fw-semibold d-block mb-3">INVOICE DETAILS</label>
                                <div class="mb-3">
                                    <label class="text-muted fs-6 d-block mb-1">Invoice Date</label>
                                    <span class="text-gray-800 fs-5">
                                        <i class="ki-duotone ki-calendar fs-5 me-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        {{ $invoice->invoiced_at?->format('M d, Y H:i') }}
                                    </span>
                                </div>
                                <div class="mb-3">
                                    <label class="text-muted fs-6 d-block mb-1">Due Date</label>
                                    <span class="text-gray-800 fs-5">
                                        <i class="ki-duotone ki-calendar-tick fs-5 me-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        {{ $invoice->due_at?->format('M d, Y') ?? 'N/A' }}
                                    </span>
                                </div>
                                <div class="mb-3">
                                    <label class="text-muted fs-6 d-block mb-1">Invoice Number</label>
                                    <span class="text-gray-800 fs-5 fw-bold">{{ $invoice->invoice_number }}</span>
                                </div>
                                @if($invoice->order_number)
                                    <div class="mb-3">
                                        <label class="text-muted fs-6 d-block mb-1">Order Number</label>
                                        <span class="text-gray-800 fs-5">{{ $invoice->order_number }}</span>
                                    </div>
                                @endif
                                <div class="mb-3">
                                    <label class="text-muted fs-6 d-block mb-1">Created By</label>
                                    <span class="text-gray-800 fs-5">
                                        <i class="ki-duotone ki-user fs-5 me-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        {{ $invoice->user?->name ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Prescription -->
                @if($invoice->paper)
                    <div class="card mb-5">
                        <div class="card-header bg-light-info">
                            <h5 class="card-title mb-0">
                                <i class="ki-duotone ki-eye fs-3 me-2 text-info">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                                Prescription
                            </h5>
                        </div>
                        <div class="card-body">
                            <x-prescription-display :paper="$invoice->paper" :showDate="false" />
                        </div>
                    </div>
                @endif

                <!-- Items Table -->
                @if($invoice->items->count() > 0)
                    <div class="card mb-5">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="ki-duotone ki-package fs-3 me-2 text-primary">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                                Products
                            </h5>
                        </div>
                        <div class="card-body py-0">
                            <div class="table-responsive">
                                <table class="table table-row-bordered align-middle gs-0 gy-4">
                                    <thead>
                                        <tr class="fw-bold text-muted bg-light">
                                            <th class="ps-4 rounded-start">#</th>
                                            <th>Item</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-end">Price</th>
                                            <th class="text-end pe-4 rounded-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($invoice->items as $index => $item)
                                            <tr>
                                                <td class="ps-4">{{ $index + 1 }}</td>
                                                <td>
                                                    <span class="text-gray-800 fw-semibold">
                                                        {{ $item->name ?? ($item->product ? $item->product->translate(app()->getLocale())->name : 'N/A') }}
                                                    </span>
                                                    @if($item->product)
                                                        <div class="text-muted fs-7">Item Code: {{ $item->product->item_code }}</div>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-light-primary">{{ $item->quantity }}</span>
                                                </td>
                                                <td class="text-end">{{ number_format($item->price, 2) }} EGP</td>
                                                <td class="text-end pe-4 fw-bold">{{ number_format($item->total, 2) }} EGP</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    @if($invoice->items->count() > 0)
                                        <tfoot>
                                            <tr class="fw-bold bg-light">
                                                <td colspan="4" class="text-end ps-4">Subtotal (Products):</td>
                                                <td class="text-end pe-4">{{ number_format($invoice->items->sum('total'), 2) }} EGP</td>
                                            </tr>
                                        </tfoot>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Lenses Table -->
                @if($invoice->lenses->count() > 0)
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
                        <div class="card-body py-0">
                            <div class="table-responsive">
                                <table class="table table-row-bordered align-middle gs-0 gy-4">
                                    <thead>
                                        <tr class="fw-bold text-muted bg-light">
                                            <th class="ps-4 rounded-start">#</th>
                                            <th>Lens</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-end">Price</th>
                                            <th class="text-end pe-4 rounded-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($invoice->lenses as $index => $lensItem)
                                            @php
                                                $lens = $lensItem->lens;
                                            @endphp
                                            <tr>
                                                <td class="ps-4">{{ $index + 1 }}</td>
                                                <td>
                                                    <span class="text-gray-800 fw-semibold">{{ $lensItem->name ?? ($lens ? $lens->full_name : 'Lens') }}</span>
                                                    @if($lens)
                                                        <div class="text-muted fs-7">
                                                            Code: {{ $lens->lens_code }}
                                                            @if($lens->rangePower)
                                                                • Range: {{ $lens->rangePower->name }}
                                                            @endif
                                                            @if($lens->type)
                                                                • Type: {{ $lens->type->name }}
                                                            @endif
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-light-info">{{ $lensItem->quantity }}</span>
                                                </td>
                                                <td class="text-end">{{ number_format($lensItem->price, 2) }} EGP</td>
                                                <td class="text-end pe-4 fw-bold">{{ number_format($lensItem->total, 2) }} EGP</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    @if($invoice->lenses->count() > 0)
                                        <tfoot>
                                            <tr class="fw-bold bg-light">
                                                <td colspan="4" class="text-end ps-4">Subtotal (Lenses):</td>
                                                <td class="text-end pe-4">{{ number_format($invoice->lenses->sum('total'), 2) }} EGP</td>
                                            </tr>
                                        </tfoot>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Notes -->
                @if($invoice->notes)
                    <div class="card mb-5">
                        <div class="card-header bg-light-warning">
                            <h5 class="card-title mb-0 text-warning">
                                <i class="ki-duotone ki-document fs-3 me-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                Notes
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-gray-800 fs-5 mb-0">{{ $invoice->notes }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Column - Summary & Payments -->
            <div class="col-lg-4">
                <!-- Financial Summary -->
                <div class="card mb-5">
                    <div class="card-header bg-light-primary">
                        <h5 class="card-title mb-0 text-primary">
                            <i class="ki-duotone ki-wallet fs-3 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                                <span class="path4"></span>
                            </i>
                            Financial Summary
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            $productsTotal = $invoice->items->sum('total');
                            $lensesTotal = $invoice->lenses->sum('total');
                            $grandTotal = $invoice->amount;
                            $paidAmount = $invoice->paid;
                            $remainingAmount = $invoice->remaining;
                            $paidPercentage = $grandTotal > 0 ? ($paidAmount / $grandTotal) * 100 : 0;
                        @endphp
                        
                        @if($productsTotal > 0)
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span class="text-muted fs-6">Products Total</span>
                                <span class="fw-semibold text-gray-700">{{ number_format($productsTotal, 2) }} EGP</span>
                            </div>
                        @endif
                        
                        @if($lensesTotal > 0)
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span class="text-muted fs-6">Lenses Total</span>
                                <span class="fw-semibold text-gray-700">{{ number_format($lensesTotal, 2) }} EGP</span>
                            </div>
                        @endif
                        
                        <div class="d-flex justify-content-between align-items-center py-3 border-top border-bottom border-2">
                            <span class="text-gray-800 fs-5 fw-bold">Grand Total</span>
                            <span class="fw-bold text-primary fs-3">{{ number_format($grandTotal, 2) }} EGP</span>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                            <span class="text-gray-600 fs-6">
                                <i class="ki-duotone ki-check-circle fs-5 me-1 text-success">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                Paid
                            </span>
                            <span class="fw-bold text-success fs-4">{{ number_format($paidAmount, 2) }} EGP</span>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center py-3">
                            <span class="text-gray-600 fs-6">
                                <i class="ki-duotone ki-information fs-5 me-1 {{ $remainingAmount > 0 ? 'text-danger' : 'text-success' }}">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                                Remaining
                            </span>
                            <span class="fw-bold fs-4 {{ $remainingAmount > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($remainingAmount, 2) }} EGP
                            </span>
                        </div>
                        
                        @if($grandTotal > 0)
                            <div class="progress h-10px mt-4 mb-2">
                                <div class="progress-bar {{ $paidPercentage == 100 ? 'bg-success' : ($paidPercentage > 0 ? 'bg-warning' : 'bg-danger') }}" 
                                     role="progressbar" 
                                     style="width: {{ $paidPercentage }}%">
                                </div>
                            </div>
                            <div class="text-center">
                                <span class="text-muted fs-7">Payment Progress: </span>
                                <span class="fw-bold {{ $paidPercentage == 100 ? 'text-success' : ($paidPercentage > 0 ? 'text-warning' : 'text-danger') }}">
                                    {{ round($paidPercentage) }}%
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Payment History -->
                <div class="card mb-5">
                    <div class="card-header bg-light-success">
                        <h5 class="card-title mb-0 text-success">
                            <i class="ki-duotone ki-calendar fs-3 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Payment History
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        @if($invoice->transactions->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-row-bordered align-middle mb-0">
                                    <thead>
                                        <tr class="fw-bold text-muted bg-light">
                                            <th class="ps-4">Date</th>
                                            <th class="text-end pe-4">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($invoice->transactions->sortByDesc('paid_at') as $transaction)
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="text-gray-700 fw-semibold">{{ $transaction->paid_at?->format('M d, Y') }}</div>
                                                    <div class="text-muted small">{{ $transaction->paid_at?->format('H:i') }}</div>
                                                    @if($transaction->account)
                                                        <div class="text-muted fs-7 mt-1">
                                                            <i class="ki-duotone ki-wallet fs-6 me-1">
                                                                <span class="path1"></span>
                                                                <span class="path2"></span>
                                                            </i>
                                                            {{ $transaction->account->name }}
                                                        </div>
                                                    @endif
                                                    @if($transaction->payment_method)
                                                        <div class="text-muted fs-7">
                                                            <i class="ki-duotone ki-credit-cart fs-6 me-1">
                                                                <span class="path1"></span>
                                                                <span class="path2"></span>
                                                            </i>
                                                            {{ ucfirst($transaction->payment_method) }}
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="text-end pe-4">
                                                    <span class="fw-bold fs-5 {{ $transaction->amount >= 0 ? 'text-success' : 'text-danger' }}">
                                                        {{ $transaction->amount >= 0 ? '+' : '' }}{{ number_format($transaction->amount, 2) }} EGP
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted py-5">
                                <i class="ki-duotone ki-calendar fs-3x mb-3 opacity-25">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <p class="mb-0">No payments recorded</p>
                            </div>
                        @endif
                    </div>
                    @if(!$invoice->isCancelled() && $invoice->remaining > 0)
                        <div class="card-footer text-center bg-light">
                            <x-action-button 
                                :action="route('admin.invoices.paymentForm', $invoice)"
                                type="create"
                                variant="success"
                                size="sm"
                                icon="plus"
                                label="Add Payment"
                            />
                        </div>
                    @endif
                </div>

                <!-- Invoice Statistics -->
                <div class="card">
                    <div class="card-header bg-light-info">
                        <h5 class="card-title mb-0 text-info">
                            <i class="ki-duotone ki-chart-simple fs-3 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                                <span class="path4"></span>
                            </i>
                            Invoice Statistics
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="text-muted fs-6 fw-semibold d-block mb-2">Items Count</label>
                            <div class="d-flex justify-content-between">
                                <span class="text-gray-700">Products:</span>
                                <span class="fw-bold text-gray-800">{{ $invoice->items->count() }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-gray-700">Lenses:</span>
                                <span class="fw-bold text-gray-800">{{ $invoice->lenses->count() }}</span>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="text-muted fs-6 fw-semibold d-block mb-2">Payment Count</label>
                            <span class="fw-bold text-gray-800 fs-4">{{ $invoice->transactions->count() }}</span>
                            <span class="text-muted fs-7"> transaction(s)</span>
                        </div>

                        @if($invoice->lastTransaction())
                            <div class="mb-4">
                                <label class="text-muted fs-6 fw-semibold d-block mb-2">Last Payment</label>
                                <span class="text-gray-800 fs-5">
                                    {{ $invoice->lastTransaction()->paid_at?->format('M d, Y') }}
                                </span>
                                <div class="text-muted fs-7">
                                    {{ number_format($invoice->lastTransaction()->amount, 2) }} EGP
                                </div>
                            </div>
                        @endif

                        <div>
                            <label class="text-muted fs-6 fw-semibold d-block mb-2">Invoice Status</label>
                            @php
                                $statusBadges = [
                                    'paid' => 'success',
                                    'partial' => 'warning',
                                    'unpaid' => 'danger',
                                    'canceled' => 'secondary',
                                    'cancelled' => 'secondary',
                                ];
                                $badgeColor = $statusBadges[$invoice->status] ?? 'primary';
                            @endphp
                            <span class="badge badge-light-{{ $badgeColor }} badge-lg">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end::Content container-->
</x-app-layout>
