<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sale;
use Illuminate\Support\Facades\Storage;

class ExportFallbackSales extends Command
{
    protected $signature = 'sales:export-fallback';
    protected $description = 'Export last 30 days of sales to fallback_sales.csv';

    public function handle()
    {
        $sales = Sale::with('items.product')
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at') // export in chronological order
            ->get();

        $rows = [];
        $rows[] = ['Date', 'Product', 'Qty', 'Total'];

        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $rows[] = [
                    $sale->created_at->format('Y-m-d H:i'),
                    ($item->product->name_kh ?? '') . ' / ' . ($item->product->name ?? 'Unknown'),
                    $item->quantity,
                    $item->quantity * $item->price
                ];
            }
        }

        // Save to CSV
        $csv = '';
        foreach ($rows as $row) {
            $csv .= implode(',', $row) . "\n";
        }

        Storage::disk('local')->put('fallback_sales.csv', $csv);

        $this->info('✅ fallback_sales.csv exported successfully.');
    }
}