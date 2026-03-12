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
        Schema::create('lenses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('lens_code')->nullable();
            $table->unsignedInteger('type_id');
            $table->unsignedInteger('category_id');
            $table->unsignedInteger('RangePower_id');
            $table->double('purchase_price', 8, 2);
            $table->double('sale_price', 8, 2);
            $table->string('description')->nullable();
            $table->timestamps();

            $table->foreign('type_id')->references('id')->on('lens_types');
            $table->foreign('category_id')->references('id')->on('lens_categories');
            $table->foreign('RangePower_id')->references('id')->on('range_power');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lenses');
    }
};
