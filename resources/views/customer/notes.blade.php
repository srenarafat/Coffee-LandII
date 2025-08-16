@extends('layouts.app')

@section('content')
<div class="container my-4">
    @php
        $role = auth()->user()->role;
        $routePrefix = $role === 'superadmin' ? 'superadmin' : ($role === 'cashier' ? 'cashier' : 'admin');
    @endphp

    <h1 class="mb-4">Notes for {{ $customer->name }}</h1>
    
    @if ($customer->notes)
        <p>{{ $customer->notes }}</p>
    @else
        <p class="text-muted">No notes available.</p>
    @endif

    <form method="POST" action="{{ route($routePrefix . '.customers.notes.update', $customer) }}" class="mt-3">
        @csrf
        @method('PATCH')
        <div class="mb-3">
            <label for="notes" class="form-label">Edit Notes</label>
            <textarea id="notes" name="notes" class="form-control" rows="4">{{ old('notes', $customer->notes) }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
    </form>

    <a href="{{ url()->previous() }}" class="btn btn-secondary mt-3">Back</a>
</div>
@endsection
