<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserRoleController extends Controller
{
    use ApiResponse;

    /**
     * Get all managers
     */
    public function managers()
    {
        try {
            $users = User::with('country')
                ->where('user_type', 'manager')
                ->get();

            return $this->success($users, 'Managers retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve managers', 500, $e->getMessage());
        }
    }

    /**
     * Get all salespersons
     */
    public function salespersons()
    {
        try {
            $users = User::with('country')
                ->where('user_type', 'salesperson')
                ->get();

            return $this->success($users, 'Salespersons retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve salespersons', 500, $e->getMessage());
        }
    }

    /**
     * Get single user by ID
     */
    public function show($id)
    {
        try {
            $user = User::with('country')->findOrFail($id);

            return $this->success($user, 'User retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('User not found', 404);
        }
    }

    /**
     * Update user
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $id,
                'phone' => 'required|numeric',
                'password' => 'nullable|string|min:6',
            ]);

            if ($validator->fails()) {
                return $this->error('Validation failed', 422, $validator->errors());
            }

            $user = User::findOrFail($id);

            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $user->phone = $request->input('phone');

            if ($request->filled('password')) {
                $user->temp_pass = encrypt($request->input('password'));
                $user->password = Hash::make($request->input('password'));
            }

            $user->save();

            return $this->success($user, 'User updated successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to update user', 500, $e->getMessage());
        }
    }

    /**
     * Toggle user status
     */
    public function toggleStatus($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->status = $user->status === 1 ? 0 : 1;
            $user->save();

            return $this->success(
                ['status' => $user->status],
                'User status updated successfully'
            );
        } catch (\Exception $e) {
            return $this->error('Failed to update status', 500, $e->getMessage());
        }
    }
}
