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
        Schema::create('account_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('account_id');
            $table->string('locale')->index();
            $table->string('name');
            $table->timestamps();

            $table->unique(['account_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_translations');
    }
};
