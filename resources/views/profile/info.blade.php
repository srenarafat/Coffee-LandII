@extends('layouts.app')




@section('content')
<div class="container">
    <div class="card shadow-sm mx-auto" style="max-width: 750px;">
        <div class="card-body p-4">
            <a href="{{ auth()->user()->role === 'cashier'
                ? route('cashier.pos.index')
                : (auth()->user()->role === 'admin'
                    ? route('admin.pos.index')
                    : (auth()->user()->role === 'superadmin'
                        ? route('superadmin.pos.index')
                        : route('dashboard'))) }}"
               class="btn btn-success mb-3">{{ __('messages.back') }}</a>
            <div class="text-center mb-4">
                <img src="{{ asset($user->profile_image ?? 'images/default-avatar.png') }}"
                     class="rounded-circle shadow" width="120" height="120" style="object-fit: cover;">
                <h4 class="mt-3">{{ $user->name }}</h4>
                <p class="text-muted">{{ __('messages.role') }}: <strong>{{ $user->role === 'superadmin' ? __('messages.super_admin') : ucfirst($user->role) }}</strong></p>
            </div>




            <table class="table table-bordered align-middle">
               <thead class="custom-blue-header text-black text-center">
                    <tr>
                        <th style="width: 220px;">{{ __('messages.Info') }}</th>
                        <th>{{ __('messages.Details') }}</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- ✅ Email --}}
                    <tr id="email-row">
                        <td>📧 {{ __('messages.email') }}</td>
                        <td>{{ $user->email }}</td>
                    </tr>


                    {{-- ✅ Phone --}}
                    <tr id="phone-row">
                        <td>📱 {{ __('messages.phone') }}</td>
                        <td id="phone-value">{{ $user->phone ?? 'N/A' }}</td>
                    </tr>


                    {{-- ✅ Address --}}
                    <tr id="address-row">
                        <td>🏠 {{ __('messages.address') }}</td>
                        <td id="address-value">{{ $user->address ?? 'N/A' }}</td>
                    </tr>


                    {{-- ✅ Gender --}}
                    <tr id="gender-row">
                        <td>👤 {{ __('messages.gender') }}</td>
                        <td id="gender-value">{{ ucfirst($user->gender ?? 'N/A') }}</td>
                    </tr>


                    {{-- ✅ Date of Birth --}}
                    <tr id="dob-row">
                        <td>🎂 {{ __('messages.date_of_birth') }}</td>
                        <td id="dob-value">{{ $user->dob ? \Carbon\Carbon::parse($user->dob)->format('d M Y') : 'N/A' }}</td>
                    </tr>


                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
@push('styles')
<style>
.custom-blue-header th {
    background-color: #d8eaff !important;
    color: #000 !important;
    font-weight: bold;
}
</style>
@endpush















