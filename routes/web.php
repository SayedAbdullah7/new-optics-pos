<?php

use Illuminate\Support\Facades\Route;
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

});

// Include Authentication Routes
require __DIR__.'/auth.php';
