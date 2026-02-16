<?php

namespace App\DataTables;

use App\Helpers\Column;
use App\Helpers\Filter;
use App\Models\Client;
use App\Models\Invoice;
use Yajra\DataTables\Facades\DataTables;

class InvoiceDataTable extends BaseDataTable
{
    /**
     * Define searchable relations for the query.
     * These columns will be searched in related models.
     */
    protected array $searchableRelations = [
        'client' => ['name'],
    ];

    /**
     * Get the columns for the DataTable.
     */
    public function columns(): array
    {
        return [
            Column::create('id')->setOrderable(true),
            Column::create('invoice_number')->setTitle('Invoice #'),
            Column::create('client_name')->setTitle('Client')->setName('client.name')->setSearchable(false),
            Column::create('amount')->setTitle('Amount'),
            Column::create('paid')->setTitle('Paid')->setSearchable(false)->setOrderable(false),
            Column::create('remaining')->setTitle('Remaining')->setSearchable(false)->setOrderable(false),
            Column::create('status')->setTitle('Status'),
            Column::create('invoiced_at')->setTitle('Date'),
            Column::create('action')->setTitle('Actions')->setSearchable(false)->setOrderable(false),
        ];
    }

    /**
     * Get the filters for the DataTable.
     */
    public function filters(): array
    {
        $clients = Client::pluck('name', 'id')->toArray();

        return [
            'client_id' => Filter::select('Client', $clients),
            'status' => Filter::select('Status', [
                'paid' => 'Paid',
                'partial' => 'Partial',
                'unpaid' => 'Unpaid',
            ]),
            'invoiced_at' => Filter::dateRange('Invoice Date'),
        ];
    }

    /**
     * Handle the DataTable data processing.
     */
    public function handle()
    {
        $query = Invoice::query()->with(['client', 'transactions']);

        return DataTables::of($query)
            ->addColumn('action', function ($model) {
                return view('pages.invoice.columns._actions', compact('model'))->render();
            })
            ->addColumn('client_name', function ($model) {
                return $model->client ? $model->client->name : '<span class="text-muted">-</span>';
            })
            ->addColumn('paid', function ($model) {
                return number_format($model->paid, 2);
            })
            ->addColumn('remaining', function ($model) {
                $remaining = $model->remaining;
                $class = $remaining > 0 ? 'text-danger' : 'text-success';
                return '<span class="' . $class . '">' . number_format($remaining, 2) . '</span>';
            })
            ->editColumn('amount', function ($model) {
                return number_format($model->amount, 2);
            })
            ->editColumn('status', function ($model) {
                $statusClass = match ($model->status) {
                    'paid' => 'bg-success',
                    'partial' => 'bg-warning',
                    default => 'bg-danger',
                };
                return '<span class="badge ' . $statusClass . '">' . ucfirst($model->status) . '</span>';
            })
            ->editColumn('invoiced_at', function ($model) {
                return $model->invoiced_at ? $model->invoiced_at->format('Y-m-d') : '-';
            })
            ->filter(function ($query) {
                $this->applySearch($query);
                $this->applyFilters($query); // Auto-apply all filters
            }, true)
            ->rawColumns(['action', 'client_name', 'status', 'remaining'])
            ->make(true);
    }
}





