<?php

namespace App\DataTables;

use App\Helpers\Column;
use App\Models\LensPowerPreset;
use Yajra\DataTables\Facades\DataTables;

class LensPowerPresetDataTable extends BaseDataTable
{
    protected array $searchableRelations = [];

    public function columns(): array
    {
        return [
            Column::create('id')->setOrderable(true),
            Column::create('name')->setTitle('الاسم')->setSearchable(true),
            Column::create('values_count')->setTitle('عدد القيم')->setSearchable(false)->setOrderable(false),
            Column::create('created_at')->setTitle('تاريخ الإنشاء'),
            Column::create('action')->setTitle('عمليات')->setSearchable(false)->setOrderable(false),
        ];
    }

    public function filters(): array
    {
        return [
            'created_at' => \App\Helpers\Filter::dateRange('تاريخ الإنشاء'),
        ];
    }

    protected function applySearch($query): void
    {
        $search = request()->input('search.value');
        if (!$search || strlen(trim($search)) < 2) {
            return;
        }
        $term = '%' . trim($search) . '%';
        $query->orWhere('name', 'like', $term);
    }

    public function handle()
    {
        $query = LensPowerPreset::query()->withCount('values');

        return DataTables::of($query)
            ->addColumn('action', function ($model) {
                return view('pages.lens-power-presets.columns._actions', compact('model'))->render();
            })
            ->addColumn('values_count', function ($model) {
                $count = $model->values_count ?? 0;
                return '<span class="badge badge-light-primary">' . $count . '</span>';
            })
            ->editColumn('created_at', function ($model) {
                return $model->created_at ? $model->created_at->format('Y-m-d H:i') : '-';
            })
            ->filter(function ($query) {
                $this->applySearch($query);
                $this->applyFilters($query);
            }, true)
            ->rawColumns(['action', 'values_count'])
            ->make(true);
    }
}
