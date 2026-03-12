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
        Schema::create('lenses_stock', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('lens_id');
            $table->double('sph', 4, 2);
            $table->double('cyl', 4, 2);
            $table->integer('quantity');
            $table->timestamps();

            $table->foreign('lens_id')->references('id')->on('lenses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lenses_stock');
    }
};
