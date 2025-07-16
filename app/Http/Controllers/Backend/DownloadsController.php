<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Download;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;


class DownloadsController extends Controller
{
    public function download()
    {
        $downloads = Download::orderBy('created_at', 'desc')->paginate(10);
        return view('backend.pages.admin-download', compact('downloads'));
    }

    public function uploadFile(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|max:20480',
                'category' => 'required|string',
            ]);

            $category_name = $request->input('category'); // **Use 'category' here**

            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $file->move(public_path('assets/files/downloads'), $fileName);

            Download::create([
                'file_name' => $fileName,
                'category_name' => $category_name, // match your DB column name here
            ]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            \Log::error('Upload error: ' . $e->getMessage());
            return response()->json(['error' => 'Server Error'], 500);
        }
    }



    public function getUpdatedDownloadsTable(Request $request)
    {
        $getdownloads = Download::orderBy('created_at', 'desc')->paginate(10);

        // Format each download item
        $formattedDownloads = $getdownloads->getCollection()->map(function ($getdownloads) {
            return [
                'id' => $getdownloads->id,
                'file_name' => $getdownloads->file_name,
                'category_name' => $getdownloads->category_name,
                'created_at' => Carbon::parse($getdownloads->created_at)->format('Y-m-d H:i:s'),
            ];
        });

        // Render pagination HTML separately
        $downloads = View::make('vendor.pagination.bootstrap-5', ['paginator' => $getdownloads])->render();

        return response()->json([
            'data' => $formattedDownloads,
            'links' => $downloads
        ]);
    }

    public function deleteFile($id)
    {
        try {
            $download = Download::findOrFail($id);

            $filePath = public_path('assets/files/' . $download->file_name);

            if (file_exists($filePath)) {
                unlink($filePath); // Delete the physical file
            }

            $download->delete(); // Delete the database record

            return response()->json(['success' => true, 'message' => 'File deleted successfully.']);
        } catch (\Exception $e) {
            \Log::error('Delete error: ' . $e->getMessage());
            return response()->json(['error' => 'Server Error'], 500);
        }
    }

}
