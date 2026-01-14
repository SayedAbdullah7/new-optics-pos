<?php

namespace App\Http\Controllers;

use App\DataTables\LensTypeDataTable;
use App\Http\Requests\LensTypeRequest;
use App\Models\LensType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LensTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(LensTypeDataTable $dataTable, Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            return $dataTable->handle();
        }

        return view('pages.lens-type.index', [
            'columns' => $dataTable->columns(),
            'filters' => $dataTable->filters(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('pages.lens-type.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LensTypeRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $type = LensType::create($data);

            DB::commit();

            return response()->json([
                'status' => true,
                'msg' => 'Lens type created successfully.',
                'data' => $type
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'msg' => 'Failed to create lens type: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(LensType $lensType): View
    {
        $lensType->loadCount('lenses');
        return view('pages.lens-type.show', compact('lensType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LensType $lensType): View
    {
        return view('pages.lens-type.form', ['model' => $lensType]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LensTypeRequest $request, LensType $lensType): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $lensType->update($data);

            DB::commit();

            return response()->json([
                'status' => true,
                'msg' => 'Lens type updated successfully.',
                'data' => $lensType
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'msg' => 'Failed to update lens type: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LensType $lensType): JsonResponse
    {
        try {
            // Check if type is used in lenses
            if ($lensType->lenses()->exists()) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Cannot delete lens type with existing lenses.'
                ], 422);
            }

            DB::beginTransaction();

            $lensType->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'msg' => 'Lens type deleted successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'msg' => 'Failed to delete lens type: ' . $e->getMessage()
            ], 500);
        }
    }
}
