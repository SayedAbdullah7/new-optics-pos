<?php

namespace App\Http\Controllers;

use App\DataTables\BillReportDataTable;
use App\DataTables\InvoiceReportDataTable;
use App\DataTables\TransactionDataTable;
use App\DataTables\TransactionReportDataTable;
use App\Http\Requests\TransactionRequest;
use App\Models\Account;
use App\Models\Bill;
use App\Models\Invoice;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(
        TransactionReportDataTable $transactionsDataTable,
        InvoiceReportDataTable $invoicesDataTable,
        BillReportDataTable $billsDataTable,
        Request $request
    ): JsonResponse|View {
        // Handle AJAX requests for DataTables
        if ($request->ajax()) {
            $tableType = $request->get('table_type');

            if ($tableType === 'transactions') {
                return $transactionsDataTable->handle();
            } elseif ($tableType === 'invoices') {
                return $invoicesDataTable->handle();
            } elseif ($tableType === 'bills') {
                return $billsDataTable->handle();
            }
        }

        // Set today's date by default if there is no specific date in URL
        $from = $request->from ?? Carbon::now()->format('Y-m-d');
        $to = $request->to ?? Carbon::now()->format('Y-m-d');

        /**
         * Get all transactions in specified period
         */
        $transactions = Transaction::betweenDates($from, $to)
            ->with(['invoice.client', 'bill.vendor', 'vendor', 'expense', 'account'])
            ->orderBy('paid_at', 'desc')
            ->get();

        $transactions_income = $transactions->where('type', 'income');
        $transactions_expense = $transactions->where('type', 'expense');

        /**
         * Start of calculation invoices
         */
        $sum_old_invoices = Invoice::beforeDate($from)->sum('amount');

        $old_transactions = Transaction::sales()->beforeDate($from)->get();
        $sum_old_transactions = $old_transactions->sum('amount');
        $sum_old_canceled_transactions = $old_transactions->where('type', 'expense')->sum('amount');
        $sum_old_income = $sum_old_transactions - ($sum_old_canceled_transactions * 2);

        $invoices = Invoice::betweenDates($from, $to)->with(['client', 'parentInvoice'])->get();
        /**
         * End of calculation invoices
         */

        /**
         * Start of calculation Bills
         */
        $sum_old_bills = Bill::beforeDate($from)->sum('amount');

        $old_transactions_bills = Transaction::purchases()->beforeDate($from)->get();
        $sum_old_transactions_bills = $old_transactions_bills->sum('amount');
        $sum_old_canceled_transactions_bills = $old_transactions_bills->where('type', 'income')->sum('amount');
        $sum_old_purchases = $sum_old_transactions_bills - ($sum_old_canceled_transactions_bills * 2);

        $bills = Bill::betweenDates($from, $to)->with('vendor')->get();
        /**
         * End of calculation Bills
         */

        // Total sales transactions in the specified period
        $transactions_sales = $transactions->where('category_id', '1');
        // Sum sales transactions
        $sum_sales = $transactions->where('category_id', '1')->sum('amount');
        // Sum invoices
        $sum_invoices = $invoices->where('amount', '>', 0)->sum('amount');
        $sum_invoices_negative = $invoices->where('amount', '<', 0)->sum('amount');

        // Total expense transactions in the specified period
        $transactions_purchases = $transactions->where('category_id', '2');
        // Sum purchases
        $sum_purchases = $transactions->where('category_id', '2')->sum('amount');
        // Sum bills
        $sum_bills = $bills->sum('amount');
        // Sum overheads
        $transactions_overheads = $transactions->where('category_id', '3');
        $sum_overheads = $transactions->where('category_id', '3')->sum('amount');

        /**
         * Start of calculation accounts
         */
        $accounts = Account::active()->orderBy('default', 'DESC')->get();
        $accounts_amounts = [];
        foreach ($accounts as $account) {
            $id = $account->id;

            // Income
            $acc_income = $transactions_income->where('account_id', $id)->sum('amount');

            // Overheads
            $acc_overheads = $transactions_overheads->where('account_id', $id)->sum('amount');

            $acc_expense = $transactions_expense->where('account_id', $id)->sum('amount');

            $acc = $acc_income - ($acc_overheads + $acc_expense);

            $accounts_amounts[] = $acc;
        }

        $ids_of_invoices = $invoices->pluck('id');

        $payments_new_invoices = $transactions->whereIn('document_id', $ids_of_invoices);
        $sum_payments_new_invoices = $payments_new_invoices->where('amount', '>', 0)->sum('amount');
        $sum_payments_new_invoices_negative = $payments_new_invoices->where('amount', '<', 0)->sum('amount');

        $ids_of_bills = $bills->pluck('id');

        $payments_new_bills = $transactions->whereIn('document_id', $ids_of_bills)->sum('amount');

        return view('pages.transaction.index', [
            'sum_sales' => $sum_sales,
            'sum_overheads' => $sum_overheads,
            'sum_purchases' => $sum_purchases,
            'sum_invoices' => $sum_invoices,
            'sum_invoices_negative' => $sum_invoices_negative,
            'sum_bills' => $sum_bills,
            'sum_old_invoices' => $sum_old_invoices,
            'sum_old_income' => $sum_old_income,
            'sum_payments_new_invoices' => $sum_payments_new_invoices,
            'sum_payments_new_invoices_negative' => $sum_payments_new_invoices_negative,
            'sum_old_bills' => $sum_old_bills,
            'sum_old_purchases' => $sum_old_purchases,
            'payments_new_bills' => $payments_new_bills,
            'accounts' => $accounts,
            'accounts_amounts' => $accounts_amounts,
            'from' => $from,
            'to' => $to,
            'transactionsColumns' => $transactionsDataTable->columns(),
            'transactionsFilters' => $transactionsDataTable->filters(),
            'invoicesColumns' => $invoicesDataTable->columns(),
            'invoicesFilters' => $invoicesDataTable->filters(),
            'billsColumns' => $billsDataTable->columns(),
            'billsFilters' => $billsDataTable->filters(),
        ]);
    }

    public function create(): View
    {
        $accounts = Account::active()->pluck('name', 'id');
        return view('pages.transaction.form', compact('accounts'));
    }

    public function store(TransactionRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['user_id'] = Auth::id();

            $transaction = Transaction::create($data);

            return response()->json([
                'status' => true,
                'msg' => 'Transaction created successfully.',
                'data' => $transaction
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Failed to create transaction: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Transaction $transaction): View
    {
        $transaction->load(['account', 'user', 'invoice', 'bill']);
        return view('pages.transaction.show', compact('transaction'));
    }

    public function edit(Transaction $transaction): View
    {
        $accounts = Account::active()->pluck('name', 'id');
        return view('pages.transaction.form', [
            'model' => $transaction,
            'accounts' => $accounts
        ]);
    }

    public function update(TransactionRequest $request, Transaction $transaction): JsonResponse
    {
        try {
            $transaction->update($request->validated());

            return response()->json([
                'status' => true,
                'msg' => 'Transaction updated successfully.',
                'data' => $transaction
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Failed to update transaction: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Transaction $transaction): JsonResponse
    {
        try {
            $transaction->delete();

            return response()->json([
                'status' => true,
                'msg' => 'Transaction deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Failed to delete transaction: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a transaction from an invoice.
     */
    public function createFromInvoice(Invoice $invoice): View
    {
        $accounts = Account::active()->orderBy('default', 'DESC')->get();
        return view('pages.transaction.invoice-form', compact('accounts', 'invoice'));
    }

    /**
     * Store a transaction from an invoice.
     */
    public function storeFromInvoice(Request $request, Invoice $invoice)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $invoice->remaining,
            'account_id' => 'required|exists:accounts,id',
            'paid_at' => 'required|date',
        ], [
            'amount.max' => 'Payment amount cannot exceed remaining balance.',
            'amount.min' => 'Amount must be greater than 0.',
        ]);

        try {
            $remaining = $invoice->remaining;
            $amount = $request->amount;

            if ($remaining == $amount) {
                $invoice->update(['status' => 'paid']);
            } elseif ($remaining < $amount) {
                return redirect()->back()->withErrors(['amount' => 'Payment amount cannot exceed remaining balance.']);
            }

            Transaction::create([
                'user_id' => Auth::id(),
                'type' => 'income',
                'category_id' => 1,
                'document_id' => $invoice->id,
                'account_id' => $request->account_id,
                'amount' => $amount,
                'paid_at' => $request->paid_at,
                'payment_method' => $request->payment_method,
                'description' => $request->description,
            ]);

            // Update invoice status
            $invoice->refresh();
            $invoice->updateStatus();

            return redirect()->route('admin.transactions.index')->with('success', 'Payment added successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to create transaction: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing an invoice transaction.
     */
    public function editInvoiceTransaction(Transaction $transaction): View
    {
        $accounts = Account::active()->orderBy('default', 'DESC')->get();
        $transaction->load('invoice');
        return view('pages.transaction.invoice-form', compact('accounts', 'transaction'));
    }

    /**
     * Update an invoice transaction.
     */
    public function updateInvoiceTransaction(Request $request, Transaction $transaction)
    {
        $invoice = $transaction->invoice;
        $maxAmount = ($transaction->amount + $invoice->remaining);

        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $maxAmount,
            'paid_at' => 'required|date',
        ], [
            'amount.max' => 'Payment amount cannot exceed maximum allowed.',
            'amount.min' => 'Amount must be greater than 0.',
        ]);

        try {
            $transaction->update([
                'amount' => $request->amount,
                'paid_at' => $request->paid_at,
                'payment_method' => $request->payment_method,
                'description' => $request->description,
            ]);

            // Update invoice status
            $invoice->refresh();
            $invoice->updateStatus();

            return redirect()->route('admin.invoices.show', $invoice->id)->with('success', 'Transaction updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to update transaction: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete an invoice transaction.
     */
    public function destroyInvoiceTransaction(Transaction $transaction)
    {
        try {
            $invoice = $transaction->invoice;
            $transaction->delete();

            // Update invoice status
            $invoice->refresh();
            $invoice->updateStatus();

            return redirect()->route('admin.transactions.index')->with('success', 'Transaction deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to delete transaction: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for creating a transaction from a bill.
     */
    public function createFromBill(Bill $bill): View
    {
        $accounts = Account::active()->orderBy('default', 'DESC')->get();
        $bill->load('vendor');
        return view('pages.transaction.bill-form', compact('accounts', 'bill'));
    }

    /**
     * Store a transaction from a bill.
     */
    public function storeFromBill(Request $request, Bill $bill)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'account_id' => 'required|exists:accounts,id',
            'paid_at' => 'required|date',
        ], [
            'amount.min' => 'Amount must be greater than 0.',
        ]);

        try {
            Transaction::create([
                'user_id' => Auth::id(),
                'type' => 'expense',
                'category_id' => 2,
                'document_id' => $bill->id,
                'contact_id' => $bill->vendor_id,
                'account_id' => $request->account_id,
                'amount' => $request->amount,
                'paid_at' => $request->paid_at,
                'payment_method' => $request->payment_method,
                'description' => $request->description,
            ]);

            return redirect()->route('admin.transactions.index')->with('success', 'Payment added successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to create transaction: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing a bill transaction.
     */
    public function editBillTransaction(Transaction $transaction): View
    {
        $accounts = Account::active()->orderBy('default', 'DESC')->get();
        $transaction->load('bill', 'vendor');
        return view('pages.transaction.bill-form', compact('accounts', 'transaction'));
    }

    /**
     * Update a bill transaction.
     */
    public function updateBillTransaction(Request $request, Transaction $transaction)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'paid_at' => 'required|date',
        ], [
            'amount.min' => 'Amount must be greater than 0.',
        ]);

        try {
            $transaction->update([
                'amount' => $request->amount,
                'paid_at' => $request->paid_at,
                'payment_method' => $request->payment_method,
                'description' => $request->description,
            ]);

            $bill = $transaction->bill;
            if ($bill) {
                return redirect()->route('admin.bills.show', $bill->id)->with('success', 'Transaction updated successfully.');
            }

            return redirect()->route('admin.transactions.index')->with('success', 'Transaction updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to update transaction: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete a bill transaction.
     */
    public function destroyBillTransaction(Transaction $transaction)
    {
        try {
            $bill = $transaction->bill;
            $transaction->delete();

            if ($bill) {
                return redirect()->route('admin.bills.show', $bill->id)->with('success', 'Transaction deleted successfully.');
            }

            return redirect()->route('admin.transactions.index')->with('success', 'Transaction deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to delete transaction: ' . $e->getMessage()]);
        }
    }
}





