<?php

namespace App\DataTables;

use App\Helpers\Column;
use App\Helpers\Filter;
use App\Models\Bill;
use App\Models\Vendor;
use Yajra\DataTables\Facades\DataTables;

class BillDataTable extends BaseDataTable
{
    /**
     * Define searchable relations.
     */
    protected array $searchableRelations = [
        'vendor' => ['name'],
    ];

    /**
     * Get the columns for the DataTable.
     */
    public function columns(): array
    {
        return [
            Column::create('id')->setOrderable(true),
            Column::create('bill_number')->setTitle('Bill #'),
            Column::create('vendor_name')->setTitle('Vendor')->setName('vendor.name'),
            Column::create('amount')->setTitle('Amount'),
            Column::create('paid')->setTitle('Paid')->setSearchable(false)->setOrderable(false),
            Column::create('balance')->setTitle('Balance')->setSearchable(false)->setOrderable(false),
            Column::create('status')->setTitle('Status'),
            Column::create('billed_at')->setTitle('Date'),
            Column::create('action')->setTitle('Actions')->setSearchable(false)->setOrderable(false),
        ];
    }

    /**
     * Get the filters for the DataTable.
     */
    public function filters(): array
    {
        $vendors = Vendor::pluck('name', 'id')->toArray();

        return [
            'vendor_id' => Filter::select('Vendor', $vendors),
            'status' => Filter::select('Status', [
                'paid' => 'Paid',
                'partial' => 'Partial',
                'unpaid' => 'Unpaid',
            ]),
            'billed_at' => Filter::dateRange('Bill Date'),
        ];
    }

    /**
     * Handle the DataTable data processing.
     */
    public function handle()
    {
        $query = Bill::query()->with(['vendor', 'transactions']);

        return DataTables::of($query)
            ->addColumn('action', function ($model) {
                return view('pages.bill.columns._actions', compact('model'))->render();
            })
            ->addColumn('vendor_name', function ($model) {
                return $model->vendor ? $model->vendor->name : '<span class="text-muted">-</span>';
            })
            ->addColumn('paid', function ($model) {
                return number_format($model->paid, 2);
            })
            ->addColumn('balance', function ($model) {
                $balance = $model->balance;
                $class = $balance > 0 ? 'text-danger' : 'text-success';
                return '<span class="' . $class . '">' . number_format($balance, 2) . '</span>';
            })
            ->editColumn('amount', function ($model) {
                return number_format($model->amount, 2);
            })
            ->editColumn('status', function ($model) {
                $statusClass = match ($model->status) {
                    'paid' => 'bg-success',
                    'partial' => 'bg-warning',
                    default => 'bg-danger',
                };
                return '<span class="badge ' . $statusClass . '">' . ucfirst($model->status ?? 'unpaid') . '</span>';
            })
            ->editColumn('billed_at', function ($model) {
                return $model->billed_at ? $model->billed_at->format('Y-m-d') : '-';
            })
            ->filter(function ($query) {
                $this->applySearch($query);
                $this->applyFilters($query);
            }, true)
            ->rawColumns(['action', 'vendor_name', 'status', 'balance'])
            ->make(true);
    }
}





