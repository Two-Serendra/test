<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AusiBooking;
use App\Models\ResidentDetails;

class MobileAppController extends Controller
{
    public function ausiBookingUserMobile(Request $request)
    {
        return view('mobile-app.ausi-booking-mobile');
    }

    public function getBookedSlotsAusiMobile(Request $request)
    {
        $unitName = trim($request->unit_name);
        if (!$unitName) {
            return response()->json([
                'message' => 'unit_name is required'
            ], 422);
        }
        $map = [
            "Almond" => "A",
            "Belize" => "B",
            "Callery" => "C",
            "Dolce" => "D",
            "Encino" => "E",
            "Aston" => "F",
            "ReadOak" => "G",
            "Meranti" => "H",
            "Sequoia" => "I",
        ];

        $parts = explode(' ', $unitName);

        if (count($parts) !== 2) {
            return response()->json([
                'message' => 'Invalid unit format',
                'unit' => $unitName
            ], 422);
        }

        [$tower, $number] = $parts;

        $towerLetter = $map[$tower] ?? null;

        if (!$towerLetter) {
            return response()->json([
                'message' => 'Unknown tower',
            ], 422);
        }
        $unitNo = $number . $towerLetter;

        $resident = ResidentDetails::where('unit_no', $unitNo)->first();

        if (!$resident) {
            return response()->json([
                'message' => 'Resident not found',
                'unit_no' => $unitNo
            ], 404);
        }

        $areaLetter = preg_replace('/[^A-Z]/', '', $resident->unit_no);

        $lowrise = ['A', 'B', 'C', 'D', 'E'];
        $highrise = ['F', 'G', 'H', 'I'];

        $userGroup = in_array($areaLetter, $lowrise) ? 'lowrise' : 'highrise';

        $bookings = AusiBooking::whereDate('booking_date', $request->date)
            ->where('booking_status', 1)
            ->get(['booking_time_slot', 'unit_area']);

        $slotStatus = [];

        foreach ($bookings as $b) {
            $slot = $b->booking_time_slot;
            $area = $b->unit_area;

            if (!isset($slotStatus[$slot])) {
                $slotStatus[$slot] = [
                    'lowrise' => false,
                    'highrise' => false
                ];
            }

            if (in_array($area, $lowrise)) {
                $slotStatus[$slot]['lowrise'] = true;
            }

            if (in_array($area, $highrise)) {
                $slotStatus[$slot]['highrise'] = true;
            }
        }

        $blockedForUser = [];

        foreach ($slotStatus as $slot => $status) {
            if ($status[$userGroup]) {
                $blockedForUser[] = $slot;
            }
        }

        \Log::info('MOBILE AUSI SLOT REQUEST', [
            'unit_name' => $unitName,
            'unit_no' => $unitNo,
            'resident_id' => $resident->id ?? null,
            'blocked_for_user' => $blockedForUser,
        ]);

        return response()->json([
            'blocked_for_user' => $blockedForUser,
            'raw_status' => $slotStatus
        ]);
    }
}
