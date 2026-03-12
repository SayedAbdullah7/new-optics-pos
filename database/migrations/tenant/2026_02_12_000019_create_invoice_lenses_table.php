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
        Schema::create('invoice_lenses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('invoice_id');
            $table->integer('lens_id')->nullable();
            $table->string('name');
            $table->integer('quantity');
            $table->double('price', 15, 4);
            $table->double('total', 15, 4);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_lenses');
    }
};
