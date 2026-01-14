<x-app-layout>
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-xxl">
        <!--begin::Row-->
        <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
            <!--begin::Col - Total Clients-->
            <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 mb-md-5 mb-xl-10">
                <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50 mb-5 mb-xl-10" style="background-color: #F1416C;">
                    <div class="card-header pt-5">
                        <div class="card-title d-flex flex-column">
                            <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ $stats['total_clients'] ?? 0 }}</span>
                            <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Total Clients</span>
                        </div>
                    </div>
                    <div class="card-body d-flex align-items-end pt-0">
                        <a href="{{ route('admin.clients.index') }}" class="text-white fs-6 fw-semibold">View All
                            <i class="ki-duotone ki-arrow-right fs-3 text-white">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                        </a>
                    </div>
                </div>
            </div>
            <!--end::Col-->

            <!--begin::Col - Total Products-->
            <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 mb-md-5 mb-xl-10">
                <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50 mb-5 mb-xl-10" style="background-color: #7239EA;">
                    <div class="card-header pt-5">
                        <div class="card-title d-flex flex-column">
                            <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ $stats['total_products'] ?? 0 }}</span>
                            <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Total Products</span>
                        </div>
                    </div>
                    <div class="card-body d-flex align-items-end pt-0">
                        <a href="{{ route('admin.products.index') }}" class="text-white fs-6 fw-semibold">View All
                            <i class="ki-duotone ki-arrow-right fs-3 text-white">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                        </a>
                    </div>
                </div>
            </div>
            <!--end::Col-->

            <!--begin::Col - Total Vendors-->
            <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 mb-md-5 mb-xl-10">
                <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50 mb-5 mb-xl-10" style="background-color: #50CD89;">
                    <div class="card-header pt-5">
                        <div class="card-title d-flex flex-column">
                            <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ $stats['total_vendors'] ?? 0 }}</span>
                            <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Total Vendors</span>
                        </div>
                    </div>
                    <div class="card-body d-flex align-items-end pt-0">
                        <a href="{{ route('admin.vendors.index') }}" class="text-white fs-6 fw-semibold">View All
                            <i class="ki-duotone ki-arrow-right fs-3 text-white">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                        </a>
                    </div>
                </div>
            </div>
            <!--end::Col-->

            <!--begin::Col - Low Stock-->
            <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 mb-md-5 mb-xl-10">
                <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50 mb-5 mb-xl-10" style="background-color: #FFC700;">
                    <div class="card-header pt-5">
                        <div class="card-title d-flex flex-column">
                            <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ $stats['low_stock_products'] ?? 0 }}</span>
                            <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Low Stock Items</span>
                        </div>
                    </div>
                    <div class="card-body d-flex align-items-end pt-0">
                        <a href="{{ route('admin.products.index') }}" class="text-white fs-6 fw-semibold">View All
                            <i class="ki-duotone ki-arrow-right fs-3 text-white">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                        </a>
                    </div>
                </div>
            </div>
            <!--end::Col-->
        </div>
        <!--end::Row-->

        <!--begin::Row - Financial Stats-->
        <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
            <div class="col-xl-4">
                <div class="card card-flush h-100">
                    <div class="card-header pt-7">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-gray-900">Today's Sales</span>
                        </h3>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <span class="fs-2x fw-bold text-success">{{ number_format($financials['today_sales'] ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card card-flush h-100">
                    <div class="card-header pt-7">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-gray-900">Monthly Sales</span>
                        </h3>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <span class="fs-2x fw-bold text-primary">{{ number_format($financials['month_sales'] ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card card-flush h-100">
                    <div class="card-header pt-7">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-gray-900">Pending Payments</span>
                        </h3>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <span class="fs-2x fw-bold text-danger">{{ number_format($financials['pending_invoices'] ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Row-->

        <!--begin::Row-->
        <div class="row g-5 g-xl-10">
            <!--begin::Col - Recent Invoices-->
            <div class="col-xl-8">
                <div class="card card-flush h-lg-100">
                    <div class="card-header pt-7">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-gray-900">Recent Invoices</span>
                            <span class="text-gray-500 mt-1 fw-semibold fs-6">Last 5 invoices</span>
                        </h3>
                        <div class="card-toolbar">
                            <a href="{{ route('admin.invoices.index') }}" class="btn btn-sm btn-light-primary">View All</a>
                        </div>
                    </div>
                    <div class="card-body pt-5">
                        <div class="table-responsive">
                            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                <thead>
                                    <tr class="fw-bold text-muted">
                                        <th>Invoice #</th>
                                        <th>Client</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentInvoices ?? [] as $invoice)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.invoices.show', $invoice->id) }}" class="text-gray-800 text-hover-primary fw-bold">
                                                    {{ $invoice->invoice_number }}
                                                </a>
                                            </td>
                                            <td>{{ $invoice->client?->name ?? 'N/A' }}</td>
                                            <td class="fw-bold">{{ number_format($invoice->amount, 2) }}</td>
                                            <td>
                                                @php
                                                    $statusClass = match($invoice->status ?? 'draft') {
                                                        'paid' => 'success',
                                                        'partial' => 'warning',
                                                        'sent', 'viewed' => 'info',
                                                        'overdue' => 'danger',
                                                        default => 'secondary'
                                                    };
                                                @endphp
                                                <span class="badge badge-light-{{ $statusClass }}">{{ ucfirst($invoice->status ?? 'draft') }}</span>
                                            </td>
                                            <td>{{ $invoice->invoiced_at?->format('M d, Y') ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-10">No invoices yet</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Col-->

            <!--begin::Col - Quick Actions-->
            <div class="col-xl-4">
                <div class="card card-flush h-lg-100">
                    <div class="card-header pt-7">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-gray-900">Quick Actions</span>
                            <span class="text-gray-500 mt-1 fw-semibold fs-6">Frequently used operations</span>
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column gap-3">
                            <a href="#" class="btn btn-primary has_action" data-type="create" data-action="{{ route('admin.invoices.create') }}">
                                <i class="ki-duotone ki-plus fs-2"></i> New Invoice
                            </a>
                            <a href="#" class="btn btn-success has_action" data-type="create" data-action="{{ route('admin.clients.create') }}">
                                <i class="ki-duotone ki-plus fs-2"></i> New Client
                            </a>
                            <a href="#" class="btn btn-info has_action" data-type="create" data-action="{{ route('admin.products.create') }}">
                                <i class="ki-duotone ki-plus fs-2"></i> New Product
                            </a>
                            <a href="#" class="btn btn-warning has_action" data-type="create" data-action="{{ route('admin.bills.create') }}">
                                <i class="ki-duotone ki-plus fs-2"></i> New Bill
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Col-->
        </div>
        <!--end::Row-->

        <!--begin::Row - Totals-->
        <div class="row g-5 g-xl-10 mt-5">
            <div class="col-xl-4">
                <div class="card card-flush bg-light-success border-0">
                    <div class="card-body text-center py-10">
                        <span class="text-gray-600 fs-6 fw-semibold d-block mb-2">Total Sales</span>
                        <span class="text-success fs-2x fw-bold">{{ number_format($financials['total_sales'] ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card card-flush bg-light-danger border-0">
                    <div class="card-body text-center py-10">
                        <span class="text-gray-600 fs-6 fw-semibold d-block mb-2">Total Purchases</span>
                        <span class="text-danger fs-2x fw-bold">{{ number_format($financials['total_purchases'] ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card card-flush bg-light-warning border-0">
                    <div class="card-body text-center py-10">
                        <span class="text-gray-600 fs-6 fw-semibold d-block mb-2">Total Expenses</span>
                        <span class="text-warning fs-2x fw-bold">{{ number_format($financials['total_expenses'] ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Row-->
    </div>
    <!--end::Content container-->
</x-app-layout>
