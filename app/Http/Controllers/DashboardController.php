<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
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
            'low_stock_products' => Product::where('stock', '<=', 10)->count(),
        ];

        // Financial stats - using aggregated queries
        $totalSalesRevenue = Invoice::whereNotIn('status', ['canceled', 'cancelled'])->sum('amount');

        // Real COGS from invoice_items cost_price
        $actualCOGS = InvoiceItem::whereHas('invoice', function($q) {
                $q->whereNotIn('status', ['canceled', 'cancelled']);
            })
            ->selectRaw('SUM(cost_price * quantity) as total_cogs')
            ->value('total_cogs') ?? 0;

        $realizedProfit = $totalSalesRevenue - $actualCOGS;
        $totalExpenses = Expense::sum('amount');
        $netProfit = $realizedProfit - $totalExpenses;
        $profitMargin = $totalSalesRevenue > 0 ? ($realizedProfit / $totalSalesRevenue) * 100 : 0;

        $financials = [
            'total_sales' => $totalSalesRevenue,
            'total_purchases' => Bill::sum('amount'),
            'total_expenses' => $totalExpenses,
            'actual_cogs' => $actualCOGS,
            'realized_profit' => $realizedProfit,
            'net_profit' => $netProfit,
            'profit_margin' => $profitMargin,
            'today_sales' => Invoice::whereDate('invoiced_at', $today)
                ->whereNotIn('status', ['canceled', 'cancelled'])
                ->sum('amount'),
            'month_sales' => Invoice::where('invoiced_at', '>=', $thisMonth)
                ->whereNotIn('status', ['canceled', 'cancelled'])
                ->sum('amount'),
            'pending_invoices' => Invoice::where('status', '!=', 'paid')
                ->whereNotIn('status', ['canceled', 'cancelled'])
                ->sum('amount'),
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

        // Low stock products - using direct query on cached stock
        $lowStockProducts = Product::with('category')
            ->where('stock', '<=', 10)
            ->orderBy('stock', 'asc')
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





