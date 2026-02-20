<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\PestControlBooking;
use Illuminate\Http\Request;
use App\Models\ActivityBooking;
use App\Models\FunctionRoomBooking;
use App\Models\GreaseTrapBooking;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class ResidentBookingHistoryController extends Controller
{
    // public function ResidentsBookingHistory(Request $request)
    // {
    //     $allResidences = DB::table('resident_details')
    //         ->where('email', $request->user()->email)
    //         ->select('unit_no', 'resident_type')
    //         ->orderBy('created_at', 'desc')
    //         ->get();

    //     $selectedUnit = $request->unit_no ?? $allResidences->first()->unit_no ?? null;
    //     $bookingType = $request->booking_type ?? 'function_room';

    //     if ($bookingType === 'function_room') {
    //         $bookings = FunctionRoomBooking::with('functionRoom')
    //             ->when($selectedUnit, fn($q) => $q->where('unit_no', $selectedUnit))
    //             ->orderBy('function_room_booking_date', 'desc')
    //             ->paginate(5, ['*'], 'bookings_page');

    //         if ($request->ajax()) {
    //             return view(
    //                 'frontend.resident-function-room-booking-table',
    //                 compact('bookings', 'selectedUnit', 'bookingType')
    //             )->render();
    //         }

    //     } elseif ($bookingType === 'amenity') {
    //         $bookings = ActivityBooking::with('activity')
    //             ->when($selectedUnit, fn($q) => $q->where('unit', $selectedUnit))
    //             ->orderBy('booking_date', 'desc')
    //             ->paginate(5, ['*'], 'bookings_page');

    //         if ($request->ajax()) {
    //             return view(
    //                 'frontend.resident-activity-booking-table',
    //                 compact('bookings', 'selectedUnit', 'bookingType')
    //             )->render();
    //         }
    //     } else {

    //         $bookings = collect();
    //     }

    //     return view('frontend.resident-booking-history', [
    //         'user' => $request->user(),
    //         'allResidences' => $allResidences,
    //         'bookings' => $bookings,
    //         'selectedUnit' => $selectedUnit,
    //         'bookingType' => $bookingType,
    //     ]);
    // }

    public function ResidentsBookingHistory(Request $request)
    {
        $allResidences = DB::table('resident_details')
            ->where('email', $request->user()->email)
            ->select('unit_no', 'resident_type')
            ->orderBy('created_at', 'desc')
            ->get();

        $selectedUnit = $request->unit_no ?? $allResidences->first()->unit_no ?? null;
        $bookingType = $request->booking_type ?? 'function_room';
        $bookings = collect();

        if ($bookingType === 'function_room') {

            $bookings = FunctionRoomBooking::with('functionRoom')
                ->when($selectedUnit, fn($q) => $q->where('unit_no', $selectedUnit))
                ->latest('function_room_booking_date')
                ->paginate(5)
                ->withQueryString();

            if ($request->ajax()) {
                return view(
                    'frontend.resident-function-room-booking-table',
                    compact('bookings', 'selectedUnit', 'bookingType')
                )->render();
            }

        } elseif ($bookingType === 'amenity') {

            $bookings = ActivityBooking::with('activity')
                ->when($selectedUnit, fn($q) => $q->where('unit', $selectedUnit))
                ->latest('booking_date')
                ->paginate(5)
                ->withQueryString();

            if ($request->ajax()) {
                return view(
                    'frontend.resident-activity-booking-table',
                    compact('bookings', 'selectedUnit', 'bookingType')
                )->render();
            }

        } elseif ($bookingType === 'grease_trap') {

            $bookings = GreaseTrapBooking::when($selectedUnit, fn($q) => $q->where('unit_no', $selectedUnit))
                ->orderBy('booking_date', 'desc')
                ->paginate(5)
                ->withQueryString();

            if ($request->ajax()) {
                return view(
                    'frontend.resident-grease-trap-booking-table',
                    compact('bookings', 'selectedUnit', 'bookingType')
                )->render();
            }
        } elseif ($bookingType === 'pest_control') {

            $bookings = PestControlBooking::when($selectedUnit, fn($q) => $q->where('unit_no', $selectedUnit))
                ->orderBy('booking_date', 'desc')
                ->paginate(5)
                ->withQueryString();

            if ($request->ajax()) {
                return view(
                    'frontend.resident-pest-control-booking-table',
                    compact('bookings', 'selectedUnit', 'bookingType')
                )->render();
            }
        }
        return view('frontend.resident-booking-history', [
            'user' => $request->user(),
            'allResidences' => $allResidences,
            'bookings' => $bookings,
            'selectedUnit' => $selectedUnit,
            'bookingType' => $bookingType,
        ]);
    }



}
