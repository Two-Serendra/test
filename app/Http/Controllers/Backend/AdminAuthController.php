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
            // Ensure the logged in user has an admin role
            $user = Auth::guard('admin')->user();
            if ($user->role_id >= 1) {
                return redirect()->intended(route('admin.dashboard'));
            } else {
                Auth::guard('admin')->logout();
                return back()->withErrors(['email' => 'Access denied.']);
            }
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