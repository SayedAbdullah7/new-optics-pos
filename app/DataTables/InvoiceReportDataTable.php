<?php

namespace App\DataTables;

use App\Helpers\Column;
use App\Helpers\Filter;
use App\Models\Client;
use App\Models\Invoice;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class InvoiceReportDataTable extends BaseDataTable
{
    /**
     * Define searchable relations.
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
            Column::create('client_name')->setTitle('Name')->setName('client.name'),
            Column::create('invoice_number')->setTitle('Number'),
            Column::create('amount')->setTitle('Amount'),
            Column::create('paid')->setTitle('Paid')->setSearchable(false)->setOrderable(false),
            Column::create('remaining')->setTitle('Remaining')->setSearchable(false)->setOrderable(false),
            Column::create('notes')->setTitle('Notes')->setOrderable(false)->setSearchable(false),
        ];
    }

    /**
     * Get the filters for the DataTable.
     */
    public function filters(): array
    {
        $clients = Client::pluck('name', 'id')->toArray();

        return [
            'client_id' => Filter::select('Client', ['' => 'All Clients'] + $clients),
        ];
    }

    /**
     * Handle the DataTable data processing.
     */
    public function handle()
    {
        $from = request()->get('from', Carbon::now()->format('Y-m-d'));
        $to = request()->get('to', Carbon::now()->format('Y-m-d'));

        $query = Invoice::betweenDates($from, $to)
            ->with(['client', 'parentInvoice']);

        return DataTables::of($query)
            ->addColumn('client_name', function ($model) {
                return '<span class="text-gray-800 fw-bold d-block fs-7">' . ($model->client->name ?? '-') . '</span>';
            })
            ->addColumn('paid', function ($model) use ($to) {
                return '<span class="text-gray-700 fw-semibold">' . number_format($model->paidUntil($to), 2) . '</span>';
            })
            ->addColumn('remaining', function ($model) use ($to) {
                return '<span class="text-gray-700 fw-semibold">' . number_format($model->remainingUntil($to), 2) . '</span>';
            })
            ->addColumn('notes', function ($model) {
                if ($model->invoice_id) {
                    $link = route('admin.invoices.show', $model->invoice_id);
                    if ($model->amount > 0) {
                        return '<span class="text-gray-600 fs-7">Canceled by <a href="' . $link . '" class="text-primary">' . ($model->parentInvoice->invoice_number ?? '-') . '</a></span>';
                    } else {
                        return '<span class="text-gray-600 fs-7">Cancel for <a href="' . $link . '" class="text-primary">' . ($model->parentInvoice->invoice_number ?? '-') . '</a></span>';
                    }
                }
                return '<span class="text-muted">-</span>';
            })
            ->editColumn('invoice_number', function ($model) {
                return '<a href="' . route('admin.invoices.show', $model->id) . '" class="text-primary fw-semibold text-hover-primary">' . $model->invoice_number . '</a>';
            })
            ->editColumn('amount', function ($model) {
                return '<span class="text-success fw-bold fs-6">' . number_format($model->amount, 2) . '</span>';
            })
            ->filter(function ($query) {
                $this->applySearch($query);
                $this->applyFilters($query);
            }, true)
            ->filter(function ($query) {
                $this->applySearch($query);
                $this->applyFilters($query);
            }, true)
            ->rawColumns(['client_name', 'invoice_number', 'amount', 'paid', 'remaining', 'notes'])
            ->make(true);
    }

    protected function applySearch($query): void
    {
        $search = request()->input('search.value');
        if (!$search || strlen(trim($search)) < 1) {
            return;
        }

        $searchTerm = '%' . trim($search) . '%';

        $query->where(function ($q) use ($searchTerm) {
            $q->where('invoice_number', 'like', $searchTerm)
              ->orWhere('amount', 'like', $searchTerm)
              ->orWhereHas('client', function ($clientQuery) use ($searchTerm) {
                  $clientQuery->where('name', 'like', $searchTerm);
              });
        });
    }
}