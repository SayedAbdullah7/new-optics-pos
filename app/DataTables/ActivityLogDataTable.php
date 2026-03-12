<?php

namespace App\DataTables;

use App\Helpers\Column;
use App\Helpers\Filter;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;

class ActivityLogDataTable extends BaseDataTable
{
    protected array $searchableRelations = [];

    /**
     * Human-readable labels for subject types.
     */
    protected static function subjectTypeLabels(): array
    {
        return [
            'Invoice' => 'Invoice',
            'Bill' => 'Bill',
            'Client' => 'Client',
            'Product' => 'Product',
            'Category' => 'Category',
            'Vendor' => 'Vendor',
            'User' => 'User',
            'Expense' => 'Expense',
            'Transaction' => 'Transaction',
        ];
    }

    public static function getSubjectTypeLabel(?string $subjectType): string
    {
        if (!$subjectType) {
            return '-';
        }
        $basename = class_basename($subjectType);
        return self::subjectTypeLabels()[$basename] ?? $basename;
    }

    public function columns(): array
    {
        return [
            Column::create('id')->setTitle('ID')->setOrderable(true),
            Column::create('causer_name')->setTitle('User')->setSearchable(false)->setOrderable(false),
            Column::create('description')->setTitle('Description'),
            Column::create('subject_type_label')->setTitle('Subject Type')->setSearchable(false)->setOrderable(false),
            Column::create('subject_id')->setTitle('Subject ID')->setOrderable(true),
            Column::create('event')->setTitle('Event')->setSearchable(false)->setOrderable(true),
            Column::create('created_at')->setTitle('Date')->setOrderable(true),
            Column::create('details')->setTitle('Details')->setSearchable(false)->setOrderable(false),
        ];
    }

    public function filters(): array
    {
        $users = User::orderBy('name')->get()->pluck('name', 'id')->toArray();
        $subjectTypes = Activity::query()
            ->distinct()
            ->pluck('subject_type')
            ->filter()
            ->mapWithKeys(fn ($type) => [$type => self::getSubjectTypeLabel($type)])
            ->toArray();

        return [
            'created_at' => Filter::dateRange('Date Range', null, null, 'created_at'),
            'causer_id' => Filter::selectCustom('User', ['' => 'All'] + $users, function (Builder $query, $value) {
                if ($value !== '' && $value !== null) {
                    $query->where('causer_id', $value)->where('causer_type', User::class);
                }
            }),
            'subject_type' => Filter::select('Subject Type', ['' => 'All'] + $subjectTypes, 'subject_type'),
            'event' => Filter::select('Event', [
                '' => 'All',
                'created' => 'Created',
                'updated' => 'Updated',
                'deleted' => 'Deleted',
            ], 'event'),
        ];
    }

    public function handle()
    {
        $query = Activity::query()->with(['causer', 'subject']);

        return DataTables::of($query)
            ->addColumn('causer_name', function (Activity $model) {
                if (!$model->causer) {
                    return '<span class="text-muted">-</span>';
                }
                return e($model->causer->name ?? $model->causer->email ?? 'User #' . $model->causer_id);
            })
            ->addColumn('subject_type_label', function (Activity $model) {
                return e(self::getSubjectTypeLabel($model->subject_type));
            })
            ->addColumn('details', function (Activity $model) {
                $props = $model->properties;
                if (!$props || ($props->isEmpty() && !$model->event)) {
                    return '<span class="text-muted">-</span>';
                }
                $attrs = $props->get('attributes');
                $old = $props->get('old');
                if (!$attrs && !$old) {
                    return '<span class="text-muted">-</span>';
                }
                $parts = [];
                if ($old && is_array($old)) {
                    foreach ($old as $k => $v) {
                        $newVal = is_array($attrs) && isset($attrs[$k]) ? $attrs[$k] : null;
                        if ($newVal !== null || $v !== null) {
                            $parts[] = '<strong>' . e($k) . '</strong>: ' . e(json_encode($v)) . ' → ' . e(json_encode($newVal));
                        }
                    }
                }
                if (empty($parts) && $attrs && is_array($attrs)) {
                    foreach ($attrs as $k => $v) {
                        $parts[] = '<strong>' . e($k) . '</strong>: ' . e(json_encode($v));
                    }
                }
                return $parts ? '<small class="text-muted">' . implode('<br>', $parts) . '</small>' : '<span class="text-muted">-</span>';
            })
            ->editColumn('created_at', function (Activity $model) {
                return $model->created_at ? $model->created_at->format('Y-m-d H:i') : '-';
            })
            ->filter(function (Builder $query) {
                $this->applyFilters($query);
                $search = request()->input('search.value');
                if ($search && strlen(trim($search)) >= 2) {
                    $term = '%' . trim($search) . '%';
                    $query->where(function ($q) use ($term) {
                        $q->where('description', 'like', $term)
                            ->orWhere('subject_type', 'like', $term)
                            ->orWhere('event', 'like', $term);
                    });
                }
            }, true)
            ->rawColumns(['causer_name', 'subject_type_label', 'details'])
            ->make(true);
    }
}
