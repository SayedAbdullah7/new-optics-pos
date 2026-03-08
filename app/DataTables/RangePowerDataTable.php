<?php

namespace App\DataTables;

use App\Helpers\Column;
use App\Models\RangePower;
use Yajra\DataTables\Facades\DataTables;

class RangePowerDataTable extends BaseDataTable
{
    protected array $searchableRelations = [];

    public function columns(): array
    {
        return [
            Column::create('id')->setOrderable(true),
            Column::create('name')->setTitle('الاسم')->setSearchable(true),
            Column::create('values_count')->setTitle('عدد القيم')->setSearchable(false)->setOrderable(false),
            Column::create('sph_range')->setTitle('نطاق SPH')->setSearchable(false)->setOrderable(false),
            Column::create('cyl_range')->setTitle('نطاق CYL')->setSearchable(false)->setOrderable(false),
            Column::create('total_range')->setTitle('نطاق الإجمالي')->setSearchable(false)->setOrderable(false),
            Column::create('created_at')->setTitle('تاريخ الإنشاء'),
            Column::create('updated_at')->setTitle('تاريخ التحديث'),
            Column::create('action')->setTitle('عمليات')->setSearchable(false)->setOrderable(false),
        ];
    }

    public function filters(): array
    {
        return [
            'created_at' => \App\Helpers\Filter::dateRange('تاريخ الإنشاء'),
            'updated_at' => \App\Helpers\Filter::dateRange('تاريخ التحديث'),
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

    protected function formatRange(float $min, float $max): string
    {
        $fmt = fn ($v) => ($v >= 0 ? '+' : '') . number_format($v, 2);
        return $fmt($min) . ' → ' . $fmt($max);
    }

    public function handle()
    {
        $query = RangePower::query()->withCount('values');

        return DataTables::of($query)
            ->addColumn('action', function ($model) {
                return view('pages.range-power.columns._actions', compact('model'))->render();
            })
            ->addColumn('values_count', function ($model) {
                $count = $model->values_count ?? 0;
                return '<span class="badge badge-light-primary">' . $count . '</span>';
            })
            ->addColumn('sph_range', function ($model) {
                return $this->formatRange((float) $model->min_sph, (float) $model->max_sph);
            })
            ->addColumn('cyl_range', function ($model) {
                return $this->formatRange((float) $model->min_cyl, (float) $model->max_cyl);
            })
            ->addColumn('total_range', function ($model) {
                return $this->formatRange((float) $model->min_total, (float) $model->max_total);
            })
            ->editColumn('created_at', function ($model) {
                return $model->created_at ? $model->created_at->format('Y-m-d H:i') : '-';
            })
            ->editColumn('updated_at', function ($model) {
                return $model->updated_at ? $model->updated_at->format('Y-m-d H:i') : '-';
            })
            ->filter(function ($query) {
                $this->applySearch($query);
                $this->applyFilters($query);
            }, true)
            ->rawColumns(['action', 'values_count'])
            ->make(true);
    }
}
