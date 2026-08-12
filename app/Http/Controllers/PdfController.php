<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PdfController extends Controller
{
    public function viewer($filename)
    {
        return view('mobile-app.pdf-viewer', [
            'filename' => $filename,
        ]);
    }
    public function file($filename)
    {
        $filename = basename($filename);

        $path = public_path('storage/subway-pdf/' . $filename);

        \Log::info('PDF request', [
            'filename' => $filename,
            'path' => $path,
            'exists' => file_exists($path),
        ]);

        if (!is_file($path)) {
            abort(404);
        }

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}
