<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Amenity;

class AmenitiesController extends Controller
{
public function showAmenities(Request $request)
    {
        $amenities = Amenity::paginate(10);
        return view('backend.admin-amenities', compact('amenities'));
    }

    public function storeAmenities(Request $request)
    {

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

}
