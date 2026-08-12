<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PdfController extends Controller
{
    public function viewer($filename)
    {
        return view('mobile.pdf-viewer', [
            'filename' => $filename,
        ]);
    }
    public function file($filename)
    {
        $filename = basename($filename);
        
        $path = storage_path('app/public/storage/subway-pdf/' . $filename);

        if (!is_file($path)) {
            abort(404);
        }

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}
