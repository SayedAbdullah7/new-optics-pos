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
        Schema::create('papers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('client_id');
            $table->double('R_sph', 5, 2)->nullable();
            $table->double('R_cyl', 5, 2)->nullable();
            $table->integer('R_axis')->nullable();
            $table->double('L_sph', 5, 2)->nullable();
            $table->double('L_cyl', 5, 2)->nullable();
            $table->integer('L_axis')->nullable();
            $table->double('addtion', 5, 2)->nullable();
            $table->integer('ipd')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('client_id')->references('id')->on('clients');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('papers');
    }
};
