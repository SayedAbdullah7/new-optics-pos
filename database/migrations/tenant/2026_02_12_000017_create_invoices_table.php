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
        Schema::create('invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('paper_id')->nullable();
            $table->string('invoice_number');
            $table->string('order_number')->nullable();
            $table->string('status')->default('unpaid');
            $table->dateTime('invoiced_at');
            $table->dateTime('due_at');
            $table->double('amount', 7, 2);
            $table->unsignedInteger('client_id');
            $table->integer('invoice_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['invoice_number', 'deleted_at'], 'invoices_invoice_number_deleted_at_unique');
            $table->foreign('client_id')->references('id')->on('clients');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
