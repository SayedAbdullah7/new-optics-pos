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
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('type');
            $table->dateTime('paid_at');
            $table->double('amount', 15, 4);
            $table->integer('document_id')->nullable();
            $table->integer('contact_id')->nullable();
            $table->integer('account_id')->default(1);
            $table->integer('category_id');
            $table->text('description')->nullable();
            $table->string('reference')->nullable();
            $table->integer('parent_id')->default(0);
            $table->boolean('reconciled')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'type']);
            $table->index('user_id');
            $table->index('document_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
