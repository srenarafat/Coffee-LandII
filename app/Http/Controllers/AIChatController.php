<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Sale;
use App\Models\SaleItem;

class AIChatController extends Controller
{
    public function ask(Request $request)
    {
        $question = $request->input('message');

        // Limit to the most recent 100 sale items to keep payload small
        $items = SaleItem::with(['sale', 'product'])
            ->whereHas('sale', function ($q) use ($request) {
                if ($request->user()->role === 'cashier') {
                    $q->where('user_id', $request->user()->id);
                }
                $q->whereDate('created_at', '>=', now()->subDays(30));
            })
            ->orderByDesc('created_at') // newest first
            ->limit(100)
            ->get();

            // Sort chronologically after fetching the latest records
        $items = $items->sortBy('created_at')->values();

        // Build structured data from real POS sales
        $salesDataText = "Invoice | Date | Product | Qty | Total\n";
        $salesDataText .= "---------------------------------------------------\n";

        foreach ($items as $item) {
            $sale = $item->sale;
            $product = $item->product;
            $productName = $product->name_kh ?: $product->name;
            $salesDataText .= "{$sale->invoice_no} | {$sale->created_at->format('Y-m-d H:i')} | {$productName} | {$item->quantity} | \$" . number_format($item->total, 2) . "\n";
        }

        try {
            $response = Http::withToken(config('services.openai.key'))->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a POS assistant. Respond in the same language as the user question (Khmer or English). Analyze the real sales data and reply only with actual product names from the data (do not translate). The sales entries are sorted chronologically from earliest to latest. Format replies clearly with invoice, date, product, quantity, and total.'
                    ],
                    ['role' => 'user', 'content' => "POS Sales Data:\n$salesDataText"],
                    ['role' => 'user', 'content' => $question],
                    
                ],
            ]);

            $answer = $response['choices'][0]['message']['content'] ?? null;

            if (!$answer) {
                Log::info('GPT fallback triggered.');
                $answer = $this->simpleFallbackAnswer($question) ?? '⚠️ GPT returned no result. Try again later.';
            }

            return response()->json(['reply' => $answer]);

        } catch (\Exception $e) {
            Log::error('GPT Error', ['message' => $e->getMessage()]);

            $fallbackData = $this->loadCsvFallbackData();
            $answer = $this->analyzeFallbackCsv($question, $fallbackData);

            return response()->json([
                'reply' => $answer ?: '⚠️ Offline fallback loaded but no answer found.'
            ]);
        }
    }

    protected function loadCsvFallbackData()
    {
        $path = Storage::disk('local')->path('fallback_sales.csv');
        if (!file_exists($path)) return [];

        $rows = array_map('str_getcsv', file($path));
        $header = array_map('trim', array_shift($rows));
        $data = [];

        foreach ($rows as $row) {
            $rowData = array_combine($header, $row);
            $dateString = $rowData['Date'] ?? '';
            $carbon = \Carbon\Carbon::parse($dateString);
            $data[] = [
                'date' => $rowData['Date'] ?? '',
                'datetime' => $dateString,
                'date' => $carbon->toDateString(),
                'product' => $rowData['Product'] ?? '',
                'qty' => (int) ($rowData['Qty'] ?? 0),
                'total' => (float) ($rowData['Total'] ?? 0),
            ];
        }

        return $data;
    }

    protected function analyzeFallbackCsv($question, $data)
    {
        $q = strtolower($question);

        // Aggregate totals by date (day only) for quick lookups
        $dailyTotals = [];
        foreach ($data as $entry) {
            $date = $entry['date'];
            $dailyTotals[$date] = ($dailyTotals[$date] ?? 0) + $entry['total'];
        }

        // Check for requests of a specific date (YYYY-MM-DD, today, or yesterday)
        $requestedDate = null;
        if (preg_match('/\b\d{4}-\d{2}-\d{2}\b/', $q, $match)) {
            $requestedDate = $match[0];
        } elseif (str_contains($q, 'today')) {
            $requestedDate = now()->format('Y-m-d');
        } elseif (str_contains($q, 'yesterday')) {
            $requestedDate = now()->subDay()->format('Y-m-d');
        }

        if ($requestedDate) {
            $amount = $dailyTotals[$requestedDate] ?? 0;
            if ($amount > 0) {
                return "🗓️ Total sales on {$requestedDate}: \$" . number_format($amount, 2);
            }
            return "⚠️ No sales found for {$requestedDate}.";
        }

        // Provide a summary of recent daily totals
        if (str_contains($q, 'daily sales')) {
            $cutoff = now()->subDays(6); // last 7 days including today
            $lines = [];
            ksort($dailyTotals);
            foreach ($dailyTotals as $date => $amount) {
                if (\Carbon\Carbon::parse($date)->greaterThanOrEqualTo($cutoff)) {
                    $lines[] = "{$date} — \$" . number_format($amount, 2);
                }
            }

            if (!empty($lines)) {
                return "📅 Daily sales (last 7 days):\n" . implode("\n", $lines);
            }

            return '⚠️ No sales data available.';
        }

        if (str_contains($q, 'top') || str_contains($q, 'best') || str_contains($q, 'most')) {
            $thisWeek = now()->startOfWeek();
            $weeklyTotals = [];

            foreach ($data as $entry) {
                $entryDate = \Carbon\Carbon::parse($entry['datetime']);
                if ($entryDate->greaterThanOrEqualTo($thisWeek)) {
                    $name = $entry['product'];
                    $weeklyTotals[$name] = ($weeklyTotals[$name] ?? 0) + $entry['total'];
                }
            }

            arsort($weeklyTotals);
            $top = array_slice($weeklyTotals, 0, 3);

            if (empty($top)) {
                return '⚠️ No sales found for this week.';
            }

            $lines = [];
            $medals = ['🥇', '🥈', '🥉'];
            $i = 0;

            foreach ($top as $name => $amount) {
                $lines[] = "{$medals[$i]} **{$name}** — \$" . number_format($amount, 2);
                $i++;
            }

            return "📊 Top products this week:\n" . implode("\n", $lines);
        }

        if (str_contains($q, 'low') || str_contains($q, 'slow')) {
            $totals = [];
            foreach ($data as $entry) {
                $totals[$entry['product']] = ($totals[$entry['product']] ?? 0) + $entry['qty'];
            }

            asort($totals);
            $low = array_slice($totals, 0, 3);

            $lines = [];
            foreach ($low as $name => $qty) {
                $lines[] = "🕐 **{$name}** — {$qty} sold";
            }

            return "🧊 Slow-selling products:\n" . implode("\n", $lines);
        }

        return null;
    }

    protected function simpleFallbackAnswer($q)
    {
        $q = strtolower($q);

        if (str_contains($q, 'hello')) return '👋 Hello! I’m your POS Assistant.';
        if (str_contains($q, 'total')) return '🔢 Ask me something like "Total sales this week"';
        if (str_contains($q, 'first item')) return '📌 I can find the first item sold today if data is available.';

        return null;
    }
}