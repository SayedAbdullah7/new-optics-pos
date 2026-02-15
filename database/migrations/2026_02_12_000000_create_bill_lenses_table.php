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
        Schema::create('bill_lenses', function (Blueprint $table) {
            $table->id();

            // Try to match standard integer type (commonly used in older Laravel apps)
            // If this fails during migration, change to foreignId() or unsignedBigInteger()
            $table->unsignedInteger('bill_id');
            $table->foreign('bill_id')->references('id')->on('bills')->onDelete('cascade');

            $table->unsignedInteger('lens_id');
            $table->foreign('lens_id')->references('id')->on('lenses')->onDelete('cascade');

            $table->string('name')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_lenses');
    }
};
