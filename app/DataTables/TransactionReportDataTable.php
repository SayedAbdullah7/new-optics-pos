<?php

namespace App\DataTables;

use App\Helpers\Column;
use App\Helpers\Filter;
use App\Models\Account;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class TransactionReportDataTable extends BaseDataTable
{
    /**
     * Get the columns for the DataTable.
     */
    public function columns(): array
    {
        return [
            Column::create('id')->setOrderable(true),
            Column::create('name')->setTitle('Name')->setOrderable(false),
            Column::create('number')->setTitle('Number')->setOrderable(false),
            Column::create('type')->setTitle('Type'),
            Column::create('category')->setTitle('Category'),
            Column::create('amount')->setTitle('Amount'),
            Column::create('remaining')->setTitle('Remaining')->setOrderable(false),
            Column::create('account_name')->setTitle('Account')->setOrderable(false),
            Column::create('notes')->setTitle('Notes')->setOrderable(false),
            Column::create('paid_at')->setTitle('Date'),
            Column::create('action')->setTitle('Actions')->setSearchable(false)->setOrderable(false),
        ];
    }

    /**
     * Get the filters for the DataTable.
     */
    public function filters(): array
    {
        $accountsQuery = Account::with('translations')->active();
        $accounts = ['' => 'All Accounts'];
        foreach ($accountsQuery->get() as $account) {
            $accounts[$account->id] = $account->name;
        }

        return [
            'type' => Filter::select('Type', [
                'income' => 'Income',
                'expense' => 'Expense',
            ]),
            'category_id' => Filter::select('Category', [
                '1' => 'Sales',
                '2' => 'Purchases',
                '3' => 'Overheads',
            ]),
            'account_id' => Filter::select('Account', $accounts),
        ];
    }

    /**
     * Handle the DataTable data processing.
     */
    public function handle()
    {
        $from = request()->get('from', Carbon::now()->format('Y-m-d'));
        $to = request()->get('to', Carbon::now()->format('Y-m-d'));

        $query = Transaction::betweenDates($from, $to)
            ->with(['invoice.client', 'bill.vendor', 'vendor', 'expense', 'account'])
            ->orderBy('paid_at', 'desc');

        return DataTables::of($query)
            ->addColumn('name', function ($model) {
                if ($model->category_id == 1) {
                    return '<span class="text-gray-800 fw-bold d-block fs-7">' . ($model->invoice->client->name ?? '-') . '</span>';
                } elseif ($model->category_id == 2) {
                    return '<span class="text-gray-800 fw-bold d-block fs-7">' . ($model->vendor->name ?? '-') . '</span>';
                } elseif ($model->category_id == 3) {
                    return '<span class="text-gray-800 fw-bold d-block fs-7">' . ($model->expense ? ($model->expense->title ?? $model->description ?? '-') : ($model->description ?? '-')) . '</span>';
                }
                return '<span class="text-muted">-</span>';
            })
            ->addColumn('number', function ($model) {
                if ($model->category_id == 1 && $model->invoice) {
                    return '<a href="' . route('admin.invoices.show', $model->invoice->id) . '" class="text-primary fw-semibold text-hover-primary">' . $model->invoice->invoice_number . '</a>';
                } elseif ($model->category_id == 2 && $model->bill) {
                    return '<a href="' . route('admin.bills.show', $model->bill->id) . '" class="text-primary fw-semibold text-hover-primary">' . $model->bill->bill_number . '</a>';
                }
                return '<span class="text-muted">-</span>';
            })
            ->addColumn('type', function ($model) {
                if ($model->category_id == 1) {
                    return '<span class="badge badge-light-success">Sales</span>';
                } elseif ($model->category_id == 2) {
                    return '<span class="badge badge-light-danger">Purchases</span>';
                } elseif ($model->category_id == 3) {
                    return '<span class="badge badge-light-warning">Overheads</span>';
                }
                return '<span class="badge badge-secondary">-</span>';
            })
            ->addColumn('category', function ($model) {
                $badgeClass = $model->type === 'income' ? 'badge-success' : 'badge-danger';
                $text = $model->type === 'income' ? 'Income' : 'Expense';
                return '<span class="badge ' . $badgeClass . '">' . $text . '</span>';
            })
            ->addColumn('remaining', function ($model) use ($to) {
                if ($model->category_id == 1 && $model->invoice) {
                    return '<span class="text-gray-700 fw-semibold">' . number_format($model->invoice->remainingUntil($to), 2) . '</span>';
                } elseif ($model->category_id == 2 && $model->vendor) {
                    return '<span class="text-gray-700 fw-semibold">' . number_format($model->vendor->balanceUntil($to), 2) . '</span>';
                }
                return '<span class="text-muted">-</span>';
            })
            ->addColumn('account_name', function ($model) {
                return '<span class="text-gray-600 fw-semibold">' . ($model->account->name ?? '-') . '</span>';
            })
            ->addColumn('notes', function ($model) {
                $badges = [];
                if ($model->category_id == 1 && $model->invoice && $model->paid_at->format('Y-m-d') == $model->invoice->invoiced_at->format('Y-m-d')) {
                    $badges[] = '<span class="badge badge-warning">New</span>';
                }
                if ($model->category_id == 2 && $model->bill && $model->paid_at->format('Y-m-d') == $model->bill->billed_at->format('Y-m-d')) {
                    $badges[] = '<span class="badge badge-warning">New</span>';
                }
                if ($model->amount < 0 || ($model->category_id == 2 && $model->type == 'income')) {
                    $badges[] = '<span class="badge badge-danger">Canceled</span>';
                }
                if ($model->category_id == 1 && $model->amount < 0) {
                    $badges[] = '<span class="badge badge-danger">Cancellation</span>';
                }
                if ($model->category_id == 3) {
                    return '<span class="text-gray-600 fs-7">' . Str::limit($model->description ?? '-', 30) . '</span>';
                }
                return implode(' ', $badges);
            })
            ->addColumn('action', function ($model) {
                $editRoute = $model->category_id == 1
                    ? route('admin.invoices.transactions.edit', $model->id)
                    : route('admin.bills.transactions.edit', $model->id);

                $deleteType = $model->category_id == 1 ? 'invoice' : ($model->category_id == 2 ? 'bill' : 'general');

                $html = '<div class="d-flex justify-content-end gap-2">';
                if ($model->category_id != 3) {
                    $html .= '<a class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm" href="' . $editRoute . '" title="Edit">';
                    $html .= '<i class="ki-duotone ki-pencil fs-2"><span class="path1"></span><span class="path2"></span></i>';
                    $html .= '</a>';
                }
                $html .= '<button type="button" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm" onclick="deleteTransaction(' . $model->id . ', \'' . $deleteType . '\')" title="Delete">';
                $html .= '<i class="ki-duotone ki-trash fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>';
                $html .= '</button>';
                $html .= '</div>';
                return $html;
            })
            ->editColumn('amount', function ($model) {
                $amountClass = $model->type === 'income' ? 'text-success' : 'text-danger';
                $prefix = $model->type === 'income' ? '+' : '-';
                return '<span class="' . $amountClass . ' fw-bold fs-6">' . $prefix . number_format($model->amount, 2) . '</span>';
            })
            ->editColumn('paid_at', function ($model) {
                return '<span class="text-gray-600 fw-semibold">' . ($model->paid_at ? $model->paid_at->format('M d, Y') : '-') . '</span>';
            })
            ->filter(function ($query) {
                $this->applySearch($query);
                $this->applyFilters($query);
            }, true)
            ->rawColumns(['name', 'number', 'type', 'category', 'amount', 'remaining', 'account_name', 'notes', 'paid_at', 'action'])
            ->make(true);
    }

    protected function applySearch($query): void
    {
        $search = request()->input('search.value');
        if (!$search || strlen(trim($search)) < 1) {
            return;
        }

        $searchTerm = '%' . trim($search) . '%';

        $query->where(function ($q) use ($searchTerm) {
            $q->where('amount', 'like', $searchTerm)
              ->orWhere('description', 'like', $searchTerm)
              ->orWhereHas('invoice.client', function ($clientQuery) use ($searchTerm) {
                  $clientQuery->where('name', 'like', $searchTerm);
              })
              ->orWhereHas('invoice', function ($invoiceQuery) use ($searchTerm) {
                  $invoiceQuery->where('invoice_number', 'like', $searchTerm);
              })
              ->orWhereHas('vendor', function ($vendorQuery) use ($searchTerm) {
                  $vendorQuery->where('name', 'like', $searchTerm);
              })
              ->orWhereHas('bill', function ($billQuery) use ($searchTerm) {
                  $billQuery->where('bill_number', 'like', $searchTerm);
              });
        });
    }
}
