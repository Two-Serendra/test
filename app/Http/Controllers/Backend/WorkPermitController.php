<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\MinorWorkPermit;
use App\Models\WalkinWorkPermit;
use App\Models\User;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;

class WorkPermitController extends Controller
{
    public function minorWorkPermit()
    {

        $minorWorkPermits = MinorWorkPermit::orderBy('created_at', 'desc')->paginate(10);
        return view('backend.admin-minor-work-permit', compact('minorWorkPermits'));
    }

    public function searchMinorWorkPermit(Request $request)
    {
        $searchTerm = $request->input('searchMinorWorkPermit');

        $minorWorkPermits = MinorWorkPermit::with('user')
            ->when($searchTerm, function ($query, $searchTerm) {
                return $query->whereHas('user', function ($userQuery) use ($searchTerm) {
                    $userQuery->where('name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('unit_no', 'LIKE', "%{$searchTerm}%");
                });
            })
            ->paginate(10)
            ->appends(['searchMinorWorkPermit' => $searchTerm]);

        return view('backend.admin-minor-work-permit', compact('minorWorkPermits', 'searchTerm'));
    }

    public function walkinWorkPermit()
    {
        $walkinWorkPermits = WalkinWorkPermit::orderBy('created_at', 'desc')->paginate(perPage: 5);
        return view('backend.admin-walkin-work-permit', compact('walkinWorkPermits'));
    }



    public function storeWorkPermit(Request $request)
    {

        $walkin = new WalkinWorkPermit();
        $walkin->permit_type = $request->permit_type;
        $walkin->unit_no = $request->unit_no;
        $walkin->section = $request->section;
        $walkin->no_days = $request->no_days;
        $walkin->work_permit_booking_date = $request->work_permit_booking_date;
        $walkin->under_renovation = $request->under_renovation;
        $walkin->description = $request->description;
        $walkin->contractor = $request->contractor;
        $walkin->approved_by = $request->aprroved_by;
        $walkin->save();

        return redirect()->back()->with('success', 'Service added successfully.');
    }

    public function fetchWalkinWorkPermit($id)
    {
        $fetchWalkinWorkPermit = WalkinWorkPermit::findOrFail($id);
        return response()->json($fetchWalkinWorkPermit);
    }


    public function getUpdatedWalkinWorkPermitTable()
    {
        $walkinWorkPermits = WalkinWorkPermit::orderBy('created_at', 'desc')->paginate(5);
        return response()->json([
            'data' => $walkinWorkPermits->items(),
            'links' => (string) $walkinWorkPermits->links('vendor.pagination.bootstrap-5')
        ]);
    }

    public function updateWalkinWorkPermit(Request $request)
    {
        try {
            $walkinWorkPermit = WalkinWorkPermit::findOrFail($request->input('info_id'));

            $walkinWorkPermit->permit_type = $request->permit_type;
            $walkinWorkPermit->unit_no = $request->unit_no;
            $walkinWorkPermit->section = $request->section;
            $walkinWorkPermit->no_days = $request->no_days;
            $walkinWorkPermit->work_permit_booking_date = $request->work_permit_booking_date;
            $walkinWorkPermit->under_renovation = $request->under_renovation;
            $walkinWorkPermit->description = $request->description;
            $walkinWorkPermit->contractor = $request->contractor;
            $walkinWorkPermit->approved_by = $request->approved_by;

            $walkinWorkPermit->save();

            return response()->json(['status' => true, 'message' => 'Updated successfully']);
        } catch (\Exception $e) {
            \Log::error('Service update failed: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Update failed']);
        }
    }

    public function deleteWalkinWorkPermit(Request $request)
    {
        $walkinWorkPermitId = $request->input('walkinWorkPermit_id');

        try {
            $walkinWorkPermit = WalkinWorkPermit::findOrFail($walkinWorkPermitId);
            $walkinWorkPermit->delete();

            return response()->json([
                'status' => true,
                'message' => 'Service deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Service deletion failed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function searchWalkinWorkPermit(Request $request)
    {
        $searchWalkinWorkPermit = $request->input('searchWalkinWorkPermit'); // <-- match the input name

        $walkinWorkPermits = WalkinWorkPermit::when($searchWalkinWorkPermit, function ($query, $searchWalkinWorkPermit) {
            $query->where('unit_no', 'LIKE', "%{$searchWalkinWorkPermit}%");

        })
            ->orderBy('created_at', 'desc') // Optional: show newest first
            ->paginate(10)
            ->appends(['searchMinorWorkPermit' => $searchWalkinWorkPermit]); // <-- match input name here too

        return view('backend.admin-walkin-work-permit', compact('walkinWorkPermits', 'searchWalkinWorkPermit'));
    }

    public function downloadWalkinWorkPermit(Request $request)
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        Log::info("Download history request received", [
            'from_date' => $fromDate,
            'to_date' => $toDate
        ]);

        $formattedFromDate = Carbon::parse($fromDate)->format('m-d-Y');
        $formattedToDate = Carbon::parse($toDate)->format('m-d-Y');

        $data = DB::table('walkin_work_permits')

            ->select(
                'permit_type',
                'unit_no',
                'section',
                'no_days',
                'work_permit_booking_date',
                'under_renovation',
                'description',
                'contractor',
                'approved_by',
                'created_at',
                'updated_at',
            )
            ->whereBetween('work_permit_booking_date', [$fromDate, $toDate])
            ->get();


        $fileName = "2S_Walkin_Permit_Reports_{$formattedFromDate}_to_{$formattedToDate}.csv";

        $response = new StreamedResponse(function () use ($data) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Permit Type', 'Unit No.', 'Section', 'Days No', 'Booking Date', 'Under Renovation', 'Description', 'Contractor', 'Approved By', 'Created At', 'Updated At']);

            foreach ($data as $row) {
                $work_permit_booking_date = Carbon::parse($row->work_permit_booking_date)->format('F j, Y');
                fputcsv($handle, [
                    $row->permit_type,
                    $row->unit_no,
                    $row->section,
                    $row->no_days,
                    $work_permit_booking_date,
                    $row->under_renovation,
                    $row->description,
                    $row->contractor,
                    $row->approved_by,
                    $row->created_at,
                    $row->updated_at,
                ]);

                ob_flush();
                flush();
            }

            fclose($handle);
        });

        Log::info("CSV file generation completed", [
            'filename' => $fileName
        ]);

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;
    }
}
