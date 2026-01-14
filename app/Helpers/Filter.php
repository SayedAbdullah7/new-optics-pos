<?php

namespace App\Helpers;

use Carbon\Carbon;

/**
 * Filter Helper - Reusable filter definitions for DataTables
 *
 * Usage in DataTable:
 * public function filters(): array {
 *     return [
 *         'status' => Filter::select('Status', ['1' => 'Active', '0' => 'Inactive']),
 *         'created_at' => Filter::date('Created Date'),
 *         'email' => Filter::text('Email'),
 *         'amount' => Filter::number('Amount'),
 *         'price' => Filter::range('Price Range', 0, 1000000),
 *     ];
 * }
 */
class Filter
{
    /**
     * Generate filter data for a select dropdown.
     */
    public static function select(string $label, array $options, ?string $column = null): array
    {
        return [
            'type' => 'select',
            'label' => $label,
            'options' => $options,
            'column' => $column,
        ];
    }

    /**
     * Generate filter data for a date input.
     */
    public static function date(string $label, ?string $max = null, ?string $min = null, ?string $column = null): array
    {
        if ($min == 'today') {
            $min = Carbon::today()->toDateString();
        }
        if ($max == 'today') {
            $max = Carbon::today()->toDateString();
        }
        return [
            'type' => 'date',
            'label' => $label,
            'min' => $min,
            'max' => $max,
            'column' => $column,
        ];
    }

    /**
     * Generate filter data for a date range (from - to).
     */
    public static function dateRange(string $label, ?string $max = null, ?string $min = null, ?string $column = null): array
    {
        if ($min == 'today') {
            $min = Carbon::today()->toDateString();
        }
        if ($max == 'today') {
            $max = Carbon::today()->toDateString();
        }
        return [
            'type' => 'date-range',
            'label' => $label,
            'min' => $min,
            'max' => $max,
            'column' => $column,
        ];
    }

    /**
     * Generate filter data for a text input.
     */
    public static function text(string $label, ?string $placeholder = null, ?string $column = null): array
    {
        return [
            'type' => 'text',
            'label' => $label,
            'placeholder' => $placeholder ?? $label,
            'column' => $column,
        ];
    }

    /**
     * Generate filter data for a number input.
     */
    public static function number(string $label, ?float $min = null, ?float $max = null, ?string $column = null): array
    {
        return [
            'type' => 'number',
            'label' => $label,
            'min' => $min,
            'max' => $max,
            'column' => $column,
        ];
    }

    /**
     * Generate filter data for a number range (min - max).
     */
    public static function range(string $label, ?float $min = null, ?float $max = null, ?string $column = null): array
    {
        return [
            'type' => 'range',
            'label' => $label,
            'min' => $min,
            'max' => $max,
            'column' => $column,
        ];
    }

    /**
     * Generate filter data for a boolean/checkbox input.
     */
    public static function boolean(string $label, ?string $column = null): array
    {
        return [
            'type' => 'boolean',
            'label' => $label,
            'options' => [
                '1' => 'Yes',
                '0' => 'No',
            ],
            'column' => $column,
        ];
    }

    /**
     * Generate filter data for a select dropdown with custom query callback.
     */
    public static function selectCustom(string $label, array $options, callable $callback): array
    {
        return [
            'type' => 'select-custom',
            'label' => $label,
            'options' => $options,
            'callback' => $callback,
        ];
    }
}





