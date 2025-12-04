<?php

namespace App\Mail;

use App\Models\FunctionRoomBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EmailTest extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;


    /**
     * Build the message.
     */

    public function __construct()
    {
       
    }
    public function build()
    {
       

    return $this->from('circulars@twoserendra.com', 'Two Serendra')
            ->subject('Test Mail')
            ->view('emails.email-test');
    }
}
