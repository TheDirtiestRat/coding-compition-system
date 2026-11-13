<?php

namespace App\Http\Controllers;

use App\Models\Candidates;
use App\Models\Events;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthenticationController extends Controller
{
    public function showLoginForm()
    {
        return view('authentication.login');
    }

    public function showRegistrationForm()
    {
        $registrationOpen = DB::table('options')
            ->where('option_name', 'registration_open')
            ->value('option_value');

        if ($registrationOpen !== '1') {
            return view('authentication.registration-closed');
        }

        $activeEvent = Events::where('isActive', true)->first();

        if (!$activeEvent) {
            return view('authentication.registration-closed');
        }

        return view('authentication.register');
    }

    public function register(Request $request)
    {
        $registrationOpen = DB::table('options')
            ->where('option_name', 'registration_open')
            ->value('option_value');

        if ($registrationOpen !== '1') {
            return redirect('/register')->with('error', 'Registration is currently closed.');
        }

        $activeEvent = Events::where('isActive', true)->first();

        if (!$activeEvent) {
            return redirect('/register')->with('error', 'No active event available for registration.');
        }

        $validated = $request->validate([
            'fullname' => 'required|string|max:255',
            'username' => 'required|string|max:128|unique:users,name',
            'password' => 'required|string|min:4',
        ]);

        try {
            $user = DB::transaction(function () use ($validated, $activeEvent) {
                $user = User::create([
                    'name' => trim($validated['username']),
                    'email' => trim(strtolower($validated['username'])) . '@hungergame.com',
                    'password' => Hash::make($validated['password']),
                    'user_type' => 'user',
                ]);

                Candidates::create([
                    'event_id' => $activeEvent->id,
                    'fullname' => $validated['fullname'],
                    'group' => 'none',
                    'user_id' => $user->id,
                ]);

                return $user;
            });

            Auth::login($user);

            return redirect('/code-problems');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Registration failed: ' . $e->getMessage()]);
        }
    }

    protected function authenticated(Request $request, $user)
    {
        if ($user->user_type === 'admin') {
            return redirect()->intended('/admin/dashboard');
        }

        return redirect()->intended('/code-problems');
    }

    public function loginUser(Request $request)
    {
        $credentials = $request->only('username', 'password');

        if (Auth::attempt(['name' => $credentials['username'], 'password' => $credentials['password']])) {
            $request->session()->regenerate();
            $user = Auth::user();

            return $this->authenticated($request, $user);
        }

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
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
