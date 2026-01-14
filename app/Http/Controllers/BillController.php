<?php

namespace App\Http\Controllers;

use App\DataTables\BillDataTable;
use App\Http\Requests\BillRequest;
use App\Models\Account;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Carbon\Carbon;

class BillController extends Controller
{
    public function index(BillDataTable $dataTable, Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            return $dataTable->handle();
        }

        return view('pages.bill.index', [
            'columns' => $dataTable->columns(),
            'filters' => $dataTable->filters(),
        ]);
    }

    public function create(Request $request): View
    {
        $vendors = Vendor::all();
        $products = Product::with('translations')->get();
        $accounts = Account::with('translations')->active()->orderBy('default', 'DESC')->get();

        $vendor = null;
        if ($request->vendor_id) {
            $vendor = Vendor::find($request->vendor_id);
        }

        return view('pages.bill.form', compact('vendors', 'products', 'accounts', 'vendor'));
    }

    public function store(BillRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['user_id'] = Auth::id();
            $data['bill_number'] = $data['bill_number'] ?? Bill::generateBillNumber();

            // Calculate totals from items
            $productsAmount = 0;
            $items = [];

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

            // Set calculated amount
            $data['amount'] = $productsAmount;

            // Determine status based on payment
            $paidAmount = $request->paid ?? 0;
            if ($paidAmount >= $data['amount'] && $data['amount'] > 0) {
                $data['status'] = 'paid';
            } elseif ($paidAmount > 0) {
                $data['status'] = 'partial';
            } else {
                $data['status'] = 'unpaid';
            }

            // Create bill
            $bill = Bill::create($data);

            // Create bill items
            foreach ($items as $item) {
                $item['bill_id'] = $bill->id;
                BillItem::create($item);
            }

            // Create payment transaction if paid
            if ($paidAmount > 0) {
                Transaction::create([
                    'user_id' => Auth::id(),
                    'type' => 'expense',
                    'amount' => $paidAmount,
                    'paid_at' => $data['billed_at'] ?? now(),
                    'category_id' => 2,
                    'document_id' => $bill->id,
                    'contact_id' => $data['vendor_id'],
                    'account_id' => $request->account_id,
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'msg' => 'Bill created successfully.',
                'data' => $bill
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'msg' => 'Failed to create bill: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Bill $bill): View
    {
        $bill->load(['vendor', 'items.product.translations', 'transactions.account.translations', 'user']);
        return view('pages.bill.show', compact('bill'));
    }

    public function edit(Bill $bill): View
    {
        $vendors = Vendor::all();
        $products = Product::with('translations')->get();
        $accounts = Account::with('translations')->active()->orderBy('default', 'DESC')->get();
        $bill->load(['items']);

        return view('pages.bill.form', [
            'model' => $bill,
            'vendors' => $vendors,
            'products' => $products,
            'accounts' => $accounts,
        ]);
    }

    public function update(BillRequest $request, Bill $bill): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Calculate totals from items
            $productsAmount = 0;
            $items = [];

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

            // Set calculated amount
            $data['amount'] = $productsAmount;

            // Delete old items
            $bill->items()->delete();

            // Create new bill items
            foreach ($items as $item) {
                $item['bill_id'] = $bill->id;
                BillItem::create($item);
            }

            // Update bill
            $bill->update($data);

            DB::commit();

            return response()->json([
                'status' => true,
                'msg' => 'Bill updated successfully.',
                'data' => $bill
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'msg' => 'Failed to update bill: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Bill $bill): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Calculate total transactions amount
            $amount = 0;
            foreach ($bill->transactions as $transaction) {
                $amount += $transaction->amount;
            }

            // Create reverse transaction if there are payments
            if ($amount > 0) {
                Transaction::create([
                    'user_id' => Auth::id(),
                    'type' => 'income',
                    'amount' => $amount * -1,
                    'category_id' => 2,
                    'paid_at' => Carbon::now(),
                    'document_id' => $bill->id,
                    'contact_id' => $bill->vendor_id,
                ]);
            }

            $bill->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'msg' => 'Bill deleted successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'msg' => 'Failed to delete bill: ' . $e->getMessage()
            ], 500);
        }
    }
}
