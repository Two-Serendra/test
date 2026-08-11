<?php

namespace App\Http\Controllers\MobileApp;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FaqsMobileController extends Controller
{
    public function SubwayNews()
    {
        return view('mobile-app.subway.subway-news-mobile');
    }
    public function SubwayFaqs()
    {
        return view('mobile-app.subway.subway-faqs-mobile');
    }
}
