<x-app-layout>
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-xxl">
    <!-- Page Header -->
    <div class="card mb-5">
        <div class="card-body py-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="symbol symbol-60px symbol-circle me-4">
                        <div class="symbol-label bg-light-primary text-primary fs-2 fw-bold">
                            {{ strtoupper(substr($client->name, 0, 1)) }}
                        </div>
                    </div>
                    <div>
                        <h3 class="mb-1 text-gray-900">{{ $client->name }}</h3>
                        <span class="text-muted fs-7">Client #{{ $client->id }} • Created {{ $client->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <x-action-button
                        :action="route('admin.clients.edit', $client)"
                        type="edit"
                        variant="warning"
                        icon="pencil"
                        label="Edit Client"
                    />
                    <a href="{{ route('admin.clients.index') }}" class="btn btn-light">
                        <i class="ki-duotone ki-arrow-left fs-4 me-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-5">
        <!-- Left Column - Client Info -->
        <div class="col-lg-4">
            <!-- Client Information Card -->
            <div class="card mb-5">
                <div class="card-header bg-primary">
                    <h5 class="card-title mb-0 text-white">
                        <i class="ki-duotone ki-user fs-3 me-2 text-white">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Client Information
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Phone Numbers -->
                    <div class="mb-4">
                        <label class="text-muted small fw-semibold d-block mb-2">
                            <i class="ki-duotone ki-phone fs-6 me-1">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Phone Numbers
                        </label>
                        @if($client->phone && is_array($client->phone) && count(array_filter($client->phone)) > 0)
                            @foreach(array_filter($client->phone) as $phone)
                                <div class="d-flex align-items-center mb-1">
                                    <span class="bullet bullet-dot bg-primary me-2"></span>
                                    <a href="tel:{{ $phone }}" class="text-gray-800 text-hover-primary">{{ $phone }}</a>
                                </div>
                            @endforeach
                        @else
                            <span class="text-muted">No phone number</span>
                        @endif
                    </div>

                    <!-- Address -->
                    <div class="mb-4">
                        <label class="text-muted small fw-semibold d-block mb-2">
                            <i class="ki-duotone ki-geolocation fs-6 me-1">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Address
                        </label>
                        <span class="text-gray-800">{{ $client->address ?: 'No address provided' }}</span>
                    </div>

                    <!-- Created At -->
                    <div>
                        <label class="text-muted small fw-semibold d-block mb-2">
                            <i class="ki-duotone ki-calendar fs-6 me-1">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Registration Date
                        </label>
                        <span class="text-gray-800">{{ $client->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Financial Summary Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ki-duotone ki-wallet fs-3 me-2 text-primary">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                        </i>
                        Financial Summary
                    </h5>
                </div>
                <div class="card-body pt-0">
                    <div class="d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                            <span class="text-gray-600">Total Amount</span>
                            <span class="fw-bold text-gray-800 fs-5">{{ number_format($totalAmount, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                            <span class="text-gray-600">Total Paid</span>
                            <span class="fw-bold text-success fs-5">{{ number_format($totalPaid, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-3">
                            <span class="text-gray-600">Remaining</span>
                            <span class="fw-bold fs-5 {{ $totalRemaining > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($totalRemaining, 2) }}
                            </span>
                        </div>
                    </div>

                    @if($totalRemaining > 0)
                        <div class="progress h-6px mt-3">
                            <div class="progress-bar bg-success"
                                 role="progressbar"
                                 style="width: {{ $totalAmount > 0 ? ($totalPaid / $totalAmount) * 100 : 0 }}%">
                            </div>
                        </div>
                        <div class="text-muted small mt-2 text-center">
                            {{ $totalAmount > 0 ? round(($totalPaid / $totalAmount) * 100) : 0 }}% paid
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-8">
            <!-- Statistics Cards -->
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <x-stats-card
                        :value="$invoiceStats['total']"
                        label="Total Invoices"
                        variant="primary"
                        icon="notepad"
                    />
                </div>
                <div class="col-md-4">
                    <x-stats-card
                        :value="$invoiceStats['paid']"
                        label="Paid Invoices"
                        variant="success"
                        icon="check-circle"
                    />
                </div>
                <div class="col-md-4">
                    <x-stats-card
                        :value="$invoiceStats['unpaid']"
                        label="Pending Payment"
                        variant="warning"
                        icon="time"
                    />
                </div>
            </div>

            <!-- Prescription Card -->
            <div class="card mb-5">
                <div class="card-header bg-light-info">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <h5 class="card-title mb-0">
                            <i class="ki-duotone ki-eye fs-3 me-2 text-info">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                            Latest Prescription
                            @if($client->papers->count() > 1)
                                <span class="badge bg-info ms-2">{{ $client->papers->count() }} total</span>
                            @endif
                        </h5>
                        <x-action-button
                            :action="route('admin.clients.edit', $client)"
                            type="edit"
                            variant="info"
                            size="sm"
                            icon="plus"
                            label="New Prescription"
                        />
                    </div>
                </div>
                <div class="card-body">
                    <x-prescription-display :paper="$paper" />
                </div>
            </div>

            <!-- Invoices Card -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <h5 class="card-title mb-0">
                            <i class="ki-duotone ki-notepad fs-3 me-2 text-primary">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                                <span class="path4"></span>
                                <span class="path5"></span>
                            </i>
                            Invoices
                        </h5>
                        <x-action-button
                            :action="route('admin.invoices.create') . '?client_id=' . $client->id"
                            type="create"
                            variant="success"
                            size="sm"
                            icon="plus"
                            label="New Invoice"
                        />
                    </div>
                </div>
                <div class="card-body py-0">
                    @if($client->invoices->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-4">
                                <thead>
                                    <tr class="fw-bold text-muted bg-light">
                                        <th class="ps-4 rounded-start">#</th>
                                        <th>Invoice</th>
                                        <th>Amount</th>
                                        <th>Paid</th>
                                        <th>Remaining</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th class="pe-4 text-end rounded-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($client->invoices->sortByDesc('created_at') as $invoice)
                                        @php
                                            $invoicePaid = $invoice->transactions ? $invoice->transactions->sum('amount') : 0;
                                            $invoiceRemaining = $invoice->amount - $invoicePaid;
                                            $statusColors = [
                                                'paid' => 'success',
                                                'unpaid' => 'danger',
                                                'partial' => 'warning',
                                            ];
                                            $statusColor = $statusColors[$invoice->status] ?? 'secondary';
                                        @endphp
                                        <tr>
                                            <td class="ps-4">
                                                <span class="text-gray-800 fw-bold">{{ $invoice->id }}</span>
                                            </td>
                                            <td>
                                                <x-action-button
                                                    :action="route('admin.invoices.show', $invoice)"
                                                    type="show"
                                                    variant="link"
                                                    class="p-0 text-gray-800 text-hover-primary fw-bold"
                                                >
                                                    {{ $invoice->invoice_number ?? 'INV-' . $invoice->id }}
                                                </x-action-button>
                                            </td>
                                            <td>
                                                <span class="fw-semibold">{{ number_format($invoice->amount, 2) }}</span>
                                            </td>
                                            <td>
                                                <span class="text-success fw-semibold">{{ number_format($invoicePaid, 2) }}</span>
                                            </td>
                                            <td>
                                                <span class="fw-semibold {{ $invoiceRemaining > 0 ? 'text-danger' : 'text-success' }}">
                                                    {{ number_format($invoiceRemaining, 2) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-light-{{ $statusColor }}">
                                                    {{ ucfirst($invoice->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $invoice->created_at->format('M d, Y') }}</span>
                                            </td>
                                            <td class="pe-4 text-end">
                                                <div class="d-flex justify-content-end gap-1">
                                                    <x-action-button
                                                        :action="route('admin.invoices.show', $invoice)"
                                                        type="show"
                                                        variant="primary"
                                                        size="sm"
                                                        icon="eye"
                                                        :iconOnly="true"
                                                        title="View Invoice"
                                                    />
                                                    <x-action-button
                                                        :action="route('admin.invoices.edit', $invoice)"
                                                        type="edit"
                                                        variant="warning"
                                                        size="sm"
                                                        icon="pencil"
                                                        :iconOnly="true"
                                                        title="Edit Invoice"
                                                    />
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-10">
                            <i class="ki-duotone ki-notepad fs-3x mb-4 opacity-25">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                                <span class="path4"></span>
                                <span class="path5"></span>
                            </i>
                            <p class="fs-5 fw-semibold mb-3">No invoices yet</p>
                            <p class="text-muted mb-5">Create the first invoice for this client</p>
                            <x-action-button
                                :action="route('admin.invoices.create') . '?client_id=' . $client->id"
                                type="create"
                                variant="success"
                                icon="plus"
                                label="Create First Invoice"
                            />
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    </div>
    <!--end::Content container-->
</x-app-layout>
