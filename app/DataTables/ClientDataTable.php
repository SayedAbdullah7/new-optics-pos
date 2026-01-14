<?php

namespace App\DataTables;

use App\Helpers\Column;
use App\Helpers\Filter;
use App\Models\Client;
use Yajra\DataTables\Facades\DataTables;

class ClientDataTable extends BaseDataTable
{
    /**
     * Get the columns for the DataTable.
     */
    public function columns(): array
    {
        return [
            Column::create('id')->setOrderable(true),
            Column::create('name')->setTitle('Client Name'),
            Column::create('phone')->setTitle('Phone')->setSearchable(false),
            Column::create('address')->setTitle('Address'),
            Column::create('invoices_count')->setTitle('Invoices')->setSearchable(false)->setOrderable(false),
            Column::create('created_at')->setTitle('Created')->setVisible(false),
            Column::create('action')->setTitle('Actions')->setSearchable(false)->setOrderable(false),
        ];
    }

    /**
     * Get the filters for the DataTable.
     */
    public function filters(): array
    {
        return [
            'created_at' => Filter::dateRange('Created Date Range'),
        ];
    }

    /**
     * Handle the DataTable data processing.
     */
    public function handle()
    {
        $query = Client::query()->withCount('invoices');

        return DataTables::of($query)
            ->addColumn('action', function ($model) {
                return view('pages.client.columns._actions', compact('model'))->render();
            })
            ->editColumn('phone', function ($model) {
                if (!$model->phone) {
                    return '<span class="text-muted">-</span>';
                }
                $phones = is_array($model->phone) ? $model->phone : [$model->phone];
                return implode('<br>', array_map('e', $phones));
            })
            ->editColumn('invoices_count', function ($model) {
                $count = $model->invoices_count ?? 0;
                return '<span class="badge bg-primary">' . $count . '</span>';
            })
            ->editColumn('created_at', function ($model) {
                return $model->created_at ? $model->created_at->format('Y-m-d H:i') : '-';
            })
            ->filter(function ($query) {
                $this->applySearch($query);
                $this->applyFilters($query);
            }, true)
            ->rawColumns(['action', 'phone', 'invoices_count'])
            ->make(true);
    }
}





