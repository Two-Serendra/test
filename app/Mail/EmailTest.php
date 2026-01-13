<?php

namespace App\Mail;

use App\Models\FunctionRoomBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Http;

class EmailTest extends Mailable implements ShouldQueue
{ 
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $email;
    public $from;
    public $fromName;

    public function __construct($email, $from, $fromName)
    {
        $this->email = $email;
        $this->from = $from;
        $this->fromName = $fromName;
    }

    public function handle()
    {
        try {
            $apiKey = config('services.elasticemail.key');

            Log::info('Queued Elastic Email sending', [
                'from' => $this->from,
                'to' => $this->email,
            ]);

            $response = Http::asForm()->post(
                'https://api.elasticemail.com/v2/email/send',
                [
                    'apikey' => $apiKey,
                    'from' => $this->from,
                    'fromName' => $this->fromName,
                    'to' => $this->email,
                    'subject' => 'Two Serendra Test Email',
                    'template' => 'EmailTest',
                    'isTransactional' => true,
                ]
            );

            if (!$response->successful()) {
                Log::error('Elastic Email API error', [
                    'response' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Queued Elastic Email failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
