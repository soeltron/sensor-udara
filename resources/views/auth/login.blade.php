@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card card-soft shadow-sm border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <i class="fas fa-microchip fa-2x" style="color:#6366f1;"></i>
                        </div>
                        <h3 class="mb-1 fw-bold">Masuk ke Dashboard IoT</h3>
                        <p class="text-muted">Masukkan username / email dan password untuk melanjutkan.</p>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        {{-- Bug #6 Fix: Changed from email to login (username OR email) --}}
                        <div class="mb-3">
                            <label class="form-label fw-medium">Username atau Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-user text-muted"></i></span>
                                <input type="text"
                                    class="form-control @error('login') is-invalid @enderror"
                                    name="login"
                                    value="{{ old('login') }}"
                                    placeholder="Masukkan username atau email"
                                    required autofocus>
                                @error('login')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-lock text-muted"></i></span>
                                <input type="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    name="password"
                                    placeholder="Masukkan password"
                                    required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                <label class="form-check-label" for="remember">Ingat saya</label>
                            </div>
                            <a href="{{ route('password.request') }}" class="text-decoration-none" style="color:#6366f1;">Lupa password?</a>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold" style="background:#6366f1;border:none;">
                            <i class="fas fa-sign-in-alt me-2"></i>Masuk
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <p class="text-muted mb-2">Belum punya akun? <a href="{{ route('register') }}" style="color:#6366f1;">Daftar sekarang</a></p>
                        <p class="text-muted mb-0"><small>Atau <a href="{{ route('dashboard') }}" class="text-muted">lihat dashboard tanpa login</a></small></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
