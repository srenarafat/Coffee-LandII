@extends('layouts.app')


@section('content')
<div class="container my-4">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">👤 {{ __('messages.user_management') }}</h5>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                {{ __('messages.add_user') }}
            </a>
        </div>
        <div class="card-body position-relative">
            @if(session('success'))
                <div id="successToast"
                     class="alert alert-success d-flex align-items-center justify-content-between shadow-sm px-4 py-2 animate__animated animate__fadeInDown"
                     style="position: absolute; top: 0; right: 0; margin: 12px; max-width: 360px;">
                    🎉 <strong>{{ session('success') }}</strong>
                </div>
            @endif


    <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="text-center align-middle">
                            <th style="width: 40px;">{{ __('messages.serial') }}</th>
                            <th> {{ __('messages.profile') }}</th>
                            <th> {{ __('messages.role') }}</th>
                            <th> {{ __('messages.created') }}</th>
                            <th> {{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
            @foreach ($users as $key => $user)
            <tr>
                <td class="text-center">{{ $key + 1 }}</td>
                <td class="d-flex align-items-center gap-3">
                    <img src="{{ asset($user->profile_image ?? 'images/default-avatar.png') }}"
                         class="rounded-circle border shadow-sm" width="42" height="42" style="object-fit: cover;">
                    <div>
                        <strong>{{ $user->name }}</strong><br>
                        <small class="text-muted">📧 {{ $user->email }}</small>
                    </div>
                </td>
                <td class="text-center">
                    <span class="badge bg-{{ $user->role === 'admin' ? 'primary' : 'success' }}">
                        🎖️ {{ $user->role === 'superadmin' ? __('messages.super_admin') : ucfirst($user->role) }}
                    </span>
                </td>
                <td class="text-center">
                    <small class="text-muted">🕒 {{ $user->created_at->format('d M Y, H:i') }}</small>
                </td>
                <td class="text-center">
                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-outline-primary">
                             {{ __('messages.edit') }}
                        </a>
                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('{{ __('messages.delete_user_confirm') }}')">
                             {{ __('messages.delete') }}
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
            </div>


     <div class="mt-3">
                {{ $users->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection


@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<style>
    #successToast {
        border-left: 6px solid #198754;
        background-color: #d1e7dd;
        font-size: 14px;
        border-radius: 6px;
        z-index: 1050;
    }
</style>
@endpush


@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toast = document.getElementById('successToast');
        if (toast) {
            setTimeout(() => {
                toast.classList.remove('animate__fadeInDown');
                toast.classList.add('animate__fadeOutUp');
                setTimeout(() => toast.remove(), 800);
            }, 2000);
        }
    });
</script>
@endpush



