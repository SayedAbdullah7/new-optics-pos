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
        Schema::create('range_power', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->double('max_sph', 4, 2);
            $table->double('min_sph', 4, 2);
            $table->double('max_cyl', 4, 2);
            $table->double('min_cyl', 4, 2);
            $table->double('max_total', 4, 2);
            $table->double('min_total', 4, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('range_power');
    }
};
