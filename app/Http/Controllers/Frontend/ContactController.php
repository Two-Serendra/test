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
            // Send to admin
            Mail::to('lowriseadmin@twoserendra.com')->send(new AdminContactNotification($data));

            // Auto-reply to user
            Mail::to($data['email'])->send(new UserAutoReply($data));

            \Log::info('Contact form submitted & auto-reply sent:', $data);

            return back()->with('success', 'Your message has been sent successfully!');
        } catch (\Exception $e) {
            \Log::error('Contact form failed:', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return back()->with('error', 'Something went wrong while sending your message.');
        }
    }

}
