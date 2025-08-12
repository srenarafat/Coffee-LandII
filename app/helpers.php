<?php

use Illuminate\Support\Facades\App;

if (!function_exists('format_currency')) {
    function format_currency($amount, $decimals = 2): string
    {
        $setting = App::make('view')->getShared()['setting'] ?? null;
        $currency = $setting->currency ?? '$';
        return $currency . number_format($amount, $decimals);
    }
}