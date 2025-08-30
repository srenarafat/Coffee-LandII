<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <title>Invoice</title>
    <style>
    @font-face {
        font-family: 'Hanuman';
        src: url('{{ 'file:///' . str_replace('\\', '/', public_path('fonts/Hanuman-Regular.ttf')) }}') format('truetype');
    }

    body { font-family: 'Hanuman', sans-serif; font-size: 17px; }
    .invoice-box { padding: 5px; width: 100%; }
    .header { text-align: center; font-weight: bold; font-size: 14px; margin-top: 5px; }
    .contact { text-align: center; font-size: 10px; line-height: 1.4; margin-top: 4px; }

    table { width: 100%; border-collapse: collapse; margin-top: 5px; }
    th, td { padding: 3px 2px; font-size: 11px; }
    th { border-top: 1px solid #000; border-bottom: 1px solid #000; font-weight: bold; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .summary p { margin: 2px 0; font-size: 11px; }
    .summary { margin-top: 10px; }
    .center { text-align: center; margin-top: 8px; font-size: 10px; }
    .bold { font-weight: bold; }

    /* small bullet list like in cart */
    .line-list { margin: 2px 0 0 14px; padding: 0; list-style: disc; }
    .line-list li { font-size: 10px; line-height: 1.25; color:#555; margin: 1px 0; }
    </style>
</head>

<body>

    @php
        $currency = optional($setting)->currency ?? '$';
        $rate = $sale->exchange_rate;
    @endphp

    @if (!empty($logoBase64))
    <div style="text-align: center; margin-bottom: 5px; padding-top: 20px;">
        <img src="{{ $logoBase64 }}" style="height: 80px;">
    </div>
    @endif

    <div class="invoice-box">
        <div class="header">{{ optional($setting)->shop_name ?? 'Coffee Land' }}</div>
        <div class="contact">
            Street 598, Phum 4, Sangkat Chrang Chamres 1,<br>
            Khan Russey Keo, Phnom Penh<br>
            Tel: 011 997 783 / 096 797 9773
        </div>

        {{-- dashed line --}}
        <hr style="border-top: 1px dashed #000; margin: 10px 0;">

        {{-- Info --}}
        <table style="font-size: 12px; margin: 0;">
            <tr>
                <td style="text-align: left; border: none;">
                    No: {{ $sale->id }}<br>
                    Cashier: {{ $sale->user->name ?? 'N/A' }}<br>
                    Table: {{ $sale->table_number ?? 'N/A' }}
                </td>
                <td style="text-align: right; border: none;">
                    Date: {{ $sale->created_at->format('d-M-Y') }}<br>
                    Time: {{ $sale->created_at->format('h:i A') }}
                </td>
            </tr>
        </table>

        {{-- dashed line --}}
        <hr style="border-top: 1px dashed #000; margin: 10px 0;">

        {{-- Items --}}
        <table style="margin-bottom: 10px;">
            <thead>
                <tr>
                    <th style="text-align: left; width: 25px;">SN</th>
                    <th style="text-align: left; width: auto;">Item</th>
                    <th class="text-center" style="width: 30px;">Qty</th>
                    <th class="text-right" style="width: 35px;">Price</th>
                    <th class="text-right" style="width: 45px; padding-right: 10px;">Total</th> 
                </tr>
            </thead>
            <tbody>
                @php $computedTotal = 0; @endphp
                @foreach ($sale->items as $index => $item)
                    @php
                        $lineTotal = $item->price * $item->quantity;
                        $computedTotal += $lineTotal;

                        // Robustly pull options from multiple possible places
                        $meta = $item->meta ?? $item->options ?? null;
                        if (is_string($meta)) { $meta = json_decode($meta, true); }
                        if (!is_array($meta)) { $meta = []; }

                        $size  = $item->size ?? ($meta['size'] ?? 'medium');
                        $sugar = $item->sugar_level ?? ($meta['sugar_level'] ?? null);
                        $ice   = $item->ice_option ?? ($meta['ice_option'] ?? null);

                        // Build the same cleaned list shown in cart:
                        $lines = [];
                        if (!$item->product->isFood()) {
                            $lines[] = ucfirst($size) . ' Size';
                            if ($sugar !== null && (int)$sugar !== 100) {
                                $lines[] = 'Sugar: ' . ((int)$sugar) . '%';
                            }
                            if ($ice && strtolower($ice) !== 'normal') {
                                $lines[] = ucfirst($ice) . ' Ice';
                            }
                        }

                        // Notes can be an array or a string; show only the text (no "Note:")
                        $notes = $item->notes ?? ($item->note ?? null);
                        if (is_string($notes) && trim($notes) !== '') {
                            $lines[] = $notes;
                        } elseif (is_array($notes)) {
                            foreach ($notes as $n) {
                                if (is_string($n) && trim($n) !== '') {
                                    $lines[] = $n;
                                }
                            }
                        }
                    @endphp
                    <tr>
                        <td style="vertical-align: top;">{{ $index + 1 }}</td>

                        <td style="text-align: left; vertical-align: top;">
                            <div style="font-weight: bold;">{{ $item->product->name }}</div>
                            @if(!empty($lines))
                                <ul class="line-list">
                                    @foreach($lines as $l)
                                        <li>{{ $l }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </td>

                        <td class="text-center" style="vertical-align: top;">
                            {{ $item->quantity }}
                        </td>

                        <td class="text-right" style="vertical-align: top;">
                            {{ $currency }}{{ number_format($item->price, 2) }}
                        </td>

                        <td class="text-right" style="vertical-align: top; padding-right: 10px;">
                            {{ $currency }}{{ number_format($lineTotal, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- dashed line --}}
        <hr style="border-top: 1px dashed #000; margin: 10px 0;">

        {{-- Summary & QR --}}
        <div class="summary">
            <table style="width: 100%; font-size: 10px; line-height: 1.6;">
                <tr>
                    <td style="vertical-align: top; width: 65%;">
                        <table style="width: 100%;">
                            @if ($sale->discount > 0)
                            <tr>
                                <td style="border: none;">
                                    {{ __('messages.discount') }}:
                                    -{{ $currency }} {{ number_format($sale->discount, 2) }}
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <td style="border: none;">
                                    <strong>{{ __('messages.grand_total') }}:</strong>
                                    {{ $currency }}{{ number_format($sale->total, 2) }}
                                </td>
                            </tr>
                            <tr>
                                <td style="border: none;">
                                    {{ __('messages.cash_received') }} ({{ $currency }}):
                                    {{ $currency }}{{ number_format($sale->cash_usd, 2) }}
                                </td>
                            </tr>
                            <tr>
                                <td style="border: none;">
                                    {{ __('messages.cash_received') }} (Riel):
                                    {{ number_format($sale->cash_riel) }} ៛
                                </td>
                            </tr>
                            <tr>
                                <td style="border: none;">
                                    {{ __('messages.change') }} ({{ $currency }}):
                                    {{ $currency }}{{ number_format($sale->change_usd, 2) }}
                                </td>
                            </tr>
                            <tr>
                                <td style="border: none;">
                                    {{ __('messages.change') }} (Riel):
                                    {{ number_format($sale->change_riel) }} ៛
                                </td>
                            </tr>
                            <tr>
                                <td style="border: none;">
                                    {{ __('messages.payment_method') }}: {{ ucfirst($sale->payment_method) }}
                                </td>
                            </tr>
                        </table>
                    </td>

                    @if (!empty($scanBase64))
                    <td style="width: 35%; text-align: center; vertical-align: top; padding-top:15px; padding-right:10px;">
                        <img src="{{ $scanBase64 }}" alt="QR Code" style="width: 90px; margin-bottom: 3px;">
                        <div style="font-size: 10px; font-weight: bold;">SIEK SREYMOM</div>
                        <div style="font-size: 8px; margin-top: 2px;">
                            ពន្ធអាករ/Tax = 10% <br> អាត្រាប្តូប្រាក់ {{ $currency }}1 = {{ number_format($rate) }}៛
                        </div>
                    </td>
                    @endif
                </tr>
            </table>
        </div>

        {{-- dashed line --}}
        <hr style="border-top: 1px dashed #000; margin: 10px 0;">

        <div class="footer" style="font-size: 12px; text-align:center;">
            <p class="mb-0" style="line-height: 1.05; margin-bottom: -2px;">Wi‑Fi: Coffee Land</p>
            <p class="mb-0" style="line-height: 1.05; margin-bottom: -2px;">Password: khmerfood</p>
            <p class="mt-1">សូមអរគុណសម្រាប់ការអញ្ជើញមក</p>
        </div>

    </div>
    
    <script>
        window.addEventListener('load', () => window.print());
        window.addEventListener('afterprint', () => {
            const target = document.referrer || @json($backRoute);
            window.location.href = target;
        });
    </script>
</body>

</html>