<?php

namespace App\DataTables;

use App\Helpers\Column;
use App\Helpers\Filter;
use App\Models\Expense;
use Yajra\DataTables\Facades\DataTables;

class ExpenseDataTable extends BaseDataTable
{
    /**
     * Get the columns for the DataTable.
     */
    public function columns(): array
    {
        return [
            Column::create('id')->setOrderable(true),
            Column::create('title')->setTitle('Title'),
            Column::create('category')->setTitle('Category'),
            Column::create('amount')->setTitle('Amount'),
            Column::create('date')->setTitle('Date'),
            Column::create('description')->setTitle('Description'),
            Column::create('action')->setTitle('Actions')->setSearchable(false)->setOrderable(false),
        ];
    }

    /**
     * Get the filters for the DataTable.
     */
    public function filters(): array
    {
        return [
            'category' => Filter::text('Category'),
            'date' => Filter::dateRange('Expense Date'),
            'amount' => Filter::range('Amount Range'),
        ];
    }

    /**
     * Handle the DataTable data processing.
     */
    public function handle()
    {
        $query = Expense::query()->with('user');

        return DataTables::of($query)
            ->addColumn('action', function ($model) {
                return view('pages.expense.columns._actions', compact('model'))->render();
            })
            ->editColumn('amount', function ($model) {
                return '<span class="text-danger fw-semibold">' . number_format($model->amount, 2) . '</span>';
            })
            ->editColumn('date', function ($model) {
                return $model->date ? $model->date->format('Y-m-d') : '-';
            })
            ->editColumn('description', function ($model) {
                return $model->description
                    ? \Illuminate\Support\Str::limit($model->description, 50)
                    : '<span class="text-muted">-</span>';
            })
            ->editColumn('category', function ($model) {
                return $model->category
                    ? '<span class="badge bg-secondary">' . e($model->category) . '</span>'
                    : '-';
            })
            ->filter(function ($query) {
                $this->applySearch($query);
                $this->applyFilters($query);
            }, true)
            ->rawColumns(['action', 'amount', 'description', 'category'])
            ->make(true);
    }
}





