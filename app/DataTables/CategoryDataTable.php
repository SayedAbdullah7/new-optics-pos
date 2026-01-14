<?php

namespace App\DataTables;

use App\Helpers\Column;
use App\Helpers\Filter;
use App\Models\Category;
use Yajra\DataTables\Facades\DataTables;

class CategoryDataTable extends BaseDataTable
{
    /**
     * Get the columns for the DataTable.
     */
    public function columns(): array
    {
        return [
            Column::create('id')->setOrderable(true),
            Column::create('name')->setTitle('Category Name'),
            Column::create('description')->setTitle('Description'),
            Column::create('products_count')->setTitle('Products')->setSearchable(false)->setOrderable(false),
            Column::create('is_active')->setTitle('Status'),
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
            'is_active' => Filter::select('Status', [
                '1' => 'Active',
                '0' => 'Inactive'
            ]),
        ];
    }

    /**
     * Handle the DataTable data processing.
     */
    public function handle()
    {
        $query = Category::query()->withCount('products');

        return DataTables::of($query)
            ->addColumn('action', function ($model) {
                return view('pages.category.columns._actions', compact('model'))->render();
            })
            ->editColumn('is_active', function ($model) {
                return $model->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>';
            })
            ->editColumn('products_count', function ($model) {
                return '<span class="badge bg-info">' . $model->products_count . '</span>';
            })
            ->editColumn('description', function ($model) {
                return $model->description
                    ? \Illuminate\Support\Str::limit($model->description, 50)
                    : '<span class="text-muted">-</span>';
            })
            ->editColumn('created_at', function ($model) {
                return $model->created_at ? $model->created_at->format('Y-m-d H:i') : '-';
            })
            ->filter(function ($query) {
                $this->applySearch($query);
                $this->applyFilters($query);
            }, true)
            ->rawColumns(['action', 'is_active', 'products_count', 'description'])
            ->make(true);
    }
}





