<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\FitnessHubScheduleBlocking;
use Illuminate\Http\Request;
use App\Models\FitnessHub;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\FitnessHubDateBlocking;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FitnessHubController extends Controller
{
    public function AdminShowFitnessHub()
    {
        $fitnessHubs = FitnessHub::paginate(10);
        return view('backend.fitness-hubs.admin-fitness-hubs', compact('fitnessHubs'));
    }

    public function searchFitnessHubs(Request $request)
    {
        $searchActivity = $request->get('searchActivity');
        $fitnessHubs = FitnessHub::where('fitness_hub_name', 'like', "%$searchActivity%")
            ->paginate(10);
        return view('backend.fitness-hub.admin-fitness-hub', compact('fitnessHubs', 'searchActivity'));
    }


    public function storeFitnessHub(Request $request)
    {

        $filename = null;
        if ($request->hasFile('fitness_hub_image')) {
            try {
                $file = $request->file('fitness_hub_image');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path(path: 'assets/images/fitness-hubs'), $filename);
                Log::info('File uploaded successfully', ['filename' => $filename]);
            } catch (\Exception $e) {
                Log::error('File upload error', ['error' => $e->getMessage()]);
                return redirect()->back()->with('error', 'File upload failed.');
            }
        }

        try {
            // Save new activity
            $newFitnessHub = new FitnessHub();
            $newFitnessHub->fitness_hub_name = strtoupper($request->input('fitness_hub_name'));
            $newFitnessHub->fitness_hub_description = strtoupper($request->input('fitness_hub_description'));
            $newFitnessHub->fitness_hub_remarks = strtoupper($request->input('fitness_hub_remarks'));
            $newFitnessHub->fitness_hub_start_time = $request->start_time;   // ✅ added
            $newFitnessHub->fitness_hub_end_time = $request->end_time;
            $newFitnessHub->fitness_hub_max_booking = $request->input('fitness_hub_max_booking');
            $newFitnessHub->fitness_hub_image = $filename;
            $newFitnessHub->save();

            return redirect()->back()->with('success', 'Fitness Hub added successfully.');
        } catch (\Exception $e) {
            Log::error('Error saving fitness hub', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to save fitness hub.');
        }
    }

    public function getUpdatedFitnessHubsTable()
    {
        $fitnessHubs = FitnessHub::latest()->get();

        $fitnessHubsData = $fitnessHubs->map(function ($fitnessHub) {
            return [
                'id' => $fitnessHub->id,
                'name' => strtoupper($fitnessHub->fitness_hub_name ?? 'N/A'),
                'description' => $fitnessHub->fitness_hub_description ?? 'N/A',
                'remarks' => strtoupper($fitnessHub->fitness_hub_remarks ?? 'N/A'),
                'max_booking' => strtoupper($fitnessHub->fitness_hub_max_booking ?? 'N/A'),
                'start_time_formatted' => $fitnessHub->start_time_formatted,
                'end_time_formatted' => $fitnessHub->end_time_formatted,
                'image' => $fitnessHub->fitness_hub_image,
                'status' => $fitnessHub->fitness_hub_status,
            ];
        });

        return response()->json([
            'data' => $fitnessHubsData
        ]);
    }

    public function fetchFitnessHub($id)
    {

        $fitnessHub = FitnessHub::find($id);

        if (!$fitnessHub) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        return response()->json([
            'id' => $fitnessHub->id,
            'fitness_hub_name' => $fitnessHub->fitness_hub_name,
            'fitness_hub_description' => $fitnessHub->fitness_hub_description,
            'fitness_hub_remarks' => $fitnessHub->fitness_hub_remarks,
            'fitness_hub_max_booking' => $fitnessHub->fitness_hub_max_booking,
            'fitness_hub_start_time' => Carbon::parse($fitnessHub->fitness_hub_start_time)->format('H:i'),
            'fitness_hub_end_time' => Carbon::parse($fitnessHub->fitness_hub_end_time)->format('H:i'),

            'fitness_hub_image' => $fitnessHub->fitness_hub_image,
        ]);
    }


    public function updateFitnessHub(Request $request)
    {
        try {
            $fitnessHub = FitnessHub::findOrFail($request->id);

            // ✅ HANDLE IMAGE UPDATE
            if ($request->hasFile('fitness_hub_image')) {

                // delete old image (optional but recommended)
                if (
                    $fitnessHub->fitness_hub_image &&
                    file_exists(public_path('assets/images/fitness-hubs/' . $fitnessHub->fitness_hub_image))
                ) {

                    unlink(public_path('assets/images/fitness-hubs/' . $fitnessHub->fitness_hub_image));
                }

                $file = $request->file('fitness_hub_image');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('assets/images/fitness-hubs'), $filename);

                $fitnessHub->fitness_hub_image = $filename;
            }

            // ✅ UPDATE FIELDS
            $fitnessHub->fitness_hub_name = strtoupper($request->fitness_hub_name);
            $fitnessHub->fitness_hub_description = strtoupper($request->fitness_hub_description);
            $fitnessHub->fitness_hub_remarks = strtoupper($request->fitness_hub_remarks);
            $fitnessHub->fitness_hub_start_time = $request->start_time;
            $fitnessHub->fitness_hub_end_time = $request->end_time;

            // ⚠️ IMPORTANT FIX (radio name mismatch)
            $fitnessHub->fitness_hub_max_booking = $request->edit_fitness_hub_max_booking;

            $fitnessHub->save();

            return redirect()->back()->with('success', 'Fitness Hub updated successfully.');

        } catch (\Exception $e) {

            \Log::error('Update Fitness Hub Error', [
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Failed to update fitness hub.');
        }
    }



    public function deactivateFitnessHub(Request $request)
    {
        $request->validate([
            'fitnessHub_id' => 'required|exists:fitness_hubs,id',
            'fitnessHub_remarks' => 'required|string|max:255'
        ]);

        $fitnessHub = FitnessHub::find($request->fitnessHub_id);

        if (!$fitnessHub) {
            return response()->json(['message' => 'Fitness Hub not found'], 404);
        }

        $fitnessHub->fitness_hub_remarks = strtoupper($request->fitnessHub_remarks);

        $fitnessHub->fitness_hub_status = 2;

        $fitnessHub->save();

        return response()->json([
            'message' => 'Remarks saved and Fitness Hub deactivated successfully'
        ]);
    }

    public function activateFitnessHub(Request $request)
    {
        $fitnessHubId = $request->input('fitness_hub_id');
        $statusId = $request->input('fitness_hub_status');
        try {
            $fitnessHub = FitnessHub::findOrFail($fitnessHubId);
            $fitnessHub->fitness_hub_status = $statusId;
            if ($statusId == 1) {
                $fitnessHub->fitness_hub_remarks = null;
            }
            $fitnessHub->update();

            return response()->json(['status' => true, 'message' => 'Status Updated Successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Status UpdatedFailed.']);
        }
    }



    public function showDateBlockingFitnessHub(Request $request)
    {
        $fitnessHubDateBlockings = FitnessHubDateBlocking::with(['fitnessHub'])->paginate(10);
        $fitnessHubs = FitnessHub::all();

        return view('backend.fitness-hubs.admin-fitness-hub-date-blocking', compact('fitnessHubDateBlockings', 'fitnessHubs'));

    }

    public function fetchDateBlockingFitnessHub(Request $request)
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

    public function newDateBlockingFitnessHub(Request $request)
    {


        $exists = DB::table('fitness_hub_date_blockings')
            ->where('fitness_hub_id', $request->fitnessHub_id_blocking)
            ->where('blocking_status', 1)
            ->where(function ($query) use ($request) {
                $query->whereBetween('date_blocking_start', [$request->date_blocking_start, $request->date_blocking_end])
                    ->orWhereBetween('date_blocking_end', [$request->date_blocking_start, $request->date_blocking_end])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('date_blocking_start', '<=', $request->date_blocking_start)
                            ->where('date_blocking_end', '>=', $request->date_blocking_end);
                    });
            })
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Date range overlaps with an existing block.'
            ], 422);
        }
        FitnessHubDateBlocking::create([
            'fitness_hub_id' => $request->fitnessHub_id_blocking,
            'blocking_remarks' => $request->blocking_remarks,
            'date_blocking_start' => $request->date_blocking_start,
            'date_blocking_end' => $request->date_blocking_end,
            'blocking_status' => 1,
        ]);

        return back()->with('success', 'Date blocked successfully!');
    }

    public function getUpdatedFitnessHubBlockingTable()
    {
        $dateBlockings = FitnessHubDateBlocking::with('fitnessHub')
            ->latest()
            ->paginate(10);

        $data = $dateBlockings->map(function ($item) {
            return [
                'id' => $item->id,
                'fitness_hub_name' => optional($item->fitnessHub)->fitness_hub_name,
                'blocking_remarks' => $item->blocking_remarks,
                'date_blocking_start' => $item->date_blocking_start,
                'date_blocking_end' => $item->date_blocking_end,
            ];
        });

        return response()->json([
            'data' => $data,
            'links' => (string) $dateBlockings->links('vendor.pagination.bootstrap-5')
        ]);
    }

    public function deleteBlockedDateFitnessHub(Request $request)
    {
        try {
            $block = FitnessHubDateBlocking::find($request->block_id);
            if (!$block) {
                return response()->json(['status' => false, 'message' => 'Blocked date not found'], 404);
            }

            $block->delete();
            return response()->json(['status' => true, 'message' => 'Blocked date deleted successfully']);
        } catch (\Exception $e) {
            \Log::error('Error deleting blocked date:', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Failed to delete blocked date']);
        }
    }

    public function fetchScheduleBlockingFitnessHub(Request $request)
    {
        $fitness_hub_blockings = FitnessHubScheduleBlocking::with('fitnessHub')->paginate(10);

        $fitnessHubs = FitnessHub::all();

        return view('backend.fitness-hubs.admin-fitness-hub-schedule-blocking', compact('fitness_hub_blockings', 'fitnessHubs'));

    }


    public function newScheduleBlockingFitnessHub(Request $request)
    {
        $request->validate([
            'fitness_hub_id' => 'required',
            'days' => 'required|array',
            'blocking_start_time' => 'required',
            'blocking_end_time' => 'required'
        ]);

        $created = 0;

        foreach ($request->days as $day) {

            $exists = FitnessHubScheduleBlocking::where('fitness_hub_id', $request->fitness_hub_id)
                ->where('day', $day)
                ->where('start_time', $request->blocking_start_time)
                ->where('end_time', $request->blocking_end_time)
                ->exists();

            if (!$exists) {

                FitnessHubScheduleBlocking::create([
                    'fitness_hub_id' => $request->fitness_hub_id,
                    'day' => $day,
                    'start_time' => $request->blocking_start_time,
                    'end_time' => $request->blocking_end_time,
                    'remarks' => strtoupper($request->remarks),
                    'repeat_weekly' => $request->repeat_weekly
                ]);

                $created++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "$created blocking schedule(s) created."
        ]);
    }
    public function getUpdatedFitnessHubScheduleBlockingTable()
    {
        $dateBlockings = FitnessHubScheduleBlocking::with('fitnessHub')->latest()
            ->paginate(10);
        return response()->json([
            'data' => $dateBlockings->items(),
            'links' => (string) $dateBlockings->links('vendor.pagination.bootstrap-5')
        ]);

    }


    public function deleteBlockedScheduleFitnessHub(Request $request)
    {
        try {
            $block = FitnessHubScheduleBlocking::find($request->block_id);
            if (!$block) {
                return response()->json(['status' => false, 'message' => 'Blocked schedule not found'], 404);
            }

            $block->delete();
            return response()->json(['status' => true, 'message' => 'Blocked schedule deleted successfully']);
        } catch (\Exception $e) {
            \Log::error('Error deleting blocked schedule:', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Failed to delete blocked schedule']);
        }
    }

}
