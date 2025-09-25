<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FunctionRoomBooking;
use App\Models\AmenityBooking; // future
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $monthlyBookings = FunctionRoomBooking::selectRaw('MONTH(function_room_booking_date) as month, COUNT(*) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // 📊 Stats
        $todayBookings = FunctionRoomBooking::whereDate('function_room_booking_date', Carbon::today())->count();
        $upcomingBookings = FunctionRoomBooking::whereDate('function_room_booking_date', '>', Carbon::today())->count();
        $totalBookings = FunctionRoomBooking::count();

        return view('backend.dashboard', compact('monthlyBookings', 'todayBookings', 'upcomingBookings', 'totalBookings'));
    }

    public function getFunctionRoomBookingStats()
    {
        $currentYear = now()->year;
        $previousYear = $currentYear - 1;

        $currentBookings = FunctionRoomBooking::whereYear('created_at', $currentYear)->count();
        $previousBookings = FunctionRoomBooking::whereYear('created_at', $previousYear)->count();
        $growth = $previousBookings > 0 ? round((($currentBookings - $previousBookings) / $previousBookings) * 100, 2) : 100;

        $pending = FunctionRoomBooking::where('booking_status', 0)->count();
        $approved = FunctionRoomBooking::where('booking_status', 1)->count();
        $cancelled = FunctionRoomBooking::where('booking_status', 2)->count();
        $bookingToday = FunctionRoomBooking::where('function_room_booking_date', Carbon::today())->count();

        $chartLabels = range(1, 12);
        $chartValues = [];
        foreach ($chartLabels as $month) {
            $chartValues[] = FunctionRoomBooking::whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $month)
                ->count();
        }

        return response()->json([
            'current_year' => $currentYear,
            'previous_year' => $previousYear,
            'years' => [$currentYear, $previousYear],
            'current_bookings' => $currentBookings,
            'previous_bookings' => $previousBookings,
            'growth' => $growth,
            'pending' => $pending,
            'approved' => $approved,
            'cancelled' => $cancelled,
            'bookingToday' => $bookingToday,
            'chart_data' => [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                'values' => $chartValues
            ]
        ]);
    }


}
