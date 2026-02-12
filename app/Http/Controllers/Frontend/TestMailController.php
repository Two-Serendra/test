<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailTest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Exception;
use App\Jobs\SendElasticEmail;

class TestMailController extends Controller
{
    public function emailTest()
    {
        return view('frontend.email-test');
    }
    public function sendEmail(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'from_email' => 'required|in:circulars,finance',
        ]);

        $email = $validated['email'];
        $fromKey = $validated['from_email'];

        $senderMap = [
            'circulars' => ['from' => 'circulars@twoserendra.com', 'name' => '2S Circulars'],
            'finance' => ['from' => 'finance@twoserendra.com', 'name' => '2S Billing Assistant Finance'],
        ];

        $fromEmail = $senderMap[$fromKey]['from'];
        $fromName = $senderMap[$fromKey]['name'];

        // ✅ Queue the job
        SendElasticEmail::dispatch($email, $fromEmail, $fromName);

        return response()->json([
            'message' => 'Email queued successfully! You will receive it shortly.',
        ]);
    }

}
