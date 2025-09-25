<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AddOn;

class AddOnsController extends Controller
{
    public function showAddons()
    {
        $addOns = AddOn::orderBy('created_at', 'desc')
            ->paginate(5);

        return view('backend.admin-add-ons', compact('addOns'));
    }


    public function storeAddons(Request $request)
    {

        $addOns = new AddOn();
        $addOns->item = $request->item;
        $addOns->qty = $request->qty;
        $addOns->price = $request->price;
        $addOns->save();
        return redirect()->back()->with('success', 'AddOns added successfully.');
    }

    public function getUpdatedAddOnsTable(Request $request)
    {
        $searchAddOns = $request->input('searchAddOns');

        $addOnsPagination = AddOn::when($searchAddOns, function ($query, $searchAddOns) {
            return $query->where('item', 'LIKE', '%' . $searchAddOns . '%');
        })
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        $formattedAddOns = $addOnsPagination->getCollection()->map(function ($addOn) {
            return [
                'id' => $addOn->id,
                'item' => $addOn->item,
                'qty' => $addOn->qty,
                'price' => $addOn->price,
                'status' => $addOn->status,
            ];
        });

        return response()->json([
            'data' => $formattedAddOns,
            'links' => (string) $addOnsPagination
                ->appends(['searchAddOns' => $searchAddOns])
                ->withPath('/admin/get-updated-add-ons-table')
                ->links('vendor.pagination.bootstrap-5')
        ]);
    }

    public function fetchAddOns($id)
    {
        $addOns = AddOn::find($id);
        if (!$addOns) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        return response()->json([
            'item' => $addOns->item,
            'qty' => $addOns->qty,
        ]);
    }


    public function updateAddOns(Request $request)
    {
        try {
            $addOns = AddOn::findOrFail($request->input('info_id'));
            $addOns->item = $request->input('item');
            $addOns->price = $request->input('price');
            $addOns->qty = $request->input('qty');
            $addOns->save();

            return response()->json(['status' => true, 'message' => 'Updated successfully']);
        } catch (\Exception $e) {
            \Log::error('Event update failed: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Update failed']);
        }
    }


    public function deleteAddOns(Request $request)
    {
        $addOnsId = $request->input('addOns_id');

        try {
            $addOns = AddOn::findOrFail($addOnsId);

            $addOns->delete();

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
        $addOns = AddOn::findOrFail($id);
        $addOns->status = 0; // correct column name
        $addOns->save();

        return response()->json(['message' => 'Disabled successfully.']);
    }

    public function enable($id)
    {
        $addOns = AddOn::findOrFail($id);
        $addOns->status = 1; // correct column name
        $addOns->save();

        return response()->json(['message' => 'Enabled successfully.']);
    }
}

