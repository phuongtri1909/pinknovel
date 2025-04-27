@extends('layouts.main')
@section('title', 'Đăng nhập')

@push('styles-main')
    <link rel="stylesheet" href="{{ asset('assets/css/auth.css') }}">
@endpush

@section('content-main')
    <div class="auth-container d-flex align-items-center justify-content-center py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                    <div class="auth-card p-4 p-md-5">
                        <div class="text-center mb-4">
                            <a href="{{ route('home') }}">
                                @php
                                    // Get the logo and favicon from LogoSite model
                                    $logoSite = \App\Models\LogoSite::first();
                                    $logoPath =
                                        $logoSite && $logoSite->logo
                                            ? Storage::url($logoSite->logo)
                                            : asset('assets/images/logo/logo_site.webp');
                                @endphp
                                <img class="auth-logo mb-4" src="{{ $logoPath }}" alt="logo">
                            </a>
                            <h1 class="auth-title">Chào Mừng Trở Lại!</h1>
                        </div>

                        <form action="{{ route('login') }}" method="post">
                            @csrf
                            <div class="mb-4">
                                <div class="form-floating">
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                        name="email" id="email" placeholder="name@example.com"
                                        value="{{ old('email') }}" required>
                                    <label for="email">Email của bạn</label>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-floating position-relative">
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                        name="password" id="password" placeholder="Password" required>
                                    <label for="password">Mật khẩu</label>
                                    <i class="fa fa-eye position-absolute top-50 end-0 translate-middle-y me-3 cursor-pointer"
                                        id="togglePassword"></i>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-4 text-end">
                                <a href="{{ route('forgot-password') }}" class="auth-link">Quên mật khẩu?</a>
                            </div>

                            <button type="submit" class="auth-btn btn w-100 mb-4">Đăng Nhập</button>

                            <div class="text-center">
                                <span>Chưa có tài khoản? </span>
                                <a href="{{ route('register') }}" class="auth-link">Đăng ký ngay</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
