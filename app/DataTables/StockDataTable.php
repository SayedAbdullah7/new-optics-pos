<?php

namespace App\DataTables;

use App\Helpers\Column;
use App\Helpers\Filter;
use App\Models\Product;
use Appstract\Stock\StockMutation;
use Yajra\DataTables\Facades\DataTables;

class StockDataTable extends BaseDataTable
{
    /**
     * Get the columns for the DataTable.
     */
    public function columns(): array
    {
        return [
            Column::create('id')->setOrderable(true),
            Column::create('product_name')->setTitle('Product Name')->setOrderable(false),
            Column::create('description')->setTitle('Description'),
            Column::create('reference')->setTitle('Reference')->setOrderable(false),
            Column::create('amount')->setTitle('Amount'),
            Column::create('stock_after')->setTitle('Stock After')->setOrderable(false),
            Column::create('created_at')->setTitle('Date'),
        ];
    }

    /**
     * Get the filters for the DataTable.
     */
    public function filters(): array
    {
        // Get all products for filter
        $productsQuery = Product::with('translations');
        $products = ['' => 'All Products'];
        foreach ($productsQuery->get() as $product) {
            $products[$product->id] = $product->name;
        }

        return [
            'product_id' => [
                'type' => 'select-custom',
                'label' => 'Product',
                'options' => $products,
                'callback' => function ($query, $value) {
                    if ($value) {
                        $query->where('stockable_id', $value);
                    }
                }
            ],
            'created_at' => Filter::dateRange('Date Range', null, null, 'created_at'),
        ];
    }

    /**
     * Override applySearch to support product name search.
     */
    protected function applySearch($query): void
    {
        $search = request()->input('search.value');
        if (!$search || strlen(trim($search)) < 1) {
            return;
        }

        $searchTerm = '%' . trim($search) . '%';

        $query->where(function ($q) use ($searchTerm) {
            $q->where('description', 'like', $searchTerm)
              ->orWhere('amount', 'like', $searchTerm)
              ->orWhere(function ($subQ) use ($searchTerm) {
                  // Search in Product stockables (already filtered to Product::class)
                  $subQ->where('stockable_type', Product::class)
                       ->whereHasMorph('stockable', [Product::class], function ($stockableQuery) use ($searchTerm) {
                           $stockableQuery->whereTranslationLike('name', $searchTerm)
                                        ->orWhere('item_code', 'like', $searchTerm);
                       });
              })
              ->orWhere(function ($subQ) use ($searchTerm) {
                  // Search in reference (only new Invoice class)
                  $subQ->where('reference_type', 'App\Models\Invoice')
                       ->whereHas('reference', function ($refQuery) use ($searchTerm) {
                           $refQuery->where('invoice_number', 'like', $searchTerm);
                       });
              });
        });
    }

    /**
     * Handle the DataTable data processing.
     */
    public function handle()
    {
        // Filter only new system products (exclude old App\Models\Item\Product)
        // Don't use eager loading to avoid Laravel trying to load old model types
        // Use raw where to ensure we only get new system products
        $query = StockMutation::where('stockable_type', Product::class);

        return DataTables::of($query)
            ->addColumn('product_name', function ($model) {
                try {
                    // Only try to access stockable if type is correct and load it safely
                    if ($model->stockable_type === Product::class) {
                        // Use whereHasMorph to safely load only Product type
                        $product = Product::find($model->stockable_id);
                        if ($product) {
                            return $product->name ?? '<span class="text-muted">-</span>';
                        }
                    }
                } catch (\Exception $e) {
                    // Handle case where stockable couldn't be loaded
                    \Log::warning('Failed to load stockable for mutation ' . $model->id . ': ' . $e->getMessage());
                }
                return '<span class="text-muted">-</span>';
            })
            ->addColumn('reference', function ($model) {
                try {
                    // Only try to load if reference_type is Invoice
                    if ($model->reference_type && $model->reference_type === 'App\Models\Invoice') {
                        $reference = $model->reference;
                        if ($reference) {
                            $invoiceNumber = $reference->invoice_number ?? $reference->id ?? '-';
                            $invoiceId = $reference->id ?? null;
                            if ($invoiceId) {
                                return '<a href="' . route('admin.invoices.show', $invoiceId) . '" class="btn btn-sm btn-primary">' . $invoiceNumber . '</a>';
                            }
                            return $invoiceNumber;
                        }
                    } elseif ($model->reference_type && $model->reference_id) {
                        // Old reference - just show ID
                        return '<span class="text-muted">Ref #' . $model->reference_id . '</span>';
                    }
                } catch (\Exception $e) {
                    // Handle case where reference couldn't be loaded
                }
                return '<span class="text-muted">-</span>';
            })
            ->addColumn('stock_after', function ($model) {
                try {
                    // Only try to access stockable if type is correct
                    if ($model->stockable_type === Product::class) {
                        $product = Product::find($model->stockable_id);
                        if ($product) {
                            // Calculate stock at the time of this mutation
                            // Get base stock from DB
                            $baseStock = (int) ($product->attributes['stock'] ?? 0);
                            // Get sum of mutations up to this point (only new system)
                            $mutationsStock = (int) \Appstract\Stock\StockMutation::where('stockable_type', Product::class)
                                ->where('stockable_id', $product->id)
                                ->where('created_at', '<=', $model->created_at)
                                ->sum('amount');
                            $stock = $baseStock + $mutationsStock;

                            $class = 'text-success';
                            if ($stock <= 0) {
                                $class = 'text-danger';
                            } elseif ($stock <= 10) {
                                $class = 'text-warning';
                            }
                            return '<span class="' . $class . ' fw-bold">' . $stock . '</span>';
                        }
                    }
                } catch (\Exception $e) {
                    // Handle case where stockable couldn't be loaded
                    \Log::warning('Failed to calculate stock for mutation ' . $model->id . ': ' . $e->getMessage());
                }
                return '<span class="text-muted">-</span>';
            })
            ->editColumn('amount', function ($model) {
                $class = $model->amount >= 0 ? 'text-success' : 'text-danger';
                $sign = $model->amount >= 0 ? '+' : '';
                return '<span class="' . $class . ' fw-bold">' . $sign . $model->amount . '</span>';
            })
            ->editColumn('description', function ($model) {
                return $model->description ?: '<span class="text-muted">-</span>';
            })
            ->editColumn('created_at', function ($model) {
                return $model->created_at ? $model->created_at->format('Y-m-d H:i') : '-';
            })
            ->filter(function ($query) {
                $this->applySearch($query);
                $this->applyFilters($query);
            }, true)
            ->orderColumn('created_at', 'created_at $1')
            ->rawColumns(['product_name', 'reference', 'stock_after', 'amount', 'description'])
            ->make(true);
    }
}
