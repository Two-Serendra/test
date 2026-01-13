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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
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
        // $bookings = FunctionRoomBooking::with('functionRoom')
        //     ->when($selectedUnit, function ($query) use ($selectedUnit) {
        //         $query->where('unit_no', $selectedUnit);
        //     })
        //     ->orderBy('created_at', 'desc')
        //     ->paginate(5, ['*'], 'bookings_page');

        $bookings = FunctionRoomBooking::with('functionRoom')
            ->select(DB::raw('MIN(id) as id'), 'transaction_no')
            ->when($selectedUnit, function ($query) use ($selectedUnit) {
                $query->where('unit_no', $selectedUnit);
            })
            ->groupBy('transaction_no')
            ->orderByRaw('MIN(created_at) DESC')
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

    public function soa()
    {
        $residences = auth()->check()
            ? DB::table('resident_details')
                ->where('email', auth()->user()->email)
                ->select('id', 'unit_no', 'resident_type')
                ->get()
            : collect();

        return view('frontend.soa', compact('residences'));
    }


    // public function GenerateSoa(Request $request)
    // {
    //     $request->validate([
    //         'resident_id' => 'required',
    //         'year' => 'required',
    //         'month' => 'required',
    //         'billing_type' => 'required',
    //     ]);

    //     $residence = ResidentDetails::findOrFail($request->resident_id);

    //     $unit = $residence->unit_no;
    //     $year = $request->year;
    //     $month = str_pad($request->month, 2, '0', STR_PAD_LEFT);

    //     // Determine URL based on billing type
    //     if ($request->billing_type === 'Electricity') {
    //         $soaUrl = "http://192.168.194.113:3000/request-electricity/{$unit}/{$year}/{$month}";
    //     } elseif ($request->billing_type === 'Soa') {
    //         $soaUrl = "http://192.168.194.113:3000/request-soa/{$unit}/{$year}/{$month}";
    //     } else {
    //         return response()->json([
    //             'error' => 'Invalid billing type selected.'
    //         ], 400);
    //     }

    //     return response()->json([
    //         'soaUrl' => $soaUrl
    //     ]);
    // }


    public function GenerateSoa(Request $request)
    {
        $request->validate([
            'resident_id' => 'required',
            'year' => 'required',
            'month' => 'required',
            'billing_type' => 'required',
        ]);

        $residence = ResidentDetails::findOrFail($request->resident_id);

        $unit = $residence->unit_no;
        $year = $request->year;
        $month = str_pad($request->month, 2, '0', STR_PAD_LEFT);

        if ($request->billing_type === 'Electricity') {
            $apiUrl = "http://192.168.194.113:3000/request-electricity/{$unit}/{$year}/{$month}";
        } else {
            $apiUrl = "http://192.168.194.113:3000/request-soa/{$unit}/{$year}/{$month}";
        }

        // 🔑 Laravel talks to API
        $response = Http::timeout(90)->get($apiUrl);

        if ($response->failed()) {
            return response()->json(['error' => 'SOA generation failed'], 500);
        }

        // 🔐 Create temporary token
        $token = (string) Str::uuid();

        // Store PDF in cache for 10 minutes
        Cache::put("soa_pdf_$token", $response->body(), now()->addMinutes(10));

        return response()->json([
            'token' => $token
        ]);
    }

    public function view($token)
    {
        $pdf = Cache::get("soa_pdf_$token");

        if (!$pdf) {
            abort(404);
        }

        return response($pdf, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline');
    }

    
    public function requestElectricity()
    {

    }
}
