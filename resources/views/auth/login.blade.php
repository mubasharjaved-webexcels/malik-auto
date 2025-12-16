@extends('layouts.app')
@section('content')
<div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center" style="background: url('{{ asset('images/showroom-blur.jpg') }}') center/cover no-repeat;">
    <div class="row shadow-lg rounded-4 overflow-hidden" style="max-width: 900px; background-color: rgba(255, 255, 255, 0.9);">
        <!-- Left Side: Form -->
        <div class="col-md-6 p-5">
            <h3 class="fw-bold mb-3">Welcome Back</h3>
            <p class="text-muted small">Enter your login credentials, including your username and password, to securely access your account.</p>
            <br>
            
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="example@gmail.com" required autofocus>
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <!-- Password -->
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="At least 8 characters" required>
                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <div></div>
                    <a href="/login" class="text-decoration-none small">Forgot Password?</a>
                </div>
                <button type="submit" class="btn btn-primary w-100 mb-3">Sign in</button>
            </form>
            {{-- <div class="text-center mb-3">OR</div> --}}
            {{-- <a href="/" class="flex items-center justify-center gap-2 bg-gray-300 text-black rounded-2 px-6 py-2 mb-4">
                <img src="{{ asset('images/google-icon.png') }}" alt="Google" class="w-5 h-5">
                <span>Sign in with Google</span>
            </a> --}}
            {{-- <a href="/" class="flex items-center justify-center gap-2 bg-gray-300 text-black rounded-2 px-6 py-2">
                <img src="{{ asset('images/facebook-icon.png') }}" alt="Facebook" class="w-5 h-5">
                <span>Sign in with Facebook</span>
            </a> --}}
            {{-- <div class="text-center mt-4 small">
                Don't you have an account? <a href="{{ route('register') }}" class="text-decoration-none">Sign Up</a>
            </div> --}}
        </div>
        <!-- Right Side: Image -->
        <div class="col-md-6 d-none d-md-block p-0">
            <img src="{{ asset('images/yellow-car.jpg') }}" alt="Showroom" class="img-fluid h-100 w-100" style="object-fit: cover;">
        </div>
    </div>
</div>
@endsection