<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DynamicTable extends Component
{
    public $tableId;
    public $columns;
    public $actions;
    public $showCheckbox;
    public $defaultOrder;
    public $ajaxUrl;
    public $jsColumns;
    public $filters;
    public $createUrl;
    public $tableType;
    public $createAsLink;
    public $createLinkText;

    /**
     * Create a new component instance.
     */
    public function __construct(
        $tableId = null,
        $columns = [],
        $actions = false,
        $showCheckbox = false,
        $defaultOrder = null,
        $ajaxUrl = null,
        $filters = [],
        $createUrl = null,
        $tableType = null,
        $createAsLink = false,
        $createLinkText = null
    ) {
        $this->createUrl = $createUrl;
        $this->createAsLink = $createAsLink;
        $this->createLinkText = $createLinkText;
        $this->tableId = $tableId ?? 'data_table';
        $this->columns = $columns;
        $this->actions = $actions;
        $this->showCheckbox = $showCheckbox;
        $this->defaultOrder = $defaultOrder;
        $this->ajaxUrl = $ajaxUrl ?: url()->current();
        $this->filters = $filters;
        $this->tableType = $tableType;

        // Build JavaScript columns configuration
        $jsColumns = [];
        
        if ($this->showCheckbox) {
            $jsColumns[] = ['data' => '', 'name' => ''];
        }

        // Convert Collection to array if needed
        $columnsArray = $this->columns instanceof \Illuminate\Support\Collection
            ? $this->columns->toArray()
            : $this->columns;

        $jsColumns = array_merge($jsColumns, array_map(function($item) {
            // Convert Column object to array if needed
            if (!is_array($item)) {
                $item = $item->toArray();
            }
            
            $column = [
                'data' => $item['data'] ?? $item['name'] ?? '',
                'name' => $item['name'] ?? $item['data'] ?? '',
                'title' => $item['title'] ?? ucfirst(str_replace('_', ' ', $item['name'] ?? $item['data'] ?? '')),
                'searchable' => $item['searchable'] ?? true,
                'orderable' => $item['orderable'] ?? true,
                'visible' => $item['visible'] ?? true,
            ];

            // Include className and width if they exist
            if (isset($item['className'])) {
                $column['className'] = $item['className'];
            }
            if (isset($item['width'])) {
                $column['width'] = $item['width'];
            }

            return $column;
        }, $columnsArray));

        if ($this->actions) {
            $jsColumns[] = ['data' => null, 'name' => null];
        }
        
        $this->jsColumns = $jsColumns;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.dynamic-table');
    }
}





