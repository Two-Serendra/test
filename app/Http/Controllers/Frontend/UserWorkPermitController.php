<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MinorWorkPermit;
class UserWorkPermitController extends Controller
{
    public function submitMinorWorkPermit(Request $request)
    {
        $request->validate([
            'work_permit_booking_date' => 'required|date',
            'work_type' => 'required|string',
            'contractor' => 'required|string|min:3',
        ]);

        $user = Auth::user();

        MinorWorkPermit::create([
            'user_id' => $user->id,
            'work_permit_booking_date' => $request->work_permit_booking_date,
            'work_type' => $request->work_type,
            'contractor' => $request->contractor,
        ]);

        return redirect()->back()->with('success', 'Work permit submitted successfully.');
    }
}
