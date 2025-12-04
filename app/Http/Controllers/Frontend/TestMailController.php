<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailTest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
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

        try {
            $response = Http::post('https://api.elasticemail.com/v2/email/send', [
                'apikey' => env('ELASTICEMAIL_API_KEY'),
                'from' => 'no-reply@twoserendra.com',
                'fromName' => 'Two Serendra',
                'to' => $email,
                'subject' => 'Test Email',
                'template' => 'EmailTest', 
                'isTransactional' => true,
            ]);

            // Log the response
            \Log::info('Elastic Email Response: ' . $response->body());

            return response()->json(['message' => 'Email sent via Elastic Email template']);
        } catch (Exception $e) {
            \Log::error('Elastic Email failed: ' . $e->getMessage());
            return response()->json(['message' => 'Email sending failed', 'error' => $e->getMessage()], 500);
        }

    }
}
