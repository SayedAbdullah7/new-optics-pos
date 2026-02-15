<?php

namespace App\Http\Controllers;

use App\DataTables\StockDataTable;
use App\Models\Category;
use App\Models\InvoiceItem;
use App\Models\Lens;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(StockDataTable $dataTable, Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            return $dataTable->handle();
        }

        // Overall KPIs
        $totalProducts = Product::count();
        $totalLenses = Lens::count();

        // Get all products with their stock (using accessor)
        $products = Product::with(['category', 'translations'])->get();

        $totalStockUnits = 0;
        $inventoryValueAtCost = 0;
        $inventoryValueAtRetail = 0;
        $lowStockCount = 0;
        $outOfStockCount = 0;

        foreach ($products as $product) {
            $stock = $product->stock;
            $totalStockUnits += $stock;
            $inventoryValueAtCost += $stock * $product->purchase_price;
            $inventoryValueAtRetail += $stock * $product->sale_price;

            if ($stock <= 0) {
                $outOfStockCount++;
            } elseif ($stock <= 10) {
                $lowStockCount++;
            }
        }

        $expectedProfit = $inventoryValueAtRetail - $inventoryValueAtCost;
        $expectedMarginPercent = $inventoryValueAtRetail > 0
            ? ($expectedProfit / $inventoryValueAtRetail) * 100
            : 0;

        $kpis = [
            'total_products' => $totalProducts,
            'total_lenses' => $totalLenses,
            'total_stock_units' => $totalStockUnits,
            'inventory_value_at_cost' => $inventoryValueAtCost,
            'inventory_value_at_retail' => $inventoryValueAtRetail,
            'expected_profit' => $expectedProfit,
            'expected_margin_percent' => $expectedMarginPercent,
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
        ];

        // Per-Product Analytics
        $productAnalytics = [];
        foreach ($products as $product) {
            // Get sold quantity and revenue (excluding cancelled invoices)
            $soldData = InvoiceItem::where('item_id', $product->id)
                ->whereHas('invoice', function($q) {
                    $q->whereNotIn('status', ['canceled', 'cancelled']);
                })
                ->selectRaw('SUM(quantity) as total_sold_qty, SUM(total) as total_revenue')
                ->first();

            $soldQty = (int) ($soldData->total_sold_qty ?? 0);
            $revenue = (float) ($soldData->total_revenue ?? 0);

            // Get bought quantity and total spent
            $boughtData = DB::table('bill_items')
                ->where('item_id', $product->id)
                ->selectRaw('SUM(quantity) as total_bought_qty, SUM(total) as total_spent')
                ->first();

            $boughtQty = (int) ($boughtData->total_bought_qty ?? 0);
            $purchaseSpent = (float) ($boughtData->total_spent ?? 0);

            // Calculate metrics
            $cogs = $soldQty * $product->purchase_price;
            $realizedProfit = $revenue - $cogs;
            $avgSalePrice = $soldQty > 0 ? $revenue / $soldQty : 0;
            $stockValueAtCost = $product->stock * $product->purchase_price;

            $productAnalytics[] = [
                'product' => $product,
                'current_stock' => $product->stock,
                'stock_value_at_cost' => $stockValueAtCost,
                'total_sold_qty' => $soldQty,
                'total_revenue' => $revenue,
                'cogs' => $cogs,
                'realized_profit' => $realizedProfit,
                'avg_sale_price' => $avgSalePrice,
                'total_bought_qty' => $boughtQty,
                'total_purchase_spent' => $purchaseSpent,
            ];
        }

        // Per-Category Analytics
        $categories = Category::with('translations')->get();
        $categoryAnalytics = [];

        foreach ($categories as $category) {
            $categoryProducts = $products->where('category_id', $category->id);

            if ($categoryProducts->isEmpty()) {
                continue;
            }

            $categoryStock = 0;
            $categoryStockValueAtCost = 0;
            $categorySoldQty = 0;
            $categoryRevenue = 0;
            $categoryBoughtQty = 0;
            $categoryCogs = 0;

            foreach ($categoryProducts as $product) {
                $categoryStock += $product->stock;
                $categoryStockValueAtCost += $product->stock * $product->purchase_price;

                // Get sold data for this product
                $soldData = InvoiceItem::where('item_id', $product->id)
                    ->whereHas('invoice', function($q) {
                        $q->whereNotIn('status', ['canceled', 'cancelled']);
                    })
                    ->selectRaw('SUM(quantity) as total_sold_qty, SUM(total) as total_revenue')
                    ->first();

                $productSoldQty = (int) ($soldData->total_sold_qty ?? 0);
                $categorySoldQty += $productSoldQty;
                $categoryRevenue += (float) ($soldData->total_revenue ?? 0);

                // Calculate COGS for this product
                $categoryCogs += $productSoldQty * $product->purchase_price;

                // Get bought data
                $boughtData = DB::table('bill_items')
                    ->where('item_id', $product->id)
                    ->selectRaw('SUM(quantity) as total_bought_qty')
                    ->first();

                $categoryBoughtQty += (int) ($boughtData->total_bought_qty ?? 0);
            }

            $categoryRealizedProfit = $categoryRevenue - $categoryCogs;

            $categoryAnalytics[] = [
                'category' => $category,
                'current_stock' => $categoryStock,
                'stock_value_at_cost' => $categoryStockValueAtCost,
                'total_sold_qty' => $categorySoldQty,
                'total_revenue' => $categoryRevenue,
                'cogs' => $categoryCogs,
                'realized_profit' => $categoryRealizedProfit,
                'total_bought_qty' => $categoryBoughtQty,
            ];
        }

        return view('pages.stock.index', [
            'columns' => $dataTable->columns(),
            'filters' => $dataTable->filters(),
            'kpis' => $kpis,
            'productAnalytics' => $productAnalytics,
            'categoryAnalytics' => $categoryAnalytics,
        ]);
    }
}
