<?php

namespace App\Http\Controllers;

use App\DataTables\ProductDataTable;
use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ProductDataTable $dataTable, Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            return $dataTable->handle();
        }

        return view('pages.product.index', [
            'columns' => $dataTable->columns(),
            'filters' => $dataTable->filters(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $categoriesQuery = Category::with('translations');
        $categories = [];
        foreach ($categoriesQuery->get() as $category) {
            $categories[$category->id] = $category->name;
        }
        return view('pages.product.form', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('uploads/product_images'), $imageName);
                $data['image'] = $imageName;
            }

            $product = Product::create($data);

            return response()->json([
                'status' => true,
                'msg' => 'Product created successfully.',
                'data' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Failed to create product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): View
    {
        $product->load(['category', 'translations']);
        
        // Calculate financial analytics
        // Get sold quantity and revenue (excluding cancelled invoices)
        $soldData = \App\Models\InvoiceItem::where('item_id', $product->id)
            ->whereHas('invoice', function($q) {
                $q->whereNotIn('status', ['canceled', 'cancelled']);
            })
            ->selectRaw('SUM(quantity) as total_sold_qty, SUM(total) as total_revenue')
            ->first();
        
        $soldQty = (int) ($soldData->total_sold_qty ?? 0);
        $revenue = (float) ($soldData->total_revenue ?? 0);
        
        // Get bought quantity and total spent
        $boughtData = \Illuminate\Support\Facades\DB::table('bill_items')
            ->where('item_id', $product->id)
            ->selectRaw('SUM(quantity) as total_bought_qty, SUM(total) as total_spent')
            ->first();
        
        $boughtQty = (int) ($boughtData->total_bought_qty ?? 0);
        $purchaseSpent = (float) ($boughtData->total_spent ?? 0);
        
        // Calculate metrics
        $cogs = $soldQty * $product->purchase_price;
        $realizedGrossProfit = $revenue - $cogs;
        $avgActualSalePrice = $soldQty > 0 ? $revenue / $soldQty : 0;
        $stockValueAtCost = $product->stock * $product->purchase_price;
        $expectedRevenue = $product->stock * $product->sale_price;
        $expectedProfit = $product->stock * ($product->sale_price - $product->purchase_price);
        
        $analytics = [
            'stock_value_at_cost' => $stockValueAtCost,
            'expected_revenue' => $expectedRevenue,
            'expected_profit' => $expectedProfit,
            'total_sold_qty' => $soldQty,
            'total_revenue' => $revenue,
            'cogs' => $cogs,
            'realized_gross_profit' => $realizedGrossProfit,
            'avg_actual_sale_price' => $avgActualSalePrice,
            'total_bought_qty' => $boughtQty,
            'total_purchase_spent' => $purchaseSpent,
        ];
        
        return view('pages.product.show', compact('product', 'analytics'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product): View
    {
        $categoriesQuery = Category::with('translations');
        $categories = [];
        foreach ($categoriesQuery->get() as $category) {
            $categories[$category->id] = $category->name;
        }
        return view('pages.product.form', [
            'model' => $product,
            'categories' => $categories
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, Product $product): JsonResponse
    {
        try {
            $data = $request->validated();

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image
                if ($product->image && file_exists(public_path('uploads/product_images/' . $product->image))) {
                    unlink(public_path('uploads/product_images/' . $product->image));
                }

                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('uploads/product_images'), $imageName);
                $data['image'] = $imageName;
            }

            $product->update($data);

            return response()->json([
                'status' => true,
                'msg' => 'Product updated successfully.',
                'data' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Failed to update product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): JsonResponse
    {
        try {
            // Delete image
            if ($product->image && file_exists(public_path('uploads/product_images/' . $product->image))) {
                unlink(public_path('uploads/product_images/' . $product->image));
            }

            // Delete translations
            $product->deleteTranslations();

            // Delete the product
            $product->delete();

            return response()->json([
                'status' => true,
                'msg' => 'Product deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Failed to delete product: ' . $e->getMessage()
            ], 500);
        }
    }
}





