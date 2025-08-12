@extends('layouts.app')
@section('content')
<div class="container mt-4">
    <h3>System Logs</h3>
    <table class="table table-bordered">
        <thead><tr><th>ID</th><th>User</th><th>Action</th><th>Date</th></tr></thead>
        <tbody>
            @foreach($logs as $log)
                <tr>
                    <td>{{ $log->id }}</td>
                    <td>{{ optional($log->user)->name }}</td>
                    <td>{{ $log->action }}</td>
                    <td>{{ $log->created_at }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $logs->links() }}
</div>
@endsection