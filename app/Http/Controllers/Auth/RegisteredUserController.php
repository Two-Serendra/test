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
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\DB;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:users',
            ],
            'password' => ['required', 'confirmed', Password::defaults()],
            'resident_type' => ['required', 'array'],
            'resident_type.*' => ['required', Rule::in(['Owner', 'Tenant'])],
            'section' => ['required', 'array'],
            'section.*' => ['required', Rule::in(['Almond', 'Belize', 'Callery', 'Dolce', 'Aston', 'Red Oak', 'Meranti', 'Sequoia'])],
            'unit_no' => ['required', 'array'],
            'unit_no.*' => ['required', 'string', 'max:255'],
        ]);

        // ✅ Email check only for regular users (role_id = 0)
        $roleId = 0;
        $emailAllowed = DB::table('emails')->where('email', $request->email)->exists();
       
        if (!$emailAllowed && $roleId === 0) {
            return back()->withErrors(['email' => 'This email is not authorized to register.']);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $roleId,
            'is_active' => true,
        ]);

        foreach ($request->resident_type as $i => $type) {
            ResidentDetails::create([
                'user_id' => $user->id,
                'resident_type' => $type,
                'section' => $request->section[$i],
                'unit_no' => $request->unit_no[$i],
            ]);
        }

        event(new Registered($user));
        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }


}
