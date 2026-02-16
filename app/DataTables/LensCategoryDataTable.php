<?php

namespace App\DataTables;

use App\Helpers\Column;
use App\Helpers\Filter;
use App\Models\LensCategory;
use Yajra\DataTables\Facades\DataTables;

class LensCategoryDataTable extends BaseDataTable
{
    /**
     * Define searchable relations for the query.
     * These columns will be searched in related models.
     */
    protected array $searchableRelations = [];

    /**
     * Get the columns for the DataTable.
     */
    public function columns(): array
    {
        return [
            Column::create('id')->setOrderable(true),
            Column::create('brand_name')->setTitle('Brand Name')->setSearchable(false),
            Column::create('country_name')->setTitle('Country')->setSearchable(false),
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
     * Override applySearch to support brand_name and country_name search.
     * This extends the base search functionality.
     */
    protected function applySearch($query): void
    {
        $search = request()->input('search.value');
        if (!$search || strlen(trim($search)) < 2) {
            return;
        }

        $searchTerm = '%' . trim($search) . '%';

        // Search in brand_name and country_name
        $query->orWhere(function ($q) use ($searchTerm) {
            $q->where('brand_name', 'like', $searchTerm)
              ->orWhere('country_name', 'like', $searchTerm);
        });
    }

    /**
     * Handle the DataTable data processing.
     */
    public function handle()
    {
        $query = LensCategory::query()->withCount('lenses');

        return DataTables::of($query)
            ->addColumn('action', function ($model) {
                return view('pages.lens-brand.columns._actions', compact('model'))->render();
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
            ->editColumn('country_name', function ($model) {
                return $model->country_name ? $model->country_name : '<span class="text-muted">-</span>';
            })
            ->editColumn('created_at', function ($model) {
                return $model->created_at ? $model->created_at->format('Y-m-d H:i') : '-';
            })
            ->filter(function ($query) {
                $this->applySearch($query);
                $this->applyFilters($query); // Auto-apply all filters
            }, true)
            ->rawColumns(['action', 'lenses_count', 'country_name'])
            ->make(true);
    }
}
