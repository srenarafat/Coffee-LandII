<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <style>
        @font-face {
            font-family: 'Hanuman';
            src: url("file:///{{ str_replace('\\\\', '/', public_path('fonts/Hanuman-Regular.ttf')) }}") format('truetype');
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
    <h4 style="text-align:center;">Ingredient Stock Log</h4>
    <table>
        <thead>
            <tr>
                <th>Ingredient</th>
                <th>{{ __('messages.type') }}</th>
                <th>{{ __('messages.qty') }}</th>
                <th>Unit</th>
                <th>{{ __('messages.current_stock') }}</th>
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
</body>
</html>