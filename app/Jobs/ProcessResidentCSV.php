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
    public $timeout = 600;
    public $tries = 1;
    protected $filePath;

    public function __construct($filePath)
    {

        $this->filePath = $filePath;
    }


    public function handle()
    {
        try {
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
            fgetcsv($csv);

            $currentRow = 0;
            $skippedFile = storage_path('app/skipped_emails.csv');
            $skippedHandle = fopen($skippedFile, 'w');
            $lineNumber = 1;
            fputcsv($skippedHandle, ['row', 'unit_no', 'email', 'resident_type', 'reason']);
            $seen = [];

            while (($row = fgetcsv($csv)) !== false) {
                $currentRow++;
                $lineNumber++;

                Log::info('ROW DEBUG', [
                    'line' => $lineNumber,
                    'raw_row' => $row
                ]);

                if (!$row || count(array_filter($row)) === 0) {
                    $skipped++;
                    fputcsv($skippedHandle, [$currentRow, '', '', '', 'Empty row']);
                    continue;
                }

                if (count($row) < 2) {
                    $skipped++;
                    fputcsv($skippedHandle, [$currentRow, $row[0] ?? '', $row[1] ?? '', $row[2] ?? '', 'Not enough columns']);
                    continue;
                }

                if ($currentRow % 100 === 0) {
                    cache()->put('upload_progress', round(($currentRow / $totalRows) * 100));
                }

                $unitNo = trim($row[0] ?? '');
                $email = $row[1] ?? '';

                $email = mb_convert_encoding($email, 'UTF-8', 'UTF-8');
                $email = preg_replace('/[\x{00A0}\x{200B}\x{200C}\x{200D}]/u', '', $email);
                $email = trim($email);
                $email = preg_replace('/[\x00-\x1F\x7F]/u', '', $email);

                // lowercase
                $email = strtolower($email);
                if (function_exists('idn_to_ascii')) {
                    $asciiEmail = @idn_to_ascii($email, 0, INTL_IDNA_VARIANT_UTS46);
                    if ($asciiEmail)
                        $email = $asciiEmail;
                }

                $residentType = isset($row[2]) ? strtoupper(trim($row[2])) : null;

                $key = $unitNo . '|' . $email . '|' . $residentType;

                if (isset($seen[$key])) {
                    $skipped++;
                    fputcsv($skippedHandle, [
                        $currentRow,
                        $unitNo,
                        $email,
                        $residentType,
                        'Duplicate unit + email + resident_type'
                    ]);
                    continue;
                }

                $seen[$key] = true;

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


            cache()->put('upload_progress', 100);


            Log::info("CSV Processing complete: Processed=$processed, Skipped=$skipped. Skipped rows saved to skipped_emails.csv");
            if ($skipped > 0 && file_exists($skippedFile)) {
                $skippedContent = file_get_contents($skippedFile);
                Log::info("Skipped rows content:\n" . $skippedContent);
            }


        } catch (\Throwable $e) {
            Log::error('CSV Job Failed: ' . $e->getMessage());
            Log::error($e->getTraceAsString());


            throw $e;
        }
    }
}