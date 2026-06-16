@extends('layouts.app')

@section('title', 'Forgot Password')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card card-soft shadow-sm border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h3 class="mb-1">Lupa Password</h3>
                        <p class="text-muted">Masukkan email untuk reset password.</p>
                    </div>

                    @if(session('status'))
                        <div class="alert alert-info">{{ session('status') }}</div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">Kirim Link Reset</button>

                        <div class="text-center">
                            <p class="mb-0">Ingat password? <a href="{{ route('login') }}" class="text-decoration-none">Masuk</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
