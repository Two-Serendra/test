<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\Activity;
use Illuminate\Http\Request;

class AmenitiesController extends Controller
{

    public function amenities(Request $request)
    {
        $amenities = Amenity::paginate(5);
        $activities = Activity::all();
        return view('backend.amenities.amenities', compact('amenities', 'activities'));
    }

    public function addAmenities(Request $request)
    {
        $request->validate([
            'amenity_name' => 'required|string|max:255',
            'amenity_description' => 'required|string',
            'amenity_remarks' => 'nullable|string',
            'amenity_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('amenity_image')) {
            $file = $request->file('amenity_image');
            $filename = $file->getClientOriginalName();

            $file->move(public_path('assets/images/amenities'), $filename);
        }
        $newAmenity = new Amenity();
        $newAmenity->amenity_name = strtoupper($request->input('amenity_name'));
        $newAmenity->amenity_description = strtoupper($request->input('amenity_description'));
        $newAmenity->amenity_remarks = strtoupper($request->input('amenity_remarks'));
        $newAmenity->amenity_image = $filename;
        $newAmenity->save();

        return redirect()->back()->with('success', 'Added Successfully');
    }


    public function getUpdatedAmenitiesTable()
    {
        $amenities = Amenity::paginate(5);
        return response()->json([
            'data' => $amenities->items(),
            'links' => (string) $amenities->links('vendor.pagination.bootstrap-5')
        ]);
    }

    public function fetchInfoAmenity($id)
    {
        // dd($id);
        $info = Amenity::find($id);
        if (!$info) {
            return response()->json(['message' => 'Data not found'], 404);
        }
        return response()->json($info);
    }

    public function updateAmenities(Request $request)
    {
        $request->validate([
            'amenity_name' => 'required|string|max:255',
            'amenity_description' => 'required|string',
            'amenity_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        try {
            $amenity = Amenity::findOrFail($request->input('info_id'));
            $amenity->amenity_name = strtoupper($request->input('amenity_name'));
            $amenity->amenity_description = strtoupper($request->input('amenity_description'));
            if ($request->hasFile('amenity_image')) {
                $file = $request->file('amenity_image');
                $originalFilename = $file->getClientOriginalName();
                $file->move(public_path('assets/images/amenities'), $originalFilename);
                $amenity->amenity_image = $originalFilename;
            } else {
                $amenity->amenity_image = $request->input('current_image_file_name');
            }
            $amenity->save();
            return response()->json(['status' => true, 'message' => "Updated Successfully"]);
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json(['status' => false, 'message' => "Update Failed"]);
        }
    }

    public function fetchAmenityAddRemarks($id)
    {
        $info = Amenity::find($id);
        if (!$info) {
            return response()->json(['message' => 'Data not found'], 404);
        }
        return response()->json($info);
    }

    public function addRemarks(Request $request)
    {
        $amenityId = $request->input('amenity_id');
        $amenityRemarks = $request->input('amenity_remarks');
        $statusId = $request->input('status_id');

        try {
            $$amenityRemarks = strtoupper($amenityRemarks);
            $amenity = Amenity::findOrFail($amenityId);
            $amenity->amenity_remarks = $amenityRemarks;
            $amenity->amenity_status = $statusId;
            $amenity->save();

            return response()->json(['status' => true, 'message' => 'Amenity Hidden Successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Amenity Hidden Failed.']);
        }
    }

    public function showAmenities(Request $request)
    {
        $amenity = Amenity::findOrFail($request->amenity_id);

        $amenity->amenity_status = (int) $request->status_id;
        $amenity->amenity_remarks = null;

        $amenity->save(); // ✅ IMPORTANT

        return response()->json([
            'status' => true,
            'message' => 'Status Updated Successfully.'
        ]);
    }

    public function searchAmenity(Request $request)
    {
        $searchAmenity = $request->input('searchAmenity');
        $amenities = Amenity::when($searchAmenity, function ($query, $searchAmenity) {
            return $query->where('amenity_name', 'LIKE', "{$searchAmenity}%");
        })->paginate(10);

        $amenities->appends(['searchAmenity' => $searchAmenity]);
        $activities = Activity::all();
        return view('backend.amenities.amenities', compact('amenities', 'amenities', 'activities', 'searchAmenity'));
    }

}
