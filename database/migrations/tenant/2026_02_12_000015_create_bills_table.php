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
        Schema::create('bills', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('bill_number')->unique();
            $table->string('order_number')->nullable();
            $table->string('status')->default('unpaid');
            $table->dateTime('billed_at');
            $table->dateTime('due_at');
            $table->double('amount', 15, 4);
            $table->integer('vendor_id');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['bill_number', 'deleted_at'], 'bills_bill_number_deleted_at_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
