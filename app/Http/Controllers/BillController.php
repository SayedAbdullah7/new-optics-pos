<?php

namespace App\Http\Controllers;

use App\DataTables\BillDataTable;
use App\Http\Requests\BillRequest;
use App\Models\Account;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\BillLens;
use App\Models\Lens;
use App\Models\LensCategory;
use App\Models\LensType;
use App\Models\Product;
use App\Models\RangePower;
use App\Models\Transaction;
use App\Models\Vendor;
use App\Services\InventoryService;
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

    public function create(Request $request)
    {
        // Handle AJAX requests for dynamic lens filtering (copied from InvoiceController)
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
                $categoryId = $lens->category_id . "&" . $lens->purchase_price . "&" . $lens->id;
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
                        'value' => $category->id . "&" . $lens->purchase_price . "&" . $lens->id,
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

        $vendors = Vendor::all();
        $products = Product::with('translations')->get();
        $accounts = Account::with('translations')->active()->orderBy('default', 'DESC')->get();
        $ranges = RangePower::all();
        $types = LensType::all();
        $categories = LensCategory::all();

        $vendor = null;
        if ($request->vendor_id) {
            $vendor = Vendor::find($request->vendor_id);
        }

        return view('pages.bill.form', compact('vendors', 'products', 'accounts', 'vendor', 'ranges', 'types', 'categories'));
    }

    public function store(BillRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $inventoryService = app(InventoryService::class);
            $data = $request->validated();
            $data['user_id'] = Auth::id();
            $data['bill_number'] = $data['bill_number'] ?? Bill::generateBillNumber();

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
                    $quantity = $request->lens_quantity[$i] ?? 1;
                    $total = $price * $quantity;
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

            // Create bill
            $bill = Bill::create($data);

            // Create bill items
            foreach ($items as $item) {
                $item['bill_id'] = $bill->id;
                BillItem::create($item);

                // Handle inventory purchase
                $product = Product::find($item['item_id']);
                if ($product) {
                    $inventoryService->handlePurchase(
                        $product,
                        (int) $item['quantity'],
                        (float) $item['price'],
                        $bill,
                        'Bill #' . $bill->bill_number
                    );
                }
            }

            // Create bill lenses
            foreach ($lenses as $lens) {
                $lens['bill_id'] = $bill->id;
                BillLens::create($lens);

                // Handle inventory purchase for lens
                $lensModel = Lens::find($lens['lens_id']);
                if ($lensModel) {
                    $inventoryService->handlePurchase(
                        $lensModel,
                        (int) $lens['quantity'],
                        (float) $lens['price'],
                        $bill,
                        'Bill #' . $bill->bill_number
                    );
                }
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
        $bill->load(['vendor', 'items.product.translations', 'lenses.lens.type', 'lenses.lens.category', 'lenses.lens.rangePower', 'transactions.account.translations', 'user']);
        return view('pages.bill.show', compact('bill'));
    }

    public function edit(Bill $bill): View
    {
        $vendors = Vendor::all();
        $products = Product::with('translations')->get();
        $accounts = Account::with('translations')->active()->orderBy('default', 'DESC')->get();
        $ranges = RangePower::all();
        $types = LensType::all();
        $categories = LensCategory::all();

        $bill->load(['items', 'lenses.lens']);

        return view('pages.bill.form', [
            'model' => $bill,
            'vendors' => $vendors,
            'products' => $products,
            'accounts' => $accounts,
            'ranges' => $ranges,
            'types' => $types,
            'categories' => $categories,
        ]);
    }

    public function update(BillRequest $request, Bill $bill): JsonResponse
    {
        try {
            DB::beginTransaction();

            $inventoryService = app(InventoryService::class);
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
                    $quantity = $request->lens_quantity[$i] ?? 1;
                    $total = $price * $quantity;
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

            // Restore old stock for products (purchase return)
            foreach ($bill->items as $oldItem) {
                $product = Product::find($oldItem->item_id);
                if ($product) {
                    $inventoryService->handlePurchaseReturn(
                        $product,
                        (int) $oldItem->quantity,
                        (float) $oldItem->price,
                        $bill,
                        'Bill update - restore stock for #' . $bill->bill_number
                    );
                }
            }

            // Restore old stock for lenses (purchase return)
            foreach ($bill->lenses as $oldLens) {
                $lens = Lens::find($oldLens->lens_id);
                if ($lens) {
                    $inventoryService->handlePurchaseReturn(
                        $lens,
                        (int) $oldLens->quantity,
                        (float) $oldLens->price,
                        $bill,
                        'Bill update - restore stock for #' . $bill->bill_number
                    );
                }
            }

            // Delete old items and lenses
            $bill->items()->delete();
            $bill->lenses()->delete();

            // Create new bill items
            foreach ($items as $item) {
                $item['bill_id'] = $bill->id;
                BillItem::create($item);

                // Handle inventory purchase
                $product = Product::find($item['item_id']);
                if ($product) {
                    $inventoryService->handlePurchase(
                        $product,
                        (int) $item['quantity'],
                        (float) $item['price'],
                        $bill,
                        'Bill update #' . $bill->bill_number
                    );
                }
            }

            // Create new bill lenses
            foreach ($lenses as $lens) {
                $lens['bill_id'] = $bill->id;
                BillLens::create($lens);

                // Handle inventory purchase for lens
                $lensModel = Lens::find($lens['lens_id']);
                if ($lensModel) {
                    $inventoryService->handlePurchase(
                        $lensModel,
                        (int) $lens['quantity'],
                        (float) $lens['price'],
                        $bill,
                        'Bill update #' . $bill->bill_number
                    );
                }
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

            $inventoryService = app(InventoryService::class);

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

            // Handle purchase return for items
            foreach ($bill->items as $item) {
                $product = Product::find($item->item_id);
                if ($product) {
                    $inventoryService->handlePurchaseReturn(
                        $product,
                        (int) $item->quantity,
                        (float) $item->price,
                        $bill,
                        'Bill deleted #' . $bill->bill_number
                    );
                }
            }

            // Handle purchase return for lenses
            foreach ($bill->lenses as $lens) {
                $lensModel = Lens::find($lens->lens_id);
                if ($lensModel) {
                    $inventoryService->handlePurchaseReturn(
                        $lensModel,
                        (int) $lens->quantity,
                        (float) $lens->price,
                        $bill,
                        'Bill deleted #' . $bill->bill_number
                    );
                }
            }

            $bill->lenses()->delete();
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
