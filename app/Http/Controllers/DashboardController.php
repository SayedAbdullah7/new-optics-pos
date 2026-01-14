<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        // Statistics
        $stats = [
            'total_clients' => Client::count(),
            'total_products' => Product::count(),
            'total_vendors' => Vendor::count(),
            'low_stock_products' => Product::lowStock()->count(),
        ];

        // Financial stats
        $financials = [
            'total_sales' => Invoice::sum('amount'),
            'total_purchases' => Bill::sum('amount'),
            'total_expenses' => Expense::sum('amount'),
            'today_sales' => Invoice::whereDate('invoiced_at', $today)->sum('amount'),
            'month_sales' => Invoice::where('invoiced_at', '>=', $thisMonth)->sum('amount'),
            'pending_invoices' => Invoice::where('status', '!=', 'paid')->sum('amount'),
            'pending_bills' => Bill::where('status', '!=', 'paid')->sum('amount'),
        ];

        // Recent invoices
        $recentInvoices = Invoice::with('client')
            ->latest()
            ->take(5)
            ->get();

        // Recent transactions
        $recentTransactions = Transaction::with(['account', 'user'])
            ->latest()
            ->take(5)
            ->get();

        // Low stock products
        $lowStockProducts = Product::with('category')
            ->lowStock()
            ->take(5)
            ->get();

        return view('pages.dashboard.index', compact(
            'stats',
            'financials',
            'recentInvoices',
            'recentTransactions',
            'lowStockProducts'
        ));
    }
}





