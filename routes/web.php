<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\LensController;
use App\Http\Controllers\LensTypeController;
use App\Http\Controllers\LensCategoryController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SystemUpdateController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public Routes
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard Route (redirect to admin dashboard)
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Admin Routes
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {

    // Clients Management
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::resource('clients', ClientController::class)->except(['index']);

    // Categories Management
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::resource('categories', CategoryController::class)->except(['index']);

    // Products Management
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::resource('products', ProductController::class)->except(['index']);

    // Stock Management
    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');

    // Vendors Management
    Route::get('/vendors', [VendorController::class, 'index'])->name('vendors.index');
    Route::resource('vendors', VendorController::class)->except(['index']);

    // Invoices (Sales) Management
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
    Route::get('/invoices/{invoice}/payment', [InvoiceController::class, 'paymentForm'])->name('invoices.paymentForm');
    Route::post('/invoices/{invoice}/payment', [InvoiceController::class, 'addPayment'])->name('invoices.addPayment');
    Route::resource('invoices', InvoiceController::class)->except(['index']);

    // Invoices - Transactions
    Route::get('/invoices/{invoice}/transactions/create', [TransactionController::class, 'createFromInvoice'])->name('invoices.transactions.create');
    Route::post('/invoices/{invoice}/transactions', [TransactionController::class, 'storeFromInvoice'])->name('invoices.transactions.store');
    Route::get('/invoices/transactions/{transaction}/edit', [TransactionController::class, 'editInvoiceTransaction'])->name('invoices.transactions.edit');
    Route::put('/invoices/transactions/{transaction}', [TransactionController::class, 'updateInvoiceTransaction'])->name('invoices.transactions.update');
    Route::delete('/invoices/transactions/{transaction}', [TransactionController::class, 'destroyInvoiceTransaction'])->name('invoices.transactions.destroy');

    // Bills (Purchases) Management
    Route::get('/bills', [BillController::class, 'index'])->name('bills.index');
    Route::resource('bills', BillController::class)->except(['index']);

    // Bills - Transactions
    Route::get('/bills/{bill}/transactions/create', [TransactionController::class, 'createFromBill'])->name('bills.transactions.create');
    Route::post('/bills/{bill}/transactions', [TransactionController::class, 'storeFromBill'])->name('bills.transactions.store');
    Route::get('/bills/transactions/{transaction}/edit', [TransactionController::class, 'editBillTransaction'])->name('bills.transactions.edit');
    Route::put('/bills/transactions/{transaction}', [TransactionController::class, 'updateBillTransaction'])->name('bills.transactions.update');
    Route::delete('/bills/transactions/{transaction}', [TransactionController::class, 'destroyBillTransaction'])->name('bills.transactions.destroy');

    // Transactions Management
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::resource('transactions', TransactionController::class)->except(['index']);

    // Expenses Management
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::resource('expenses', ExpenseController::class)->except(['index']);

    // Lenses Management
    Route::get('/lenses', [LensController::class, 'index'])->name('lenses.index');
    Route::resource('lenses', LensController::class)->except(['index']);

    // Lens Types Management
    Route::get('/lens-types', [LensTypeController::class, 'index'])->name('lens-types.index');
    Route::resource('lens-types', LensTypeController::class)->except(['index']);

    // Lens Brands Management
    Route::get('/lens-brands', [LensCategoryController::class, 'index'])->name('lens-brands.index');
    Route::resource('lens-brands', LensCategoryController::class)->except(['index'])->parameters([
        'lens-brands' => 'lens_category'
    ]);

    // System Updates
    Route::get('/system/update', [SystemUpdateController::class, 'update'])->name('system.update');

});

/*
|--------------------------------------------------------------------------
| Data Reset Routes (Use with caution!)
|--------------------------------------------------------------------------
| Access via: /reset/{model} where model is: clients, invoices, products, stock, vendors, bills, all
| الجداول الفرعية تُحذف تلقائياً مع الجداول الرئيسية
*/
Route::get('/reset/{model}', function ($model) {
    // تعريف الجداول الفرعية لكل جدول رئيسي (يتم حذفها تلقائياً)
    $childTables = [
        'clients'  => [
            'invoice_items',      // فرعي لـ invoices
            'invoice_lenses',     // فرعي لـ invoices
            'transactions',       // فرعي لـ invoices (category_id = 1)
            'invoices',           // فرعي لـ clients
            'papers',             // فرعي لـ clients
            'clients'             // الرئيسي
        ],
        'invoices' => [
            'invoice_items',      // فرعي لـ invoices
            'invoice_lenses',     // فرعي لـ invoices
            'transactions',       // فرعي لـ invoices (category_id = 1)
            'invoices'            // الرئيسي
        ],
        'products' => [
            'invoice_items',      // فرعي (item_id → products)
            'bill_items',        // فرعي (item_id → products)
            'stock_mutations',    // فرعي لـ products
            'product_translations', // فرعي لـ products
            'products'            // الرئيسي
        ],
        'stock'    => [
            'stock_mutations'     // فرعي لـ products
        ],
        'vendors'  => [
            'bill_items',        // فرعي لـ bills
            'transactions',       // فرعي لـ bills (category_id = 2)
            'bills',              // فرعي لـ vendors
            'vendors'             // الرئيسي
        ],
        'bills'    => [
            'bill_items',        // فرعي لـ bills
            'transactions',       // فرعي لـ bills (category_id = 2)
            'bills'               // الرئيسي
        ],
        'all'      => [
            'invoice_items',      // فرعي
            'invoice_lenses',     // فرعي
            'bill_items',        // فرعي
            'transactions',       // فرعي
            'expenses',           // فرعي
            'stock_mutations',    // فرعي
            'invoices',           // فرعي
            'bills',              // فرعي
            'papers',             // فرعي
            'clients',            // رئيسي
            'vendors',            // رئيسي
            'product_translations', // فرعي
            'products'            // رئيسي
        ],
    ];

    if (!isset($childTables[$model])) {
        return response()->json(['success' => false, 'message' => 'Invalid model. Use: ' . implode(', ', array_keys($childTables))], 400);
    }

    DB::statement('SET FOREIGN_KEY_CHECKS=0;');

    // حذف الجداول بالترتيب: الفرعية أولاً ثم الرئيسية
    foreach ($childTables[$model] as $table) {
        if (Schema::hasTable($table)) {
            // حذف transactions المرتبطة بناءً على category_id
            if ($table === 'transactions') {
                if ($model === 'clients' || $model === 'invoices') {
                    DB::table('transactions')->where('category_id', 1)->delete();
                } elseif ($model === 'vendors' || $model === 'bills') {
                    DB::table('transactions')->where('category_id', 2)->delete();
                } else {
                    DB::table('transactions')->truncate();
                }
            } else {
                DB::table($table)->truncate();
            }
        }
    }

    // Special: reset stock column to 0 for stock reset
    if ($model === 'stock') {
        DB::table('products')->update(['stock' => 0]);
    }

    DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    return response()->json(['success' => true, 'message' => ucfirst($model) . ' data and related child tables have been reset.']);
})->name('reset');

// Include Authentication Routes
require __DIR__.'/auth.php';
