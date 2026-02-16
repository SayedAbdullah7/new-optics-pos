<?php

namespace App\DataTables;

use App\Helpers\Column;
use App\Helpers\Filter;
use App\Models\Bill;
use App\Models\Vendor;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class BillReportDataTable extends BaseDataTable
{
    /**
     * Define searchable relations for the query.
     * These columns will be searched in related models.
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
            Column::create('vendor_name')->setTitle('Name')->setName('vendor.name')->setSearchable(false),
            Column::create('bill_number')->setTitle('Number'),
            Column::create('amount')->setTitle('Amount'),
            Column::create('paid')->setTitle('Paid')->setSearchable(false)->setOrderable(false),
            Column::create('balance')->setTitle('Balance')->setSearchable(false)->setOrderable(false),
        ];
    }

    /**
     * Get the filters for the DataTable.
     */
    public function filters(): array
    {
        $vendors = Vendor::pluck('name', 'id')->toArray();

        return [
            'vendor_id' => Filter::select('Vendor', ['' => 'All Vendors'] + $vendors),
        ];
    }

    /**
     * Handle the DataTable data processing.
     */
    public function handle()
    {
        $from = request()->get('from', Carbon::now()->format('Y-m-d'));
        $to = request()->get('to', Carbon::now()->format('Y-m-d'));

        $query = Bill::betweenDates($from, $to)
            ->with(['vendor']);

        return DataTables::of($query)
            ->addColumn('vendor_name', function ($model) {
                return '<span class="text-gray-800 fw-bold d-block fs-7">' . ($model->vendor->name ?? '-') . '</span>';
            })
            ->addColumn('paid', function ($model) use ($to) {
                return '<span class="text-gray-700 fw-semibold">' . number_format($model->vendor->paidUntil($to), 2) . '</span>';
            })
            ->addColumn('balance', function ($model) use ($to) {
                return '<span class="text-gray-700 fw-semibold">' . number_format($model->vendor->balanceUntil($to), 2) . '</span>';
            })
            ->editColumn('bill_number', function ($model) {
                return '<a href="' . route('admin.bills.show', $model->id) . '" class="text-primary fw-semibold text-hover-primary">' . $model->bill_number . '</a>';
            })
            ->editColumn('amount', function ($model) {
                return '<span class="text-success fw-bold fs-6">' . number_format($model->amount, 2) . '</span>';
            })
            ->filter(function ($query) {
                $this->applySearch($query);
                $this->applyFilters($query); // Auto-apply all filters
            }, true)
            ->rawColumns(['vendor_name', 'bill_number', 'amount', 'paid', 'balance'])
            ->make(true);
    }

    /**
     * Override applySearch to support vendor name search.
     * This extends the base search functionality.
     */
    protected function applySearch($query): void
    {
        $search = request()->input('search.value');
        if (!$search || strlen(trim($search)) < 2) {
            return;
        }

        $searchTerm = '%' . trim($search) . '%';

        $query->orWhere(function ($q) use ($searchTerm) {
            $q->where('bill_number', 'like', $searchTerm)
              ->orWhere('amount', 'like', $searchTerm)
              ->orWhereHas('vendor', function ($vendorQuery) use ($searchTerm) {
                  $vendorQuery->where('name', 'like', $searchTerm);
              });
        });
    }
}
