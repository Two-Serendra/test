<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FunctionRoomBooking;
use App\Models\AmenityBooking; // future
use App\Models\AusiBooking;
use App\Models\PestControlBooking;
use App\Models\GreaseTrapBooking;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        return view('backend.dashboard');
    }

    public function getServiceBookingStats()
    {
        $today = Carbon::today();

        $currentYear = now()->year;
        $previousYear = $currentYear - 1;

        /*
        |--------------------------------------------------------------------------
        | CURRENT YEAR BOOKINGS
        |--------------------------------------------------------------------------
        */

        $currentAusi = AusiBooking::whereYear('booking_date', $currentYear)->count();

        $currentPestControl = PestControlBooking::whereYear('booking_date', $currentYear)->count();

        $currentGreaseTrap = GreaseTrapBooking::whereYear('booking_date', $currentYear)->count();

        $currentBookings =
            $currentAusi +
            $currentPestControl +
            $currentGreaseTrap;


        /*
        |--------------------------------------------------------------------------
        | PREVIOUS YEAR BOOKINGS
        |--------------------------------------------------------------------------
        */

        $previousAusi = AusiBooking::whereYear('booking_date', $previousYear)->count();

        $previousPestControl = PestControlBooking::whereYear('booking_date', $previousYear)->count();

        $previousGreaseTrap = GreaseTrapBooking::whereYear('booking_date', $previousYear)->count();

        $previousBookings =
            $previousAusi +
            $previousPestControl +
            $previousGreaseTrap;


        /*
        |--------------------------------------------------------------------------
        | GROWTH
        |--------------------------------------------------------------------------
        */

        if ($previousBookings > 0) {

            $growth = round(
                (($currentBookings - $previousBookings) / $previousBookings) * 100,
                2
            );

        } else {

            $growth = $currentBookings > 0 ? 100 : 0;

        }


        /*
        |--------------------------------------------------------------------------
        | STATUS COUNTS
        |--------------------------------------------------------------------------
        */

        $scheduled =
            AusiBooking::where('booking_status', 1)->count() +
            PestControlBooking::where('booking_status', 1)->count() +
            GreaseTrapBooking::where('booking_status', 1)->count();

        $completed =
            AusiBooking::where('booking_status', 2)->count() +
            PestControlBooking::where('booking_status', 2)->count() +
            GreaseTrapBooking::where('booking_status', 2)->count();

        $cancelled =
            AusiBooking::where('booking_status', 0)->count() +
            PestControlBooking::where('booking_status', 0)->count() +
            GreaseTrapBooking::where('booking_status', 0)->count();


        /*
        |--------------------------------------------------------------------------
        | TODAY
        |--------------------------------------------------------------------------
        */

        $todayAusi = AusiBooking::whereDate('booking_date', $today)->count();

        $todayPestControl = PestControlBooking::whereDate('booking_date', $today)->count();

        $todayGreaseTrap = GreaseTrapBooking::whereDate('booking_date', $today)->count();

        $bookingToday =
            $todayAusi +
            $todayPestControl +
            $todayGreaseTrap;


        /*
        |--------------------------------------------------------------------------
        | UPCOMING
        |--------------------------------------------------------------------------
        */

        $upcomingAusi = AusiBooking::whereDate('booking_date', '>', $today)->count();

        $upcomingPestControl = PestControlBooking::whereDate('booking_date', '>', $today)->count();

        $upcomingGreaseTrap = GreaseTrapBooking::whereDate('booking_date', '>', $today)->count();

        $upcomingBookings =
            $upcomingAusi +
            $upcomingPestControl +
            $upcomingGreaseTrap;


        /*
        |--------------------------------------------------------------------------
        | EMERGENCY BOOKINGS
        |--------------------------------------------------------------------------
        */

        $emergencyAusi = AusiBooking::where('emergency', 1)->count();

        $emergencyPestControl = PestControlBooking::where('emergency', 1)->count();

        $emergencyGreaseTrap = GreaseTrapBooking::where('emergency', 1)->count();

        $emergencyBookings =
            $emergencyAusi +
            $emergencyPestControl +
            $emergencyGreaseTrap;


        /*
        |--------------------------------------------------------------------------
        | PENALTIES
        |--------------------------------------------------------------------------
        */

        $pestPenaltyCount = PestControlBooking::where('has_penalty', 1)->count();

        $greasePenaltyCount = GreaseTrapBooking::where('has_penalty', 1)->count();

        $penaltyCount =
            $pestPenaltyCount +
            $greasePenaltyCount;


        $pestPenaltyAmount = PestControlBooking::sum('penalty_amount');

        $greasePenaltyAmount = GreaseTrapBooking::sum('penalty_amount');

        $penaltyAmount =
            $pestPenaltyAmount +
            $greasePenaltyAmount;


        /*
        |--------------------------------------------------------------------------
        | MONTHLY CHART
        |--------------------------------------------------------------------------
        */

        $chartLabels = [
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'May',
            'Jun',
            'Jul',
            'Aug',
            'Sep',
            'Oct',
            'Nov',
            'Dec'
        ];

        $ausiValues = [];
        $pestControlValues = [];
        $greaseTrapValues = [];
        $totalValues = [];


        for ($month = 1; $month <= 12; $month++) {

            $ausi = AusiBooking::whereYear('booking_date', $currentYear)
                ->whereMonth('booking_date', $month)
                ->count();

            $pestControl = PestControlBooking::whereYear('booking_date', $currentYear)
                ->whereMonth('booking_date', $month)
                ->count();

            $greaseTrap = GreaseTrapBooking::whereYear('booking_date', $currentYear)
                ->whereMonth('booking_date', $month)
                ->count();


            $ausiValues[] = $ausi;
            $pestControlValues[] = $pestControl;
            $greaseTrapValues[] = $greaseTrap;

            $totalValues[] =
                $ausi +
                $pestControl +
                $greaseTrap;
        }


        /*
        |--------------------------------------------------------------------------
        | COMPLETION RATE
        |--------------------------------------------------------------------------
        */

        $ausiTotal = AusiBooking::count();
        $pestTotal = PestControlBooking::count();
        $greaseTotal = GreaseTrapBooking::count();


        $ausiCompleted = AusiBooking::where('booking_status', 2)->count();
        $pestCompleted = PestControlBooking::where('booking_status', 2)->count();
        $greaseCompleted = GreaseTrapBooking::where('booking_status', 2)->count();


        $ausiCompletionRate = $ausiTotal > 0
            ? round(($ausiCompleted / $ausiTotal) * 100, 1)
            : 0;

        $pestCompletionRate = $pestTotal > 0
            ? round(($pestCompleted / $pestTotal) * 100, 1)
            : 0;

        $greaseCompletionRate = $greaseTotal > 0
            ? round(($greaseCompleted / $greaseTotal) * 100, 1)
            : 0;


        /*
        |--------------------------------------------------------------------------
        | TODAY'S BOOKINGS
        |--------------------------------------------------------------------------
        */

        $todayBookings = collect();


        $ausiToday = AusiBooking::whereDate('booking_date', $today)
            ->orderBy('booking_time_slot')
            ->get();

        foreach ($ausiToday as $booking) {

            $todayBookings->push([
                'service' => 'AUSI',
                'service_key' => 'ausi',
                'transaction_no' => $booking->transaction_no,
                'unit_no' => $booking->unit_no,
                'name' => $booking->name,
                'booking_date' => $booking->booking_date,
                'booking_time_slot' => $booking->booking_time_slot,
                'booking_status' => $booking->booking_status,
            ]);
        }


        $pestToday = PestControlBooking::whereDate('booking_date', $today)
            ->orderBy('booking_time_slot')
            ->get();

        foreach ($pestToday as $booking) {

            $todayBookings->push([
                'service' => 'Pest Control',
                'service_key' => 'pest_control',
                'transaction_no' => $booking->transaction_no,
                'unit_no' => $booking->unit_no,
                'name' => $booking->name,
                'booking_date' => $booking->booking_date,
                'booking_time_slot' => $booking->booking_time_slot,
                'booking_status' => $booking->booking_status,
            ]);
        }


        $greaseToday = GreaseTrapBooking::whereDate('booking_date', $today)
            ->orderBy('booking_time_slot')
            ->get();

        foreach ($greaseToday as $booking) {

            $todayBookings->push([
                'service' => 'Grease Trap',
                'service_key' => 'grease_trap',
                'transaction_no' => $booking->transaction_no,
                'unit_no' => $booking->unit_no,
                'name' => $booking->name,
                'booking_date' => $booking->booking_date,
                'booking_time_slot' => $booking->booking_time_slot,
                'booking_status' => $booking->booking_status,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | UPCOMING BOOKINGS
        |--------------------------------------------------------------------------
        */

        $upcomingList = collect();


        $upcomingAusiList = AusiBooking::whereDate('booking_date', '>', $today)
            ->where('booking_status', 1)
            ->orderBy('booking_date')
            ->orderBy('booking_time_slot')
            ->limit(10)
            ->get();

        foreach ($upcomingAusiList as $booking) {

            $upcomingList->push([
                'service' => 'AUSI',
                'service_key' => 'ausi',
                'transaction_no' => $booking->transaction_no,
                'unit_no' => $booking->unit_no,
                'name' => $booking->name,
                'booking_date' => $booking->booking_date,
                'booking_time_slot' => $booking->booking_time_slot,
                'booking_status' => $booking->booking_status,
            ]);
        }


        $upcomingPestList = PestControlBooking::whereDate('booking_date', '>', $today)
            ->where('booking_status', 1)
            ->orderBy('booking_date')
            ->orderBy('booking_time_slot')
            ->limit(10)
            ->get();

        foreach ($upcomingPestList as $booking) {

            $upcomingList->push([
                'service' => 'Pest Control',
                'service_key' => 'pest_control',
                'transaction_no' => $booking->transaction_no,
                'unit_no' => $booking->unit_no,
                'name' => $booking->name,
                'booking_date' => $booking->booking_date,
                'booking_time_slot' => $booking->booking_time_slot,
                'booking_status' => $booking->booking_status,
            ]);
        }


        $upcomingGreaseList = GreaseTrapBooking::whereDate('booking_date', '>', $today)
            ->where('booking_status', 1)
            ->orderBy('booking_date')
            ->orderBy('booking_time_slot')
            ->limit(10)
            ->get();

        foreach ($upcomingGreaseList as $booking) {

            $upcomingList->push([
                'service' => 'Grease Trap',
                'service_key' => 'grease_trap',
                'transaction_no' => $booking->transaction_no,
                'unit_no' => $booking->unit_no,
                'name' => $booking->name,
                'booking_date' => $booking->booking_date,
                'booking_time_slot' => $booking->booking_time_slot,
                'booking_status' => $booking->booking_status,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | SORT UPCOMING
        |--------------------------------------------------------------------------
        */

        $upcomingList = $upcomingList
            ->sortBy(function ($booking) {

                return $booking['booking_date'] . ' ' .
                    $booking['booking_time_slot'];

            })
            ->take(10)
            ->values();


        /*
        |--------------------------------------------------------------------------
        | RESPONSE
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'current_year' => $currentYear,
            'previous_year' => $previousYear,

            'years' => [
                $currentYear,
                $previousYear
            ],

            'current_bookings' => $currentBookings,
            'previous_bookings' => $previousBookings,

            'growth' => $growth,

            'scheduled' => $scheduled,
            'completed' => $completed,
            'cancelled' => $cancelled,

            'bookingToday' => $bookingToday,
            'upcomingBookings' => $upcomingBookings,

            'emergencyBookings' => $emergencyBookings,

            'penalties' => [
                'count' => $penaltyCount,
                'amount' => $penaltyAmount,
            ],

            'services' => [

                'ausi' => $currentAusi,

                'pest_control' => $currentPestControl,

                'grease_trap' => $currentGreaseTrap,

            ],

            'completion_rate' => [

                'ausi' => $ausiCompletionRate,

                'pest_control' => $pestCompletionRate,

                'grease_trap' => $greaseCompletionRate,

            ],

            'status_data' => [

                'scheduled' => $scheduled,

                'completed' => $completed,

                'cancelled' => $cancelled,

            ],

            'service_data' => [

                'ausi' => $currentAusi,

                'pest_control' => $currentPestControl,

                'grease_trap' => $currentGreaseTrap,

            ],

            'today_bookings' => $todayBookings->values(),

            'upcoming_bookings' => $upcomingList,

            'chart_data' => [

                'labels' => $chartLabels,

                'ausi' => $ausiValues,

                'pest_control' => $pestControlValues,

                'grease_trap' => $greaseTrapValues,

                'total' => $totalValues,

            ]

        ]);
    }

}
