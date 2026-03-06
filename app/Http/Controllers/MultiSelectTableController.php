<?php

namespace App\Http\Controllers;

use App\DataTables\RangePowerDataTable;
use App\Models\RangePower;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MultiSelectTableController extends Controller
{
    /**
     * List all range powers (index page).
     */
    public function presetsIndex(RangePowerDataTable $dataTable, Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            return $dataTable->handle();
        }

        return view('pages.range-power.index', [
            'columns' => $dataTable->columns(),
            'filters' => $dataTable->filters(),
        ]);
    }

    /**
     * Display the lens power range table view (create or edit when range_power is set).
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

        $presets = RangePower::orderBy('name')->get();
        $preset = null;
        if ($request->filled('preset')) {
            $preset = RangePower::with('values')->find($request->input('preset'));
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
     * Save current selection as a new range power.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'values' => 'required|array',
            'values.*.sph' => 'required|numeric',
            'values.*.cyl' => 'required|numeric',
        ]);

        $values = array_map(function ($item) {
            return [
                'sph' => round((float) $item['sph'], 2),
                'cyl' => round((float) $item['cyl'], 2),
            ];
        }, $request->input('values'));

        $minSph = $maxSph = $minCyl = $maxCyl = $minTotal = $maxTotal = 0;
        if (!empty($values)) {
            $sphs = array_column($values, 'sph');
            $cyls = array_column($values, 'cyl');
            $totals = array_map(fn ($v) => $v['sph'] + $v['cyl'], $values);
            $minSph = min($sphs);
            $maxSph = max($sphs);
            $minCyl = min($cyls);
            $maxCyl = max($cyls);
            $minTotal = min($totals);
            $maxTotal = max($totals);
        }

        $range = RangePower::create([
            'name' => $request->input('name'),
            'max_sph' => $maxSph,
            'min_sph' => $minSph,
            'max_cyl' => $maxCyl,
            'min_cyl' => $minCyl,
            'max_total' => $maxTotal,
            'min_total' => $minTotal,
        ]);

        $range->values()->createMany($values);

        return response()->json([
            'status' => true,
            'message' => __('تم الحفظ بنجاح'),
            'data' => ['id' => $range->id, 'name' => $range->name],
        ]);
    }

    /**
     * Update an existing range power with current selection.
     */
    public function update(Request $request, RangePower $range_power): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'values' => 'required|array',
            'values.*.sph' => 'required|numeric',
            'values.*.cyl' => 'required|numeric',
        ]);

        if ($request->has('name')) {
            $range_power->update(['name' => $request->input('name')]);
        }

        $values = array_map(function ($item) {
            return [
                'sph' => round((float) $item['sph'], 2),
                'cyl' => round((float) $item['cyl'], 2),
            ];
        }, $request->input('values'));

        $minSph = $maxSph = $minCyl = $maxCyl = $minTotal = $maxTotal = 0;
        if (!empty($values)) {
            $sphs = array_column($values, 'sph');
            $cyls = array_column($values, 'cyl');
            $totals = array_map(fn ($v) => $v['sph'] + $v['cyl'], $values);
            $minSph = min($sphs);
            $maxSph = max($sphs);
            $minCyl = min($cyls);
            $maxCyl = max($cyls);
            $minTotal = min($totals);
            $maxTotal = max($totals);
        }

        $range_power->update([
            'max_sph' => $maxSph,
            'min_sph' => $minSph,
            'max_cyl' => $maxCyl,
            'min_cyl' => $minCyl,
            'max_total' => $maxTotal,
            'min_total' => $minTotal,
        ]);

        $range_power->values()->delete();
        $range_power->values()->createMany($values);

        return response()->json([
            'status' => true,
            'message' => __('تم التحديث بنجاح'),
            'data' => ['id' => $range_power->id, 'name' => $range_power->name],
        ]);
    }

    /**
     * Search range powers that contain the given SPH + CYL combination.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'sph' => 'required|numeric',
            'cyl' => 'required|numeric',
        ]);

        $sph = round((float) $request->input('sph'), 2);
        $cyl = round((float) $request->input('cyl'), 2);

        $presets = RangePower::query()
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
     * Delete a range power.
     */
    public function destroy(RangePower $range_power): JsonResponse
    {
        $range_power->delete();
        return response()->json([
            'status' => true,
            'message' => __('تم الحذف بنجاح'),
            'msg' => __('تم الحذف بنجاح'),
        ]);
    }
}
