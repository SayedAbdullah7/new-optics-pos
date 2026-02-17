<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lens_power_presets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('values'); // [{"sph": -1.25, "cyl": -0.5}, ...]
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lens_power_presets');
    }
};
