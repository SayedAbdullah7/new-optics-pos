<?php

namespace App\Http\Controllers;

use App\DataTables\CategoryDataTable;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(CategoryDataTable $dataTable, Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            return $dataTable->handle();
        }

        return view('pages.category.index', [
            'columns' => $dataTable->columns(),
            'filters' => $dataTable->filters(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('pages.category.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request): JsonResponse
    {
        try {
            $category = Category::create($request->validated());

            return response()->json([
                'status' => true,
                'msg' => 'Category created successfully.',
                'data' => $category
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Failed to create category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): View
    {
        $category->load(['products.category', 'products.translations']);

        // Calculate category analytics
        $products = $category->products;

        $totalStock = 0;
        $stockValueAtCost = 0;
        $totalSoldQty = 0;
        $totalRevenue = 0;
        $totalBoughtQty = 0;
        $totalPurchaseSpent = 0;
        $totalCogs = 0;

        foreach ($products as $product) {
            $stock = $product->stock;
            $totalStock += $stock;
            $stockValueAtCost += $stock * $product->purchase_price;

            // Get sold data for this product
            $soldData = \App\Models\InvoiceItem::where('item_id', $product->id)
                ->whereHas('invoice', function($q) {
                    $q->whereNotIn('status', ['canceled', 'cancelled']);
                })
                ->selectRaw('SUM(quantity) as total_sold_qty, SUM(total) as total_revenue')
                ->first();

            $productSoldQty = (int) ($soldData->total_sold_qty ?? 0);
            $totalSoldQty += $productSoldQty;
            $totalRevenue += (float) ($soldData->total_revenue ?? 0);
            $totalCogs += $productSoldQty * $product->purchase_price;

            // Get bought data
            $boughtData = \Illuminate\Support\Facades\DB::table('bill_items')
                ->where('item_id', $product->id)
                ->selectRaw('SUM(quantity) as total_bought_qty, SUM(total) as total_spent')
                ->first();

            $totalBoughtQty += (int) ($boughtData->total_bought_qty ?? 0);
            $totalPurchaseSpent += (float) ($boughtData->total_spent ?? 0);
        }

        $realizedGrossProfit = $totalRevenue - $totalCogs;
        $expectedRevenue = 0;
        $expectedProfit = 0;

        foreach ($products as $product) {
            $expectedRevenue += $product->stock * $product->sale_price;
            $expectedProfit += $product->stock * ($product->sale_price - $product->purchase_price);
        }

        $analytics = [
            'total_products' => $products->count(),
            'total_stock' => $totalStock,
            'stock_value_at_cost' => $stockValueAtCost,
            'expected_revenue' => $expectedRevenue,
            'expected_profit' => $expectedProfit,
            'total_sold_qty' => $totalSoldQty,
            'total_revenue' => $totalRevenue,
            'total_cogs' => $totalCogs,
            'realized_gross_profit' => $realizedGrossProfit,
            'total_bought_qty' => $totalBoughtQty,
            'total_purchase_spent' => $totalPurchaseSpent,
        ];

        return view('pages.category.show', compact('category', 'analytics', 'products'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category): View
    {
        return view('pages.category.form', ['model' => $category]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, Category $category): JsonResponse
    {
        try {
            $category->update($request->validated());

            return response()->json([
                'status' => true,
                'msg' => 'Category updated successfully.',
                'data' => $category
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Failed to update category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): JsonResponse
    {
        try {
            if ($category->products()->exists()) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Cannot delete category with existing products.'
                ], 422);
            }

            $category->delete();

            return response()->json([
                'status' => true,
                'msg' => 'Category deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Failed to delete category: ' . $e->getMessage()
            ], 500);
        }
    }
}





