<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds range_power_values table and migrates data from lens_power_presets.
     */
    public function up(): void
    {
        if (Schema::hasTable('range_power_values')) {
            return;
        }

        Schema::create('range_power_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('range_power_id');
            $table->decimal('sph', 5, 2);
            $table->decimal('cyl', 5, 2);
            $table->unique(['range_power_id', 'sph', 'cyl']);
        });

        Schema::table('range_power_values', function (Blueprint $table) {
            $table->foreign('range_power_id')->references('id')->on('range_power')->cascadeOnDelete();
            $table->index(['sph', 'cyl']);
        });

        if (Schema::hasTable('lens_power_presets')) {
            $presets = DB::table('lens_power_presets')->get();
            foreach ($presets as $row) {
                $values = [];
                if (Schema::hasColumn('lens_power_presets', 'values')) {
                    $decoded = json_decode($row->values ?? '[]', true);
                    $values = is_array($decoded) ? $decoded : [];
                } else {
                    $valuesRows = DB::table('lens_power_preset_values')->where('preset_id', $row->id)->get();
                    foreach ($valuesRows as $v) {
                        $values[] = ['sph' => (float) $v->sph, 'cyl' => (float) $v->cyl];
                    }
                }

                $minSph = $minCyl = $minTotal = 999;
                $maxSph = $maxCyl = $maxTotal = -999;
                foreach ($values as $item) {
                    $sph = (float) ($item['sph'] ?? 0);
                    $cyl = (float) ($item['cyl'] ?? 0);
                    $total = $sph + $cyl;
                    $minSph = min($minSph, $sph);
                    $maxSph = max($maxSph, $sph);
                    $minCyl = min($minCyl, $cyl);
                    $maxCyl = max($maxCyl, $cyl);
                    $minTotal = min($minTotal, $total);
                    $maxTotal = max($maxTotal, $total);
                }
                if ($minSph == 999) {
                    $minSph = $maxSph = $minCyl = $maxCyl = $minTotal = $maxTotal = 0;
                }

                $newId = DB::table('range_power')->insertGetId([
                    'name' => $row->name,
                    'max_sph' => $maxSph,
                    'min_sph' => $minSph,
                    'max_cyl' => $maxCyl,
                    'min_cyl' => $minCyl,
                    'max_total' => $maxTotal,
                    'min_total' => $minTotal,
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ]);

                foreach ($values as $item) {
                    DB::table('range_power_values')->insert([
                        'range_power_id' => $newId,
                        'sph' => round((float) ($item['sph'] ?? 0), 2),
                        'cyl' => round((float) ($item['cyl'] ?? 0), 2),
                    ]);
                }
            }

            Schema::dropIfExists('lens_power_preset_values');
            Schema::dropIfExists('lens_power_presets');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('range_power_values');
    }
};
