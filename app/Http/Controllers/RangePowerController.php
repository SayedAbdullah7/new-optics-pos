<?php

namespace App\Http\Controllers;

use App\Models\RangePower;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RangePowerController extends Controller
{
    /**
     * Show the form for creating a new range power (modal - name only).
     */
    public function create(): View
    {
        return view('pages.range-power.form');
    }

    /**
     * Store a newly created range power (name only). Values can be set later via the grid form.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $range = RangePower::create([
            'name' => $request->input('name'),
            'max_sph' => 0,
            'min_sph' => 0,
            'max_cyl' => 0,
            'min_cyl' => 0,
            'max_total' => 0,
            'min_total' => 0,
        ]);

        return response()->json([
            'status' => true,
            'msg' => __('تم الحفظ بنجاح'),
            'data' => $range,
        ]);
    }

    /**
     * Show the form for editing the range power name (modal).
     */
    public function edit(RangePower $range_power): View
    {
        return view('pages.range-power.form', ['model' => $range_power]);
    }

    /**
     * Update the range power name only.
     */
    public function update(Request $request, RangePower $range_power): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $range_power->update([
            'name' => $request->input('name'),
        ]);

        return response()->json([
            'status' => true,
            'msg' => __('تم التحديث بنجاح'),
            'data' => $range_power,
        ]);
    }
}
