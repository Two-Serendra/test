<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\FunctionRoom;
use App\Models\AddOn;
use App\Models\FunctionRoomDateBlocking;
use App\Models\FunctionRoomImages;
use App\Models\FunctionRoomBooking;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
class FunctionRoomsController extends Controller
{
    public function showFunctionRooms(Request $request)
    {
        $functionRooms = FunctionRoom::orderBy('created_at', 'desc')->paginate(10);
        return view('backend.admin-function-rooms', compact('functionRooms'));
    }


    public function storeFunctionRooms(Request $request)
    {
        \Log::info('Incoming request data: ', $request->all());
        $featured = $request->has('featured') ? 1 : 0;
        if ($request->hasFile('function_room_360')) {
            $file360 = $request->file('function_room_360');
            $fileName360 = $file360->getClientOriginalName();

            $destination360 = public_path('assets/images/uploads/function-rooms/360');

            if (!file_exists($destination360)) {
                mkdir($destination360, 0777, true);
            }

            $file360->move($destination360, $fileName360);
        } else {
            $fileName360 = null;
        }

        $functionRoom = FunctionRoom::create([
            'function_room_section' => $request->function_room_section,
            'function_room_name' => $request->function_room_name,
            'function_room_rate' => $request->function_room_rate,
            'function_room_capacity' => $request->function_room_capacity,
            'function_room_description' => $request->function_room_description,
            'function_room_policy' => $request->function_room_policy,
            'function_room_360' => $fileName360,
            'featured' => $featured,

        ]);

        \Log::info('Function room created: ', $functionRoom->toArray());

        if ($request->hasFile('function_room_image')) {
            $destinationImages = public_path('assets/images/uploads/function-rooms/images');
            if (!file_exists($destinationImages)) {
                mkdir($destinationImages, 0777, true);
            }

            foreach ($request->file('function_room_image') as $image) {
                $filename = $image->getClientOriginalName();
                $image->move($destinationImages, $filename);

                FunctionRoomImages::create([
                    'function_room_id' => $functionRoom->id,
                    'image' => $filename,
                ]);
            }
        } else {
            \Log::warning('No images found in request.');
        }

        return response()->json([
            'success' => true,
            'message' => 'Function room and images saved successfully.'
        ]);
    }


    public function fetchFunctionRooms($id)
    {
        $function_room = FunctionRoom::with('images')->find($id);

        if (!$function_room) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        return response()->json([
            'function_room_section' => $function_room->function_room_section,
            'function_room_name' => $function_room->function_room_name,
            'function_room_rate' => $function_room->function_room_rate,
            'function_room_capacity' => $function_room->function_room_capacity,
            'function_room_short_description' => $function_room->function_room_short_description,
            'function_room_description' => $function_room->function_room_description,
            'function_room_policy' => $function_room->function_room_policy,
            'function_room_images' => $function_room->images->pluck('image'), // returns array of filenames
            'function_room_360' => $function_room->function_room_360,
            'featured' => $function_room->featured,
            'discount' => $function_room->discount,
        ]);
    }

