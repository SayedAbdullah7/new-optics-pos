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

        // 1. Check and create inventory_ledger table
        if (!Schema::hasTable('inventory_ledger')) {
            try {
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
                $messages[] = 'Created table: inventory_ledger';
            } catch (\Exception $e) {
                $messages[] = 'Error creating inventory_ledger table: ' . $e->getMessage();
            }
        } else {
            $messages[] = 'Table inventory_ledger already exists.';
        }

        // 2. Add cost_price to invoice_items and invoice_lenses
        if (Schema::hasTable('invoice_items') && !Schema::hasColumn('invoice_items', 'cost_price')) {
            try {
                Schema::table('invoice_items', function (Blueprint $table) {
                    $table->decimal('cost_price', 15, 4)->default(0)->after('price');
                });
                $messages[] = 'Added cost_price column to invoice_items';
            } catch (\Exception $e) {
                $messages[] = 'Error adding cost_price to invoice_items: ' . $e->getMessage();
            }
        } else {
            if (Schema::hasColumn('invoice_items', 'cost_price')) {
                $messages[] = 'Column cost_price already exists in invoice_items.';
            } else {
                $messages[] = 'Table invoice_items does not exist, skipping cost_price column.';
            }
        }

        if (Schema::hasTable('invoice_lenses') && !Schema::hasColumn('invoice_lenses', 'cost_price')) {
            try {
                Schema::table('invoice_lenses', function (Blueprint $table) {
                    $table->decimal('cost_price', 15, 4)->default(0)->after('price');
                });
                $messages[] = 'Added cost_price column to invoice_lenses';
            } catch (\Exception $e) {
                $messages[] = 'Error adding cost_price to invoice_lenses: ' . $e->getMessage();
            }
        } else {
            if (Schema::hasColumn('invoice_lenses', 'cost_price')) {
                $messages[] = 'Column cost_price already exists in invoice_lenses.';
            } else {
                $messages[] = 'Table invoice_lenses does not exist, skipping cost_price column.';
            }
        }

        // 3. Add weighted_cost to products and lenses
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'weighted_cost')) {
            try {
                Schema::table('products', function (Blueprint $table) {
                    $table->decimal('weighted_cost', 15, 4)->default(0)->after('purchase_price');
                });
                $messages[] = 'Added weighted_cost column to products';
            } catch (\Exception $e) {
                $messages[] = 'Error adding weighted_cost to products: ' . $e->getMessage();
            }
        } else {
            if (Schema::hasColumn('products', 'weighted_cost')) {
                $messages[] = 'Column weighted_cost already exists in products.';
            } else {
                $messages[] = 'Table products does not exist, skipping weighted_cost column.';
            }
        }

        if (Schema::hasTable('lenses') && !Schema::hasColumn('lenses', 'weighted_cost')) {
            try {
                Schema::table('lenses', function (Blueprint $table) {
                    $table->decimal('weighted_cost', 15, 4)->default(0)->after('purchase_price');
                });
                $messages[] = 'Added weighted_cost column to lenses';
            } catch (\Exception $e) {
                $messages[] = 'Error adding weighted_cost to lenses: ' . $e->getMessage();
            }
        } else {
            if (Schema::hasColumn('lenses', 'weighted_cost')) {
                $messages[] = 'Column weighted_cost already exists in lenses.';
            } else {
                $messages[] = 'Table lenses does not exist, skipping weighted_cost column.';
            }
        }

        // 4. Check and create bill_lenses table
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

        // 5. Sync Bill Stock (Historical Data)
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
