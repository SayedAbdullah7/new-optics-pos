<?php

namespace App\DataTables;

use App\Helpers\Column;
use App\Helpers\Filter;
use App\Models\Account;
use App\Models\Transaction;
use Yajra\DataTables\Facades\DataTables;

class TransactionDataTable extends BaseDataTable
{
    /**
     * Define searchable relations for the query.
     * These columns will be searched in related models.
     */
    protected array $searchableRelations = [];

    /**
     * Get the columns for the DataTable.
     */
    public function columns(): array
    {
        return [
            Column::create('id')->setOrderable(true),
            Column::create('type')->setTitle('Type'),
            Column::create('amount')->setTitle('Amount'),
            Column::create('payment_method')->setTitle('Payment Method'),
            Column::create('account_name')->setTitle('Account')->setName('account.name')->setSearchable(false),
            Column::create('reference')->setTitle('Reference'),
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
            'paid_at' => Filter::dateRange('Transaction Date'),
        ];
    }

    /**
     * Handle the DataTable data processing.
     */
    public function handle()
    {
        $query = Transaction::query()->with(['account', 'user']);

        return DataTables::of($query)
            ->addColumn('action', function ($model) {
                return view('pages.transaction.columns._actions', compact('model'))->render();
            })
            ->addColumn('account_name', function ($model) {
                return $model->account ? $model->account->name : '<span class="text-muted">-</span>';
            })
            ->editColumn('type', function ($model) {
                $typeClass = $model->type === 'income' ? 'bg-success' : 'bg-danger';
                return '<span class="badge ' . $typeClass . '">' . ucfirst($model->type ?? 'N/A') . '</span>';
            })
            ->editColumn('amount', function ($model) {
                $amountClass = $model->type === 'income' ? 'text-success' : 'text-danger';
                $prefix = $model->type === 'income' ? '+' : '-';
                return '<span class="' . $amountClass . ' fw-semibold">' . $prefix . number_format($model->amount, 2) . '</span>';
            })
            ->editColumn('paid_at', function ($model) {
                return $model->paid_at ? $model->paid_at->format('Y-m-d H:i') : '-';
            })
            ->editColumn('payment_method', function ($model) {
                return $model->payment_method ? ucfirst($model->payment_method) : '-';
            })
            ->filter(function ($query) {
                $this->applySearch($query);
                $this->applyFilters($query); // Auto-apply all filters
            }, true)
            ->rawColumns(['action', 'type', 'amount', 'account_name'])
            ->make(true);
    }
}





