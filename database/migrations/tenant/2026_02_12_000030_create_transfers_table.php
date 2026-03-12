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
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('expense_transaction_id');
            $table->unsignedInteger('income_transaction_id');
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['expense_transaction_id', 'income_transaction_id']);
            $table->foreign('income_transaction_id')->references('id')->on('transactions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