    public function updateFunctionRooms(Request $request)
    {
        try {
            $functionRoom = FunctionRoom::findOrFail($request->input('id'));
            $functionRoom->function_room_name = $request->input('function_room_name');
            $functionRoom->function_room_section = $request->input('function_room_section');
            $functionRoom->function_room_rate = $request->input('function_room_rate');
            $functionRoom->function_room_capacity = $request->input('function_room_capacity');
            $functionRoom->function_room_short_description = $request->input('function_room_short_description');
            $functionRoom->function_room_description = $request->input('function_room_description');
            $functionRoom->function_room_policy = $request->input('function_room_policy');
            $functionRoom->featured = $request->input('featured') ? 1 : 0;
            if ($request->hasFile('function_room_image')) {
                $oldImages = FunctionRoomImages::where('function_room_id', $functionRoom->id)->get();
                foreach ($oldImages as $oldImage) {
                    $oldImagePath = public_path('assets/images/uploads/function-rooms/images/' . $oldImage->image);
                    if (File::exists($oldImagePath)) {
                        File::delete($oldImagePath);
                    }
                    $oldImage->delete();
                }
                foreach ($request->file('function_room_image') as $image) {
                    $filename = $image->getClientOriginalName();
                    $image->move(public_path('assets/images/uploads/function-rooms/images'), $filename);
                    FunctionRoomImages::create([
                        'function_room_id' => $functionRoom->id,
                        'image' => $filename
                    ]);
                }
            }
            if ($request->hasFile('function_room_360')) {
                $old360Path = public_path('assets/images/uploads/function-rooms/360/' . $functionRoom->function_room_360);
                if (File::exists($old360Path)) {
                    File::delete($old360Path);
                }

                $file360 = $request->file('function_room_360');
                $filename360 = $file360->getClientOriginalName();
                $file360->move(public_path('assets/images/uploads/function-rooms/360'), $filename360);
                $functionRoom->function_room_360 = $filename360;
            }

            $functionRoom->save();

            return response()->json(['status' => true, 'message' => 'Function Room updated successfully']);
        } catch (\Exception $e) {
            \Log::error('Function Room update failed: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Function Room update failed']);
        }
    }


    public function getUpdatedFunctionRoomsTable()
    {
        $functionRooms = FunctionRoom::orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'data' => $functionRooms->items(),
            'links' => (string) $functionRooms->links('vendor.pagination.bootstrap-5')
        ]);
    }

    public function deleteFunctionRooms(Request $request)
    {
        $functionRoomId = $request->input('functionRoom_id');

        try {
            $functionRoom = FunctionRoom::findOrFail($functionRoomId);

            $images = FunctionRoomImages::where('function_room_id', $functionRoom->id)->get();
            foreach ($images as $img) {
                $imagePath = public_path('assets/images/uploads/function-rooms/images/' . $img->image);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
                $img->delete();
            }

            if (!empty($functionRoom->function_room_360)) {
                $image360Path = public_path('assets/images/uploads/function-rooms/360/' . $functionRoom->function_room_360);
                if (file_exists($image360Path)) {
                    unlink($image360Path);
                }
            }

            $functionRoom->delete();

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

    public function disable(Request $request, $id)
    {
        $room = FunctionRoom::findOrFail($id);
        $room->function_room_status = 0; // correct column name
        $room->function_room_remarks = $request->remarks; // correct column name
        $room->save();

        return response()->json(['message' => 'Function room disabled successfully.']);
    }

    public function enable($id)
    {
        $room = FunctionRoom::findOrFail($id);
        $room->function_room_status = 1; // correct column name
        $room->function_room_remarks = null; // correct column name
        $room->save();

        return response()->json(['message' => 'Function room enabled successfully.']);
    }


    public function showFunctionRoomDateBlockingTable(Request $request)
    {
        $functionRoomDateBlockings = FunctionRoomDateBlocking::with('functionRoom')->paginate(10);
        $functionRooms = FunctionRoom::all();

        return view('backend.admin-function-room-date-blocking', compact('functionRoomDateBlockings', 'functionRooms'));
    }

    public function fetchFunctionRoomBlockDates(Request $request)
    {
        $function_room_id = $request->input('function_room_id');

        if (!$function_room_id) {
            return response()->json([]);
        }

        // --- Fetch booked dates ---
        $bookedDates = FunctionRoomBooking::where('function_room_id', $function_room_id)
            ->whereIn('booking_status', [0, 1]) // 0 = pending, 1 = approved, adjust as needed
            ->pluck('function_room_booking_date')
            ->toArray();

        // --- Fetch blocked date ranges ---
        $blockedDatesQuery = FunctionRoomDateBlocking::where('blocking_status', 1)
            ->where('function_room_id', $function_room_id)
            ->get();

        $blockedDates = [];
        foreach ($blockedDatesQuery as $block) {
            $start = new \DateTime($block->date_blocking_start);
            $end = new \DateTime($block->date_blocking_end);
            while ($start <= $end) {
                $blockedDates[] = $start->format('Y-m-d');
                $start->modify('+1 day');
            }
        }

        // --- Merge and remove duplicates ---
        $disabledDates = array_values(array_unique(array_merge($bookedDates, $blockedDates)));

        return response()->json($disabledDates);
    }


    public function newDateBlocking(Request $request)
    {
        try {
            $functionRoomId = $request->input('function_room_id_blocking');
            $startDate = $request->input('function_room_date_blocking_start');
            $endDate = $request->input('function_room_date_blocking_end');

            if (!$startDate || !$endDate) {
                return response()->json(['status' => 'error', 'message' => 'Invalid date range.'], 400);
            }

            $hasBookingConflict = FunctionRoomBooking::where('function_room_id', $functionRoomId)
                ->whereIn('booking_status', [0, 1])
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('function_room_booking_date', [$startDate, $endDate])
                        ->orWhere(function ($query) use ($startDate, $endDate) {
                            $query->where('function_room_booking_date', '<=', $startDate)
                                ->where('function_room_booking_date', '>=', $endDate);
                        });
                })
                ->exists();

            if ($hasBookingConflict) {
                return response()->json(['status' => 'error', 'message' => 'This date range overlaps with an existing booking.'], 409);
            }

            $hasBlockingConflict = FunctionRoomDateBlocking::where('function_room_id', $functionRoomId)
                ->where('blocking_status', 1)
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('date_blocking_start', [$startDate, $endDate])
                        ->orWhereBetween('date_blocking_end', [$startDate, $endDate])
                        ->orWhere(function ($query) use ($startDate, $endDate) {
                            $query->where('date_blocking_start', '<=', $startDate)
                                ->where('date_blocking_end', '>=', $endDate);
                        });
                })
                ->exists();

            if ($hasBlockingConflict) {
                return response()->json(['status' => 'error', 'message' => 'This date range overlaps with another block.'], 409);
            }
            $newBlocking = new FunctionRoomDateBlocking();
            $newBlocking->function_room_id = $functionRoomId;
            $newBlocking->blocking_remarks = strtoupper($request->input('blocking_remarks'));
            $newBlocking->date_blocking_start = $startDate;
            $newBlocking->date_blocking_end = $endDate;
            $newBlocking->blocking_status = 1;
            $newBlocking->save();

            return response()->json(['status' => 'success', 'message' => 'Blocked Successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Blocking Failed: ' . $e->getMessage()], 500);
        }
    }

    public function getUpdatedFunctionRoomBlockingTable()
    {
        $functionRoomDateBlockings = FunctionRoomDateBlocking::with('functionRoom')->latest()
            ->paginate(10);
        return response()->json([
            'data' => $functionRoomDateBlockings->items(),
            'links' => (string) $functionRoomDateBlockings->links('vendor.pagination.bootstrap-5') // Pagination links
        ]);

    }

    public function deleteDateBlocking(Request $request)
    {
        $dateBlockingId = $request->input('dateBlockingId');

        try {
            $dateBlocking = FunctionRoomDateBlocking::findOrFail($dateBlockingId);

            $dateBlocking->delete();

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


}

