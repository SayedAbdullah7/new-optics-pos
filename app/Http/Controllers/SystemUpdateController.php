<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class SystemUpdateController extends Controller
{
    /**
     * Run system updates and migrations manually.
     */
    public function update()
    {
        $messages = [];

        // 1. Check and create bill_lenses table
        if (!Schema::hasTable('bill_lenses')) {
            try {
                Schema::create('bill_lenses', function (Blueprint $table) {
                    $table->id();

                    // Use unsignedInteger for compatibility with older Laravel increments() tables
                    // If your tables use bigIncrements(), change this to foreignId() or unsignedBigInteger()
                    $table->unsignedInteger('bill_id');
                    $table->foreign('bill_id')->references('id')->on('bills')->onDelete('cascade');

                    // Assuming lenses is also unsignedInteger, if not, try unsignedBigInteger
                    $table->unsignedInteger('lens_id');
                    $table->foreign('lens_id')->references('id')->on('lenses')->onDelete('cascade');

                    $table->string('name')->nullable();
                    $table->integer('quantity')->default(1);
                    $table->decimal('price', 15, 2)->default(0);
                    $table->decimal('total', 15, 2)->default(0);
                    $table->timestamps();
                });
                $messages[] = 'Created table: bill_lenses';
            } catch (\Exception $e) {
                // Retry with BigInteger if Integer fails (fallback strategy)
                try {
                     Schema::create('bill_lenses', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('bill_id')->constrained()->onDelete('cascade');
                        $table->foreignId('lens_id')->constrained('lenses')->onDelete('cascade');
                        $table->string('name')->nullable();
                        $table->integer('quantity')->default(1);
                        $table->decimal('price', 15, 2)->default(0);
                        $table->decimal('total', 15, 2)->default(0);
                        $table->timestamps();
                    });
                    $messages[] = 'Created table: bill_lenses (using BigInt)';
                } catch (\Exception $e2) {
                     $messages[] = 'Error creating bill_lenses (Int): ' . $e->getMessage();
                     $messages[] = 'Error creating bill_lenses (BigInt): ' . $e2->getMessage();
                }
            }
        } else {
            $messages[] = 'Table bill_lenses already exists.';
        }

        // 2. Sync Bill Stock (Historical Data)
        // We run this to ensure old bills have their stock mutations created if missing
        try {
            Artisan::call('stock:sync-bills');
            $output = Artisan::output();
            $messages[] = 'Stock Sync Output: ' . $output;
        } catch (\Exception $e) {
            $messages[] = 'Error syncing stock: ' . $e->getMessage();
        }

        return response()->json([
            'status' => true,
            'messages' => $messages
        ]);
    }
}
