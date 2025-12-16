<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserRoleController extends Controller
{
    public function managers()
    {
        $users = User::with('country')->where('user_type', 'manager')->get();
        
        // dd($users->casts()->toArray());
        return view('dashboards.users.managers', compact('users'));
    }

    public function salespersons()
    {
        $users = User::with('country')->where('user_type', 'salesperson')->get();
        return view('dashboards.users.salespersons', compact('users'));
    }
    
    public function edit($id)
    {
        $manager = User::findOrFail($id); // or Manager::findOrFail($id) if using a separate model
    
        return view('dashboards.users.managers-edit', [
            'manager' => $manager,
        ]);
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'required|numeric',
            'password' => 'nullable|string|min:6',
        ]);
    
        $manager = User::findOrFail($id);
    
        $manager->name = $request->input('name');
        $manager->email = $request->input('email');
        $manager->phone = $request->input('phone');
        if($request->input('password')){
            $manager->temp_pass = encrypt($request->input('password'));
            $manager->password = Hash::make($request->input('password'));
        }
    
        $manager->save();
    
        return redirect()->route('users.managers')->with('success', 'Manager updated successfully.');
    }

    public function managerToggleStatus(User $user)
    {
        $user->status = $user->status === 1 ? 0 : 1;
        $user->save();

        return redirect()->back()->with('success', 'Account status updated.');
    }

}
