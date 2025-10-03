<?php

namespace App\Http\Controllers\Backend;

use App;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FunctionRoomDiscount;
use App\Models\FunctionRoom;


class FunctionRoomDiscountController extends Controller
{
    public function showFunctionRoomDiscounts()
    {
        $functionRoomDiscounts = FunctionRoomDiscount::with('functionRoom')->paginate(10);
        $functionRooms = FunctionRoom::all();
        return view('backend.admin-function-room-dicounts', compact('functionRoomDiscounts', 'functionRooms'));
    }

    public function createFunctionRoomDiscounts(Request $request)
    {
        $validated = $request->validate([
            'function_room_id' => 'required|array',
            'function_room_id.*' => 'exists:function_rooms,id',
            'discount' => 'required|numeric|min:1|max:100',
            'remarks' => 'nullable|string|max:255',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        foreach ($validated['function_room_id'] as $roomId) {
            FunctionRoomDiscount::create([
                'function_room_id' => $roomId,
                'discount' => $validated['discount'],
                'remarks' => $validated['remarks'] ?? null,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Discount(s) created successfully!',
        ]);
    }

    public function getUpdatedFunctionRoomDiscountTable(Request $request)
    {
        $searchTerm = $request->input('searchFunctionRoomDiscount');

        $discounts = FunctionRoomDiscount::with('functionRoom')
            ->when($searchTerm, function ($query, $searchTerm) {
                return $query->whereHas('functionRoom', function ($q) use ($searchTerm) {
                    $q->where('function_room_name', 'LIKE', '%' . $searchTerm . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        $formatted = $discounts->getCollection()->map(function ($discount) {
            return [
                'id' => $discount->id,
                'functionRoom' => $discount->functionRoom ? $discount->functionRoom->function_room_name : 'N/A',
                'discount' => $discount->discount,
                'remarks' => $discount->remarks,
                'start_date' => $discount->start_date,
                'end_date' => $discount->end_date,
            ];
        });

        return response()->json([
            'data' => $formatted,
            'links' => (string) $discounts
                ->appends(['searchFunctionRoomDiscount' => $searchTerm])
                ->withPath('/admin/get-updated-function-room-discount-table')
                ->links('vendor.pagination.bootstrap-5')
        ]);
    }

    public function deleteFunctionRoomDiscounts(Request $request)
    {
        $functionRoomDiscountId = $request->input('functionRoomDiscountId');

        try {
            $functionRoomDiscount = FunctionRoomDiscount::findOrFail($functionRoomDiscountId);

            $functionRoomDiscount->delete();

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

    public function fetchFunctionRoomDiscounts($id)
    {
        $functionRoomDiscount = FunctionRoomDiscount::with('functionRoom')->find($id);
        if (!$functionRoomDiscount) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        return response()->json([
            'function_room_id' => $functionRoomDiscount->function_room_id,
            'function_room_name' => $functionRoomDiscount->functionRoom->function_room_name ?? 'N/A',
            'discount' => $functionRoomDiscount->discount,
            'remarks' => $functionRoomDiscount->remarks,
            'start_date' => $functionRoomDiscount->start_date,
            'end_date' => $functionRoomDiscount->end_date,
        ]);
    }


    public function updateFunctionRoomDiscount(Request $request)
    {
        try {
            $functionRoomDiscount = FunctionRoomDiscount::findOrFail($request->input('functionRoomDiscountId'));

            $functionRoomDiscount->function_room_id = $request->input('function_room_id'); // ✅ no [0]
            $functionRoomDiscount->discount = $request->input('discount');
            $functionRoomDiscount->remarks = $request->input('remarks');
            $functionRoomDiscount->start_date = $request->input('start_date');
            $functionRoomDiscount->end_date = $request->input('end_date');
            $functionRoomDiscount->save();

            return response()->json(['status' => true, 'message' => 'Discount updated successfully']);
        } catch (\Exception $e) {
            \Log::error('Discount update failed: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Update failed']);
        }
    }


}
