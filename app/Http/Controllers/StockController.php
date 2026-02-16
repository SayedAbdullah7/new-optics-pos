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

        // Overall KPIs - using aggregated queries
        $totalProducts = Product::count();
        $totalLenses = Lens::count();

        // Aggregate inventory metrics using cached stock and weighted_cost
        $inventoryAggregates = Product::selectRaw('
                SUM(stock) as total_stock_units,
                SUM(stock * weighted_cost) as inventory_value_at_cost,
                SUM(stock * sale_price) as inventory_value_at_retail,
                SUM(CASE WHEN stock <= 0 THEN 1 ELSE 0 END) as out_of_stock_count,
                SUM(CASE WHEN stock > 0 AND stock <= 10 THEN 1 ELSE 0 END) as low_stock_count
            ')
            ->first();

        $totalStockUnits = (int) ($inventoryAggregates->total_stock_units ?? 0);
        $inventoryValueAtCost = (float) ($inventoryAggregates->inventory_value_at_cost ?? 0);
        $inventoryValueAtRetail = (float) ($inventoryAggregates->inventory_value_at_retail ?? 0);
        $lowStockCount = (int) ($inventoryAggregates->low_stock_count ?? 0);
        $outOfStockCount = (int) ($inventoryAggregates->out_of_stock_count ?? 0);

        $expectedProfit = $inventoryValueAtRetail - $inventoryValueAtCost;
        $expectedMarginPercent = $inventoryValueAtRetail > 0
            ? ($expectedProfit / $inventoryValueAtRetail) * 100
            : 0;

        // Get all products with their stock (using accessor) for detailed analytics
        $products = Product::with(['category', 'translations'])->get();

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

        // Per-Product Analytics - using aggregated queries for better performance
        $productIds = $products->pluck('id');
        
        // Aggregate sold data with real COGS from cost_price
        $soldAggregates = InvoiceItem::whereIn('item_id', $productIds)
            ->whereHas('invoice', function($q) {
                $q->whereNotIn('status', ['canceled', 'cancelled']);
            })
            ->selectRaw('
                item_id,
                SUM(quantity) as total_sold_qty,
                SUM(total) as total_revenue,
                SUM(cost_price * quantity) as total_cogs
            ')
            ->groupBy('item_id')
            ->get()
            ->keyBy('item_id');

        // Aggregate purchase data
        $purchaseAggregates = DB::table('bill_items')
            ->whereIn('item_id', $productIds)
            ->selectRaw('
                item_id,
                SUM(quantity) as total_bought_qty,
                SUM(total) as total_spent
            ')
            ->groupBy('item_id')
            ->get()
            ->keyBy('item_id');

        $productAnalytics = [];
        foreach ($products as $product) {
            $soldData = $soldAggregates->get($product->id);
            $boughtData = $purchaseAggregates->get($product->id);

            $soldQty = (int) ($soldData->total_sold_qty ?? 0);
            $revenue = (float) ($soldData->total_revenue ?? 0);
            $cogs = (float) ($soldData->total_cogs ?? 0); // Real COGS from cost_price
            $boughtQty = (int) ($boughtData->total_bought_qty ?? 0);
            $purchaseSpent = (float) ($boughtData->total_spent ?? 0);

            $realizedProfit = $revenue - $cogs;
            $avgSalePrice = $soldQty > 0 ? $revenue / $soldQty : 0;
            $stockValueAtCost = $product->stock * ($product->weighted_cost ?: $product->purchase_price);

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
                $categoryStockValueAtCost += $product->stock * ($product->weighted_cost ?: $product->purchase_price);

                // Get sold data for this product (using pre-aggregated data)
                $soldData = $soldAggregates->get($product->id);
                $productSoldQty = (int) ($soldData->total_sold_qty ?? 0);
                $categorySoldQty += $productSoldQty;
                $categoryRevenue += (float) ($soldData->total_revenue ?? 0);

                // Calculate real COGS for this product from cost_price
                $categoryCogs += (float) ($soldData->total_cogs ?? 0);

                // Get bought data (using pre-aggregated data)
                $boughtData = $purchaseAggregates->get($product->id);
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
