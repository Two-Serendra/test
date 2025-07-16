<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // dd('This should never run');
        // $request->authenticate();

        // $request->session()->regenerate();

        // return redirect()->intended(RouteServiceProvider::HOME);

        $request->authenticate(); // Performs credential check

        $user = Auth::user(); // Get the authenticated user

        // ✅ Check if regular user
        if ($user->role_id === 0) {
            $emailExists = \DB::table('emails')->where('email', $user->email)->exists();

            if (!$emailExists) {
                Auth::logout(); // Logout the user immediately

                return back()->withErrors([
                    'email' => 'Your account is no longer authorized to log in.',
                ]);
            }
        }

        // ✅ Optional: Check is_active flag as well
        if ($user->is_active === 0) {
            Auth::logout();

            return back()->withErrors([
                'email' => 'Your account has been deactivated.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
