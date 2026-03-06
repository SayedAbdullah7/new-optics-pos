<?php

namespace App\DataTables;

use App\Helpers\Column;
use App\Helpers\Filter;
use App\Models\User;
use Yajra\DataTables\Facades\DataTables;

class UserDataTable extends BaseDataTable
{
    protected array $searchableRelations = [];

    public function columns(): array
    {
        return [
            Column::create('id')->setOrderable(true),
            Column::create('first_name')->setTitle('First Name'),
            Column::create('last_name')->setTitle('Last Name'),
            Column::create('email')->setTitle('Email'),
            Column::create('roles_list')->setTitle('Roles')->setSearchable(false)->setOrderable(false),
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
        $query = User::query()->with('roles');

        return DataTables::of($query)
            ->addColumn('action', function ($model) {
            return view('pages.users.columns._actions', compact('model'))->render();
        })
            ->addColumn('roles_list', function ($model) {
            $roles = $model->roles->pluck('display_name')->toArray();
            if (empty($roles)) {
                return '<span class="text-muted">-</span>';
            }
            return implode(' ', array_map(function ($role) {
                    return '<span class="badge bg-primary me-1">' . e($role) . '</span>';
                }
                    , $roles));
            })
            ->editColumn('created_at', function ($model) {
            return $model->created_at ? $model->created_at->format('Y-m-d H:i') : '-';
        })
            ->filter(function ($query) {
            $this->applySearch($query);
            $this->applyFilters($query); // Auto-apply all filters
        }, true)
            ->rawColumns(['action', 'roles_list'])
            ->make(true);
    }
}