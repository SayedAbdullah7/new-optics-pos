<?php

namespace App\DataTables;

use App\Helpers\Column;
use App\Helpers\Filter;
use App\Models\Lens;
use Yajra\DataTables\Facades\DataTables;

class LensDataTable extends BaseDataTable
{
    /**
     * Define searchable relations for the query.
     * These columns will be searched in related models.
     */
    protected array $searchableRelations = [
        'category' => ['name', 'brand_name'],
        'rangePower' => ['name'],
        'type' => ['name'],
    ];

    /**
     * Get the columns for the DataTable.
     */
    public function columns(): array
    {
        return [
            Column::create('id')->setOrderable(true),
            Column::create('lens_code')->setTitle('Lens Code'),
            Column::create('range_power')->setTitle('Range Power')->setSearchable(false),
            Column::create('type')->setTitle('Type')->setSearchable(false),
            Column::create('category')->setTitle('Brand/Category')->setSearchable(false),
            Column::create('sale_price')->setTitle('Sale Price')->setSearchable(false),
            Column::create('purchase_price')->setTitle('Purchase Price')->setSearchable(false),
            Column::create('created_at')->setTitle('Created')->setVisible(false),
            Column::create('action')->setTitle('Actions')->setSearchable(false)->setOrderable(false),
        ];
    }

    /**
     * Get the filters for the DataTable.
     */
    public function filters(): array
    {
        return [
            'range_power' => Filter::selectCustom('Range Power',
                \App\Models\RangePower::pluck('name', 'id')->toArray(),
                function($query, $value) {
                    $query->where('RangePower_id', $value);
                }
            ),
            'type' => Filter::selectCustom('Type',
                \App\Models\LensType::pluck('name', 'id')->toArray(),
                function($query, $value) {
                    $query->where('type_id', $value);
                }
            ),
            'category' => Filter::selectCustom('Category',
                \App\Models\LensCategory::pluck('brand_name', 'id')->toArray(),
                function($query, $value) {
                    $query->where('category_id', $value);
                }
            ),
            'created_at' => Filter::dateRange('Created Date Range'),
        ];
    }

    /**
     * Handle the DataTable data processing.
     */
    public function handle()
    {
        $query = Lens::query()->with(['rangePower', 'type', 'category']);

        return DataTables::of($query)
            ->addColumn('action', function ($model) {
                return view('pages.lens.columns._actions', compact('model'))->render();
            })
            ->addColumn('range_power', function ($model) {
                return $model->rangePower ? $model->rangePower->name : '<span class="text-muted">-</span>';
            })
            ->addColumn('type', function ($model) {
                return $model->type ? $model->type->name : '<span class="text-muted">-</span>';
            })
            ->addColumn('category', function ($model) {
                if (!$model->category) {
                    return '<span class="text-muted">-</span>';
                }
                return $model->category->brand_name ?? $model->category->name ?? '-';
            })
            ->editColumn('sale_price', function ($model) {
                return $model->sale_price ? number_format($model->sale_price, 2) : '<span class="text-muted">-</span>';
            })
            ->editColumn('purchase_price', function ($model) {
                return $model->purchase_price ? number_format($model->purchase_price, 2) : '<span class="text-muted">-</span>';
            })
            ->editColumn('created_at', function ($model) {
                return $model->created_at ? $model->created_at->format('Y-m-d H:i') : '-';
            })
            ->filter(function ($query) {
                $this->applySearch($query);
                $this->applyFilters($query); // Auto-apply all filters
            }, true)
            ->rawColumns(['action', 'range_power', 'type', 'category', 'sale_price', 'purchase_price'])
            ->make(true);
    }
}
