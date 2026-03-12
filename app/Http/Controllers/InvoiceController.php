<?php

namespace App\Http\Controllers;

use App\DataTables\InvoiceDataTable;
use App\Http\Requests\InvoiceRequest;
use App\Models\Account;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceLens;
use App\Models\Lens;
use App\Models\LensCategory;
use App\Models\LensType;
use App\Models\Product;
use App\Models\RangePower;
use App\Models\Transaction;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    public function index(InvoiceDataTable $dataTable, Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            return $dataTable->handle();
        }

        return view('pages.invoice.index', [
            'columns' => $dataTable->columns(),
            'filters' => $dataTable->filters(),
        ]);
    }

    public function create(Request $request)
    {
        // Handle AJAX requests for dynamic lens filtering
        if ($request->ajax()) {
            if ($request->has('lens_code')) {
                // Get lens by code and return range, type, category info
                $lens = Lens::with(['type', 'category'])->where('lens_code', $request->lens_code)->first();

                if (!$lens) {
                    return response()->json(['error' => 'Lens not found'], 404);
                }

                $range = $lens->RangePower_id;
                $type = $lens->type;
                $typeOption = ['value' => $type->id, 'text' => $type->name];
                $category = $lens->category;
                $categoryId = $lens->category_id . "&" . $lens->sale_price . "&" . $lens->id;
                $categoryOption = ['value' => $categoryId, 'text' => $category->brand_name ?? $category->name];

                return response()->json([
                    'range' => $range,
                    'type' => $typeOption,
                    'type_id' => $type->id,
                    'category' => $categoryOption,
                    'category_id' => $categoryId,
                ]);
            } elseif ($request->has('type') && $request->has('range')) {
                // Get categories based on type and range
                $skipRangeId = config('optics.skip_range_filter_id');
                $lenses = Lens::with('category')
                    ->where('type_id', $request->type)
                    ->when($skipRangeId === null || (string) $request->range !== (string) $skipRangeId, function ($q) use ($request) {
                        return $q->where('RangePower_id', $request->range);
                    })
                    ->get();

                $array = [];
                foreach ($lenses as $lens) {
                    $category = $lens->category;
                    $array[] = [
                        'value' => $category->id . "&" . $lens->sale_price . "&" . $lens->id,
                        'text' => $category->brand_name ?? $category->name
                    ];
                }

                return response()->json($array);
            } elseif ($request->has('range')) {
                // Get types based on range
                $skipRangeId = config('optics.skip_range_filter_id');
                $lenses = Lens::with('type')
                    ->when($skipRangeId === null || (string) $request->range !== (string) $skipRangeId, function ($q) use ($request) {
                        return $q->where('RangePower_id', $request->range);
                    })
                    ->get();

                $types = $lenses->pluck('type')->unique('id')->values();

                $array = [];
                foreach ($types as $type) {
                    if ($type) {
                        $array[] = ['value' => $type->id, 'text' => $type->name];
                    }
                }

                return response()->json($array);
            } elseif ($request->has('suggest_range') && $request->has('sph') && $request->has('cyl')) {
                // Suggest range powers matching prescription SPH+CYL (for invoice lens selection)
                $sph = round((float) $request->input('sph'), 2);
                $cyl = round((float) $request->input('cyl'), 2);

                $ranges = RangePower::query()
                    ->whereHas('values', function ($q) use ($sph, $cyl) {
                        $q->where('sph', $sph)->where('cyl', $cyl);
                    })
                    ->orderBy('name')
                    ->get(['id', 'name']);

                return response()->json([
                    'status' => true,
                    'data' => $ranges,
                ]);
            } elseif ($request->has('get_brands_by_type') && $request->has('type')) {
                // Get strictly categories (brands) available for a specific Type (ignoring range)
                $skipRangeId = config('optics.skip_range_filter_id');
                $lenses = Lens::with('category')
                    ->where('type_id', $request->type)
                    ->get();

                $categories = $lenses->pluck('category')->unique('id')->values();
                $array = [];
                foreach ($categories as $category) {
                    if ($category) {
                        $array[] = ['value' => $category->id, 'text' => $category->brand_name ?? $category->name];
                    }
                }
                return response()->json($array);
            } elseif ($request->has('get_ranges_by_type_brand') && $request->has('type') && $request->has('category')) {
                // Get ranges and perfectly matched lens_id based on Type and Category (Brand)
                $lenses = Lens::with('rangePower')
                    ->where('type_id', $request->type)
                    ->where('category_id', $request->category)
                    ->get();
                
                $array = [];
                foreach ($lenses as $lens) {
                    $range = $lens->rangePower;
                    if ($range) {
                        $array[] = [
                            'value' => $range->id,
                            'text' => $range->name,
                            'lens_id' => $lens->id,
                            'lens_price' => $lens->sale_price,
                        ];
                    }
                }
                return response()->json($array);
            }
        }

        $clients = Client::pluck('name', 'id');
        $products = Product::with('translations')->get();
        $ranges = RangePower::all();
        $types = LensType::all();
        $categories = LensCategory::all();
        $accounts = Account::with('translations')->active()->orderBy('default', 'DESC')->get();
        $invoiceNumber = Invoice::generateInvoiceNumber();

        // Pre-select client if passed
        $client = null;
        if ($request->client_id) {
            $client = Client::with('papers')->find($request->client_id);
        }

        return view('pages.invoice.form', compact(
            'clients',
            'products',
            'ranges',
            'types',
            'categories',
            'accounts',
            'invoiceNumber',
            'client'
        ));
    }

    /**
     * Parse items from HTTP Request arrays and validate models.
     */
    private function parseAndValidateItems(array $validatorInput, array $priceInput, array $quantityInput, array $lensPriceInput, array $lensQuantityInput, array $lensEyeInput): array
    {
        $productsAmount = 0;
        $lensesAmount = 0;
        $items = [];
        $lenses = [];

        if (!empty($validatorInput['product'])) {
            foreach ($validatorInput['product'] as $i => $productValue) {
                $productParts = explode('&', $productValue);
                $productId = $productParts[0];
                $price = $priceInput[$i] ?? 0;
                $quantity = $quantityInput[$i] ?? 1;
                
                $product = \App\Models\Product::find($productId);
                if ($product) {
                    $total = $price * $quantity;
                    $productsAmount += $total;
                    
                    $items[] = [
                        'item_id' => $productId,
                        'name' => $product->name,
                        'price' => $price,
                        'quantity' => $quantity,
                        'total' => $total,
                        'model' => $product
                    ];
                }
            }
        }

        if (!empty($validatorInput['lens_category'])) {
            foreach ($validatorInput['lens_category'] as $i => $categoryValue) {
                $categoryParts = explode('&', $categoryValue);
                $lensId = $categoryParts[2] ?? null;
                $price = $lensPriceInput[$i] ?? 0;
                $quantity = $lensQuantityInput[$i] ?? 2;
                $eye = $lensEyeInput[$i] ?? null;
                
                if ($lensId) {
                    $lens = \App\Models\Lens::find($lensId);
                    if ($lens) {
                        $total = ($price * $quantity) / 2;
                        $lensesAmount += $total;
                        
                        $lenses[] = [
                            'lens_id' => $lensId,
                            'eye' => in_array($eye, ['right', 'left']) ? $eye : null,
                            'name' => $lens->full_name,
                            'price' => $price,
                            'quantity' => $quantity,
                            'total' => $total,
                            'model' => $lens
                        ];
                    } else {
                        throw new \InvalidArgumentException(__('Invalid lens in row :row. Please refresh and try again.', ['row' => $i + 1]));
                    }
                }
            }
        }

        return [
            'amount' => $productsAmount + $lensesAmount,
            'items' => $items,
            'lenses' => $lenses,
        ];
    }

    /**
     * Save items to the DB and deduct from inventory.
     */
    private function saveInvoiceStock(\App\Models\Invoice $invoice, array $parsedData, string $logPrefix): void
    {
        $inventoryService = app(\App\Services\InventoryService::class);
        $userId = Auth::id();

        foreach ($parsedData['items'] as $itemData) {
            $product = $itemData['model'];
            unset($itemData['model']);
            
            $itemData['invoice_id'] = $invoice->id;
            $itemData['user_id'] = $userId;
            $itemData['cost_price'] = $inventoryService->getWAC($product);
            
            \App\Models\InvoiceItem::create($itemData);
            $inventoryService->handleSale($product, (int) $itemData['quantity'], $invoice, $logPrefix . ' #' . $invoice->invoice_number);
        }

        foreach ($parsedData['lenses'] as $lensData) {
            $lens = $lensData['model'];
            unset($lensData['model']);
            
            $lensData['invoice_id'] = $invoice->id;
            $lensData['user_id'] = $userId;
            $lensData['cost_price'] = $inventoryService->getWAC($lens);
            
            \App\Models\InvoiceLens::create($lensData);
            $inventoryService->handleSale($lens, (int) $lensData['quantity'], $invoice, $logPrefix . ' #' . $invoice->invoice_number);
        }
    }

    /**
     * Return items to inventory.
     */
    private function restoreInvoiceStock(\App\Models\Invoice $invoice, string $logPrefix): void
    {
        $inventoryService = app(\App\Services\InventoryService::class);

        foreach ($invoice->items as $oldItem) {
            $product = \App\Models\Product::find($oldItem->item_id);
            if ($product) {
                $cost = (float) ($oldItem->cost_price ?? $product->weighted_cost ?? $product->purchase_price);
                $inventoryService->handleSaleReturn($product, (int) $oldItem->quantity, $cost, $invoice, $logPrefix . ' #' . $invoice->invoice_number);
            }
        }

        foreach ($invoice->lenses as $oldLens) {
            $lens = \App\Models\Lens::find($oldLens->lens_id);
            if ($lens) {
                $cost = (float) ($oldLens->cost_price ?? $lens->weighted_cost ?? $lens->purchase_price);
                $inventoryService->handleSaleReturn($lens, (int) $oldLens->quantity, $cost, $invoice, $logPrefix . ' #' . $invoice->invoice_number);
            }
        }
    }

    public function store(InvoiceRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['user_id'] = Auth::id();
            $data['invoice_number'] = $data['invoice_number'] ?? Invoice::generateInvoiceNumber();

            $parsedData = $this->parseAndValidateItems(
                $data, 
                $request->price ?? [], 
                $request->quantity ?? [], 
                $request->lens_price ?? [], 
                $request->lens_quantity ?? [],
                $request->lens_eye ?? []
            );
            
            if ($parsedData['amount'] <= 0) {
                throw new \InvalidArgumentException(__('Add at least one product or lens with a valid price.'));
            }

            $data['amount'] = $parsedData['amount'];
            
            $paidAmount = (float) ($request->paid ?? 0);
            if ($paidAmount >= $data['amount'] && $data['amount'] > 0) {
                $data['status'] = 'paid';
            } elseif ($paidAmount > 0) {
                $data['status'] = 'partial';
            } else {
                $data['status'] = 'unpaid';
            }

            $invoice = Invoice::create($data);

            $this->saveInvoiceStock($invoice, $parsedData, 'Invoice');

            if ($paidAmount > 0) {
                Transaction::create([
                    'user_id' => Auth::id(),
                    'type' => 'income',
                    'amount' => $paidAmount,
                    'paid_at' => $data['invoiced_at'] ?? now(),
                    'category_id' => 1,
                    'document_id' => $invoice->id,
                    'account_id' => $request->account_id ?? 1,
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'msg' => 'Invoice created successfully.',
                'data' => $invoice
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'msg' => 'Failed to create invoice: ' . $e->getMessage()
            ], $e instanceof \InvalidArgumentException ? 422 : 500);
        }
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load([
            'client.papers',
            'items.product.translations',
            'lenses.lens.category',
            'lenses.lens.type',
            'lenses.lens.rangePower',
            'transactions.account.translations',
            'user',
            'paper'
        ]);
        $accounts = Account::active()->get();

        return view('pages.invoice.show', compact('invoice', 'accounts'));
    }

    public function edit(Invoice $invoice): View
    {
        $invoice->load(['items', 'lenses.lens.rangePower', 'lenses.lens.type', 'lenses.lens.category']);

        $clients = Client::pluck('name', 'id');
        $products = Product::with('translations')->get();
        $ranges = RangePower::all();
        $types = LensType::all();
        $categories = LensCategory::all();
        $accounts = Account::with('translations')->active()->orderBy('default', 'DESC')->get();

        $lensPairs = [];
        $singleLenses = [];
        foreach ($invoice->lenses as $invLens) {
            if ($invLens->quantity >= 2) {
                $lensPairs[] = ['invoice_lens' => $invLens, 'lens' => $invLens->lens];
            } else {
                $singleLenses[] = $invLens;
            }
        }

        return view('pages.invoice.form', [
            'model' => $invoice,
            'clients' => $clients,
            'products' => $products,
            'ranges' => $ranges,
            'types' => $types,
            'categories' => $categories,
            'accounts' => $accounts,
            'lensPairs' => $lensPairs,
            'singleLenses' => $singleLenses,
        ]);
    }

    public function update(InvoiceRequest $request, Invoice $invoice): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            
            $parsedData = $this->parseAndValidateItems(
                $data, 
                $request->price ?? [], 
                $request->quantity ?? [], 
                $request->lens_price ?? [], 
                $request->lens_quantity ?? [],
                $request->lens_eye ?? []
            );
            
            if ($parsedData['amount'] <= 0) {
                throw new \InvalidArgumentException(__('Add at least one product or lens with a valid price.'));
            }

            $data['amount'] = $parsedData['amount'];

            $newPaidAmount = (float) ($request->paid ?? 0);
            $currentPaidAmount = (float) $invoice->paid;
            $paymentDelta = $newPaidAmount - $currentPaidAmount;

            if (abs($paymentDelta) > 0.001) {
                $accountId = $request->account_id ?? ($invoice->transactions()->first()?->account_id ?? 1);
                
                Transaction::create([
                    'user_id' => Auth::id(),
                    'type' => 'income',
                    'amount' => $paymentDelta,
                    'paid_at' => $data['invoiced_at'] ?? now(),
                    'category_id' => 1,
                    'document_id' => $invoice->id,
                    'account_id' => $accountId,
                    'description' => 'Payment adjustment for invoice #' . $invoice->invoice_number
                ]);
            }

            $this->restoreInvoiceStock($invoice, 'Invoice update - restore stock for');

            $invoice->items()->delete();
            $invoice->lenses()->delete();

            $this->saveInvoiceStock($invoice, $parsedData, 'Invoice update');

            $invoice->update($data);
            
            $invoice->refresh(); 
            $invoice->updateStatus();

            DB::commit();

            return response()->json([
                'status' => true,
                'msg' => 'Invoice updated successfully.',
                'data' => $invoice
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'msg' => 'Failed to update invoice: ' . $e->getMessage()
            ], $e instanceof \InvalidArgumentException ? 422 : 500);
        }
    }

    public function destroy(Invoice $invoice): JsonResponse
    {
        try {
            // Don't allow deleting cancelled invoices
            if ($invoice->isCancelled()) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Cannot delete a cancelled invoice.'
                ], 422);
            }

            DB::beginTransaction();

            $inventoryService = app(InventoryService::class);
            $now = Carbon::now();

            // Create cancellation invoice with negative amount
            $cancelInvoice = Invoice::create([
                'client_id' => $invoice->client_id,
                'paper_id' => $invoice->paper_id,
                'invoice_number' => 'INV1-' . (Invoice::withTrashed()->latest('id')->first()->id + 1001),
                'status' => 'cancelled',
                'invoiced_at' => $now,
                'due_at' => $now,
                'amount' => $invoice->amount * -1,
                'user_id' => Auth::id(),
                'invoice_id' => $invoice->id,
                'notes' => 'Cancellation of invoice #' . $invoice->invoice_number,
            ]);

            // Create negative transaction
            $paidAmount = $invoice->paid;
            if ($paidAmount > 0) {
                Transaction::create([
                    'user_id' => Auth::id(),
                    'type' => 'income',
                    'amount' => $paidAmount * -1,
                    'category_id' => 1,
                    'paid_at' => $now,
                    'document_id' => $cancelInvoice->id,
                    'account_id' => $invoice->transactions()->first()?->account_id ?? 1,
                ]);
            }

            // Restore stock for items (sale return using original cost_price)
            foreach ($invoice->items as $item) {
                $product = Product::find($item->item_id);
                if ($product) {
                    $inventoryService->handleSaleReturn(
                        $product,
                        (int) $item->quantity,
                        (float) ($item->cost_price ?? $product->weighted_cost ?? $product->purchase_price),
                        $invoice,
                        'Invoice cancelled #' . $invoice->invoice_number
                    );
                }
            }

            // Restore stock for lenses (sale return using original cost_price)
            foreach ($invoice->lenses as $lens) {
                $lensModel = Lens::find($lens->lens_id);
                if ($lensModel) {
                    $inventoryService->handleSaleReturn(
                        $lensModel,
                        (int) $lens->quantity,
                        (float) ($lens->cost_price ?? $lensModel->weighted_cost ?? $lensModel->purchase_price),
                        $invoice,
                        'Invoice cancelled #' . $invoice->invoice_number
                    );
                }
            }

            // Mark original invoice as cancelled
            $invoice->status = 'canceled';
            $invoice->invoice_id = $cancelInvoice->id;
            $invoice->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'msg' => 'Invoice cancelled successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'msg' => 'Failed to cancel invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show payment form.
     */
    public function paymentForm(Invoice $invoice): View
    {
        $accounts = Account::with('translations')->active()->orderBy('default', 'DESC')->get();
        return view('pages.invoice.payment-form', compact('invoice', 'accounts'));
    }

    /**
     * Add payment to invoice.
     */
    public function addPayment(Request $request, Invoice $invoice): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'account_id' => 'required|exists:accounts,id',
            'paid_at' => 'nullable|date',
        ]);

        try {
            DB::beginTransaction();

            $amount = $request->amount;
            $remaining = $invoice->remaining;

            if ($amount > $remaining) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Payment amount exceeds remaining balance.'
                ], 422);
            }

            Transaction::create([
                'user_id' => Auth::id(),
                'type' => 'income',
                'amount' => $amount,
                'paid_at' => $request->paid_at ?? now(),
                'category_id' => 1,
                'document_id' => $invoice->id,
                'account_id' => $request->account_id,
            ]);

            // Update invoice status
            $invoice->refresh();
            $invoice->updateStatus();

            activity()
                ->performedOn($invoice)
                ->withProperties([
                    'amount' => $amount,
                    'account_id' => $request->account_id,
                    'paid_at' => $request->paid_at ?? now()->toDateTimeString(),
                ])
                ->log('Payment added');

            DB::commit();

            return response()->json([
                'status' => true,
                'msg' => 'Payment added successfully.',
                'reload' => true
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'msg' => 'Failed to add payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Print invoice.
     */
    public function print(Invoice $invoice): View
    {
        $invoice->load(['client', 'items.product', 'lenses.lens', 'transactions', 'user', 'paper']);

        return view('pages.invoice.print', compact('invoice'));
    }
}
