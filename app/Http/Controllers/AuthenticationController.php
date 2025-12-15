<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticationController extends Controller
{
    public function showLoginForm()
    {
        return view('authentication.login');
    }

    protected function authenticated(Request $request, $user)
    {
        if ($user->user_type == 'admin') {
            return redirect()->intended('/admin/dashboard');
        }

        return redirect()->intended('/code-problems'); // Default user dashboard
    }

    public function loginUser(Request $request)
    {
        $credentials = $request->only('username', 'password');

        if (Auth::attempt(['name' => $credentials['username'], 'password' => $credentials['password']])) {
            $request->session()->regenerate();
            $user = Auth::user();
            // login the user base by its user type
            return $this->authenticated($request, $user);
            // return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'username' => 'The provided username do not match our records.',
            'password' => 'The provided password do not match our records.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
