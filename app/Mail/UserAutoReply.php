<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserAutoReply extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $data)
    {
    }

    public function build()
    {
        return $this->subject('Thank you for contacting Two Serendra')
            ->view('emails.contact-auto-reply')
            ->with([
                'name' => $this->data['name'],
                'subject' => $this->data['subject'],
                'inquiry' => $this->data['inquiry'],
            ]);
    }
}