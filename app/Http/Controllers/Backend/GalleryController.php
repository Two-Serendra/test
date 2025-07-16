<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gallery;
use Illuminate\Support\Facades\DB;

class GalleryController extends Controller
{
    public function showGallery(Request $request)
    {
        $pictures = Gallery::paginate(5);
        return view('backend.pages.admin-gallery', compact('pictures'));
    }

    public function uploadGalleryImages(Request $request)
    {
        $request->validate([
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $originalName = $image->getClientOriginalName();

                $image->move(public_path('assets/images/gallery'), $originalName);

                DB::table('galleries')->insert([
                    'file_name' => $originalName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        return redirect()->back()->with('success', 'Images uploaded successfully.');
    }

    public function getUpdatedGalleryTable()
    {
        $images = Gallery::paginate(10);
        return response()->json([
            'data' => $images->items(),
            'links' => (string) $images->links('vendor.pagination.bootstrap-5')
        ]);
    }

}