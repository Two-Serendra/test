<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ResidentDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Carbon;


class ResidentDetailsController extends Controller
{
    public function showResidentDetails()
    {
        $emailPaginationLinks = ResidentDetails::paginate(10);
        return view('backend.admin-registered-emails', compact('emailPaginationLinks'));
    }

    // public function uploadResidentDetails(Request $request)
    // {
    //     $request->validate([
    //         'email_file' => 'required|file|mimes:csv,txt',
    //     ]);

    //     $file = $request->file('email_file');
    //     $fileName = $file->getClientOriginalName();
    //     $destinationPath = public_path('assets/files/emails');
    //     $file->move($destinationPath, $fileName);

    //     ResidentDetails::truncate();
    //     $filePath = $destinationPath . '/' . $fileName;
    //     $csv = fopen($filePath, 'r');

    //     $header = fgetcsv($csv);
    //     if (!$header || count($header) < 2) {
    //         fclose($csv);
    //         return back()->withErrors('Invalid CSV header. Expected at least 2 columns.');
    //     }

    //     $processed = 0;
    //     $skipped = 0;
    //     $uploadedEmails = []; 

    //     while (($row = fgetcsv($csv)) !== false) {
    //         if (count($row) < 2) {
    //             Log::warning('Skipped row due to insufficient columns', ['row' => $row]);
    //             $skipped++;
    //             continue;
    //         }

    //         $unitNo = trim($row[0]);
    //         $email = strtolower(trim($row[1]));
    //         $residentType = isset($row[2]) ? strtoupper(trim($row[2])) : null; 

    //         if (filter_var($email, FILTER_VALIDATE_EMAIL) && $unitNo !== '') {
    //             ResidentDetails::create([
    //                 'unit_no' => $unitNo,
    //                 'email' => $email,
    //                 'resident_type' => $residentType, 
    //             ]);

    //             $uploadedEmails[] = $email;

    //             Log::info('Uploaded email record', [
    //                 'unit_no' => $unitNo,
    //                 'email' => $email,
    //                 'resident_type' => $residentType,
    //             ]);

    //             $processed++;
    //         } else {
    //             Log::warning('Invalid or missing data in row', ['row' => $row]);
    //             $skipped++;
    //         }
    //     }

    //     fclose($csv);

    //     \DB::table('users')
    //         ->where('role_id', 0)
    //         ->whereNotIn('email', $uploadedEmails)
    //         ->update(['is_active' => false]);

    //     Log::info("Email CSV upload summary", [
    //         'processed' => $processed,
    //         'skipped' => $skipped,
    //         'uploaded_by' => auth()->user()->email ?? 'guest'
    //     ]);

    //     Log::info("Email CSV file stored at: assets/files/emails/$fileName");

    //     return back()->with('success', "CSV uploaded successfully. Processed: $processed, Skipped: $skipped");
    // }

    public function uploadResidentDetails(Request $request)
    {
        $request->validate([
            'email_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('email_file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $destinationPath = public_path('assets/files/emails');
        $file->move($destinationPath, $fileName);

        // Dispatch the job
        \App\Jobs\ProcessResidentCSV::dispatch($destinationPath . '/' . $fileName);

        // Immediately return success
        return response()->json(['success' => true]);
    }


    public function getUpdatedResidentDetailsTable(Request $request)
    {
        $search = $request->input('searchEmails');

        $emailPaginationLinks = ResidentDetails::when($search, function ($query, $search) {
            return $query->where(function ($q) use ($search) {
                $q->where('unit_no', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $formatted = $emailPaginationLinks->getCollection()->map(function ($email) {
            return [
                'id' => $email->id,
                'unit_no' => $email->unit_no,
                'email' => $email->email,
                'resident_type' => $email->resident_type,
                'created_at' => Carbon::parse($email->created_at)->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'data' => $formatted,
            'links' => $emailPaginationLinks
                ->appends(['searchEmails' => $search])
                ->withPath('/admin/get-updated-emails-table')
                ->links('vendor.pagination.bootstrap-5')->render()
        ]);
    }

    public function fetchResidentDetails($id)
    {
        $email = ResidentDetails::find($id);
        if (!$email) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        return response()->json([
            'unit_no' => $email->unit_no,
            'email' => $email->email,
            'resident_type' => $email->resident_type,
        ]);

    }

    public function updateEmail(Request $request)
    {
        try {
            $email = ResidentDetails::findOrFail($request->input('info_id'));

            $email->unit_no = $request->input('unit_no');
            $email->email = $request->input('email');
            $email->resident_type = $request->input('resident_type');
            $email->save();

            return response()->json(['status' => true, 'message' => 'Email updated successfully']);
        } catch (\Exception $e) {
            \Log::error('Email update failed: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Email update failed']);
        }
    }

    public function deleteEmail(Request $request)
    {
        $emailId = $request->input('email_id');
        try {
            $email = ResidentDetails::findOrFail($emailId);
            $email->delete();

            return response()->json([
                'status' => true,
                'message' => 'Email deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Email deletion failed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
