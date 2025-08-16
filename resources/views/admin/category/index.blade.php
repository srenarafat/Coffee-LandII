@extends('layouts.app')


@section('content')
<div class="container-fluid mt-4">
   <div class="card shadow-sm border-0 rounded-4 animate__animated" style="animation-delay: 0.5s;">
        <div class="card-body position-relative">


            <!-- ✅ Toast Success Message Inside Card -->
            @if(session('success'))
                <div id="successToast"
                     class="alert alert-success d-flex align-items-center justify-content-between shadow-sm px-4 py-2 animate__animated animate__fadeInDown"
                     style="position: absolute; top: 0; right: 0; margin: 12px; max-width: 360px;">
                    🎉 <strong>{{ session('success') }}</strong>
                </div>
            @endif


            <!-- ✅ Header -->
            <h4 class="fw-bold mb-3 text-brown">🗄️ {{ __('messages.category_list') }}</h4>


            <!-- ✅ Add Category -->
            <form action="{{ route('admin.categories.store') }}" method="POST" class="mb-4">
                @csrf
                <div class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <input type="text" name="name" class="form-control shadow-sm"
                               placeholder="{{ __('messages.new_category_placeholder') }}" required>
                    </div>
                    <div class="col-md-4">
                        <select name="parent_id" class="form-select shadow-sm">
                            <option value="">{{ __('messages.no_parent') ?? 'No Parent' }}</option>
                            @foreach($parentCategories as $parent)
                                <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary shadow-sm px-4">
                            {{ __('messages.add_category') }}
                        </button>
                    </div>
                </div>
            </form>


            <!-- ✅ Category Tree -->
            <ul class="list-unstyled">
                @foreach($categories as $cat)
                    @include('admin.category.partials.node', ['category' => $cat, 'parentCategories' => $parentCategories])
                @endforeach
            </ul>


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


    .text-brown {
        color: #4E342E;
    }


    .btn-outline-primary:hover {
        background-color: #0d6efd;
        color: white;
    }


    .btn-outline-danger:hover {
        background-color: #dc3545;
        color: white;
    }
</style>
@endpush




@push('scripts')
<script>
    function toggleEdit(id) {
        document.getElementById('nameDisplay' + id).classList.add('d-none');
        document.getElementById('editForm' + id).classList.remove('d-none');
    }


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





