<?php

namespace App\Jobs;

use App\Models\ResidentDetails;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class ProcessResidentCSV implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    // public function handle()
    // {
    //     cache()->put('upload_progress', 0);

    //     $chunkSize = 500;
    //     $batch = [];
    //     $uploadedEmails = [];
    //     $processed = 0;
    //     $skipped = 0;

    //     $csv = fopen($this->filePath, 'r');

    //     $header = fgetcsv($csv);
    //     if (!$header || count($header) < 2) {
    //         fclose($csv);
    //         return;
    //     }

    //     // Clear old data
    //     ResidentDetails::truncate();

    //     // Count total rows
    //     $totalRows = 0;
    //     $fileHandle = fopen($this->filePath, 'r');
    //     while (fgetcsv($fileHandle) !== false)
    //         $totalRows++;
    //     fclose($fileHandle);

    //     $csv = fopen($this->filePath, 'r');
    //     fgetcsv($csv); // skip header

    //     $currentRow = 0;

    //     while (($row = fgetcsv($csv)) !== false) {
    //         $currentRow++;

    //         if ($currentRow % 100 == 0) {
    //             cache()->put('upload_progress', round(($currentRow / $totalRows) * 100));
    //         }

    //         if (count($row) < 2) {
    //             $skipped++;
    //             continue;
    //         }

    //         $unitNo = trim($row[0]);
    //         $email = strtolower(trim($row[1]));
    //         $residentType = isset($row[2]) ? strtoupper(trim($row[2])) : null;

    //         if (filter_var($email, FILTER_VALIDATE_EMAIL) && $unitNo !== '') {
    //             $batch[] = [
    //                 'unit_no' => $unitNo,
    //                 'email' => $email,
    //                 'resident_type' => $residentType,
    //                 'created_at' => now(),
    //                 'updated_at' => now(),
    //             ];

    //             $uploadedEmails[] = $email;
    //             $processed++;

    //             if (count($batch) >= $chunkSize) {
    //                 ResidentDetails::insert($batch);
    //                 $batch = [];
    //             }
    //         } else {
    //             $skipped++;
    //         }
    //     }

    //     if (!empty($batch)) {
    //         ResidentDetails::insert($batch);
    //     }

    //     fclose($csv);

    //     \DB::table('users')
    //         ->where('role_id', 0)
    //         ->whereNotIn('email', $uploadedEmails)
    //         ->update(['is_active' => false]);

    //     cache()->put('upload_progress', 100);
    // }


    public function handle()
    {
        cache()->put('upload_progress', 0);

        $chunkSize = 500;
        $batch = [];
        $uploadedEmails = [];
        $processed = 0;
        $skipped = 0;

        $csv = fopen($this->filePath, 'r');
        $header = fgetcsv($csv);
        if (!$header || count($header) < 2) {
            fclose($csv);
            Log::error('CSV header invalid or missing');
            return;
        }

        ResidentDetails::truncate();
        $totalRows = 0;
        $fileHandle = fopen($this->filePath, 'r');
        while (fgetcsv($fileHandle) !== false)
            $totalRows++;
        fclose($fileHandle);

        $csv = fopen($this->filePath, 'r');
        fgetcsv($csv); // skip header

        $currentRow = 0;
        $skippedFile = storage_path('app/skipped_emails.csv');
        $skippedHandle = fopen($skippedFile, 'w');
        fputcsv($skippedHandle, ['row', 'unit_no', 'email', 'resident_type', 'reason']);

        while (($row = fgetcsv($csv)) !== false) {
            $currentRow++;

            if ($currentRow % 100 === 0) {
                cache()->put('upload_progress', round(($currentRow / $totalRows) * 100));
            }

            if (count($row) < 2) {
                $skipped++;
                fputcsv($skippedHandle, [$currentRow, $row[0] ?? '', $row[1] ?? '', $row[2] ?? '', 'Not enough columns']);
                continue;
            }

            $unitNo = trim($row[0] ?? '');
            $email = $row[1] ?? '';
            $email = strtolower(trim($email));
            $email = preg_replace('/[\x{00A0}\s]+/u', '', $email);
            $email = rtrim($email, ',');
            $email = preg_replace('/^(cc:|CC:)/', '', $email);
            $email = rtrim($email, '\\');

            if (function_exists('idn_to_ascii')) {
                $asciiEmail = @idn_to_ascii($email, 0, INTL_IDNA_VARIANT_UTS46);
                if ($asciiEmail)
                    $email = $asciiEmail;
            }

            $residentType = isset($row[2]) ? strtoupper(trim($row[2])) : null;

            if ($unitNo === '') {
                $skipped++;
                fputcsv($skippedHandle, [$currentRow, $unitNo, $email, $residentType, 'Empty unit_no']);
                continue;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipped++;
                fputcsv($skippedHandle, [$currentRow, $unitNo, $email, $residentType, 'Invalid email']);
                continue;
            }

            if ($residentType && strlen($residentType) > 50) {
                $skipped++;
                fputcsv($skippedHandle, [$currentRow, $unitNo, $email, $residentType, 'resident_type too long']);
                continue;
            }

            $batch[] = [
                'unit_no' => $unitNo,
                'email' => $email,
                'resident_type' => $residentType,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $uploadedEmails[] = $email;
            $processed++;

            if (count($batch) >= $chunkSize) {
                ResidentDetails::insert($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            ResidentDetails::insert($batch);
        }

        fclose($csv);
        fclose($skippedHandle);

        DB::table('users')
            ->where('role_id', 0)
            ->whereNotIn('email', $uploadedEmails)
            ->update(['is_active' => false]);

        cache()->put('upload_progress', 100);

        // Log summary
        Log::info("CSV Processing complete: Processed=$processed, Skipped=$skipped. Skipped rows saved to skipped_emails.csv");

        // Log content of skipped rows
        if ($skipped > 0 && file_exists($skippedFile)) {
            $skippedContent = file_get_contents($skippedFile);
            Log::info("Skipped rows content:\n" . $skippedContent);
        }
    }
}