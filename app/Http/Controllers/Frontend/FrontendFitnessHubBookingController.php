<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FitnessHub;
use Illuminate\Support\Facades\DB;

class FrontendFitnessHubBookingController extends Controller
{
    public function fullDetailsFitnessHub($type, $fitness_hub_id)
    {
        $fitness_hub = FitnessHub::findOrFail($fitness_hub_id);

        $suggestions = FitnessHub::where('fitness_hub_status', 1)
            ->where('id', '!=', $fitness_hub->id)
            ->inRandomOrder()
            ->take(4)
            ->get();

        $residences = auth()->check()
            ? DB::table('resident_details')
                ->where('email', auth()->user()->email)
                ->select('id', 'unit_no', 'resident_type')
                ->get()
            : collect();

        return view('frontend.booking-full-details-fitness-hub', compact(
            'fitness_hub',
            'suggestions',
            'residences'
        ));
    }
}
