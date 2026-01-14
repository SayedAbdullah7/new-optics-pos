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

        return view('pages.lens.show', compact('lens'));
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
