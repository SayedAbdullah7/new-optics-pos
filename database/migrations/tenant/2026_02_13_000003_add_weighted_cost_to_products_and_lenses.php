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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('weighted_cost', 15, 4)->default(0)->after('purchase_price');
        });

        Schema::table('lenses', function (Blueprint $table) {
            $table->decimal('weighted_cost', 15, 4)->default(0)->after('purchase_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('weighted_cost');
        });

        Schema::table('lenses', function (Blueprint $table) {
            $table->dropColumn('weighted_cost');
        });
    }
};
