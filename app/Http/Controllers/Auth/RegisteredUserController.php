<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ResidentDetails;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\ResidentInviteMail;
use Illuminate\Support\Facades\DB;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    // public function store(Request $request): RedirectResponse
    // {
    //     $email = strtolower(trim($request->email));
    //     $request->merge(['email' => $email]);

    //     $request->validate([
    //         'name' => ['required', 'string', 'max:255'],
    //         'email' => ['required', 'email', 'max:255', 'unique:users,email'],
    //         'password' => ['required', 'confirmed', Password::defaults()],
    //         'invite_token' => ['required', 'string'],
    //     ]);

    //     $valid = ResidentDetails::whereRaw('LOWER(email) = ?', [$email])
    //         ->where('invite_token', $request->invite_token)
    //         ->exists();

    //     if (!$valid) {
    //         throw ValidationException::withMessages([
    //             'invite_token' => 'Invalid token.',
    //         ]);
    //     }

    //     $user = User::create([
    //         'name' => $request->name,
    //         'email' => $email,
    //         'password' => Hash::make($request->password),
    //         'role_id' => 0,
    //         'is_active' => true,
    //     ]);

    //     event(new Registered($user));

    //     return redirect()->route('verification.notice');
    // }


    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed'],
            'invite_token' => ['required', 'string'],
        ]);

        $email = strtolower(trim($request->email));

        $resident = DB::table('resident_details')
            ->whereRaw('LOWER(email) = ?', [$email])
            ->where('invite_token', $request->invite_token)
            ->first();

        if (!$resident) {
            return back()->withErrors([
                'invite_token' => 'Expired or invalid token. Please request a new one.',
            ])->withInput();
        }

        if ($resident->token_expires_at && now()->greaterThan($resident->token_expires_at)) {
            return back()->withErrors([
                'invite_token' => 'Token has expired. Please request a new one.',
            ])->withInput();
        }

      
        if (User::where('email', $email)->exists()) {
            return back()->withErrors([
                'email' => 'Account already exists. Please login instead.',
            ])->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $email,
            'password' => bcrypt($request->password),
            'role_id' => 0,
            'is_active' => 1,
        ]);

        $user->markEmailAsVerified();

        DB::table('resident_details')
            ->where('email', $email)
            ->update([
                'invite_token' => null,
                'token_expires_at' => null,
            ]);

        Auth::login($user);

        return redirect()->intended('/');
    }

    public function sendToken(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email']
        ]);

        $email = strtolower(trim($request->email));

        $resident = DB::table('resident_details')
            ->whereRaw('LOWER(email) = ?', [$email])
            ->orderByDesc('last_token_sent_at')
            ->first();

        if (!$resident) {
            return response()->json([
                'message' => 'Email is not registered.'
            ], 404);
        }

        // ONLY cooldown check
        if (
            $resident->last_token_sent_at &&
            now()->diffInSeconds($resident->last_token_sent_at) < 90
        ) {
            $remaining = 90 - now()->diffInSeconds($resident->last_token_sent_at);

            return response()->json([
                'message' => "Please wait {$remaining}s before requesting another token.",
                'retry_after' => $remaining
            ], 429);
        }

        $token = Str::upper(Str::random(10));

        DB::table('resident_details')
            ->whereRaw('LOWER(email) = ?', [$email])
            ->update([
                'invite_token' => $token,
                'last_token_sent_at' => now(),
                'token_expires_at' => now()->addMinutes(10),
            ]);

        Mail::to($email)->queue(new ResidentInviteMail($token));

        return response()->json([
            'message' => 'Token sent successfully to the registered email.',
            'retry_after' => 90
        ]);
    }
}
