<?php

namespace App\Services;

use App\Models\InventoryLedger;
use App\Models\Product;
use App\Models\Lens;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Handle a purchase transaction (add stock).
     *
     * @param Product|Lens $stockable
     * @param int $quantity
     * @param float $unitCost
     * @param mixed $reference (Bill, etc.)
     * @param string|null $description
     * @return InventoryLedger
     */
    public function handlePurchase($stockable, int $quantity, float $unitCost, $reference = null, ?string $description = null): InventoryLedger
    {
        $totalCost = $quantity * $unitCost;

        // Create ledger entry
        $ledger = InventoryLedger::create([
            'stockable_type' => get_class($stockable),
            'stockable_id' => $stockable->id,
            'type' => 'purchase',
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'total_cost' => $totalCost,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference ? $reference->id : null,
            'user_id' => Auth::id(),
            'description' => $description ?? ($reference ? 'Purchase from ' . class_basename($reference) : 'Purchase'),
        ]);

        // Update cached stock and weighted cost
        $this->updateCachedStockAndWAC($stockable);

        return $ledger;
    }

    /**
     * Handle a sale transaction (remove stock).
     * Returns the WAC that was used for this sale.
     *
     * @param Product|Lens $stockable
     * @param int $quantity
     * @param mixed $reference (Invoice, etc.)
     * @param string|null $description
     * @return float The WAC used for this sale
     */
    public function handleSale($stockable, int $quantity, $reference = null, ?string $description = null): float
    {
        // Get current WAC
        $wac = $this->getWAC($stockable);

        $totalCost = $quantity * $wac;

        // Create ledger entry (quantity is negative for sales)
        InventoryLedger::create([
            'stockable_type' => get_class($stockable),
            'stockable_id' => $stockable->id,
            'type' => 'sale',
            'quantity' => -$quantity,
            'unit_cost' => $wac,
            'total_cost' => -$totalCost,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference ? $reference->id : null,
            'user_id' => Auth::id(),
            'description' => $description ?? ($reference ? 'Sale from ' . class_basename($reference) : 'Sale'),
        ]);

        // Update cached stock (WAC doesn't change on sale)
        $this->updateCachedStock($stockable);

        return $wac;
    }

    /**
     * Handle a purchase return (reverse purchase transaction).
     *
     * @param Product|Lens $stockable
     * @param int $quantity
     * @param float $unitCost Original unit cost from the purchase
     * @param mixed $reference (Bill, etc.)
     * @param string|null $description
     * @return InventoryLedger
     */
    public function handlePurchaseReturn($stockable, int $quantity, float $unitCost, $reference = null, ?string $description = null): InventoryLedger
    {
        $totalCost = $quantity * $unitCost;

        // Create ledger entry (quantity is negative for returns)
        $ledger = InventoryLedger::create([
            'stockable_type' => get_class($stockable),
            'stockable_id' => $stockable->id,
            'type' => 'purchase_return',
            'quantity' => -$quantity,
            'unit_cost' => $unitCost,
            'total_cost' => -$totalCost,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference ? $reference->id : null,
            'user_id' => Auth::id(),
            'description' => $description ?? ($reference ? 'Purchase return from ' . class_basename($reference) : 'Purchase return'),
        ]);

        // Update cached stock and weighted cost
        $this->updateCachedStockAndWAC($stockable);

        return $ledger;
    }

    /**
     * Handle a sale return (reverse sale transaction).
     *
     * @param Product|Lens $stockable
     * @param int $quantity
     * @param float $unitCost Original cost_price from the invoice item
     * @param mixed $reference (Invoice, etc.)
     * @param string|null $description
     * @return InventoryLedger
     */
    public function handleSaleReturn($stockable, int $quantity, float $unitCost, $reference = null, ?string $description = null): InventoryLedger
    {
        $totalCost = $quantity * $unitCost;

        // Create ledger entry (quantity is positive for returns)
        $ledger = InventoryLedger::create([
            'stockable_type' => get_class($stockable),
            'stockable_id' => $stockable->id,
            'type' => 'sale_return',
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'total_cost' => $totalCost,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference ? $reference->id : null,
            'user_id' => Auth::id(),
            'description' => $description ?? ($reference ? 'Sale return from ' . class_basename($reference) : 'Sale return'),
        ]);

        // Update cached stock and weighted cost
        $this->updateCachedStockAndWAC($stockable);

        return $ledger;
    }

    /**
     * Handle a manual stock adjustment.
     *
     * @param Product|Lens $stockable
     * @param int $quantity Positive to add, negative to remove
     * @param float $unitCost
     * @param string|null $description
     * @return InventoryLedger
     */
    public function handleAdjustment($stockable, int $quantity, float $unitCost, ?string $description = null): InventoryLedger
    {
        $totalCost = $quantity * $unitCost;

        $ledger = InventoryLedger::create([
            'stockable_type' => get_class($stockable),
            'stockable_id' => $stockable->id,
            'type' => 'adjustment',
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'total_cost' => $totalCost,
            'user_id' => Auth::id(),
            'description' => $description ?? 'Manual stock adjustment',
        ]);

        // Update cached stock and weighted cost
        $this->updateCachedStockAndWAC($stockable);

        return $ledger;
    }

    /**
     * Get current stock from ledger (source of truth).
     *
     * @param Product|Lens $stockable
     * @return int
     */
    public function getStock($stockable): int
    {
        return (int) InventoryLedger::where('stockable_type', get_class($stockable))
            ->where('stockable_id', $stockable->id)
            ->sum('quantity');
    }

    /**
     * Get current Weighted Average Cost.
     * Returns cached value if available, otherwise recalculates.
     *
     * @param Product|Lens $stockable
     * @return float
     */
    public function getWAC($stockable): float
    {
        // Use cached value if available and stock matches
        $cachedStock = $this->getStockFromCache($stockable);
        $ledgerStock = $this->getStock($stockable);

        if ($cachedStock == $ledgerStock && isset($stockable->weighted_cost) && $stockable->weighted_cost > 0) {
            return (float) $stockable->weighted_cost;
        }

        // Recalculate from ledger
        return $this->recalculateWAC($stockable);
    }

    /**
     * Recalculate and update cached stock and WAC from ledger.
     *
     * @param Product|Lens $stockable
     * @return array ['stock' => int, 'weighted_cost' => float]
     */
    public function recalculateCache($stockable): array
    {
        $stock = $this->getStock($stockable);
        $wac = $this->recalculateWAC($stockable);

        $stockable->update([
            'stock' => $stock,
            'weighted_cost' => $wac,
        ]);

        return ['stock' => $stock, 'weighted_cost' => $wac];
    }

    /**
     * Recalculate WAC from ledger transactions.
     *
     * @param Product|Lens $stockable
     * @return float
     */
    protected function recalculateWAC($stockable): float
    {
        // Get all purchase and return transactions
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
            // No stock, return purchase_price as fallback
            return (float) ($stockable->purchase_price ?? 0);
        }

        return (float) ($totalValue / $totalQuantity);
    }

    /**
     * Update cached stock from ledger.
     *
     * @param Product|Lens $stockable
     * @return void
     */
    protected function updateCachedStock($stockable): void
    {
        $stock = $this->getStock($stockable);
        $stockable->update(['stock' => $stock]);
    }

    /**
     * Update cached stock and weighted cost from ledger.
     *
     * @param Product|Lens $stockable
     * @return void
     */
    protected function updateCachedStockAndWAC($stockable): void
    {
        $stock = $this->getStock($stockable);
        $wac = $this->recalculateWAC($stockable);

        $stockable->update([
            'stock' => $stock,
            'weighted_cost' => $wac,
        ]);
    }

    /**
     * Get stock from cached column.
     *
     * @param Product|Lens $stockable
     * @return int
     */
    protected function getStockFromCache($stockable): int
    {
        return (int) ($stockable->getAttributes()['stock'] ?? 0);
    }
}
