<?php

namespace App\DataTables;

use App\Helpers\Column;
use App\Helpers\Filter;
use App\Models\LensType;
use Yajra\DataTables\Facades\DataTables;

class LensTypeDataTable extends BaseDataTable
{
    /**
     * Define searchable relations (relation => columns).
     */
    protected array $searchableRelations = [];

    /**
     * Get the columns for the DataTable.
     */
    public function columns(): array
    {
        return [
            Column::create('id')->setOrderable(true),
            Column::create('name')->setTitle('Type Name'),
            Column::create('lenses_count')->setTitle('Lenses Count')->setSearchable(false)->setOrderable(false),
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
        $query = LensType::query()->withCount('lenses');

        return DataTables::of($query)
            ->addColumn('action', function ($model) {
                return view('pages.lens-type.columns._actions', compact('model'))->render();
            })
            ->addColumn('lenses_count', function ($model) {
                $count = $model->lenses_count ?? 0;
                return '<div class="d-flex align-items-center gap-2">
                    <span class="badge badge-circle badge-light-primary fs-5 fw-bold px-3 py-2">
                        <i class="ki-duotone ki-eye fs-6 me-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        ' . $count . '
                    </span>
                </div>';
            })
            ->editColumn('created_at', function ($model) {
                return $model->created_at ? $model->created_at->format('Y-m-d H:i') : '-';
            })
            ->filter(function ($query) {
                $this->applySearch($query);
                $this->applyFilters($query);
            }, true)
            ->rawColumns(['action', 'lenses_count'])
            ->make(true);
    }
}
