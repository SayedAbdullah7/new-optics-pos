<?php

namespace App\DataTables;

use App\Helpers\Column;
use App\Helpers\Filter;
use App\Models\Category;
use Yajra\DataTables\Facades\DataTables;

class CategoryDataTable extends BaseDataTable
{
    /**
     * Define searchable relations for the query.
     * These columns will be searched in related models.
     * Note: For translated fields, we handle them in applySearch method.
     */
    protected array $searchableRelations = [];

    /**
     * Get the columns for the DataTable.
     */
    public function columns(): array
    {
        return [
            Column::create('id')->setOrderable(true),
            Column::create('name')->setTitle('Category Name')->setSearchable(false),
            Column::create('description')->setTitle('Description')->setSearchable(false),
            Column::create('products_count')->setTitle('Products')->setSearchable(false)->setOrderable(false),
            // Column::create('is_active')->setTitle('Status'),
            Column::create('created_at')->setTitle('Created')->setVisible(false),
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
     * Override applySearch to support translated fields in main model.
     * This extends the base search functionality to handle translatable models.
     */
    protected function applySearch($query): void
    {
        $search = request()->input('search.value');
        if (!$search || strlen(trim($search)) < 2) {
            return;
        }

        $searchTerm = '%' . trim($search) . '%';

        // Search in translated fields of the main model (Category)
        $query->orWhere(function ($q) use ($searchTerm) {
            // Search in category name (translated)
            $q->whereTranslationLike('name', $searchTerm);
        });
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
                $this->applyFilters($query); // Auto-apply all filters
            }, true)
            ->rawColumns(['action', 'products_count', 'description'])
            ->make(true);
    }
}





