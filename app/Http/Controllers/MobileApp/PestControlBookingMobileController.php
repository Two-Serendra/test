<?php

namespace App\Http\Controllers\MobileApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PestControlBookingMobileController extends Controller
{
    public function pestControlBookingUserMobile(Request $request)
    {
        return response()
            ->view('mobile-app.pest-control.pest-control-booking-mobile', [
                'cache_bust' => microtime(true),
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private, no-transform')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0')
            ->header('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT')
            ->header('ETag', '"' . uniqid() . '"');
    }

}
