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
                $lenses = Lens::with('category')
                    ->where('type_id', $request->type)
                    ->when($request->range != 4, function($q) use ($request) {
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
                $lenses = Lens::with('type')
                    ->when($request->range != 4, function($q) use ($request) {
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

    public function store(InvoiceRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['user_id'] = Auth::id();
            $data['invoice_number'] = $data['invoice_number'] ?? Invoice::generateInvoiceNumber();

            // Calculate totals from items
            $productsAmount = 0;
            $lensesAmount = 0;
            $items = [];
            $lenses = [];

            // Process products
            if ($request->has('product')) {
                foreach ($request->product as $i => $productValue) {
                    if (empty($productValue)) continue;

                    $productParts = explode('&', $productValue);
                    $productId = $productParts[0];
                    $price = $request->price[$i] ?? 0;
                    $quantity = $request->quantity[$i] ?? 1;
                    $total = $price * $quantity;
                    $productsAmount += $total;

                    $product = Product::find($productId);
                    if ($product) {
                        $items[] = [
                            'item_id' => $productId,
                            'name' => $product->name,
                            'price' => $price,
                            'quantity' => $quantity,
                            'total' => $total,
                        ];
                    }
                }
            }

            // Process lenses
            if ($request->has('lens_category')) {
                foreach ($request->lens_category as $i => $categoryValue) {
                    if (empty($categoryValue)) continue;

                    $categoryParts = explode('&', $categoryValue);
                    $lensId = $categoryParts[2] ?? null;
                    $price = $request->lens_price[$i] ?? 0;
                    $quantity = $request->lens_quantity[$i] ?? 2;
                    $total = ($price * $quantity) / 2;
                    $lensesAmount += $total;

                    if ($lensId) {
                        $lens = Lens::with(['rangePower', 'type', 'category'])->find($lensId);
                        $lensName = $lens ? $lens->full_name : 'Lens';

                        $lenses[] = [
                            'lens_id' => $lensId,
                            'name' => $lensName,
                            'price' => $price,
                            'quantity' => $quantity,
                            'total' => $total,
                        ];
                    }
                }
            }

            // Set calculated amount
            $data['amount'] = $productsAmount + $lensesAmount;

            // Determine status based on payment
            $paidAmount = $request->paid ?? 0;
            if ($paidAmount >= $data['amount'] && $data['amount'] > 0) {
                $data['status'] = 'paid';
            } elseif ($paidAmount > 0) {
                $data['status'] = 'partial';
            } else {
                $data['status'] = 'unpaid';
            }

            // Create invoice
            $invoice = Invoice::create($data);

            // Create invoice items
            foreach ($items as $item) {
                $item['invoice_id'] = $invoice->id;
                $item['user_id'] = Auth::id();
                InvoiceItem::create($item);

                // Decrease stock
                $product = Product::find($item['item_id']);
                if ($product && method_exists($product, 'decreaseStock')) {
                    $product->decreaseStock($item['quantity'], [
                        'description' => 'Invoice #' . $invoice->invoice_number,
                        'reference' => $invoice,
                    ]);
                }
            }

            // Create invoice lenses
            foreach ($lenses as $lens) {
                $lens['invoice_id'] = $invoice->id;
                $lens['user_id'] = Auth::id();
                InvoiceLens::create($lens);

                // Decrease stock
                $lensModel = Lens::find($lens['lens_id']);
                if ($lensModel && method_exists($lensModel, 'decreaseStock')) {
                    $lensModel->decreaseStock($lens['quantity'], [
                        'description' => 'Invoice #' . $invoice->invoice_number,
                        'reference' => $invoice,
                    ]);
                }
            }

            // Create payment transaction if paid
            if ($paidAmount > 0) {
                Transaction::create([
                    'user_id' => Auth::id(),
                    'type' => 'income',
                    'amount' => $paidAmount,
                    'paid_at' => $data['invoiced_at'] ?? now(),
                    'category_id' => 1,
                    'document_id' => $invoice->id,
                    'account_id' => $request->account_id,
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
            ], 500);
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
        $invoice->load(['items', 'lenses.lens']);

        $clients = Client::pluck('name', 'id');
        $products = Product::with('translations')->get();
        $ranges = RangePower::all();
        $types = LensType::all();
        $categories = LensCategory::all();
        $accounts = Account::with('translations')->active()->orderBy('default', 'DESC')->get();

        return view('pages.invoice.form', [
            'model' => $invoice,
            'clients' => $clients,
            'products' => $products,
            'ranges' => $ranges,
            'types' => $types,
            'categories' => $categories,
            'accounts' => $accounts,
        ]);
    }

    public function update(InvoiceRequest $request, Invoice $invoice): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Calculate totals from items
            $productsAmount = 0;
            $lensesAmount = 0;
            $items = [];
            $lenses = [];

            // Process products
            if ($request->has('product')) {
                foreach ($request->product as $i => $productValue) {
                    if (empty($productValue)) continue;

                    $productParts = explode('&', $productValue);
                    $productId = $productParts[0];
                    $price = $request->price[$i] ?? 0;
                    $quantity = $request->quantity[$i] ?? 1;
                    $total = $price * $quantity;
                    $productsAmount += $total;

                    $product = Product::find($productId);
                    if ($product) {
                        $items[] = [
                            'item_id' => $productId,
                            'name' => $product->name,
                            'price' => $price,
                            'quantity' => $quantity,
                            'total' => $total,
                        ];
                    }
                }
            }

            // Process lenses
            if ($request->has('lens_category')) {
                foreach ($request->lens_category as $i => $categoryValue) {
                    if (empty($categoryValue)) continue;

                    $categoryParts = explode('&', $categoryValue);
                    $lensId = $categoryParts[2] ?? null;
                    $price = $request->lens_price[$i] ?? 0;
                    $quantity = $request->lens_quantity[$i] ?? 2;
                    $total = ($price * $quantity) / 2;
                    $lensesAmount += $total;

                    if ($lensId) {
                        $lens = Lens::with(['rangePower', 'type', 'category'])->find($lensId);
                        $lensName = $lens ? $lens->full_name : 'Lens';

                        $lenses[] = [
                            'lens_id' => $lensId,
                            'name' => $lensName,
                            'price' => $price,
                            'quantity' => $quantity,
                            'total' => $total,
                        ];
                    }
                }
            }

            // Set calculated amount
            $data['amount'] = $productsAmount + $lensesAmount;

            // Restore old stock before updating items
            foreach ($invoice->items as $oldItem) {
                $product = Product::find($oldItem->item_id);
                if ($product && method_exists($product, 'increaseStock')) {
                    $product->increaseStock($oldItem->quantity, [
                        'description' => 'Invoice update - restore stock for #' . $invoice->invoice_number,
                        'reference' => $invoice,
                    ]);
                }
            }

            // Restore old stock for lenses
            foreach ($invoice->lenses as $oldLens) {
                $lens = Lens::find($oldLens->lens_id);
                if ($lens && method_exists($lens, 'increaseStock')) {
                    $lens->increaseStock($oldLens->quantity, [
                        'description' => 'Invoice update - restore stock for #' . $invoice->invoice_number,
                        'reference' => $invoice,
                    ]);
                }
            }

            // Delete old items and lenses
            $invoice->items()->delete();
            $invoice->lenses()->delete();

            // Create new invoice items
            foreach ($items as $item) {
                $item['invoice_id'] = $invoice->id;
                $item['user_id'] = Auth::id();
                InvoiceItem::create($item);

                // Decrease stock
                $product = Product::find($item['item_id']);
                if ($product && method_exists($product, 'decreaseStock')) {
                    $product->decreaseStock($item['quantity'], [
                        'description' => 'Invoice update #' . $invoice->invoice_number,
                        'reference' => $invoice,
                    ]);
                }
            }

            // Create new invoice lenses
            foreach ($lenses as $lens) {
                $lens['invoice_id'] = $invoice->id;
                $lens['user_id'] = Auth::id();
                InvoiceLens::create($lens);

                // Decrease stock
                $lensModel = Lens::find($lens['lens_id']);
                if ($lensModel && method_exists($lensModel, 'decreaseStock')) {
                    $lensModel->decreaseStock($lens['quantity'], [
                        'description' => 'Invoice update #' . $invoice->invoice_number,
                        'reference' => $invoice,
                    ]);
                }
            }

            // Update invoice
            $invoice->update($data);

            // Update status based on payments
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
            ], 500);
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

            // Restore stock for items
            foreach ($invoice->items as $item) {
                $product = Product::find($item->item_id);
                if ($product && method_exists($product, 'increaseStock')) {
                    $product->increaseStock($item->quantity, [
                        'description' => 'Invoice cancelled #' . $invoice->invoice_number,
                        'reference' => $invoice,
                    ]);
                }
            }

            // Restore stock for lenses
            foreach ($invoice->lenses as $lens) {
                $lensModel = Lens::find($lens->lens_id);
                if ($lensModel && method_exists($lensModel, 'increaseStock')) {
                    $lensModel->increaseStock($lens->quantity, [
                        'description' => 'Invoice cancelled #' . $invoice->invoice_number,
                        'reference' => $invoice,
                    ]);
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
