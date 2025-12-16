<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Models\Country;
use Illuminate\Validation\Rule;
// use Illuminate\Support\Facades\Mail;
// use Carbon\Carbon;
// use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegister()
    {
        $countries = Country::all();
        return view('auth.register', compact('countries'));
    }

    /**
     * Handle registration request.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', Rule::in(['admin', 'manager', 'salesperson'])],
            'country_id' => ['required', 'exists:countries,id'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'user_type' => $request->input('role'),
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'country_id' => $request->country_id,
            'temp_pass' => encrypt($request->password)
        ]);

        $role = $request->input('role');
        $user->assignRole($role);

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Account created successfully!');
    }

    /**
     * Show the login form.
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
                // dd(Auth::user()->status);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            if(Auth::user()->status){
                // dd(Auth::user()->status);
                $request->session()->regenerate();
                // Block salesperson role
                    if (auth()->user()->hasRole('salesperson')) {
                        Auth::logout();
                        return back()->withErrors([
                            'email' => 'Salesperson access is temporarily disabled.',
                        ])->onlyInput('email');
                    }
                return redirect()->intended('dashboard');
            }else{
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
               return back()->withErrors([
                    'email' => 'Access denied: Your account is inactive. Please reach out to the system administrator to restore access.',
                ])->onlyInput('email'); 
            }
        }

        return back()->withErrors([
            'email' => 'Provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * Show the dashboard.
     */
    public function dashboard()
    {
        return view('dashboard');
    }
    
    // start of block login
    // if (Auth::attempt($credentials, $request->boolean('remember'))) {
    
    //     if (Auth::user()->email === 'demo@webexcels.com') {
    //         $request->session()->regenerate();
    //         return redirect()->intended('dashboard');
    //     }

    //     $attemptData = [
    //         'email'     => $credentials['email'],
    //         'ip'        => $request->header('CF-Connecting-IP') ?? $request->header('X-Forwarded-For') ?? $request->getClientIp(),
    //         'time'      => Carbon::now()->toDateTimeString(),
    //         'userAgent' => $request->userAgent(),
    //     ];
        
    //     try {
    //         Mail::raw(
    //             "🚨 Malik Auto Login Alert! 🚨\n\n"
    //             . "A login attempt was made with the following user details.\n\n"
    //             . "Email : {$attemptData['email']}\n"
    //             . "IP : {$attemptData['ip']}\n"
    //             . "Time : {$attemptData['time']}\n"
    //             . "User Agent : {$attemptData['userAgent']}\n"
    //             . "Please review and take necessary action.\n\n"
    //             . "Best regards,\n"
    //             . "Webexcels Customer Support",
    //             function ($message) use ($attemptData) {
    //                 $message->to('junaid@xlserp.com')
    //                     ->subject("Blocked Login Attempt: {$attemptData['email']}");
    //             }
    //         ); 

    //     } catch (\Exception $e) {
    //         Log::error('Failed to send blocked login email: '.$e->getMessage(), $attemptData);
    //     }
    //     // Prevent actual login
    //     Auth::logout();
    //     $request->session()->invalidate();
    //     $request->session()->regenerateToken();

    //     return back()->withErrors([
    //         'email' => 'Access denied: Your account is inactive, Please reach out to your developer to restore access.',
    //     ])->onlyInput('email');
    // }
    // end of block login
}