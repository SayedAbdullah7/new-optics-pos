<?php

namespace App\DataTables;

use App\Helpers\Column;
use App\Helpers\Filter;
use App\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class RoleDataTable extends BaseDataTable
{
    protected array $searchableRelations = [];

    public function columns(): array
    {
        return [
            Column::create('id')->setOrderable(true),
            Column::create('name')->setTitle('Slug'),
            Column::create('display_name')->setTitle('Name'),
            Column::create('permissions_list')->setTitle('Permissions')->setSearchable(false)->setOrderable(false),
            Column::create('created_at')->setTitle('Created Date')->setVisible(false),
            Column::create('action')->setTitle('Actions')->setSearchable(false)->setOrderable(false),
        ];
    }

    public function filters(): array
    {
        return [
            'created_at' => Filter::dateRange('Created Date Range'),
        ];
    }

    public function handle()
    {
        $query = Role::query()->withCount('permissions');

        return DataTables::of($query)
            ->addColumn('action', function ($model) {
                return view('pages.roles.columns._actions', compact('model'))->render();
            })
            ->addColumn('permissions_list', function ($model) {
                $count = $model->permissions_count ?? 0;
                return '<span class="badge bg-info">' . $count . '</span>';
            })
            ->editColumn('created_at', function ($model) {
                return $model->created_at ? $model->created_at->format('Y-m-d H:i') : '-';
            })
            ->filter(function ($query) {
                $this->applySearch($query);
                $this->applyFilters($query); // Auto-apply all filters
            }, true)
            ->rawColumns(['action', 'permissions_list'])
            ->make(true);
    }
}
