@extends('layouts.app')

@section('content')
<div class="container my-4">
    <h1 class="mb-4">Notes for {{ $customer->name }}</h1>
    <p class="text-muted">No notes available.</p>
    <a href="{{ url()->previous() }}" class="btn btn-secondary mt-3">Back</a>
</div>
@endsection
