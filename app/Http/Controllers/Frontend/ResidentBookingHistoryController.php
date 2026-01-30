<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ActivityBooking;
use App\Models\FunctionRoomBooking;
use App\Models\Amenity;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class ResidentBookingHistoryController extends Controller
{
    public function ResidentsBookingHistory(Request $request)
    {
        $allResidences = DB::table('resident_details')
            ->where('email', $request->user()->email)
            ->select('unit_no', 'resident_type')
            ->orderBy('created_at', 'desc')
            ->get();

        $selectedUnit = $request->unit_no ?? $allResidences->first()->unit_no ?? null;
        $bookingType = $request->booking_type ?? 'function_room';

        // Determine bookings based on type
        if ($bookingType === 'function_room') {
            $bookings = FunctionRoomBooking::with('functionRoom')
                ->when($selectedUnit, fn($q) => $q->where('unit_no', $selectedUnit))
                ->orderBy('function_room_booking_date', 'desc')
                ->paginate(5, ['*'], 'bookings_page');

            // AJAX request returns only table
            if ($request->ajax()) {
                return view(
                    'frontend.resident-function-room-booking-table',
                    compact('bookings', 'selectedUnit', 'bookingType')
                )->render();
            }

        } elseif ($bookingType === 'amenity') {
            $bookings = ActivityBooking::with('activity')
                ->when($selectedUnit, fn($q) => $q->where('unit', $selectedUnit))
                ->orderBy('booking_date', 'desc')
                ->paginate(5, ['*'], 'bookings_page');

            if ($request->ajax()) {
                return view(
                    'frontend.resident-activity-booking-table',
                    compact('bookings', 'selectedUnit', 'bookingType')
                )->render();
            }
        } else {
            // If you have more booking types later, handle here
            $bookings = collect();
        }

        // Default page load
        return view('frontend.resident-booking-history', [
            'user' => $request->user(),
            'allResidences' => $allResidences,
            'bookings' => $bookings,
            'selectedUnit' => $selectedUnit,
            'bookingType' => $bookingType,
        ]);
    }




}
