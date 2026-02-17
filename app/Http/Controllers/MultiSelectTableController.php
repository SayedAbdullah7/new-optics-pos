<?php

namespace App\Http\Controllers;

use App\DataTables\LensPowerPresetDataTable;
use App\Models\LensPowerPreset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MultiSelectTableController extends Controller
{
    /**
     * List all lens power presets (index page).
     */
    public function presetsIndex(LensPowerPresetDataTable $dataTable, Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            return $dataTable->handle();
        }

        return view('pages.lens-power-presets.index', [
            'columns' => $dataTable->columns(),
            'filters' => $dataTable->filters(),
        ]);
    }

    /**
     * Display the lens power range table view (create or edit when preset is set).
     */
    public function index(Request $request): View
    {
        $minSph = $request->input('min_sph', -5.00);
        $maxSph = $request->input('max_sph', 5.00);
        $minCyl = $request->input('min_cyl', -5.00);
        $maxCyl = $request->input('max_cyl', 5.00);

        $sphValues = [];
        for ($sph = $maxSph; $sph >= $minSph; $sph -= 0.25) {
            $sphValues[] = round($sph, 2);
        }

        $cylValues = [];
        for ($cyl = $maxCyl; $cyl >= $minCyl; $cyl -= 0.25) {
            $cylValues[] = round($cyl, 2);
        }

        $presets = LensPowerPreset::orderBy('name')->get();
        $preset = null;
        if ($request->filled('preset')) {
            $preset = LensPowerPreset::with('values')->find($request->input('preset'));
        }

        return view('pages.multi-select-table.form', compact(
            'sphValues',
            'cylValues',
            'minSph',
            'maxSph',
            'minCyl',
            'maxCyl',
            'presets',
            'preset'
        ));
    }

    /**
     * Save current selection as a new preset.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'values' => 'required|array',
            'values.*.sph' => 'required|numeric',
            'values.*.cyl' => 'required|numeric',
        ]);

        $preset = LensPowerPreset::create([
            'name' => $request->input('name'),
        ]);

        $values = array_map(function ($item) {
            return [
                'sph' => round((float) $item['sph'], 2),
                'cyl' => round((float) $item['cyl'], 2),
            ];
        }, $request->input('values'));

        $preset->values()->createMany($values);

        return response()->json([
            'status' => true,
            'message' => __('تم الحفظ بنجاح'),
            'data' => ['id' => $preset->id, 'name' => $preset->name],
        ]);
    }

    /**
     * Update an existing preset with current selection.
     */
    public function update(Request $request, LensPowerPreset $preset): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'values' => 'required|array',
            'values.*.sph' => 'required|numeric',
            'values.*.cyl' => 'required|numeric',
        ]);

        if ($request->has('name')) {
            $preset->update(['name' => $request->input('name')]);
        }

        $preset->values()->delete();

        $values = array_map(function ($item) {
            return [
                'sph' => round((float) $item['sph'], 2),
                'cyl' => round((float) $item['cyl'], 2),
            ];
        }, $request->input('values'));

        $preset->values()->createMany($values);

        return response()->json([
            'status' => true,
            'message' => __('تم التحديث بنجاح'),
            'data' => ['id' => $preset->id, 'name' => $preset->name],
        ]);
    }

    /**
     * Search presets that contain the given SPH + CYL combination.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'sph' => 'required|numeric',
            'cyl' => 'required|numeric',
        ]);

        $sph = round((float) $request->input('sph'), 2);
        $cyl = round((float) $request->input('cyl'), 2);

        $presets = LensPowerPreset::query()
            ->whereHas('values', function ($q) use ($sph, $cyl) {
                $q->where('sph', $sph)->where('cyl', $cyl);
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'status' => true,
            'data' => $presets,
        ]);
    }

    /**
     * Delete a preset.
     */
    public function destroy(LensPowerPreset $preset): JsonResponse
    {
        $preset->delete();
        return response()->json([
            'status' => true,
            'message' => __('تم الحذف بنجاح'),
            'msg' => __('تم الحذف بنجاح'),
        ]);
    }
}
