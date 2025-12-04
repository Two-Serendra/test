<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailTest;
use Illuminate\Support\Facades\Log;
use Exception;

class TestMailController extends Controller
{
    public function emailTest()
    {
        return view('frontend.email-test');
    }

    public function sendEmail(Request $request)
    {
        $email = $request->email;

        Log::info("Attempting to send email to: " . $email);

        try {
            Mail::to($email)->send(new EmailTest()); // use send() for testing
            Log::info("Email sent immediately to: " . $email);

            return response()->json(['message' => 'Email sent']);

        } catch (Exception $e) {

            Log::error("Email sending failed: " . $e->getMessage());

            return response()->json([
                'message' => 'Email sending failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
