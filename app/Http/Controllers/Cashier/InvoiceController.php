<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Setting;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\Snappy\Facades\SnappyPdf;

class InvoiceController extends Controller
{
    public function download(Sale $sale)
    {
        // Authorization: only allow cashier to view their own invoice
        if ($sale->user_id !== auth()->id() && auth()->user()->role === 'cashier') {
            abort(403);
        }
        $setting = Setting::first();
        $currency = $setting->currency ?? '$';
        // Load relationships
        $sale->load(['items.product', 'user']);
        $sale->items->each(function ($item) {
            $opts = $item->options ?? $item->meta ?? null;
            if (is_string($opts)) {
                $opts = json_decode($opts, true);
            }
            $item->options = is_array($opts) ? ($opts['options'] ?? $opts) : [];
        });

        // Generate QR code as SVG base64
        $qrSvg = QrCode::format('svg')
            ->size(100)
            ->encoding('UTF-8')
            ->generate("Invoice #{$sale->id} | Total: {$currency}" . number_format($sale->total, 2));
        $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);

        // Load base64 images
        $logoBase64 = $this->getBase64Image(public_path('images/coffeeland-logo.png'));
        $scanBase64 = $this->getBase64Image(public_path('images/scan.png'));

        // Render HTML
        $html = view('cashier.pos.invoice', [
            'sale' => $sale,
            'logoBase64' => $logoBase64,
            'scanBase64' => $scanBase64,
            'qrBase64' => $qrBase64,
            'setting' => $setting,
        ])->render();

        // Generate PDF with Snappy
        return SnappyPdf::loadHTML($html)
        ->setOption('encoding', 'UTF-8')
        ->setOption('enable-local-file-access', true)
        ->setOption('disable-smart-shrinking', false)
        ->setOption('page-width', '80mm')  // ✅ correct way
        ->setOption('margin-top', '5mm')
        ->setOption('margin-bottom', '5mm')
        ->setOption('margin-left', '5mm')
        ->setOption('margin-right', '5mm')
        ->download("invoice-{$sale->id}.pdf");

    }


    public function printView(Sale $sale)
    {
        if ($sale->user_id !== auth()->id() && auth()->user()->role === 'cashier') {
            abort(403);
        }

        if (request('auto')) {
            session()->forget('table_number');
        }

        $setting = Setting::first();
        $currency = $setting->currency ?? '$';

        // Ensure relationships are loaded so the view has access
        $sale->load(['items.product', 'user']);
        $sale->items->each(function ($item) {
            $opts = $item->options ?? $item->meta ?? null;
            if (is_string($opts)) {
                $opts = json_decode($opts, true);
            }
            $item->options = is_array($opts) ? ($opts['options'] ?? $opts) : [];
        });

        // Generate QR code (optional for the view)
        $qrSvg = QrCode::format('svg')
            ->size(100)
            ->encoding('UTF-8')
            ->generate("Invoice #{$sale->id} | Total: {$currency}" . number_format($sale->total, 2));
        $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);

        // Load logo and scan images as base64
        $logoBase64 = $this->getBase64Image(public_path('images/coffeeland-logo.png'));
        $scanBase64 = $this->getBase64Image(public_path('images/scan.png'));

        return view('cashier.pos.invoice', [
            'sale' => $sale,
            'setting' => $setting,
            'logoBase64' => $logoBase64,
            'scanBase64' => $scanBase64,
            'qrBase64' => $qrBase64,
        ]);
    }

    private function getBase64Image($path)
    {
        return file_exists($path)
            ? 'data:image/' . pathinfo($path, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($path))
            : null;
    }
}