<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminContactNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        return $this->from('itdept@twoserendra.com', 'Two Serendra Website')
            ->replyTo($this->data['email'], $this->data['name'])
            ->subject('New Contact Form Submission: ' . $this->data['subject'])
            ->view('emails.contact')
            ->with([
                'name' => $this->data['name'],
                'email' => $this->data['email'],
                'mobile' => $this->data['mobile'],
                'inquiry' => $this->data['inquiry'],
                'subject' => $this->data['subject'],
            ])
            ->withSwiftMessage(function ($message) {
                $message->getHeaders()->addTextHeader('X-ElasticEmail-MessageCategory', 'Transactional');
            });
    }
}