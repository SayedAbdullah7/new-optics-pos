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
        Schema::create('inventory_ledger', function (Blueprint $table) {
            $table->id();
            $table->string('stockable_type');              // Product or Lens
            $table->unsignedBigInteger('stockable_id');
            $table->enum('type', [
                'purchase', 'sale', 'purchase_return', 'sale_return', 'adjustment'
            ]);
            $table->integer('quantity');                    // +/- (positive=in, negative=out)
            $table->decimal('unit_cost', 15, 4);           // cost per unit at this transaction
            $table->decimal('total_cost', 15, 4);          // quantity * unit_cost
            $table->string('reference_type')->nullable();   // Bill, Invoice, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['stockable_type', 'stockable_id']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_ledger');
    }
};
