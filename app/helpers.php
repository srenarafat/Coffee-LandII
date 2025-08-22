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
            ->orderByDesc('created_at')
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

if (!function_exists('render_category_options')) {
    /**
     * Render nested category <option> tags recursively.
     *
     * @param  \Illuminate\Support\Collection<int,\App\Models\Category>  $categories
     * @param  int|string|null  $selected
     * @param  string  $prefix
     * @return string
     */
    function render_category_options($categories, $selected = null, string $prefix = ''): string
    {
        $html = '';

        $traverse = function ($cats, $prefix) use (&$traverse, $selected, &$html) {
            foreach ($cats as $cat) {
                $isSelected = (string) $selected === (string) $cat->id ? ' selected' : '';
                $html .= '<option value="' . $cat->id . '"' . $isSelected . '>' . $prefix . $cat->name . '</option>';
                if ($cat->childrenRecursive && $cat->childrenRecursive->isNotEmpty()) {
                    $traverse($cat->childrenRecursive, $prefix . '-- ');
                }
            }
        };

        $traverse($categories, $prefix);

        return $html;
    }
}