<?php

namespace App\Http\Controllers;

use App\DataTables\ExpenseDataTable;
use App\Http\Requests\ExpenseRequest;
use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(ExpenseDataTable $dataTable, Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            return $dataTable->handle();
        }

        return view('pages.expense.index', [
            'columns' => $dataTable->columns(),
            'filters' => $dataTable->filters(),
        ]);
    }

    public function create(): View
    {
        return view('pages.expense.form');
    }

    public function store(ExpenseRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['user_id'] = Auth::id();

            $expense = Expense::create($data);

            return response()->json([
                'status' => true,
                'msg' => 'Expense created successfully.',
                'data' => $expense
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Failed to create expense: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit(Expense $expense): View
    {
        return view('pages.expense.form', ['model' => $expense]);
    }

    public function update(ExpenseRequest $request, Expense $expense): JsonResponse
    {
        try {
            $expense->update($request->validated());

            return response()->json([
                'status' => true,
                'msg' => 'Expense updated successfully.',
                'data' => $expense
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Failed to update expense: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Expense $expense): JsonResponse
    {
        try {
            $expense->delete();

            return response()->json([
                'status' => true,
                'msg' => 'Expense deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Failed to delete expense: ' . $e->getMessage()
            ], 500);
        }
    }
}





