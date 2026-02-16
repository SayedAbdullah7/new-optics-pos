<?php

namespace App\DataTables;

use App\Helpers\Column;
use App\Helpers\Filter;
use App\Models\Category;
use App\Models\Product;
use Yajra\DataTables\Facades\DataTables;

class ProductDataTable extends BaseDataTable
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
            Column::create('name')->setTitle('Product Name')->setSearchable(false),
            Column::create('category_name')->setTitle('Category')->setName('category.name')->setSearchable(false),
            Column::create('purchase_price')->setTitle('Purchase Price'),
            Column::create('sale_price')->setTitle('Sale Price'),
            Column::create('stock')->setTitle('Stock'),
            Column::create('created_at')->setTitle('Created')->setVisible(false),
            Column::create('action')->setTitle('Actions')->setSearchable(false)->setOrderable(false),
        ];
    }

    /**
     * Get the filters for the DataTable.
     */
    public function filters(): array
    {
        // Get categories with translations
        $categoriesQuery = Category::with('translations');
        $categories = [];
        foreach ($categoriesQuery->get() as $category) {
            $categories[$category->id] = $category->name;
        }

        return [
            'category_id' => Filter::select('Category', $categories),
            'stock' => Filter::selectCustom('Stock Status', [
                'low' => 'Low Stock (< 10)',
                'out' => 'Out of Stock',
                'in' => 'In Stock',
            ], function ($query, $value) {
                switch ($value) {
                    case 'low':
                        $query->where('stock', '>', 0)->where('stock', '<=', 10);
                        break;
                    case 'out':
                        $query->where('stock', '<=', 0);
                        break;
                    case 'in':
                        $query->where('stock', '>', 0);
                        break;
                }
            }),
        ];
    }

    /**
     * Override applySearch to support translated fields in main model and relations.
     * This extends the base search functionality to handle translatable models.
     */
    protected function applySearch($query): void
    {
        $search = request()->input('search.value');
        if (!$search || strlen(trim($search)) < 2) {
            return;
        }

        $searchTerm = '%' . trim($search) . '%';

        // Search in translated fields of the main model (Product)
        // and other non-relation fields like item_code
        $query->orWhere(function ($q) use ($searchTerm) {
            // Search in product name (translated)
            $q->whereTranslationLike('name', $searchTerm)
              ->orWhereTranslationLike('description', $searchTerm)
              // Search in item_code
              ->orWhere('item_code', 'like', $searchTerm);
        });

        // Handle searchableRelations with translation support
        // Search in category name (translated)
        $query->orWhereHas('category', function ($catQuery) use ($searchTerm) {
            $catQuery->whereTranslationLike('name', $searchTerm);
        });
    }

    /**
     * Handle the DataTable data processing.
     */
    public function handle()
    {
        $query = Product::query()->with('category');

        return DataTables::of($query)
            ->addColumn('action', function ($model) {
                return view('pages.product.columns._actions', compact('model'))->render();
            })
            ->addColumn('category_name', function ($model) {
                return $model->category ? $model->category->name : '<span class="text-muted">-</span>';
            })
            ->editColumn('stock', function ($model) {
                $class = 'bg-success';
                if ($model->stock <= 0) {
                    $class = 'bg-danger';
                } elseif ($model->stock <= 10) {
                    $class = 'bg-warning';
                }
                return '<span class="badge ' . $class . '">' . $model->stock . '</span>';
            })
            ->editColumn('purchase_price', function ($model) {
                return number_format($model->purchase_price, 2);
            })
            ->editColumn('sale_price', function ($model) {
                return number_format($model->sale_price, 2);
            })
            ->editColumn('created_at', function ($model) {
                return $model->created_at ? $model->created_at->format('Y-m-d H:i') : '-';
            })
            ->filter(function ($query) {
                $this->applySearch($query);
                $this->applyFilters($query); // Auto-apply all filters
            }, true)
            ->rawColumns(['action', 'stock', 'category_name'])
            ->make(true);
    }
}





