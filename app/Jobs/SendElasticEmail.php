<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable; // ✅ important
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendElasticEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $email;
    public string $fromEmail;
    public string $fromName;

    public function __construct(string $email, string $fromEmail, string $fromName)
    {
        $this->email = $email;
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
    }

    public function handle(): void
    {
        try {
            $apiKey = config('services.elasticemail.key');

            Log::info('Queued Elastic Email sending', [
                'from' => $this->fromEmail,
                'to' => $this->email,
            ]);

            $response = Http::asForm()->post('https://api.elasticemail.com/v2/email/send', [
                'apikey' => $apiKey,
                'from' => $this->fromEmail,
                'fromName' => $this->fromName,
                'to' => $this->email,
                'subject' => 'Two Serendra Test Email',
                'template' => 'EmailTest',
                'isTransactional' => true,
            ]);

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
