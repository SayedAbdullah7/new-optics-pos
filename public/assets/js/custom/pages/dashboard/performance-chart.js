"use strict";

/**
 * Performance Chart Widget 36
 *
 * Displays investment performance metrics including:
 * - Expected Profit (SAR)
 * - Actual Profit (SAR)
 * - Short Investments (Count)
 *
 * Features:
 * - Date range picker for filtering data
 * - Dynamic chart updates via API
 * - Responsive design
 */
var KTChartsWidget36 = function () {
    var chart = {
        self: null,
        rendered: false
    };

    var dateRangePicker = null;
    var config = {};
    var apiUrl = '';
    var currentRangeName = null; // Store current selected range name

    /**
     * Update chart with new data
     * @param {Object} data - Chart data containing months and series data
     */
    var updateChart = function(data) {
        if (!chart.self || !chart.rendered) {
            return;
        }

        var months = data.months || [];
        var seriesConfig = config.series || [];

        // Build series array dynamically from configuration
        var chartSeries = [];
        var maxValue = 0;

        seriesConfig.forEach(function(seriesItem) {
            var key = seriesItem.key || '';
            var name = seriesItem.name || key;
            var scale = seriesItem.scale || 1;
            var seriesData = data[key] || [];

            // Apply scaling
            var scaledData = seriesData.map(function(val) {
                var numVal = parseFloat(val) || 0;
                return numVal * scale;
            });

            // Calculate max value for y-axis
            if (scaledData && scaledData.length > 0) {
                var seriesMax = Math.max(...scaledData.filter(function(v) { return isFinite(v); }));
                if (!isNaN(seriesMax) && seriesMax > 0) {
                    maxValue = Math.max(maxValue, seriesMax);
                }
            }

            chartSeries.push({
                name: name,
                data: scaledData
            });
        });

        if (maxValue === 0 || !isFinite(maxValue)) {
            maxValue = 100;
        }

        // Update chart series
        chart.self.updateSeries(chartSeries);

        // Update x-axis categories
        chart.self.updateOptions({
            xaxis: {
                categories: months
            },
            yaxis: {
                max: maxValue * 1.1,
                min: 0
            }
        });
    };

    /**
     * Fetch data from API
     * @param {moment} startDate - Start date
     * @param {moment} endDate - End date
     */
    var fetchChartData = function(startDate, endDate, rangeName) {
        var dateRangePickerId = config.dateRangePickerId;
        var subtitleId = config.subtitleId;
        var subtitleFormat = config.subtitleFormat || 'investment';

        // Show loading indicator
        var pickerElement = dateRangePickerId ? document.getElementById(dateRangePickerId) :
                           document.querySelector('[data-kt-daterangepicker="true"]');
        var displayElement = pickerElement ? pickerElement.querySelector('.text-gray-600.fw-bold') : null;
        if (!displayElement && pickerElement) {
            displayElement = pickerElement.querySelector('div');
        }

        // Determine display text: show range name if available, otherwise show formatted date
        var displayText = '';
        if (rangeName && rangeName !== 'Custom Range') {
            // Use range name (e.g., "Last 7 Days", "This Month")
            displayText = rangeName;
            currentRangeName = rangeName;
        } else {
            // Format date range for custom selection
            var current = moment();
            if (current.isSame(startDate, "day") && current.isSame(endDate, "day")) {
                displayText = startDate.format('D MMM YYYY');
            } else {
                displayText = startDate.format('D MMM YYYY') + ' - ' + endDate.format('D MMM YYYY');
            }
            currentRangeName = null;
        }

        if (displayElement) {
            displayElement.textContent = 'Loading...';
        }

        // Fetch data from API
        $.ajax({
            url: apiUrl,
            method: 'GET',
            data: {
                start_date: startDate.format('YYYY-MM-DD'),
                end_date: endDate.format('YYYY-MM-DD')
            },
            success: function(response) {
                if (response.success && response.data) {
                    // Update chart with new data
                    updateChart(response.data);

                    // Update subtitle with totals
                    var subtitle = subtitleId ? document.getElementById(subtitleId) :
                                  document.querySelector('.performance-subtitle');
                    if (subtitle) {
                        if (subtitleFormat === 'investment' && response.data) {
                            // Try to get totals from response data
                            var totalText = '';
                            if (response.data.total_short_investments !== undefined &&
                                response.data.total_expected_profit !== undefined) {
                                totalText = response.data.total_short_investments +
                                    ' Short Investments with ' +
                                    parseFloat(response.data.total_expected_profit).toLocaleString('en-US', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    }) + ' SAR Expected Profit';
                            } else if (response.data.subtitle) {
                                totalText = response.data.subtitle;
                            }
                            if (totalText) {
                                subtitle.textContent = totalText;
                            }
                        } else if (response.data.subtitle) {
                            subtitle.textContent = response.data.subtitle;
                        }
                    }

                    // Update date range display after successful load
                    if (displayElement) {
                        displayElement.textContent = displayText;
                    }
                } else {
                    // On error, restore date range text
                    if (displayElement) {
                        displayElement.textContent = displayText;
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching chart data:', error);
                // On error, restore date range text instead of showing error
                if (displayElement) {
                    displayElement.textContent = displayText;
                }
            }
        });
    };

    /**
     * Initialize date range picker
     */
    var initDateRangePicker = function() {
        if (!config.showDatePicker) {
            return;
        }

        var dateRangePickerId = config.dateRangePickerId;
        var defaultDateRangeText = config.defaultDateRangeText || 'Last 12 Months';
        var pickerElement = dateRangePickerId ? document.getElementById(dateRangePickerId) :
                           document.querySelector('[data-kt-daterangepicker="true"]');

        if (!pickerElement) {
            return;
        }

        // Check if required libraries are available
        if (typeof jQuery === 'undefined' || typeof $.fn.daterangepicker === 'undefined') {
            console.warn('Date range picker dependencies not loaded, retrying...');
            setTimeout(function() {
                initDateRangePicker();
            }, 500);
            return;
        }

        // Check if moment.js is available (required by daterangepicker)
        if (typeof moment === 'undefined') {
            console.warn('Moment.js not found');
            return;
        }

        var display = pickerElement.querySelector('.text-gray-600.fw-bold');
        if (!display) {
            display = pickerElement.querySelector('div');
        }
        var start = moment().subtract(11, 'months').startOf('month');
        var end = moment().endOf('month');

        // Define range names mapping
        var rangeLabels = {
            'Today': 'Today',
            'Yesterday': 'Yesterday',
            'Last 7 Days': 'Last 7 Days',
            'Last 30 Days': 'Last 30 Days',
            'This Month': 'This Month',
            'Last Month': 'Last Month',
            'Last 3 Months': 'Last 3 Months',
            'Last 6 Months': 'Last 6 Months',
            'Last 12 Months': 'Last 12 Months'
        };

        // Function to get range name from dates
        var getRangeName = function(startDate, endDate) {
            // Check if dates match any predefined range
            var ranges = {
                'Today': [moment().startOf('day'), moment().endOf('day')],
                'Yesterday': [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days').endOf('day')],
                'Last 7 Days': [moment().subtract(6, 'days').startOf('day'), moment().endOf('day')],
                'Last 30 Days': [moment().subtract(29, 'days').startOf('day'), moment().endOf('day')],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'Last 3 Months': [moment().subtract(2, 'months').startOf('month'), moment().endOf('month')],
                'Last 6 Months': [moment().subtract(5, 'months').startOf('month'), moment().endOf('month')],
                'Last 12 Months': [moment().subtract(11, 'months').startOf('month'), moment().endOf('month')]
            };

            for (var rangeName in ranges) {
                var rangeDates = ranges[rangeName];
                if (startDate.isSame(rangeDates[0], 'day') && endDate.isSame(rangeDates[1], 'day')) {
                    return rangeName;
                }
            }

            return null; // Custom range
        };

        var cb = function(startDate, endDate, rangeLabel) {
            var rangeName = rangeLabel || getRangeName(startDate, endDate);
            var displayText = '';

            if (rangeName && rangeLabels[rangeName]) {
                // Use predefined range name
                displayText = rangeLabels[rangeName];
                currentRangeName = rangeName;
            } else {
                // Custom range - show formatted dates
                var current = moment();
                if (current.isSame(startDate, "day") && current.isSame(endDate, "day")) {
                    displayText = startDate.format('D MMM YYYY');
                } else {
                    displayText = startDate.format('D MMM YYYY') + ' - ' + endDate.format('D MMM YYYY');
                }
                currentRangeName = null;
            }

            if (display) {
                display.textContent = displayText;
            }

            // Fetch and update chart data when date range changes
            fetchChartData(startDate, endDate, rangeName);
        };

        try {
            $(pickerElement).daterangepicker({
                startDate: start,
                endDate: end,
                opens: 'left',
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    'Last 3 Months': [moment().subtract(2, 'months').startOf('month'), moment().endOf('month')],
                    'Last 6 Months': [moment().subtract(5, 'months').startOf('month'), moment().endOf('month')],
                    'Last 12 Months': [moment().subtract(11, 'months').startOf('month'), moment().endOf('month')]
                },
                locale: {
                    format: 'DD/MM/YYYY'
                }
            }, cb);

            // Listen to the apply event - this fires when user clicks Apply button
            $(pickerElement).on('apply.daterangepicker', function(ev, picker) {
                // Get the label if available from the picker
                // chosenLabel is set when user clicks on a predefined range
                var rangeLabel = null;
                if (picker.chosenLabel && picker.chosenLabel !== 'Custom Range') {
                    rangeLabel = picker.chosenLabel;
                }
                cb(picker.startDate, picker.endDate, rangeLabel);
            });

            // Listen to cancel event - restore previous range
            $(pickerElement).on('cancel.daterangepicker', function(ev, picker) {
                // Don't update on cancel
                return;
            });

            dateRangePicker = $(pickerElement).data('daterangepicker');

            // Initialize display with range name
            var initialRangeName = getRangeName(start, end);
            if (display) {
                if (initialRangeName && rangeLabels[initialRangeName]) {
                    display.textContent = rangeLabels[initialRangeName];
                    currentRangeName = initialRangeName;
                } else {
                    display.textContent = start.format('D MMM YYYY') + ' - ' + end.format('D MMM YYYY');
                }
            }
        } catch (e) {
            console.error('Error initializing date range picker:', e);
        }
    };

    /**
     * Initialize chart
     * @param {Object} chartConfig - Configuration object
     */
    var initChart = function(chartConfig) {
        config = chartConfig || {};
        apiUrl = config.apiUrl || '';
        var chartId = config.chartId || 'kt_charts_widget_36';
        var initialData = config.initialData || {};
        var chartHeight = config.chartHeight || 300;

        var element = document.getElementById(chartId);

        if (!element) {
            console.error('Chart element not found: #' + chartId);
            return;
        }

        // Check if ApexCharts is available
        if (typeof ApexCharts === 'undefined') {
            console.error('ApexCharts library is not loaded');
            return;
        }

        var height = parseInt(KTUtil.css(element, 'height')) || chartHeight;
        var labelColor = KTUtil.getCssVariableValue('--bs-gray-500');
        var borderColor = KTUtil.getCssVariableValue('--bs-border-dashed-color');
        var baseprimaryColor = KTUtil.getCssVariableValue('--bs-primary');
        var lightprimaryColor = KTUtil.getCssVariableValue('--bs-primary');
        var basesuccessColor = KTUtil.getCssVariableValue('--bs-success');
        var lightsuccessColor = KTUtil.getCssVariableValue('--bs-success');
        var basewarningColor = KTUtil.getCssVariableValue('--bs-warning');
        var lightwarningColor = KTUtil.getCssVariableValue('--bs-warning');

        // Investment performance data from backend
        var months = initialData.months || [];
        var seriesConfig = config.series || [];

        // Build series array dynamically from configuration
        var chartSeries = [];
        var maxValue = 0;
        var colors = [];
        var strokeColors = [];
        var markerColors = [];

        // Color mapping for Bootstrap theme colors
        var colorMap = {
            'primary': {
                base: KTUtil.getCssVariableValue('--bs-primary'),
                light: KTUtil.getCssVariableValue('--bs-primary')
            },
            'success': {
                base: KTUtil.getCssVariableValue('--bs-success'),
                light: KTUtil.getCssVariableValue('--bs-success')
            },
            'warning': {
                base: KTUtil.getCssVariableValue('--bs-warning'),
                light: KTUtil.getCssVariableValue('--bs-warning')
            },
            'danger': {
                base: KTUtil.getCssVariableValue('--bs-danger'),
                light: KTUtil.getCssVariableValue('--bs-danger')
            },
            'info': {
                base: KTUtil.getCssVariableValue('--bs-info'),
                light: KTUtil.getCssVariableValue('--bs-info')
            },
            'secondary': {
                base: KTUtil.getCssVariableValue('--bs-secondary'),
                light: KTUtil.getCssVariableValue('--bs-secondary')
            }
        };

        // Default colors if not enough series colors provided
        var defaultColors = [
            { base: baseprimaryColor, light: lightprimaryColor },
            { base: basesuccessColor, light: lightsuccessColor },
            { base: basewarningColor, light: lightwarningColor }
        ];

        seriesConfig.forEach(function(seriesItem, index) {
            var key = seriesItem.key || '';
            var name = seriesItem.name || key;
            var scale = seriesItem.scale || 1;
            var seriesData = initialData[key] || [];

            // Apply scaling
            var scaledData = seriesData.map(function(val) {
                var numVal = parseFloat(val) || 0;
                return numVal * scale;
            });

            // Calculate max value for y-axis
            if (scaledData && scaledData.length > 0) {
                var seriesMax = Math.max(...scaledData.filter(function(v) { return isFinite(v); }));
                if (!isNaN(seriesMax) && seriesMax > 0) {
                    maxValue = Math.max(maxValue, seriesMax);
                }
            }

            // Get color for this series
            var seriesColor = null;
            if (seriesItem.color && colorMap[seriesItem.color]) {
                seriesColor = colorMap[seriesItem.color];
            } else {
                seriesColor = defaultColors[index % defaultColors.length] || defaultColors[0];
            }

            colors.push(seriesColor.light);
            strokeColors.push(seriesColor.base);
            markerColors.push(seriesColor.base);

            chartSeries.push({
                name: name,
                data: scaledData
            });
        });

        if (maxValue === 0 || !isFinite(maxValue)) {
            maxValue = 100;
        }
        var minValue = 0;

        var options = {
            series: chartSeries,
            chart: {
                fontFamily: 'inherit',
                type: 'area',
                height: height,
                toolbar: {
                    show: false
                }
            },
            plotOptions: {},
            legend: {
                show: true,
                position: 'top',
                horizontalAlign: 'right'
            },
            dataLabels: {
                enabled: false
            },
            fill: {
                type: "gradient",
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.2,
                    stops: [15, 120, 100]
                }
            },
            stroke: {
                curve: 'smooth',
                show: true,
                width: 3,
                colors: strokeColors.length > 0 ? strokeColors : [baseprimaryColor, basesuccessColor, basewarningColor]
            },
            xaxis: {
                categories: months,
                axisBorder: {
                    show: false,
                },
                axisTicks: {
                    show: false
                },
                tickAmount: 6,
                labels: {
                    rotate: 0,
                    rotateAlways: false,
                    style: {
                        colors: labelColor,
                        fontSize: '12px'
                    }
                },
                crosshairs: {
                    position: 'front',
                    stroke: {
                        color: strokeColors.length > 0 ? strokeColors : [baseprimaryColor, basesuccessColor, basewarningColor],
                        width: 1,
                        dashArray: 3
                    }
                },
                tooltip: {
                    enabled: true,
                    formatter: undefined,
                    offsetY: 0,
                    style: {
                        fontSize: '12px'
                    }
                }
            },
            yaxis: {
                max: maxValue * 1.1,
                min: minValue,
                tickAmount: 6,
                labels: {
                    style: {
                        colors: labelColor,
                        fontSize: '12px'
                    },
                    formatter: function (val) {
                        // For short investments, show actual count
                        if (val > 1000) {
                            return Math.round(val / 100);
                        }
                        return Math.round(val);
                    }
                }
            },
            states: {
                normal: {
                    filter: {
                        type: 'none',
                        value: 0
                    }
                },
                hover: {
                    filter: {
                        type: 'none',
                        value: 0
                    }
                },
                active: {
                    allowMultipleDataPointsSelection: false,
                    filter: {
                        type: 'none',
                        value: 0
                    }
                }
            },
            tooltip: {
                style: {
                    fontSize: '12px'
                },
                y: {
                    formatter: function (val, { seriesIndex }) {
                        if (seriesConfig && seriesConfig[seriesIndex]) {
                            var seriesItem = seriesConfig[seriesIndex];
                            // Check if custom formatter is provided
                            if (seriesItem.formatter && typeof seriesItem.formatter === 'function') {
                                return seriesItem.formatter(val);
                            }
                            // Check if scale is 100 (for count-based series)
                            if (seriesItem.scale === 100) {
                                return Math.round(val / 100) + ' ' + (seriesItem.unit || '');
                            }
                            // Default format with unit
                            return val.toLocaleString() + ' ' + (seriesItem.unit || '');
                        }
                        return val.toLocaleString();
                    }
                }
            },
            colors: colors.length > 0 ? colors : [lightprimaryColor, lightsuccessColor, lightwarningColor],
            grid: {
                borderColor: borderColor,
                strokeDashArray: 4,
                yaxis: {
                    lines: {
                        show: true
                    }
                }
            },
            markers: {
                strokeColor: markerColors.length > 0 ? markerColors : [baseprimaryColor, basesuccessColor, basewarningColor],
                strokeWidth: 3
            }
        };

        chart.self = new ApexCharts(element, options);

        // Set timeout to properly get the parent elements width
        setTimeout(function() {
            chart.self.render();
            chart.rendered = true;

            // Initialize date range picker after chart is rendered
            setTimeout(function() {
                initDateRangePicker();
            }, 300);
        }, 200);
    }

    // Public methods
    return {
        /**
         * Initialize the chart widget
         * @param {Object} chartConfig - Configuration object
         */
        init: function (chartConfig) {
            initChart(chartConfig);

            // Update chart on theme mode change
            if (typeof KTThemeMode !== 'undefined') {
                KTThemeMode.on("kt.thememode.change", function() {
                    if (chart.rendered) {
                        chart.self.destroy();
                    }
                    initChart(config);
                });
            }
        },

        // Expose update function for external use
        updateChart: updateChart,
        fetchChartData: fetchChartData
    }
}();

// Webpack support
if (typeof module !== 'undefined' && module.exports) {
    module.exports = KTChartsWidget36;
}


