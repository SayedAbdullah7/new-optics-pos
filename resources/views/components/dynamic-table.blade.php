<x-table>
    <div class=" mx-5">
        <!--begin::Input group-->
        <div class="mb-10">
            <!--begin::Label-->
            <label class="form-label fw-semibold">Columns:</label>
            <!--end::Label-->
            <!--begin::Input-->
            <div>
                <select id="columns_{{ $tableId }}" class="form-select form-select-solid" multiple="multiple" data-kt-select2="true" data-close-on-select="false" data-placeholder="Select option">
                </select>
            </div>
            <!--end::Input-->
        </div>
        <!--end::Input group-->
    </div>
    <x-slot name="toolbar">

        <!--begin::Filter-->
        <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
            <i class="ki-duotone ki-filter fs-2">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>Filter</button>
        <!--begin::Menu 1-->
        <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true">
            <!--begin::Header-->
            <div class="px-7 py-5">
                <div class="fs-5 text-gray-900 fw-bold">Filter Options</div>
            </div>
            <!--end::Header-->
            <!--begin::Separator-->
            <div class="separator border-gray-200"></div>
            <!--end::Separator-->
            <!--begin::Content-->
            <div class="px-7 py-5" data-kt-user-table-filter="form">
                <!--begin::Scrollable Filters Container-->
                <div class="filter-scroll-container" style="max-height: 60vh; overflow-y: auto; padding-right: 8px;">
                    @foreach ($filters as $column => $filter)
                    <div class="col">
                        @if ($filter['type'] === 'select' || $filter['type'] === 'select-custom' || $filter['type'] === 'boolean')
                            <!--begin::Input group - Select/Boolean/Custom-->
                            <div class="mb-10">
                                <label class="form-label fs-6 fw-semibold">{{$filter['label']}}</label>
                                <select id="filter_{{ $column }}" class="form-select form-select-solid fw-bold table-filter" data-kt-select2="true" data-placeholder="Select option" data-allow-clear="true" data-kt-user-table-filter="{{$column}}" data-hide-search="true">
                                    <option></option>
                                    @foreach ($filter['options'] as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <!--end::Input group-->
                        @elseif ($filter['type'] === 'date')
                            <!--begin::Input group - Date-->
                            <div class="mb-10">
                                <x-group-input-date id="filter_{{ $column }}" class="table-filter" name="filter_{{ $column }}" :label="$filter['label']" :min="$filter['min'] ?? null" :max="$filter['max'] ?? null"></x-group-input-date>
                            </div>
                            <!--end::Input group-->
                        @elseif ($filter['type'] === 'date-range')
                            <!--begin::Input group - Date Range-->
                            <div class="mb-10">
                                <label class="form-label fs-6 fw-semibold">{{$filter['label']}}</label>
                                <!--begin::Daterangepicker-->
                                <div id="filter_{{ $column }}_daterange"
                                     data-kt-daterangepicker="true"
                                     data-kt-daterangepicker-opens="left"
                                     data-filter-key="{{ $column }}"
                                     data-kt-menu-trigger="false"
                                     class="btn btn-sm btn-light d-flex align-items-center px-4 w-100 table-filter"
                                     onclick="event.stopPropagation();">
                                    <!--begin::Display range-->
                                    <div class="text-gray-600 fw-bold" id="filter_{{ $column }}_display">
                                        Select date range
                                    </div>
                                    <!--end::Display range-->
                                    <i class="ki-duotone ki-calendar-8 text-gray-500 lh-0 fs-2 ms-2 me-0">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                        <span class="path4"></span>
                                        <span class="path5"></span>
                                        <span class="path6"></span>
                                    </i>
                                </div>
                                <!--end::Daterangepicker-->
                                <!-- Hidden inputs for from and to dates -->
                                <input type="hidden" id="filter_{{ $column }}_from" name="filter_{{ $column }}_from" value="">
                                <input type="hidden" id="filter_{{ $column }}_to" name="filter_{{ $column }}_to" value="">
                            </div>
                            <!--end::Input group-->
                        @elseif ($filter['type'] === 'text')
                            <!--begin::Input group - Text-->
                            <div class="mb-10">
                                <label class="form-label fs-6 fw-semibold">{{$filter['label']}}</label>
                                <input type="text" id="filter_{{ $column }}" class="form-control form-control-solid table-filter" placeholder="{{$filter['placeholder'] ?? ''}}" data-kt-user-table-filter="{{$column}}" />
                            </div>
                            <!--end::Input group-->
                        @elseif ($filter['type'] === 'number')
                            <!--begin::Input group - Number-->
                            <div class="mb-10">
                                <label class="form-label fs-6 fw-semibold">{{$filter['label']}}</label>
                                <input type="number" id="filter_{{ $column }}" class="form-control form-control-solid table-filter" placeholder="{{$filter['label']}}" data-kt-user-table-filter="{{$column}}" @if(isset($filter['min'])) min="{{$filter['min']}}" @endif @if(isset($filter['max'])) max="{{$filter['max']}}" @endif />
                            </div>
                            <!--end::Input group-->
                        @elseif ($filter['type'] === 'range')
                            <!--begin::Input group - Number Range-->
                            <div class="mb-10">
                                <label class="form-label fs-6 fw-semibold">{{$filter['label']}}</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="number" id="filter_{{ $column }}_min" class="form-control form-control-solid table-filter" placeholder="Min" data-kt-user-table-filter="{{$column}}" @if(isset($filter['min'])) min="{{$filter['min']}}" @endif @if(isset($filter['max'])) max="{{$filter['max']}}" @endif />
                                    </div>
                                    <div class="col-6">
                                        <input type="number" id="filter_{{ $column }}_max" class="form-control form-control-solid table-filter" placeholder="Max" data-kt-user-table-filter="{{$column}}" @if(isset($filter['min'])) min="{{$filter['min']}}" @endif @if(isset($filter['max'])) max="{{$filter['max']}}" @endif />
                                    </div>
                                </div>
                            </div>
                            <!--end::Input group-->
                        @endif
                    </div>
                    @endforeach
                </div>
                <!--end::Scrollable Filters Container-->
                <!--begin::Actions-->
                <div class="d-flex justify-content-end mt-5">
                    <button type="reset" class="btn btn-light btn-active-light-primary fw-semibold me-2 px-6" data-kt-menu-dismiss="true" data-kt-user-table-filter="reset">Reset</button>
                    <button type="submit" class="btn btn-primary fw-semibold px-6" data-kt-menu-dismiss="true" data-kt-user-table-filter="filter">Apply</button>
                </div>
                <!--end::Actions-->
            </div>
            <!--end::Content-->
        </div>
        <!--end::Menu 1-->
        <!--end::Filter-->
        @if(isset($createUrl) && $createUrl)
            <button type="button" class="btn btn-primary has_action text-capitalize" data-type="create"
                    data-action="{{ $createUrl }}">
                <i class="ki-duotone ki-plus fs-2"></i>create new
            </button>
        @endif
        <!--end::Add user-->
    </x-slot>

    <!--begin::Datatable-->
    <table id="{{ $tableId }}" class="dynamicTable table align-middle table-row-dashed fs-6 gy-5">
        <thead>
        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
            @if($showCheckbox)
                <th class="w-10px pe-2">
                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                        <input class="form-check-input" type="checkbox" data-kt-check="true"
                               data-kt-check-target="#{{ $tableId }} .form-check-input" value="1"/>
                    </div>
                </th>
            @endif
            @foreach($columns as $column)
                <th class="{{ $column->name === 'action' ? 'text-end min-w-100px' : '' }}">{{ $column->title }}</th>
            @endforeach
        </tr>
        </thead>
        <tbody class="text-gray-600 fw-semibold"></tbody>
    </table>
    <!--end::Datatable-->
</x-table>
@push('styles')
<style>
    /* Custom scrollbar for filter container */
    .filter-scroll-container {
        scrollbar-width: thin;
        scrollbar-color: rgba(155, 155, 155, 0.5) transparent;
    }

    .filter-scroll-container::-webkit-scrollbar {
        width: 6px;
    }

    .filter-scroll-container::-webkit-scrollbar-track {
        background: transparent;
    }

    .filter-scroll-container::-webkit-scrollbar-thumb {
        background-color: rgba(155, 155, 155, 0.5);
        border-radius: 10px;
    }

    .filter-scroll-container::-webkit-scrollbar-thumb:hover {
        background-color: rgba(155, 155, 155, 0.7);
    }
</style>
@endpush
@push('scripts')
    <script>
        "use strict";

        // Class definition - use unique name per table
        var KTDatatablesServerSide_{{ str_replace('-', '_', $tableId) }} = (function () {
            const columns = @json($jsColumns);
            const filters = @json($filters);
            const tableId = '{{ $tableId }}';
            const ajaxUrl = '{{ $ajaxUrl }}';
            console.log('Initializing DataTable:', tableId, 'ajaxUrl:', ajaxUrl);
            let dt;

            // Initialize DataTable
            const initDatatable = () => {
                // Check if DataTable is already initialized
                if ($.fn.DataTable.isDataTable('#' + tableId)) {
                    dt = $('#' + tableId).DataTable();
                    // Store instance globally
                    window['{{ str_replace('-', '_', $tableId) }}Table'] = dt;
                    return;
                }

                // Build columnDefs from columns with className or width
                const dynamicColumnDefs = [];
                console.log('Columns config:', columns);
                columns.forEach((col, index) => {
                    const colDef = { targets: index };
                    if (col.className) {
                        colDef.className = col.className;
                        console.log(`Column ${index} (${col.name}) has className: ${col.className}`);
                    }
                    if (col.width) {
                        colDef.width = col.width;
                        console.log(`Column ${index} (${col.name}) has width: ${col.width}`);
                    }
                    if (col.className || col.width) {
                        dynamicColumnDefs.push(colDef);
                    }
                });
                console.log('Dynamic columnDefs:', dynamicColumnDefs);

                dt = $('#' + tableId).DataTable({
                    searchDelay: 500,
                    processing: true,
                    serverSide: true,
                    stateSave: false,
                    order: [[0, 'desc']],
                    select: {
                        style: 'multi',
                        selector: 'td:first-child input[type="checkbox"]',
                        className: 'row-selected'
                    },
                    ajax: {
                        url: ajaxUrl,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                        },
                        data: (d) => {
                            @if(isset($tableType) && $tableType)
                            d.table_type = '{{ $tableType }}';
                            @endif
                            // Add date filters from URL params
                            const urlParams = new URLSearchParams(window.location.search);
                            if (urlParams.get('from')) {
                                d.from = urlParams.get('from');
                            }
                            if (urlParams.get('to')) {
                                d.to = urlParams.get('to');
                            }
                            Object.keys(filters).forEach((key) => {
                                const filterConfig = filters[key];
                                const filterType = filterConfig.type || 'select';

                                // Handle range filters (date-range, range)
                                if (filterType === 'date-range') {
                                    // Get values from hidden inputs (set by daterangepicker)
                                    const fromInput = document.querySelector(`#filter_${key}_from`);
                                    const toInput = document.querySelector(`#filter_${key}_to`);
                                    const fromValue = fromInput?.value || '';
                                    const toValue = toInput?.value || '';

                                    if (fromValue) {
                                        d[`${key}_from`] = fromValue;
                                    }
                                    if (toValue) {
                                        d[`${key}_to`] = toValue;
                                    }
                                    // Also set main key if both values exist
                                    if (fromValue && toValue) {
                                        d[key] = `${fromValue}|${toValue}`;
                                    }
                                } else if (filterType === 'range') {
                                    const minValue = document.querySelector(`#filter_${key}_min`)?.value;
                                    const maxValue = document.querySelector(`#filter_${key}_max`)?.value;
                                    if (minValue !== null && minValue !== '') {
                                        d[`${key}_min`] = minValue;
                                    }
                                    if (maxValue !== null && maxValue !== '') {
                                        d[`${key}_max`] = maxValue;
                                    }
                                    // Also set main key if both values exist
                                    if (minValue && maxValue) {
                                        d[key] = `${minValue}|${maxValue}`;
                                    }
                                } else {
                                    // Regular filters (select, text, number, date, boolean)
                                    const filterValue = document.querySelector(`#filter_${key}`)?.value;
                                    if (filterValue) {
                                        d[key] = filterValue;
                                    }
                                }
                            });
                        }
                    },
                    columns: columns,
                    columnDefs: [
                        ...dynamicColumnDefs,
                            @if($showCheckbox)
                        {
                            targets: 0,
                            orderable: false,
                            render: (data) => `
                            <div class="form-check form-check-sm form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" value="${data}" />
                            </div>`
                        },
                            @endif
                    ],
                });

                // Store DataTable instance globally for reload access
                window['{{ str_replace('-', '_', $tableId) }}Table'] = dt;

                // Reinitialize custom functions on draw
                dt.on('draw', () => {
                    initToggleToolbar();
                    handleDeleteRows();
                    KTMenu.createInstances();
                });

                // Column visibility handling
                const columnSelectId = '#columns_' + tableId;
                const $columnSelect = $(columnSelectId);

                // Populate column visibility select
                columns.forEach((col, index) => {
                    if (col.name || col.data) {
                        const colName = col.name || col.data;
                        const colTitle = col.title || colName;
                        $columnSelect.append(new Option(colTitle, colName, col.visible !== false, col.visible !== false));
                    }
                });

                // Initialize Select2 for columns select if not already initialized
                if ($columnSelect.data('select2') == null) {
                    $columnSelect.select2({
                        placeholder: 'Select columns to show',
                        allowClear: true
                    });
                }

                // Handle column visibility changes
                $columnSelect.on('change', function() {
                    const selectedValues = $(this).val() || [];
                    columns.forEach((col, index) => {
                        dt.column(index).visible(selectedValues.includes(col.name));
                    });
                    dt.columns.adjust().draw(false);
                });
            };

            // Search functionality
            const handleSearchDatatable = () => {
                const searchInput = document.querySelector('[data-kt-user-table-filter="search"]');
                console.log('*****************');

                console.log('searchInput');
                console.log(searchInput);

                searchInput?.addEventListener('keyup', (e) => {
                    console.log('keyup');
                    console.log(e.target.value);
                    dt.search(e.target.value).draw();
                });
            };

            // Apply filters
            const handleFilterDatatable = () => {
                const filterButton = document.querySelector('[data-kt-user-table-filter="filter"]');
                filterButton?.addEventListener('click', () => {
                    dt.ajax.reload();
                });
            };

            // Reset filters
            const handleResetFilters = () => {
                const resetButton = document.querySelector('[data-kt-user-table-filter="reset"]');
                resetButton?.addEventListener('click', () => {
                    dt.columns().search('').draw();
                    document.querySelectorAll('.table-filter').forEach((filter) => {
                        if (filter.tagName === 'INPUT') {
                            filter.value = '';
                            if ($(filter).hasClass('select2-hidden-accessible')) {
                                $(filter).val(null).trigger('change');
                            }
                        } else if (filter.tagName === 'SELECT') {
                            filter.selectedIndex = 0;
                            if ($(filter).hasClass('select2-hidden-accessible')) {
                                $(filter).val(null).trigger('change');
                            }
                        }
                    });
                    dt.ajax.reload();
                });
            };

            // Delete rows
            const handleDeleteRows = () => {
                document.querySelectorAll('[data-kt-user-table-filter="delete_row"]').forEach((btn) => {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        const row = e.target.closest('tr');
                        const customerName = row.querySelector('td:nth-child(2)')?.innerText;

                        Swal.fire({
                            text: `Are you sure you want to delete ${customerName}?`,
                            icon: "warning",
                            showCancelButton: true,
                            buttonsStyling: false,
                            confirmButtonText: "Yes, delete!",
                            cancelButtonText: "No, cancel",
                            customClass: {
                                confirmButton: "btn fw-bold btn-danger",
                                cancelButton: "btn fw-bold btn-active-light-primary"
                            }
                        }).then((result) => {
                            if (result.value) {
                                Swal.fire({
                                    text: `Deleting ${customerName}`,
                                    icon: "info",
                                    showConfirmButton: false,
                                    timer: 2000
                                }).then(() => {
                                    Swal.fire({
                                        text: `${customerName} has been deleted.`,
                                        icon: "success",
                                        confirmButtonText: "Ok, got it!"
                                    }).then(() => {
                                        dt.row(row).remove().draw();
                                    });
                                });
                            }
                        });
                    });
                });
            };

            // Toggle toolbar
            const initToggleToolbar = () => {
                // Custom logic for toolbar toggle can be added here
            };

            // Public methods
            return {
                init: function () {
                    initDatatable();
                    handleSearchDatatable();
                    handleFilterDatatable();
                    handleResetFilters();
                }
            };
        })();

        // On document ready
        KTUtil.onDOMContentLoaded(() => {
            KTDatatablesServerSide_{{ str_replace('-', '_', $tableId) }}.init();
        });
    </script>
@endpush
