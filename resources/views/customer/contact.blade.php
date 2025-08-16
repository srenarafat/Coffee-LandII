@extends('layouts.app')

@section('content')
<div class="container my-4">
    <h1 class="mb-4">Contact {{ $customer->name }}</h1>
    <p><strong>Email:</strong> {{ $customer->email ?? 'N/A' }}</p>
    <p><strong>Phone:</strong> {{ $customer->phone ?? 'N/A' }}</p>
    <a href="{{ url()->previous() }}" class="btn btn-secondary mt-3">Back</a>
</div>
@endsection
