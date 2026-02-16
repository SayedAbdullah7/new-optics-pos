<?php

namespace App\DataTables;

use App\Helpers\Column;
use App\Helpers\Filter;
use App\Models\Vendor;
use Yajra\DataTables\Facades\DataTables;

class VendorDataTable extends BaseDataTable
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
            Column::create('name')->setTitle('Vendor Name'),
            Column::create('phone')->setTitle('Phone')->setSearchable(false),
            Column::create('address')->setTitle('Address'),
            Column::create('bills_count')->setTitle('Bills')->setSearchable(false)->setOrderable(false),
            Column::create('action')->setTitle('Actions')->setSearchable(false)->setOrderable(false),
        ];
    }

    /**
     * Get the filters for the DataTable.
     */
    public function filters(): array
    {
        return [];
    }

    /**
     * Handle the DataTable data processing.
     */
    public function handle()
    {
        $query = Vendor::query()->withCount('bills');

        return DataTables::of($query)
            ->addColumn('action', function ($model) {
                return view('pages.vendor.columns._actions', compact('model'))->render();
            })
            ->editColumn('phone', function ($model) {
                if (!$model->phone) {
                    return '<span class="text-muted">-</span>';
                }
                $phones = is_array($model->phone) ? $model->phone : [$model->phone];
                return implode('<br>', array_map('e', $phones));
            })
            ->editColumn('address', function ($model) {
                return $model->address ? \Illuminate\Support\Str::limit($model->address, 50) : '<span class="text-muted">-</span>';
            })
            ->editColumn('bills_count', function ($model) {
                return '<span class="badge bg-info">' . $model->bills_count . '</span>';
            })
            ->filter(function ($query) {
                $this->applySearch($query);
                $this->applyFilters($query); // Auto-apply all filters
            }, true)
            ->rawColumns(['action', 'phone', 'address', 'bills_count'])
            ->make(true);
    }
}





