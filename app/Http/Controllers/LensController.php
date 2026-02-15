<?php

namespace App\Http\Controllers;

use App\DataTables\LensDataTable;
use App\Http\Requests\LensRequest;
use App\Models\Lens;
use App\Models\LensCategory;
use App\Models\LensType;
use App\Models\RangePower;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LensController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(LensDataTable $dataTable, Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            return $dataTable->handle();
        }

        return view('pages.lens.index', [
            'columns' => $dataTable->columns(),
            'filters' => $dataTable->filters(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $ranges = RangePower::orderBy('name')->get();
        $types = LensType::orderBy('name')->get();
        $categories = LensCategory::orderBy('brand_name')->get();

        return view('pages.lens.form', compact('ranges', 'types', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LensRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $lens = Lens::create($data);

            DB::commit();

            return response()->json([
                'status' => true,
                'msg' => 'Lens created successfully.',
                'data' => $lens
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'msg' => 'Failed to create lens: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Lens $lens): View
    {
        $lens->load(['rangePower', 'type', 'category']);
        
        // Calculate financial analytics
        // Get sold quantity and revenue (excluding cancelled invoices)
        $soldData = \App\Models\InvoiceLens::where('lens_id', $lens->id)
            ->whereHas('invoice', function($q) {
                $q->whereNotIn('status', ['canceled', 'cancelled']);
            })
            ->selectRaw('SUM(quantity) as total_sold_qty, SUM(total) as total_revenue')
            ->first();
        
        $soldQty = (int) ($soldData->total_sold_qty ?? 0);
        $revenue = (float) ($soldData->total_revenue ?? 0);
        
        // Get bought quantity and total spent
        $boughtData = DB::table('bill_lenses')
            ->where('lens_id', $lens->id)
            ->selectRaw('SUM(quantity) as total_bought_qty, SUM(total) as total_spent')
            ->first();
        
        $boughtQty = (int) ($boughtData->total_bought_qty ?? 0);
        $purchaseSpent = (float) ($boughtData->total_spent ?? 0);
        
        // Calculate metrics
        $cogs = $soldQty * ($lens->purchase_price ?? 0);
        $realizedGrossProfit = $revenue - $cogs;
        $stockValueAtCost = $lens->stock * ($lens->purchase_price ?? 0);
        $expectedProfit = $lens->stock * (($lens->sale_price ?? 0) - ($lens->purchase_price ?? 0));
        
        $analytics = [
            'current_stock' => $lens->stock,
            'stock_value_at_cost' => $stockValueAtCost,
            'expected_profit' => $expectedProfit,
            'total_sold_qty' => $soldQty,
            'total_revenue' => $revenue,
            'total_bought_qty' => $boughtQty,
            'total_purchase_spent' => $purchaseSpent,
            'realized_gross_profit' => $realizedGrossProfit,
        ];
        
        return view('pages.lens.show', compact('lens', 'analytics'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lens $lens): View
    {
        $lens->load(['rangePower', 'type', 'category']);
        
        $ranges = RangePower::orderBy('name')->get();
        $types = LensType::orderBy('name')->get();
        $categories = LensCategory::orderBy('brand_name')->get();

        return view('pages.lens.form', [
            'model' => $lens,
            'ranges' => $ranges,
            'types' => $types,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LensRequest $request, Lens $lens): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $lens->update($data);

            DB::commit();

            return response()->json([
                'status' => true,
                'msg' => 'Lens updated successfully.',
                'data' => $lens
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'msg' => 'Failed to update lens: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lens $lens): JsonResponse
    {
        try {
            // Check if lens is used in invoices
            if ($lens->stock()->exists()) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Cannot delete lens with existing stock records.'
                ], 422);
            }

            DB::beginTransaction();

            $lens->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'msg' => 'Lens deleted successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'msg' => 'Failed to delete lens: ' . $e->getMessage()
            ], 500);
        }
    }
}
