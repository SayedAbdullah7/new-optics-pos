<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Range Power ID that skips type/category filtering
    |--------------------------------------------------------------------------
    | When this range ID is selected in invoice/bill lens filters, types and
    | categories are returned for all ranges (no RangePower_id filter).
    | Set to null to always filter by range.
    */
    'skip_range_filter_id' => env('OPTICS_SKIP_RANGE_FILTER_ID', 4),

];
