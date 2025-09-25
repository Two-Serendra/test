<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\ResidentDetails;
use App\Models\FunctionRoomBooking;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form. 
     */
    public function edit(Request $request): View
    {
        $residences = DB::table('resident_details')
            ->where('email', $request->user()->email)
            ->select('unit_no', 'resident_type')
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        $allResidences = DB::table('resident_details')
            ->where('email', $request->user()->email)
            ->select('unit_no', 'resident_type')
            ->orderBy('created_at', 'desc')
            ->get();

        // Currently selected unit (from query string or default first unit)
        $selectedUnit = request('unit_no', $allResidences->first()->unit_no ?? null);

        // Fetch bookings only for the selected unit
        $bookings = FunctionRoomBooking::with('functionRoom')
            ->when($selectedUnit, function ($query) use ($selectedUnit) {
                $query->where('unit_no', $selectedUnit);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(5, ['*'], 'bookings_page');

        return view('profile.edit', [
            'user' => $request->user(),
            'residences' => $residences,  
            'allResidences' => $allResidences, 
            'bookings' => $bookings,
            'selectedUnit' => $selectedUnit,
        ]);
    }


    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
