<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lens_power_preset_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('preset_id')->constrained('lens_power_presets')->cascadeOnDelete();
            $table->decimal('sph', 5, 2);
            $table->decimal('cyl', 5, 2);
            $table->unique(['preset_id', 'sph', 'cyl']);
        });

        $tableName = 'lens_power_presets';
        if (Schema::hasColumn($tableName, 'values')) {
            $presets = DB::table($tableName)->get();
            foreach ($presets as $row) {
                $values = json_decode($row->values, true);
                if (!is_array($values)) {
                    continue;
                }
                $rows = [];
                foreach ($values as $item) {
                    if (isset($item['sph'], $item['cyl'])) {
                        $rows[] = [
                            'preset_id' => $row->id,
                            'sph' => round((float) $item['sph'], 2),
                            'cyl' => round((float) $item['cyl'], 2),
                        ];
                    }
                }
                if (!empty($rows)) {
                    DB::table('lens_power_preset_values')->insert($rows);
                }
            }
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('values');
            });
        }

        Schema::table('lens_power_preset_values', function (Blueprint $table) {
            $table->index(['sph', 'cyl']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lens_power_presets', function (Blueprint $table) {
            $table->json('values')->nullable()->after('name');
        });

        $presets = DB::table('lens_power_presets')->get();
        foreach ($presets as $preset) {
            $values = DB::table('lens_power_preset_values')
                ->where('preset_id', $preset->id)
                ->orderBy('sph')
                ->orderBy('cyl')
                ->get()
                ->map(fn ($v) => ['sph' => (float) $v->sph, 'cyl' => (float) $v->cyl])
                ->values()
                ->all();
            DB::table('lens_power_presets')->where('id', $preset->id)->update(['values' => json_encode($values)]);
        }

        Schema::dropIfExists('lens_power_preset_values');
    }
};
