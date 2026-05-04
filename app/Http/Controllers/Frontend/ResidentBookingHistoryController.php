<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\PestControlBooking;
use Illuminate\Http\Request;
use App\Models\ActivityBooking;
use App\Models\FunctionRoomBooking;
use App\Models\GreaseTrapBooking;
use App\Models\FitnessHubBooking;
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


        $allowedUnits = $allResidences->pluck('unit_no')
            ->map(fn($u) => strtoupper($u))
            ->toArray();

        abort_unless(!empty($allowedUnits), 403, 'No residence assigned to this account.');

        $selectedUnit = $allowedUnits[0] ?? null;

        if ($request->filled('unit_no')) {
            $requestedUnit = strtoupper($request->unit_no);

            if (!in_array($requestedUnit, $allowedUnits, true)) {
                abort(403, 'Unauthorized unit access.');
            }

            $selectedUnit = $requestedUnit;
        }

        $bookingType = $request->booking_type ?? 'function_room';
        $bookings = collect();

        $allowedTypes = ['function_room', 'amenity', 'fitness_hub', 'grease_trap', 'pest_control'];

        abort_unless(in_array($bookingType, $allowedTypes, true), 400, 'Invalid booking type.');

        if ($bookingType === 'function_room') {

            $bookings = FunctionRoomBooking::with('functionRoom')
                ->where('unit_no', $selectedUnit)
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
                ->where('unit', $selectedUnit)
                ->whereIn('id', function ($query) use ($selectedUnit) {
                    $query->selectRaw('MIN(id)')
                        ->from('activity_bookings')
                        ->where('unit', $selectedUnit)
                        ->groupBy('transaction_no');
                })
                ->latest('booking_date')
                ->paginate(5)
                ->withQueryString();


            if ($request->ajax()) {
                return view(
                    'frontend.resident-activity-booking-table',
                    compact('bookings', 'selectedUnit', 'bookingType')
                )->render();
            }

        } elseif ($bookingType === 'fitness_hub') {
            abort_unless($selectedUnit, 403, 'No valid unit assigned.');

            $bookings = FitnessHubBooking::with('fitnessHub')
                ->where('unit', $selectedUnit)
                ->latest('booking_date')
                ->paginate(5)
                ->withQueryString();

            if ($request->ajax()) {
                return view(
                    'frontend.resident-fitness-hub-booking-table',
                    compact('bookings', 'selectedUnit', 'bookingType')
                )->render();
            }

        } elseif ($bookingType === 'grease_trap') {

            $bookings = GreaseTrapBooking::with(['cancelledBy'])
                ->where('unit_no', $selectedUnit)
                ->orderBy('booking_date', 'desc')
                ->orderBy('booking_time_slot', 'desc')
                ->paginate(5)
                ->withQueryString();

            if ($request->ajax()) {
                return view(
                    'frontend.resident-grease-trap-booking-table',
                    compact('bookings', 'selectedUnit', 'bookingType')
                )->render();
            }
        } elseif ($bookingType === 'pest_control') {

            $bookings = PestControlBooking::where('unit_no', $selectedUnit)
                ->orderBy('booking_date', 'desc')
                ->orderBy('booking_time_slot', 'desc')
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
