<?php

namespace App\Http\Controllers\MobileApp;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FaqsMobileController extends Controller
{
    public function SubwayFaqs()
    {
        return view('mobile-app.faqs.subway-faqs-mobile');
    }
}
