
@extends('layouts.app')

@section('content')
<div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center" style="background: url('/images/showroom-blur.jpg') center/cover no-repeat;">
    <div class="row shadow-lg rounded-4 overflow-hidden" style="max-width: 900px; background-color: rgba(255, 255, 255, 0.9);">
        <!-- Left Side: Form -->
        <div class="col-md-6 p-5">
            <h3 class="fw-bold mb-3">Welcome Back</h3>
            <p class="text-muted small">Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC.</p>
            
            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" id="name" name="name" class="form-control" holder="Full Name" value="{{ old('name') }}" required autofocus>
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

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
                
                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="At least 8 characters" required>
                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="number" id="phone" name="phone" class="form-control" placeholder="Enter your phone number." required>
                    @error('phone')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="country_id" class="form-label">Select Country</label>
                    <select id="country_id" name="country_id" class="form-control" required>
                        <option value="" disabled selected>Select your country</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->id }}" {{ old('country_id') == $country->id ? 'selected' : '' }}>
                                {{ $country->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('country_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label">Select Role</label>
                    <select id="role" name="role" class="form-control" required>
                        <option value="" disabled selected>Select a role</option>
                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        {{-- <option value="salesperson" {{ old('role') == 'salesperson' ? 'selected' : '' }}>Sales person</option> --}}
                        <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>Manager</option>
                    </select>
                    @error('role')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3">Sign Up</button>
            </form>

            {{-- <div class="text-center mb-3">OR</div> --}}

            {{-- <a href="/" class="flex items-center justify-center gap-2 bg-gray-300 text-black rounded-2 px-6 py-2 mb-4">
                <img src="/images/google-icon.png" alt="Google" class="w-5 h-5">
                <span>Sign in with Google</span>
            </a> --}}

            {{-- <a href="/" class="flex items-center justify-center gap-2 bg-gray-300 text-black rounded-2 px-6 py-2">
                <img src="/images/facebook-icon.png" alt="Google" class="w-5 h-5">
                <span>Sign in with Facebook</span>
            </a> --}}

            <div class="text-center mt-4 small">
                Already have an account? <a href="{{ route('login') }}" class="text-decoration-none">Sign In</a>
            </div>
        </div>

        <!-- Right Side: Image -->
        <div class="col-md-6 d-none d-md-block p-0">
            <img src="/images/yellow-car.jpg" alt="Showroom" class="img-fluid h-100 w-100" style="object-fit: cover;">
        </div>
    </div>
</div>
@endsection