<x-app-layout>
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-xxl">
        <!-- Page Header -->
        <div class="card mb-5 mb-xl-10">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <h3 class="fw-bold m-0">
                        <i class="ki-duotone ki-chart-line-up fs-2x text-primary me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Financial Report
                    </h3>
                </div>
                <div class="card-toolbar">
                    <form method="get" action="{{ route('admin.transactions.index') }}" class="w-100">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-auto">
                                <div class="d-flex gap-2">
                                    @php
                                        $yesterday = \Carbon\Carbon::yesterday()->format('Y-m-d');
                                    @endphp
                                    <a href="{{ URL::current() . '?from=' . $yesterday . '&to=' . $yesterday }}" class="btn btn-light-info btn-sm">
                                        Yesterday
                                    </a>
                                    <a href="{{ URL::current() . '?from=' . \Carbon\Carbon::now()->startOfWeek()->format('Y-m-d') . '&to=' . \Carbon\Carbon::now()->format('Y-m-d') }}" class="btn btn-light-info btn-sm">
                                        This Week
                                    </a>
                                    <a href="{{ URL::current() . '?from=' . \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') . '&to=' . \Carbon\Carbon::now()->format('Y-m-d') }}" class="btn btn-light-info btn-sm">
                                        This Month
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-auto">
                                <label class="form-label fw-semibold">From</label>
                                <input name="from" type="date" class="form-control form-control-sm" value="{{ $from }}">
                            </div>
                            <div class="col-md-auto">
                                <label class="form-label fw-semibold">To</label>
                                <input name="to" type="date" class="form-control form-control-sm" value="{{ $to }}">
                            </div>
                            <div class="col-md-auto">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="ki-duotone ki-magnifier fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    Search
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
            <!-- Sales Card -->
            <div class="col-md-6 col-lg-3">
                <div class="card card-flush h-xl-100">
                    <div class="card-header pt-5">
                        <div class="card-title d-flex flex-column">
                            <span class="fs-2hx fw-bold text-success me-2 lh-1 ls-n2">{{ number_format($sum_invoices, 2) }}</span>
                            <span class="text-gray-500 pt-1 fw-semibold fs-6">Total Sales</span>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="d-flex flex-column">
                            <div class="d-flex align-items-center border-bottom border-gray-300 border-bottom-dashed py-4">
                                <div class="flex-grow-1">
                                    <span class="text-gray-600 fw-semibold fs-6 d-block">Paid</span>
                                </div>
                                <div class="text-end">
                                    <span class="text-gray-800 fw-bold fs-6">{{ number_format($sum_payments_new_invoices, 2) }}</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center border-bottom border-gray-300 border-bottom-dashed py-4">
                                <div class="flex-grow-1">
                                    <span class="text-gray-600 fw-semibold fs-6 d-block">Remaining</span>
                                </div>
                                <div class="text-end">
                                    <span class="text-gray-800 fw-bold fs-6">{{ number_format($remining = $sum_invoices - $sum_payments_new_invoices, 2) }}</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center py-4">
                                <div class="flex-grow-1">
                                    <span class="text-gray-600 fw-semibold fs-6 d-block">Old Paid</span>
                                </div>
                                <div class="text-end">
                                    <span class="text-gray-800 fw-bold fs-6">{{ number_format($x = $sum_sales - ($sum_payments_new_invoices + $sum_payments_new_invoices_negative), 2) }}</span>
                                </div>
                            </div>
                            <div class="separator separator-dashed my-3"></div>
                            <div class="d-flex align-items-center py-2">
                                <div class="flex-grow-1">
                                    <span class="text-gray-700 fw-bold fs-7">Currently Remaining</span>
                                </div>
                                <div class="text-end">
                                    <span class="text-success fw-bold fs-6">{{ number_format(($y = $sum_old_invoices - $sum_old_income + $remining) - $x, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Purchases Card -->
            <div class="col-md-6 col-lg-3">
                <div class="card card-flush h-xl-100">
                    <div class="card-header pt-5">
                        <div class="card-title d-flex flex-column">
                            <span class="fs-2hx fw-bold text-danger me-2 lh-1 ls-n2">{{ number_format($sum_bills, 2) }}</span>
                            <span class="text-gray-500 pt-1 fw-semibold fs-6">Total Purchases</span>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="d-flex flex-column">
                            <div class="d-flex align-items-center border-bottom border-gray-300 border-bottom-dashed py-4">
                                <div class="flex-grow-1">
                                    <span class="text-gray-600 fw-semibold fs-6 d-block">Paid</span>
                                </div>
                                <div class="text-end">
                                    <span class="text-gray-800 fw-bold fs-6">{{ number_format($payments_new_bills, 2) }}</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center border-bottom border-gray-300 border-bottom-dashed py-4">
                                <div class="flex-grow-1">
                                    <span class="text-gray-600 fw-semibold fs-6 d-block">Remaining</span>
                                </div>
                                <div class="text-end">
                                    <span class="text-gray-800 fw-bold fs-6">{{ number_format($remining_bills = $sum_bills - $payments_new_bills, 2) }}</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center py-4">
                                <div class="flex-grow-1">
                                    <span class="text-gray-600 fw-semibold fs-6 d-block">Old Paid</span>
                                </div>
                                <div class="text-end">
                                    <span class="text-gray-800 fw-bold fs-6">{{ number_format($x_bills = $sum_purchases - $payments_new_bills, 2) }}</span>
                                </div>
                            </div>
                            <div class="separator separator-dashed my-3"></div>
                            <div class="d-flex align-items-center py-2">
                                <div class="flex-grow-1">
                                    <span class="text-gray-700 fw-bold fs-7">Currently Remaining</span>
                                </div>
                                <div class="text-end">
                                    <span class="text-danger fw-bold fs-6">{{ number_format(($y_bills = $sum_old_bills - $sum_old_purchases + $remining_bills) - $x_bills, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Expenses Card -->
            <div class="col-md-6 col-lg-3">
                <div class="card card-flush h-xl-100">
                    <div class="card-header pt-5">
                        <div class="card-title d-flex flex-column">
                            <span class="fs-2hx fw-bold text-warning me-2 lh-1 ls-n2">{{ number_format($sum_overheads, 2) }}</span>
                            <span class="text-gray-500 pt-1 fw-semibold fs-6">Total Expenses</span>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="d-flex flex-column">
                            <div class="d-flex align-items-center border-bottom border-gray-300 border-bottom-dashed py-4">
                                <div class="flex-grow-1">
                                    <span class="text-gray-600 fw-semibold fs-6 d-block">Overheads</span>
                                </div>
                                <div class="text-end">
                                    <span class="text-gray-800 fw-bold fs-6">{{ number_format($sum_overheads, 2) }}</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center border-bottom border-gray-300 border-bottom-dashed py-4">
                                <div class="flex-grow-1">
                                    <span class="text-gray-600 fw-semibold fs-6 d-block">Canceled Sales</span>
                                </div>
                                <div class="text-end">
                                    <span class="text-gray-800 fw-bold fs-6">{{ number_format($sum_invoices_negative, 2) }}</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center py-4">
                                <div class="flex-grow-1">
                                    <span class="text-gray-600 fw-semibold fs-6 d-block">Refunds</span>
                                </div>
                                <div class="text-end">
                                    <span class="text-gray-800 fw-bold fs-6">{{ number_format($sum_payments_new_invoices_negative, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Accounts Card -->
            <div class="col-md-6 col-lg-3">
                <div class="card card-flush h-xl-100">
                    <div class="card-header pt-5">
                        <div class="card-title d-flex flex-column">
                            @php
                                $total_accounts = array_sum($accounts_amounts);
                            @endphp
                            <span class="fs-2hx fw-bold text-primary me-2 lh-1 ls-n2">{{ number_format($total_accounts, 2) }}</span>
                            <span class="text-gray-500 pt-1 fw-semibold fs-6">Total Accounts</span>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="d-flex flex-column" style="max-height: 200px; overflow-y: auto;">
                            @foreach ($accounts as $index => $account)
                                <div class="d-flex align-items-center {{ ($loop->last) ? '' : 'border-bottom border-gray-300 border-bottom-dashed' }} py-3">
                                    <div class="flex-grow-1">
                                        <span class="text-gray-700 fw-semibold fs-7 d-block">{{ $account->name }}</span>
                                    </div>
                                    <div class="text-end">
                                        <span class="text-gray-800 fw-bold fs-6">{{ number_format($accounts_amounts[$index], 2) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="card mb-5 mb-xl-10">
            <x-dynamic-table
                table-id="transactions_report_table"
                :columns="$transactionsColumns"
                :filters="$transactionsFilters"
                :show-checkbox="false"
                table-type="transactions"
            />
        </div>

        <!-- Invoices Table -->
        <div class="card mb-5 mb-xl-10">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <h3 class="fw-bold m-0">
                        <i class="ki-duotone ki-receipt fs-2x text-success me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Invoices
                    </h3>
                </div>
            </div>
            <div class="card-body pt-0">
                <x-dynamic-table
                    table-id="invoices_report_table"
                    :columns="$invoicesColumns"
                    :filters="$invoicesFilters"
                    :show-checkbox="false"
                    table-type="invoices"
                />
            </div>
        </div>

        <!-- Bills Table -->
        <div class="card mb-5 mb-xl-10">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <h3 class="fw-bold m-0">
                        <i class="ki-duotone ki-shopping-bag fs-2x text-danger me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Bills
                    </h3>
                </div>
            </div>
            <div class="card-body pt-0">
                <x-dynamic-table
                    table-id="bills_report_table"
                    :columns="$billsColumns"
                    :filters="$billsFilters"
                    :show-checkbox="false"
                    table-type="bills"
                />
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Make deleteTransaction globally available
        window.deleteTransaction = function(id, type) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const url = type === 'invoice'
                        ? '/admin/invoices/transactions/' + id
                        : type === 'bill'
                        ? '/admin/bills/transactions/' + id
                        : '/admin/transactions/' + id;

                    fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status) {
                            Swal.fire(
                                'Deleted!',
                                'Transaction has been deleted.',
                                'success'
                            ).then(() => {
                                // Reload all DataTables
                                if (window.transactions_report_tableTable) {
                                    window.transactions_report_tableTable.ajax.reload();
                                }
                                if (window.invoices_report_tableTable) {
                                    window.invoices_report_tableTable.ajax.reload();
                                }
                                if (window.bills_report_tableTable) {
                                    window.bills_report_tableTable.ajax.reload();
                                }
                                // Fallback reload if tables not found
                                setTimeout(() => location.reload(), 1000);
                            });
                        } else {
                            Swal.fire(
                                'Error!',
                                data.msg || 'Failed to delete transaction',
                                'error'
                            );
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire(
                            'Error!',
                            'An error occurred while deleting the transaction',
                            'error'
                        );
                    });
                }
            });
        };
    </script>
    @endpush
</x-app-layout>
