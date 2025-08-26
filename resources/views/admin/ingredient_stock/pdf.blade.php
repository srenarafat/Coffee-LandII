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
    @if(isset($logs))
        <h4 style="text-align:center;">{{ __('messages.ingredient_stock_log') }}</h4>
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.ingredient') }}</th>
                    <th>{{ __('messages.type') }}</th>
                    <th>{{ __('messages.qty') }}</th>
                    <th>{{ __('messages.unit') }}</th>
                    <th>{{ __('messages.in_stock') }}</th>
                    <th>{{ __('messages.Note') }}</th>
                    <th>{{ __('messages.users') }}</th>
                    <th>{{ __('messages.date') }}</th>
                </tr>
            </thead>
            <tbody>
            @foreach($logs as $log)
                <tr>
                    <td>{{ $log->ingredient->name }}</td>
                    <td>{{ strtoupper($log->type) }}</td>
                    <td>{{ $log->quantity }}</td>
                    <td>{{ $log->unit }}</td>
                    <td>{{ $log->ingredient->stock }} {{ $log->unit }}</td>
                    <td>{{ $log->note }}</td>
                    <td>{{ $log->user->name }}</td>
                    <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <h4 style="text-align:center;">{{ __('messages.ingredient_stock_summary') }}</h4>
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.ingredient') }}</th>
                    <th>{{ __('messages.total_in') }}</th>
                    <th>{{ __('messages.total_out') }}</th>
                    <th>{{ __('messages.in_stock') }}</th>
                    <th>{{ __('messages.last_at') }}</th>
                </tr>
            </thead>
            <tbody>
            @foreach($summary as $row)
                <tr>
                    <td>{{ $row->name }}</td>
                    <td>{{ $row->total_in ?? 0 }}</td>
                    <td>{{ $row->total_out ?? 0 }}</td>
                    <td>{{ $row->stock }} {{ $row->unit }}</td>
                    <td>{{ optional($row->last_at)->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>