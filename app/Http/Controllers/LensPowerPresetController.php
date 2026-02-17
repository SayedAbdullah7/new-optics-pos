<?php

namespace App\Http\Controllers;

use App\Models\LensPowerPreset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LensPowerPresetController extends Controller
{
    /**
     * Show the form for creating a new preset (modal - name only).
     */
    public function create(): View
    {
        return view('pages.lens-power-presets.form');
    }

    /**
     * Store a newly created preset (name only). Values can be set later via the grid form.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $preset = LensPowerPreset::create([
            'name' => $request->input('name'),
        ]);

        return response()->json([
            'status' => true,
            'msg' => __('تم الحفظ بنجاح'),
            'data' => $preset,
        ]);
    }

    /**
     * Show the form for editing the preset name (modal).
     */
    public function edit(LensPowerPreset $lens_power_preset): View
    {
        return view('pages.lens-power-presets.form', ['model' => $lens_power_preset]);
    }

    /**
     * Update the preset name only.
     */
    public function update(Request $request, LensPowerPreset $lens_power_preset): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $lens_power_preset->update([
            'name' => $request->input('name'),
        ]);

        return response()->json([
            'status' => true,
            'msg' => __('تم التحديث بنجاح'),
            'data' => $lens_power_preset,
        ]);
    }
}
