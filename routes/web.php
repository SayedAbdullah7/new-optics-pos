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
use App\Http\Controllers\MultiSelectTableController;
use App\Http\Controllers\RangePowerController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;

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
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index')->middleware('permission:read-clients');
    Route::resource('clients', ClientController::class)->except(['index'])->middleware('permission:create-clients|update-clients|delete-clients');

    // Categories Management
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index')->middleware('permission:read-categories');
    Route::resource('categories', CategoryController::class)->except(['index'])->middleware('permission:create-categories|update-categories|delete-categories');

    // Products Management
    Route::get('/products', [ProductController::class, 'index'])->name('products.index')->middleware('permission:read-products');
    Route::resource('products', ProductController::class)->except(['index'])->middleware('permission:create-products|update-products|delete-products');

    // Stock Management
    Route::get('/stock', [StockController::class, 'index'])->name('stock.index')->middleware('permission:read-stock');

    // Vendors Management
    Route::get('/vendors', [VendorController::class, 'index'])->name('vendors.index')->middleware('permission:read-vendors');
    Route::resource('vendors', VendorController::class)->except(['index'])->middleware('permission:create-vendors|update-vendors|delete-vendors');

    // Invoices (Sales) Management
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index')->middleware('permission:read-invoices');
    Route::get('/invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print')->middleware('permission:read-invoices');
    Route::get('/invoices/{invoice}/payment', [InvoiceController::class, 'paymentForm'])->name('invoices.paymentForm')->middleware('permission:update-invoices');
    Route::post('/invoices/{invoice}/payment', [InvoiceController::class, 'addPayment'])->name('invoices.addPayment')->middleware('permission:update-invoices');
    Route::resource('invoices', InvoiceController::class)->except(['index'])->middleware('permission:create-invoices|update-invoices|delete-invoices');

    // Invoices - Transactions
    Route::get('/invoices/{invoice}/transactions/create', [TransactionController::class, 'createFromInvoice'])->name('invoices.transactions.create')->middleware('permission:create-transactions');
    Route::post('/invoices/{invoice}/transactions', [TransactionController::class, 'storeFromInvoice'])->name('invoices.transactions.store')->middleware('permission:create-transactions');
    Route::get('/invoices/transactions/{transaction}/edit', [TransactionController::class, 'editInvoiceTransaction'])->name('invoices.transactions.edit')->middleware('permission:update-transactions');
    Route::put('/invoices/transactions/{transaction}', [TransactionController::class, 'updateInvoiceTransaction'])->name('invoices.transactions.update')->middleware('permission:update-transactions');
    Route::delete('/invoices/transactions/{transaction}', [TransactionController::class, 'destroyInvoiceTransaction'])->name('invoices.transactions.destroy')->middleware('permission:delete-transactions');

    // Bills (Purchases) Management
    Route::get('/bills', [BillController::class, 'index'])->name('bills.index')->middleware('permission:read-bills');
    Route::resource('bills', BillController::class)->except(['index'])->middleware('permission:create-bills|update-bills|delete-bills');

    // Bills - Transactions
    Route::get('/bills/{bill}/transactions/create', [TransactionController::class, 'createFromBill'])->name('bills.transactions.create')->middleware('permission:create-transactions');
    Route::post('/bills/{bill}/transactions', [TransactionController::class, 'storeFromBill'])->name('bills.transactions.store')->middleware('permission:create-transactions');
    Route::get('/bills/transactions/{transaction}/edit', [TransactionController::class, 'editBillTransaction'])->name('bills.transactions.edit')->middleware('permission:update-transactions');
    Route::put('/bills/transactions/{transaction}', [TransactionController::class, 'updateBillTransaction'])->name('bills.transactions.update')->middleware('permission:update-transactions');
    Route::delete('/bills/transactions/{transaction}', [TransactionController::class, 'destroyBillTransaction'])->name('bills.transactions.destroy')->middleware('permission:delete-transactions');

    // Transactions Management
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index')->middleware('permission:read-transactions');
    Route::resource('transactions', TransactionController::class)->except(['index'])->middleware('permission:create-transactions|update-transactions|delete-transactions');

    // Expenses Management
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index')->middleware('permission:read-expenses');
    Route::resource('expenses', ExpenseController::class)->except(['index'])->middleware('permission:create-expenses|update-expenses|delete-expenses');

    // Lenses Management
    Route::get('/lenses', [LensController::class, 'index'])->name('lenses.index')->middleware('permission:read-lenses');
    Route::resource('lenses', LensController::class)->except(['index'])->middleware('permission:create-lenses|update-lenses|delete-lenses');

    // Lens Types Management
    Route::get('/lens-types', [LensTypeController::class, 'index'])->name('lens-types.index')->middleware('permission:read-lens-types');
    Route::resource('lens-types', LensTypeController::class)->except(['index'])->middleware('permission:create-lens-types|update-lens-types|delete-lens-types');

    // Lens Brands Management
    Route::get('/lens-brands', [LensCategoryController::class, 'index'])->name('lens-brands.index')->middleware('permission:read-lens-brands');
    Route::resource('lens-brands', LensCategoryController::class)->except(['index'])->parameters([
        'lens-brands' => 'lens_category'
    ])->middleware('permission:create-lens-brands|update-lens-brands|delete-lens-brands');

    // System Updates
    Route::get('/system/update', [SystemUpdateController::class, 'update'])->name('system.update')->middleware('permission:update-system');

    // Range Power (list + modal create/edit name)
    Route::get('/range-powers', [MultiSelectTableController::class, 'presetsIndex'])->name('range-powers.index')->middleware('permission:read-range-powers');
    Route::get('/range-powers/create', [RangePowerController::class, 'create'])->name('range-powers.create')->middleware('permission:update-range-powers');
    Route::post('/range-powers', [RangePowerController::class, 'store'])->name('range-powers.store')->middleware('permission:update-range-powers');
    Route::get('/range-powers/{range_power}/edit', [RangePowerController::class, 'edit'])->name('range-powers.edit')->middleware('permission:update-range-powers');
    Route::put('/range-powers/{range_power}', [RangePowerController::class, 'update'])->name('range-powers.update')->middleware('permission:update-range-powers');

    // Multi-Select Table (تحديث القيم)
    Route::get('/multi-select-table', [MultiSelectTableController::class, 'index'])->name('multi-select-table.index')->middleware('permission:read-multi-select-table');
    Route::get('/multi-select-table/search', [MultiSelectTableController::class, 'search'])->name('multi-select-table.search')->middleware('permission:read-multi-select-table');
    Route::post('/multi-select-table/save', [MultiSelectTableController::class, 'store'])->name('multi-select-table.store')->middleware('permission:update-multi-select-table');
    Route::put('/multi-select-table/{range_power}', [MultiSelectTableController::class, 'update'])->name('multi-select-table.update')->middleware('permission:update-multi-select-table');
    Route::delete('/multi-select-table/{range_power}', [MultiSelectTableController::class, 'destroy'])->name('multi-select-table.destroy')->middleware('permission:update-multi-select-table');

    // Administration
    Route::get('/users', [UserController::class, 'index'])->name('users.index')->middleware('permission:read-users');
    Route::resource('users', UserController::class)->except(['index'])->middleware('permission:create-users|update-users|delete-users');

    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index')->middleware('permission:read-roles');
    Route::resource('roles', RoleController::class)->except(['index'])->middleware('permission:create-roles|update-roles|delete-roles');

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
})->middleware(['auth', 'permission:update-system'])->name('reset');

// Include Authentication Routes
require __DIR__.'/auth.php';
