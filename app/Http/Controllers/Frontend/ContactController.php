<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
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
        ]);

        $data = $request->only(['name', 'email', 'mobile', 'subject', 'inquiry']);

        try {
            // 1. Send to admin
            Mail::send('emails.contact', $data, function ($message) use ($data) {
                $message->to('lowriseadmin@twoserendra.com')
                    ->subject('New Contact Form Submission: ' . $data['subject']);
            });

            // 2. Auto-reply to sender
            Mail::send('emails.contact-auto-reply', $data, function ($message) use ($data) {
                $message->to($data['email'])
                    ->subject('We received your message – Two Serendra');
            });

            Log::info('Contact form submitted & auto-reply sent:', $data);

            return back()->with('success', 'Your message has been sent successfully!');
        } catch (\Exception $e) {
            Log::error('Contact form failed:', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return back()->with('error', 'Something went wrong while sending your message.');
        }
    }

}
