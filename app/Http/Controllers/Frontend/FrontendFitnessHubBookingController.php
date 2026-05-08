<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FitnessHub;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FrontendFitnessHubBookingController extends Controller
{
    public function fullDetailsFitnessHub($type, $fitness_hub_id)
    {
        $fitness_hub = FitnessHub::findOrFail($fitness_hub_id);
        $all_fitness_hubs = FitnessHub::all();

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
            'residences',
            'all_fitness_hubs'
        ));
    }

    public function fetchDateBlockingFitnessHubUser(Request $request)
    {
        $fitnessHubId = $request->fitness_hub_id;

        $blocks = DB::table('fitness_hub_date_blockings') // your table name
            ->where('fitness_hub_id', $fitnessHubId)
            ->where('blocking_status', 1) // active only
            ->get();

        $disabledDates = [];

        foreach ($blocks as $block) {
            $start = Carbon::parse($block->date_blocking_start);
            $end = Carbon::parse($block->date_blocking_end);

            while ($start->lte($end)) {
                $disabledDates[] = $start->format('Y-m-d');
                $start->addDay();
            }
        }

        return response()->json($disabledDates);
    }
}
