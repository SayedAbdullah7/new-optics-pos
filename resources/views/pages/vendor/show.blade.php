<x-app-layout>
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-xxl">
    <!-- Page Header -->
    <div class="card mb-5">
        <div class="card-body py-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="symbol symbol-60px symbol-circle me-4">
                        <div class="symbol-label bg-light-danger text-danger fs-2 fw-bold">
                            {{ strtoupper(substr($vendor->name, 0, 1)) }}
                        </div>
                    </div>
                    <div>
                        <h3 class="mb-1 text-gray-900">{{ $vendor->name }}</h3>
                        <span class="text-muted fs-7">Vendor #{{ $vendor->id }} • Created {{ $vendor->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <x-action-button
                        :action="route('admin.vendors.edit', $vendor)"
                        type="edit"
                        variant="warning"
                        icon="pencil"
                        label="Edit Vendor"
                    />
                    <a href="{{ route('admin.vendors.index') }}" class="btn btn-light">
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
        <!-- Left Column - Vendor Info -->
        <div class="col-lg-4">
            <!-- Vendor Information Card -->
            <div class="card mb-5">
                <div class="card-header bg-danger">
                    <h5 class="card-title mb-0 text-white">
                        <i class="ki-duotone ki-truck fs-3 me-2 text-white">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Vendor Information
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
                        @if($vendor->phone && is_array($vendor->phone) && count(array_filter($vendor->phone)) > 0)
                            @foreach(array_filter($vendor->phone) as $phone)
                                <div class="d-flex align-items-center mb-1">
                                    <span class="bullet bullet-dot bg-danger me-2"></span>
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
                        <span class="text-gray-800">{{ $vendor->address ?: 'No address provided' }}</span>
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
                        <span class="text-gray-800">{{ $vendor->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Financial Summary Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ki-duotone ki-wallet fs-3 me-2 text-danger">
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

                    @if($totalRemaining > 0 && $totalAmount > 0)
                        <div class="progress h-6px mt-3">
                            <div class="progress-bar bg-success"
                                 role="progressbar"
                                 style="width: {{ ($totalPaid / $totalAmount) * 100 }}%">
                            </div>
                        </div>
                        <div class="text-muted small mt-2 text-center">
                            {{ round(($totalPaid / $totalAmount) * 100) }}% paid
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
                        :value="$billStats['total']"
                        label="Total Bills"
                        variant="danger"
                        icon="notepad"
                    />
                </div>
                <div class="col-md-4">
                    <x-stats-card
                        :value="$billStats['paid']"
                        label="Paid Bills"
                        variant="success"
                        icon="check-circle"
                    />
                </div>
                <div class="col-md-4">
                    <x-stats-card
                        :value="$billStats['unpaid']"
                        label="Pending Payment"
                        variant="warning"
                        icon="time"
                    />
                </div>
            </div>

            <!-- Bills Card -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <h5 class="card-title mb-0">
                            <i class="ki-duotone ki-shopping-bag fs-3 me-2 text-danger">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                            Bills
                        </h5>
                        <x-action-button
                            :action="route('admin.bills.create') . '?vendor_id=' . $vendor->id"
                            type="create"
                            variant="danger"
                            size="sm"
                            icon="plus"
                            label="New Bill"
                        />
                    </div>
                </div>
                <div class="card-body py-0">
                    @if($vendor->bills->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-4">
                                <thead>
                                    <tr class="fw-bold text-muted bg-light">
                                        <th class="ps-4 rounded-start">#</th>
                                        <th>Bill Number</th>
                                        <th>Amount</th>
                                        <th>Paid</th>
                                        <th>Remaining</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th class="pe-4 text-end rounded-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($vendor->bills->sortByDesc('created_at') as $bill)
                                        @php
                                            $billPaid = $bill->transactions ? $bill->transactions->sum('amount') : 0;
                                            $billRemaining = $bill->amount - $billPaid;
                                            $statusColors = [
                                                'paid' => 'success',
                                                'unpaid' => 'danger',
                                                'partial' => 'warning',
                                            ];
                                            $statusColor = $statusColors[$bill->status] ?? 'secondary';
                                        @endphp
                                        <tr>
                                            <td class="ps-4">
                                                <span class="text-gray-800 fw-bold">{{ $bill->id }}</span>
                                            </td>
                                            <td>
                                                <x-action-button
                                                    :action="route('admin.bills.show', $bill)"
                                                    type="show"
                                                    variant="link"
                                                    class="p-0 text-gray-800 text-hover-primary fw-bold"
                                                >
                                                    {{ $bill->bill_number ?? 'BIL-' . $bill->id }}
                                                </x-action-button>
                                            </td>
                                            <td>
                                                <span class="fw-semibold">{{ number_format($bill->amount, 2) }}</span>
                                            </td>
                                            <td>
                                                <span class="text-success fw-semibold">{{ number_format($billPaid, 2) }}</span>
                                            </td>
                                            <td>
                                                <span class="fw-semibold {{ $billRemaining > 0 ? 'text-danger' : 'text-success' }}">
                                                    {{ number_format($billRemaining, 2) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-light-{{ $statusColor }}">
                                                    {{ ucfirst($bill->status ?? 'unpaid') }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $bill->billed_at?->format('M d, Y') ?? $bill->created_at->format('M d, Y') }}</span>
                                            </td>
                                            <td class="pe-4 text-end">
                                                <div class="d-flex justify-content-end gap-1">
                                                    <x-action-button
                                                        :action="route('admin.bills.show', $bill)"
                                                        type="show"
                                                        variant="primary"
                                                        size="sm"
                                                        icon="eye"
                                                        :iconOnly="true"
                                                        title="View Bill"
                                                    />
                                                    <x-action-button
                                                        :action="route('admin.bills.edit', $bill)"
                                                        type="edit"
                                                        variant="warning"
                                                        size="sm"
                                                        icon="pencil"
                                                        :iconOnly="true"
                                                        title="Edit Bill"
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
                            <i class="ki-duotone ki-shopping-bag fs-3x mb-4 opacity-25">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                            <p class="fs-5 fw-semibold mb-3">No bills yet</p>
                            <p class="text-muted mb-5">Create the first bill for this vendor</p>
                            <x-action-button
                                :action="route('admin.bills.create') . '?vendor_id=' . $vendor->id"
                                type="create"
                                variant="danger"
                                icon="plus"
                                label="Create First Bill"
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