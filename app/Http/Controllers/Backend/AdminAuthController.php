<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('backend.admin-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('admin')->attempt($credentials)) {
            $user = Auth::guard('admin')->user();

            // Route mapping for each role
            $roleRoutes = [
                1 => 'admin.dashboard',
                2 => 'admin.show.function.room.bookings',
                3 => 'admin.booking.ausi',
                4 => 'engineering.dashboard',
                5 => 'admin.show.function.room.bookings',
                6 => 'admin.booking.activities',
                7 => 'admin.grease.trap.booking',
                8 => 'admin.show.function.room.bookings',
                9 => 'admin.grease.trap.booking',
                10 => 'admin.show.events',
            ];

            // If role_id is 0 or not in the map, deny access
            if (!isset($roleRoutes[$user->role_id])) {
                Auth::guard('admin')->logout();
                return back()->withErrors(['email' => 'Access denied.']);
            }

            // Redirect based on role_id
            return redirect()->intended(route($roleRoutes[$user->role_id]));
        }

        return back()->withErrors(['email' => 'Invalid credentials.']);
    }



    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}