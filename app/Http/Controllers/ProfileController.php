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
use Illuminate\Support\Facades\Log;
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

    //     $apiBase = rtrim(config('services.ebt.base_url'), '/');

    //     $endpoint = $request->billing_type === 'Electricity'
    //         ? "/request-electricity/{$unit}/{$year}/{$month}"
    //         : "/request-soa/{$unit}/{$year}/{$month}";

    //     $apiUrl = $apiBase . $endpoint;

    //     Log::info('GenerateSoa called', ['api_url' => $apiUrl]);

    //     try {
    //         $response = Http::timeout(90)
    //             ->withToken(config('services.ebt.token')) // 🔐 secure
    //             ->withBasicAuth(
    //                 config('services.ebt.username'),
    //                 config('services.ebt.password')
    //             )
    //             ->withHeaders([
    //                 'X-API-KEY' => config('services.ebt.api_key'),
    //             ])
    //             ->get($apiUrl);

    //         if (!$response->successful()) {
    //             Log::error('SOA API error', [
    //                 'status' => $response->status(),
    //                 'body' => $response->body(),
    //             ]);

    //             return response()->json([
    //                 'error' => 'SOA API returned an error'
    //             ], 502);
    //         }

    //     } catch (\Exception $e) {
    //         Log::error('SOA generation failed', [
    //             'message' => $e->getMessage(),
    //             'api_url' => $apiUrl
    //         ]);

    //         return response()->json([
    //             'error' => 'SOA generation failed',
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }

    //     $token = (string) Str::uuid();
    //     Cache::put("soa_pdf_$token", $response->body(), now()->addMinutes(10));

    //     return response()->json([
    //         'token' => $token
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

        $apiBase = rtrim(config('services.ebt.base_url'), '/');

        $apiUrl = $apiBase . ($request->billing_type === 'Electricity'
            ? "/request-electricity/{$unit}/{$year}/{$month}"
            : "/request-soa/{$unit}/{$year}/{$month}");



        Log::info("GenerateSoa called", ['api_url' => $apiUrl]);

        try {
            $response = Http::timeout(90)
                ->retry(2, 3000)
                ->withToken(config('services.ebt.api_key'))
                ->get($apiUrl);

            if (!$response->successful()) {
                Log::error('EBT API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return response()->json([
                    'error' => 'SOA service unavailable'
                ], 502);
            }

        } catch (\Exception $e) {
            Log::error("SOA generation failed", [
                'message' => $e->getMessage(),
                'api_url' => $apiUrl
            ]);

            return response()->json([
                'error' => 'SOA generation failed',
                'message' => $e->getMessage()
            ], 500);
        }
        $token = (string) Str::uuid();
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
