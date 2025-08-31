<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Setting;
use Barryvdh\Snappy\Facades\SnappyPdf;

class SaleDocumentController extends Controller
{
    public function pdf(string $role, Sale $sale)
    {
        if (auth()->user()->role === 'cashier' && $sale->user_id !== auth()->id()) {
            abort(403);
        }

        $sale->load(['items.product', 'user']);
        $sale->items->each(function ($item) {
            $opts = $item->options ?? $item->meta ?? null;
            if (is_string($opts)) {
                $opts = json_decode($opts, true);
            }
            $item->options = is_array($opts) ? ($opts['options'] ?? $opts) : [];

            $notes = $item->note ?? $item->notes ?? null;
            if (is_string($notes) && trim($notes) !== '') {
                $item->notes = [$notes];
            } elseif (is_array($notes)) {
                $item->notes = array_filter($notes);
            } else {
                $item->notes = [];
            }
        });

        $setting = Setting::first();
        $currency = $setting->currency ?? '$';

        $orderNotes = [];
        foreach ($sale->items as $item) {
            foreach ($item->notes as $n) {
                $orderNotes[] = $n;
            }
            foreach ($item->options as $opt) {
                if (!in_array($opt, ['Small Size','Medium Size','Large Size']) &&
                    stripos($opt, 'Sugar') === false && stripos($opt, 'Ice') === false) {
                    $orderNotes[] = $opt;
                }
            }
        }
        $orderNotes = array_unique(array_filter($orderNotes));

        $html = view('reports.sale-document', [
            'sale' => $sale,
            'setting' => $setting,
            'currency' => $currency,
            'orderNotes' => $orderNotes,
        ])->render();

        return SnappyPdf::loadHTML($html)
            ->setOption('encoding', 'UTF-8')
            ->setOption('enable-local-file-access', true)
            ->download("sale-{$sale->id}.pdf");
    }
}