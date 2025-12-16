<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Traits\ApiResponse; // import the trait

class AuthController extends Controller
{
    use ApiResponse; // use the trait

    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'user_type' => $request->role,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'country_id' => $request->country_id,
            'temp_pass' => encrypt($request->password),
        ]);

        $user->assignRole($request->role);

        $token = $user->createToken('api_token')->plainTextToken;

        return $this->success([
            'user' => new UserResource($user),
            'token' => $token
        ], 'Account created successfully!');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return $this->error('Invalid credentials', 401);
        }

        $user = Auth::user();

        if (!$user->status) {
            Auth::logout();
            return $this->error('Account inactive. Contact admin.', 403);
        }

        if ($user->hasRole('salesperson')) {
            Auth::logout();
            return $this->error('Salesperson access is temporarily disabled.', 403);
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return $this->success([
            'user' => new UserResource($user),
            'token' => $token
        ], 'Login successful');
    }
    //funtion forlogged in user
    public function user()
    {
        $user = Auth::user();
        return $this->success(new UserResource($user), 'User retrieved successfully.');
    }
    public function logout()
    {
        $user = Auth::user();
        $user->currentAccessToken()->delete();

        return $this->success(null, 'Logged out successfully.');
    }
}
