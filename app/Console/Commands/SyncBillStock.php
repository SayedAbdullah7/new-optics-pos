<?php

namespace App\Console\Commands;

use App\Models\Bill;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncBillStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:sync-bills {--force : Force sync even if mutations exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync product stock mutations with existing bills';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting stock sync for Bills...');

        $bills = Bill::with('items')->get();
        $this->info('Found ' . $bills->count() . ' bills.');

        $count = 0;
        $skipped = 0;

        foreach ($bills as $bill) {
            foreach ($bill->items as $item) {
                $product = Product::find($item->item_id);

                if (!$product) {
                    $this->warn("Product not found for Bill #{$bill->bill_number}, Item ID: {$item->item_id}");
                    continue;
                }

                // Check if mutation exists for this bill
                // We use the polymorphic relationship to find mutations linked to this bill
                $mutationExists = $product->stockMutations()
                    ->where('reference_type', Bill::class)
                    ->where('reference_id', $bill->id)
                    ->exists();

                if ($mutationExists && !$this->option('force')) {
                    $skipped++;
                    continue;
                }

                if ($mutationExists && $this->option('force')) {
                    // Delete existing mutations for this bill to avoid duplicates if forcing
                    $product->stockMutations()
                        ->where('reference_type', Bill::class)
                        ->where('reference_id', $bill->id)
                        ->delete();
                }

                // Create mutation
                try {
                    $product->increaseStock($item->quantity, [
                        'description' => 'Bill Sync #' . $bill->bill_number,
                        'reference' => $bill,
                    ]);
                    $count++;
                    $this->info("Synced Bill #{$bill->bill_number} - Product: {$product->name} (+{$item->quantity})");
                } catch (\Exception $e) {
                    $this->error("Failed to sync Bill #{$bill->bill_number}: " . $e->getMessage());
                }
            }
        }

        $this->info("Sync completed. Created {$count} mutations. Skipped {$skipped} existing.");
    }
}
