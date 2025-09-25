<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\ContactAutoReply;
use App\Mail\ContactFormToAdmin;
use App\Mail\AdminContactNotification;
use App\Mail\UserAutoReply;
use Illuminate\Support\Facades\Http;

class ContactController extends Controller
{

    public function send(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'mobile' => 'nullable|string',
            'subject' => 'required|string',
            'inquiry' => 'required|string',
            'g-recaptcha-response' => 'required',
        ]);

        // Verify with Google
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => env('NOCAPTCHA_SECRET'),
            'response' => $request->input('g-recaptcha-response'),
            'remoteip' => $request->ip(),
        ]);

        $result = $response->json();

        \Log::info('reCAPTCHA verification result:', $result);

        if (!($result['success'] ?? false) || ($result['score'] ?? 0) < 0.5) {
            return back()->withErrors([
                'g-recaptcha-response' => 'reCAPTCHA verification failed. Please try again.'
            ])->withInput();
        }

        // if (
        //     !($result['success'] ?? false)
        //     || ($result['score'] ?? 0) < 0.7  // stricter threshold
        //     || ($result['action'] ?? '') !== 'contact'
        // ) {
        //     return back()->withErrors([
        //         'g-recaptcha-response' => 'reCAPTCHA verification failed. Please try again.'
        //     ])->withInput();
        // }


        $data = $request->only(['name', 'email', 'mobile', 'subject', 'inquiry']);

        try {
            Mail::to('lowriseadmin@twoserendra.com')->send(new AdminContactNotification($data));
            Mail::to($data['email'])->send(new UserAutoReply($data));

            \Log::info('Contact form submitted & auto-reply sent:', $data);

            return back()->with('success', 'Your message has been sent successfully!');
        } catch (\Exception $e) {
            \Log::error('Contact form failed:', ['error' => $e->getMessage(), 'data' => $data]);
            return back()->with('error', 'Something went wrong while sending your message.');
        }
    }

}
