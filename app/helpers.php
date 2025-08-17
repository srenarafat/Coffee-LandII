<?php

use Illuminate\Support\Facades\App;
use App\Models\Category;

if (!function_exists('format_currency')) {
    function format_currency($amount, $decimals = 2): string
    {
        $setting = App::make('view')->getShared()['setting'] ?? null;
        $currency = $setting->currency ?? '$';
        return $currency . number_format($amount, $decimals);
    }
    }

if (!function_exists('category_options')) {
    /**
     * Build flattened category options for a given shop.
     *
     * @param  int|null  $shopId
     * @return array<int,string>
     */
    function category_options(?int $shopId): array
    {
        $categories = Category::query()
            ->when($shopId, fn ($q) => $q->where('shop_id', $shopId))
            ->whereNull('parent_id')
            ->with('childrenRecursive')
            ->orderBy('name')
            ->get();

        $options = [];

        $traverse = function ($cats, $prefix = '') use (&$traverse, &$options) {
            foreach ($cats as $cat) {
                $options[$cat->id] = $prefix . $cat->name;
                if ($cat->childrenRecursive->isNotEmpty()) {
                    $traverse($cat->childrenRecursive, $prefix . '-- ');
                }
            }
        };

        $traverse($categories);

        return $options;
    }
}