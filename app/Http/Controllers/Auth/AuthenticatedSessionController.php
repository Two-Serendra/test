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
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();

        if ($user->role_id === 0) {
            $emailExists = \DB::table('resident_details')->where('email', $user->email)->exists();

            if (!$emailExists) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Your account is no longer authorized to log in.',
                ]);
            }
        }

        if ($user->is_active === 0) {
            Auth::logout();
            return back()->withErrors([
                'email' => 'Your account has been deactivated.',
            ]);
        }

        $request->session()->regenerate();

        // 🔑 This will redirect back to where the guest was before login,
        // or fallback to `/` if nothing is stored
        return redirect()->intended('/');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
