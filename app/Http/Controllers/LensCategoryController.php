<?php

namespace App\Http\Controllers;

use App\DataTables\LensCategoryDataTable;
use App\Http\Requests\LensCategoryRequest;
use App\Models\LensCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LensCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(LensCategoryDataTable $dataTable, Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            return $dataTable->handle();
        }

        return view('pages.lens-brand.index', [
            'columns' => $dataTable->columns(),
            'filters' => $dataTable->filters(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('pages.lens-brand.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LensCategoryRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $category = LensCategory::create($data);

            DB::commit();

            return response()->json([
                'status' => true,
                'msg' => 'Lens brand created successfully.',
                'data' => $category
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'msg' => 'Failed to create lens brand: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(LensCategory $lensCategory): View
    {
        $lensCategory->loadCount('lenses');
        return view('pages.lens-brand.show', compact('lensCategory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LensCategory $lensCategory): View
    {
        return view('pages.lens-brand.form', ['model' => $lensCategory]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LensCategoryRequest $request, LensCategory $lensCategory): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $lensCategory->update($data);

            DB::commit();

            return response()->json([
                'status' => true,
                'msg' => 'Lens brand updated successfully.',
                'data' => $lensCategory
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'msg' => 'Failed to update lens brand: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LensCategory $lensCategory): JsonResponse
    {
        try {
            // Check if category is used in lenses
            if ($lensCategory->lenses()->exists()) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Cannot delete lens brand with existing lenses.'
                ], 422);
            }

            DB::beginTransaction();

            $lensCategory->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'msg' => 'Lens brand deleted successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'msg' => 'Failed to delete lens brand: ' . $e->getMessage()
            ], 500);
        }
    }
}
