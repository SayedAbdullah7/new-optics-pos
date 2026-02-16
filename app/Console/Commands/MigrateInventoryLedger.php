<?php

namespace App\Console\Commands;

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\BillLens;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceLens;
use App\Models\InventoryLedger;
use App\Models\Lens;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateInventoryLedger extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:migrate-ledger {--force : Force migration even if ledger already has data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing Bill and Invoice data to inventory_ledger table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Check if ledger already has data
        if (InventoryLedger::count() > 0 && !$this->option('force')) {
            $this->error('Inventory ledger already has data. Use --force to proceed anyway.');
            return 1;
        }

        $this->info('Starting inventory ledger migration...');
        $this->newLine();

        DB::beginTransaction();

        try {
            // Step 1: Migrate purchase transactions from BillItems
            $this->info('Step 1: Migrating purchase transactions from bills...');
            $billItemsCount = $this->migrateBillItems();
            $this->info("  ✓ Migrated {$billItemsCount} product purchases from bills");

            $billLensesCount = $this->migrateBillLenses();
            $this->info("  ✓ Migrated {$billLensesCount} lens purchases from bills");

            // Step 2: Calculate WAC for all products and lenses
            $this->newLine();
            $this->info('Step 2: Calculating Weighted Average Cost (WAC)...');
            $productsUpdated = $this->calculateWACForProducts();
            $this->info("  ✓ Updated WAC for {$productsUpdated} products");

            $lensesUpdated = $this->calculateWACForLenses();
            $this->info("  ✓ Updated WAC for {$lensesUpdated} lenses");

            // Step 3: Migrate sale transactions and backfill cost_price
            $this->newLine();
            $this->info('Step 3: Migrating sale transactions and backfilling cost_price...');
            $invoiceItemsCount = $this->migrateInvoiceItems();
            $this->info("  ✓ Migrated {$invoiceItemsCount} product sales from invoices");

            $invoiceLensesCount = $this->migrateInvoiceLenses();
            $this->info("  ✓ Migrated {$invoiceLensesCount} lens sales from invoices");

            // Step 4: Update cached stock values
            $this->newLine();
            $this->info('Step 4: Updating cached stock values...');
            $this->updateCachedStock();

            // Step 5: Verify data integrity
            $this->newLine();
            $this->info('Step 5: Verifying data integrity...');
            $this->verifyDataIntegrity();

            DB::commit();

            $this->newLine();
            $this->info('✓ Migration completed successfully!');
            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Migration failed: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }

    /**
     * Migrate BillItems to inventory_ledger.
     */
    protected function migrateBillItems(): int
    {
        $count = 0;
        $bills = Bill::with('items')->orderBy('billed_at')->get();

        foreach ($bills as $bill) {
            foreach ($bill->items as $item) {
                $product = Product::find($item->item_id);
                if (!$product) continue;

                // Check if already migrated
                $exists = InventoryLedger::where('stockable_type', Product::class)
                    ->where('stockable_id', $product->id)
                    ->where('reference_type', Bill::class)
                    ->where('reference_id', $bill->id)
                    ->where('type', 'purchase')
                    ->exists();

                if ($exists && !$this->option('force')) {
                    continue;
                }

                InventoryLedger::create([
                    'stockable_type' => Product::class,
                    'stockable_id' => $product->id,
                    'type' => 'purchase',
                    'quantity' => (int) $item->quantity,
                    'unit_cost' => (float) $item->price,
                    'total_cost' => (float) $item->total,
                    'reference_type' => Bill::class,
                    'reference_id' => $bill->id,
                    'user_id' => $bill->user_id,
                    'description' => 'Bill #' . $bill->bill_number,
                    'created_at' => $bill->billed_at ?? $bill->created_at,
                    'updated_at' => $bill->updated_at,
                ]);

                $count++;
            }
        }

        return $count;
    }

    /**
     * Migrate BillLenses to inventory_ledger.
     */
    protected function migrateBillLenses(): int
    {
        $count = 0;
        $bills = Bill::with('lenses')->orderBy('billed_at')->get();

        foreach ($bills as $bill) {
            foreach ($bill->lenses as $lensItem) {
                $lens = Lens::find($lensItem->lens_id);
                if (!$lens) continue;

                // Check if already migrated
                $exists = InventoryLedger::where('stockable_type', Lens::class)
                    ->where('stockable_id', $lens->id)
                    ->where('reference_type', Bill::class)
                    ->where('reference_id', $bill->id)
                    ->where('type', 'purchase')
                    ->exists();

                if ($exists && !$this->option('force')) {
                    continue;
                }

                InventoryLedger::create([
                    'stockable_type' => Lens::class,
                    'stockable_id' => $lens->id,
                    'type' => 'purchase',
                    'quantity' => (int) $lensItem->quantity,
                    'unit_cost' => (float) $lensItem->price,
                    'total_cost' => (float) $lensItem->total,
                    'reference_type' => Bill::class,
                    'reference_id' => $bill->id,
                    'user_id' => $bill->user_id,
                    'description' => 'Bill #' . $bill->bill_number,
                    'created_at' => $bill->billed_at ?? $bill->created_at,
                    'updated_at' => $bill->updated_at,
                ]);

                $count++;
            }
        }

        return $count;
    }

    /**
     * Calculate WAC for all products.
     */
    protected function calculateWACForProducts(): int
    {
        $count = 0;
        $products = Product::all();

        foreach ($products as $product) {
            $wac = $this->calculateWAC($product);
            $product->update(['weighted_cost' => $wac]);
            $count++;
        }

        return $count;
    }

    /**
     * Calculate WAC for all lenses.
     */
    protected function calculateWACForLenses(): int
    {
        $count = 0;
        $lenses = Lens::all();

        foreach ($lenses as $lens) {
            $wac = $this->calculateWAC($lens);
            $lens->update(['weighted_cost' => $wac]);
            $count++;
        }

        return $count;
    }

    /**
     * Calculate WAC for a stockable item.
     */
    protected function calculateWAC($stockable): float
    {
        $transactions = InventoryLedger::where('stockable_type', get_class($stockable))
            ->where('stockable_id', $stockable->id)
            ->whereIn('type', ['purchase', 'purchase_return', 'sale_return'])
            ->orderBy('created_at')
            ->get();

        $totalQuantity = 0;
        $totalValue = 0;

        foreach ($transactions as $transaction) {
            $totalQuantity += $transaction->quantity;
            $totalValue += $transaction->total_cost;
        }

        if ($totalQuantity <= 0) {
            return (float) ($stockable->purchase_price ?? 0);
        }

        return (float) ($totalValue / $totalQuantity);
    }

    /**
     * Migrate InvoiceItems to inventory_ledger and backfill cost_price.
     */
    protected function migrateInvoiceItems(): int
    {
        $count = 0;
        $inventoryService = app(InventoryService::class);

        // Get all invoices ordered by date
        $invoices = Invoice::with('items')
            ->whereNotIn('status', ['canceled', 'cancelled'])
            ->orderBy('invoiced_at')
            ->get();

        foreach ($invoices as $invoice) {
            foreach ($invoice->items as $item) {
                $product = Product::find($item->item_id);
                if (!$product) continue;

                // Get WAC at the time of sale (before this sale)
                $wac = $this->getWACBeforeSale($product, $invoice->invoiced_at ?? $invoice->created_at);

                // Backfill cost_price if not set
                if (!$item->cost_price || $item->cost_price == 0) {
                    $item->update(['cost_price' => $wac]);
                } else {
                    $wac = $item->cost_price; // Use existing cost_price if already set
                }

                // Check if already migrated
                $exists = InventoryLedger::where('stockable_type', Product::class)
                    ->where('stockable_id', $product->id)
                    ->where('reference_type', Invoice::class)
                    ->where('reference_id', $invoice->id)
                    ->where('type', 'sale')
                    ->exists();

                if ($exists && !$this->option('force')) {
                    continue;
                }

                InventoryLedger::create([
                    'stockable_type' => Product::class,
                    'stockable_id' => $product->id,
                    'type' => 'sale',
                    'quantity' => -(int) $item->quantity,
                    'unit_cost' => $wac,
                    'total_cost' => -($wac * (int) $item->quantity),
                    'reference_type' => Invoice::class,
                    'reference_id' => $invoice->id,
                    'user_id' => $item->user_id ?? $invoice->user_id,
                    'description' => 'Invoice #' . $invoice->invoice_number,
                    'created_at' => $invoice->invoiced_at ?? $invoice->created_at,
                    'updated_at' => $invoice->updated_at,
                ]);

                $count++;
            }
        }

        return $count;
    }

    /**
     * Migrate InvoiceLenses to inventory_ledger and backfill cost_price.
     */
    protected function migrateInvoiceLenses(): int
    {
        $count = 0;

        // Get all invoices ordered by date
        $invoices = Invoice::with('lenses')
            ->whereNotIn('status', ['canceled', 'cancelled'])
            ->orderBy('invoiced_at')
            ->get();

        foreach ($invoices as $invoice) {
            foreach ($invoice->lenses as $lensItem) {
                $lens = Lens::find($lensItem->lens_id);
                if (!$lens) continue;

                // Get WAC at the time of sale (before this sale)
                $wac = $this->getWACBeforeSale($lens, $invoice->invoiced_at ?? $invoice->created_at);

                // Backfill cost_price if not set
                if (!$lensItem->cost_price || $lensItem->cost_price == 0) {
                    $lensItem->update(['cost_price' => $wac]);
                } else {
                    $wac = $lensItem->cost_price; // Use existing cost_price if already set
                }

                // Check if already migrated
                $exists = InventoryLedger::where('stockable_type', Lens::class)
                    ->where('stockable_id', $lens->id)
                    ->where('reference_type', Invoice::class)
                    ->where('reference_id', $invoice->id)
                    ->where('type', 'sale')
                    ->exists();

                if ($exists && !$this->option('force')) {
                    continue;
                }

                InventoryLedger::create([
                    'stockable_type' => Lens::class,
                    'stockable_id' => $lens->id,
                    'type' => 'sale',
                    'quantity' => -(int) $lensItem->quantity,
                    'unit_cost' => $wac,
                    'total_cost' => -($wac * (int) $lensItem->quantity),
                    'reference_type' => Invoice::class,
                    'reference_id' => $invoice->id,
                    'user_id' => $lensItem->user_id ?? $invoice->user_id,
                    'description' => 'Invoice #' . $invoice->invoice_number,
                    'created_at' => $invoice->invoiced_at ?? $invoice->created_at,
                    'updated_at' => $invoice->updated_at,
                ]);

                $count++;
            }
        }

        return $count;
    }

    /**
     * Get WAC before a specific sale date.
     */
    protected function getWACBeforeSale($stockable, $saleDate): float
    {
        $transactions = InventoryLedger::where('stockable_type', get_class($stockable))
            ->where('stockable_id', $stockable->id)
            ->whereIn('type', ['purchase', 'purchase_return', 'sale_return'])
            ->where('created_at', '<', $saleDate)
            ->orderBy('created_at')
            ->get();

        $totalQuantity = 0;
        $totalValue = 0;

        foreach ($transactions as $transaction) {
            $totalQuantity += $transaction->quantity;
            $totalValue += $transaction->total_cost;
        }

        if ($totalQuantity <= 0) {
            return (float) ($stockable->purchase_price ?? 0);
        }

        return (float) ($totalValue / $totalQuantity);
    }

    /**
     * Update cached stock values from ledger.
     */
    protected function updateCachedStock(): void
    {
        $inventoryService = app(InventoryService::class);

        // Update products
        $products = Product::all();
        foreach ($products as $product) {
            $inventoryService->recalculateCache($product);
        }

        // Update lenses
        $lenses = Lens::all();
        foreach ($lenses as $lens) {
            $inventoryService->recalculateCache($lens);
        }
    }

    /**
     * Verify data integrity.
     */
    protected function verifyDataIntegrity(): void
    {
        $errors = [];

        // Verify products
        $products = Product::all();
        foreach ($products as $product) {
            $ledgerStock = InventoryLedger::where('stockable_type', Product::class)
                ->where('stockable_id', $product->id)
                ->sum('quantity');

            if ($ledgerStock != $product->stock) {
                $errors[] = "Product #{$product->id} stock mismatch: ledger={$ledgerStock}, cached={$product->stock}";
            }
        }

        // Verify lenses
        $lenses = Lens::all();
        foreach ($lenses as $lens) {
            $ledgerStock = InventoryLedger::where('stockable_type', Lens::class)
                ->where('stockable_id', $lens->id)
                ->sum('quantity');

            // Lenses don't have stock column, so we just check ledger exists
            if ($ledgerStock < 0) {
                $errors[] = "Lens #{$lens->id} has negative stock in ledger: {$ledgerStock}";
            }
        }

        if (empty($errors)) {
            $this->info('  ✓ Data integrity verified - no errors found');
        } else {
            $this->warn('  ⚠ Found ' . count($errors) . ' data integrity issues:');
            foreach ($errors as $error) {
                $this->warn("    - {$error}");
            }
        }
    }
}
