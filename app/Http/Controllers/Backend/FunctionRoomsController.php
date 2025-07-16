<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\FunctionRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
class FunctionRoomsController extends Controller
{
    public function showFunctionRooms(Request $request)
    {
        $functionRooms = FunctionRoom::paginate(10);
        return view('backend.admin-function-rooms', compact('functionRooms'));
    }

    public function storeFunctionRooms(Request $request)
    {

        if ($request->hasFile('function_room_image')) {
            $file = $request->file('function_room_image');
            $filename = $file->getClientOriginalName();
            $file->move(public_path('assets/images/function-rooms'), $filename);
        }

        if ($request->hasFile('function_room_360')) {
            $file = $request->file('function_room_360');
            $image360Filename = $file->getClientOriginalName();
            $file->move(public_path('assets/images/function-rooms-360'), $image360Filename);
        }

        $newFunctionRoom = new FunctionRoom();
        $newFunctionRoom->function_room_section = strtoupper($request->input('function_room_section'));
        $newFunctionRoom->function_room_name = strtoupper($request->input('function_room_name'));
        $newFunctionRoom->function_room_rate = strtoupper($request->input('function_room_rate'));
        $newFunctionRoom->function_room_capacity = strtoupper($request->input('function_room_capacity'));
        $newFunctionRoom->function_room_description = strtoupper($request->input('function_room_description'));
        $newFunctionRoom->function_room_policy = strtoupper($request->input('function_room_policy'));
        $newFunctionRoom->function_room_image = $filename;
        $newFunctionRoom->function_room_360 = $image360Filename;
        $newFunctionRoom->featured = $request->has('featured') ? 1 : 0;
        $newFunctionRoom->save();
        return redirect()->back()->with('success', 'Added Successfully');
    }

    public function fetchFunctionRooms($id)
    {
        $function_room = FunctionRoom::find($id);
        if (!$function_room) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        return response()->json([
            'function_room_section' => $function_room->function_room_section,
            'function_room_name' => $function_room->function_room_name,
            'function_room_rate' => $function_room->function_room_rate,
            'function_room_capacity' => $function_room->function_room_capacity,
            'function_room_description' => $function_room->function_room_description,
            'function_room_policy' => $function_room->function_room_policy,
            'function_room_image' => $function_room->function_room_image,
            'function_room_360' => $function_room->function_room_360,
            'featured' => $function_room->featured,
        ]);
    }

    public function updateFunctionRooms(Request $request)
    {
        try {
            $functionRoom = FunctionRoom::findOrFail($request->input('info_id'));
            $functionRoom->function_room_name = $request->input('function_room_name');
            $functionRoom->function_room_section = $request->input('function_room_section');
            $functionRoom->function_room_rate = $request->input('function_room_rate');
            $functionRoom->function_room_capacity = $request->input('function_room_capacity');
            $functionRoom->function_room_description = $request->input('function_room_description');
            $functionRoom->function_room_policy = $request->input('function_room_policy');

            $functionRoom->featured = $request->input('featured') ? 1 : 0;

            if ($request->hasFile('function_room_image')) {
                $oldImagePath = public_path('assets/images/function-rooms/' . $functionRoom->function_room_image);
                if (File::exists($oldImagePath)) {
                    File::delete($oldImagePath);
                }

                $file = $request->file('function_room_image');
                $filename = $file->getClientOriginalName();
                $file->move(public_path('assets/images/function-rooms'), $filename);
                $functionRoom->function_room_image = $filename;
            }

            // Update 360 image
            if ($request->hasFile('function_room_360')) {
                $old360Path = public_path('assets/images/function-rooms-360/' . $functionRoom->function_room_360);
                if (File::exists($old360Path)) {
                    File::delete($old360Path);
                }

                $file360 = $request->file('function_room_360');
                $filename360 = $file360->getClientOriginalName();
                $file360->move(public_path('assets/images/function-rooms-360'), $filename360);
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
        $functionRooms = FunctionRoom::paginate(5);
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
}

