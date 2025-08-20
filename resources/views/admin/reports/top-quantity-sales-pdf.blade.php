<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <style>
        @font-face {
            font-family: 'Hanuman';
            src: url('{{ 'file:///' . str_replace('\\', '/', public_path('fonts/Hanuman-Regular.ttf')) }}') format('truetype');
        }
        body {
            font-family: 'Hanuman', DejaVu Sans, sans-serif;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
        }
    </style>
</head>
<body>
    <h4 style="text-align:center;">{{ __('messages.top_quantity_sale_products') }}</h4>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('messages.product') }}</th>
                <th>{{ __('messages.category') }}</th>
                <th>{{ __('messages.total_quantity') }}</th>
                <th>{{ __('messages.month') }}</th>
                <th>{{ __('messages.year') }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach($topProducts as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->category_name }}</td>
                <td>{{ $item->total_quantity }}</td>
                <td>{{ \Carbon\Carbon::create()->month($item->month)->format('F') }}</td>
                <td>{{ $item->year }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>