<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Events;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class EventsController extends Controller
{
    public function showEvents()
    {

        $events = Events::orderBy('created_at', 'desc')->paginate(5);
        return view('backend.admin-events', compact('events'));
    }

    public function storeEvents(Request $request)
    {
        $request->validate([
            'event_title' => 'required|string',
            'event_details' => 'required|string',
            'event_image' => 'required|image|mimes:jpg,jpeg,png',
            'event_date' => 'required|date|after_or_equal:today',
        ]);

        if ($request->hasFile('event_image')) {
            $file = $request->file('event_image');
            $filename = $file->getClientOriginalName();
            $file->move(public_path('assets/images/events'), $filename);
        }

        $newEvent = new Events();
        $newEvent->event_title = strtoupper($request->input('event_title'));
        $newEvent->event_details = $request->input('event_details');
        $newEvent->event_image = $filename;
        $newEvent->event_date = $request->input('event_date'); // ✅ added this line
        $newEvent->save();

        return redirect()->back()->with('success', 'Added Successfully');
    }


    public function fetchEvents($id)
    {
        $event = Events::find($id);
        if (!$event) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        return response()->json([
            'event_title' => $event->event_title,
            'event_date' => $event->event_date,
            'event_details' => $event->event_details,
            'event_image' => $event->event_image,
        ]);
    }

    public function updateEvents(Request $request)
    {
        try {
            $event = Events::findOrFail($request->input('info_id'));
            $event->event_title = $request->input('event_title');
            $event->event_details = $request->input('event_details');
            $event->event_date = $request->input('event_date'); // ✅ Add this line

            // Check if a new file was uploaded
            if ($request->hasFile('event_image')) {
                // Delete old image if it exists
                $oldImagePath = public_path('assets/images/events/' . $event->event_image);
                if (File::exists($oldImagePath)) {
                    File::delete($oldImagePath);
                }

                $file = $request->file('event_image');
                $filename = $file->getClientOriginalName();
                $file->move(public_path('assets/images/events'), $filename);
                $event->event_image = $filename;
            }

            $event->save();

            return response()->json(['status' => true, 'message' => 'Event updated successfully']);
        } catch (\Exception $e) {
            \Log::error('Event update failed: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Event update failed']);
        }
    }


    public function getUpdatedEventsTable(Request $request)
    {
        if (!$request->ajax()) {
            return abort(403, 'Unauthorized action');
        }

        $searchEvent = $request->input('searchEvent');

        $eventPagination = Events::when($searchEvent, function ($query, $searchEvent) {
            return $query->where('event_title', 'LIKE', '%' . $searchEvent . '%');
        })
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        $formattedEvents = $eventPagination->getCollection()->map(function ($event) {
            return [
                'id' => $event->id,
                'event_title' => $event->event_title,
                'event_date' => $event->event_date,
                'event_details' => $event->event_details,
                'event_image' => $event->event_image,
                'created_at' => $event->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $event->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'data' => $formattedEvents,
            'links' => $eventPagination
                ->appends(['searchEvent' => $searchEvent]) // retain search in pagination
                ->withPath('/admin/get-updated-events-table')
                ->links('vendor.pagination.bootstrap-5')->render()
        ]);
    }


    public function deleteEvents(Request $request)
    {
        $eventId = $request->input('event_id');

        try {
            $event = Events::findOrFail($eventId);
            $event->delete();

            return response()->json([
                'status' => true,
                'message' => 'Deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Deletion failed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // public function searchEvents(Request $request)
    // {
    //     $searchEvent = $request->input('searchEvent'); // ✅ fix here

    //     $events = Events::when($searchEvent, function ($query, $searchEvent) {
    //         return $query->where('event_details', 'LIKE', '%' . $searchEvent . '%');
    //     })
    //         ->orderBy('created_at', 'desc')
    //         ->paginate(2);

    //     // Retain search keyword in pagination
    //     $events->appends(['searchEvent' => $searchEvent]);

    //     return view('backend.admin-events', compact('events', 'searchEvent'));
    // }

}
