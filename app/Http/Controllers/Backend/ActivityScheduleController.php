<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ActivitySchedule;
use App\Models\Amenity;
use App\Models\Activity;

use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ActivityScheduleController extends Controller
{
    public function schedules(Request $request)
    {
        $schedules = ActivitySchedule::with ('activity')->paginate(5);
        foreach ($schedules as $schedule) {
            $schedule->start_time = Carbon::parse($schedule->start_time)->format('h:i A');
            $schedule->end_time = Carbon::parse($schedule->end_time)->format('h:i A');
        }
        $amenities = Amenity::all();
        $activities = Activity::all();
        return view('backend.activity-schedule', compact('schedules','amenities','activities'));
    }

}
