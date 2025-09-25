<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ResidentDetails;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
class ResidenceRequestController extends Controller
{
    public function showResidenceRequests()
    {
        $residenceRequests = ResidentDetails::with(relations: 'user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('backend.admin-residence-request', compact('residenceRequests'));
    }

    public function addResidenceRequests(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'array'],
            'user_id.*' => ['required', 'exists:users,id'],
            'resident_type' => ['required', 'array'],
            'resident_type.*' => ['required', Rule::in(['Owner', 'Tenant'])],
            'section' => ['required', 'array'],
            'section.*' => ['required', Rule::in(['Almond', 'Belize', 'Callery', 'Dolce', 'Aston', 'Red Oak', 'Meranti', 'Sequoia'])],
            'unit_no' => ['required', 'array'],
            'unit_no.*' => ['required', 'string', 'max:255'],
        ]);

        foreach ($request->user_id as $i => $userId) {
            ResidentDetails::create([
                'user_id' => $userId,
                'resident_type' => $request->resident_type[$i],
                'section' => $request->section[$i],
                'unit_no' => $request->unit_no[$i],
                'status' => 'Active',
            ]);
        }

        return back()->with('status', 'Residence request(s) submitted and pending approval.');
    }

    // public function getUpdatedResidenceTable()
    // {
    //     $residences = ResidentDetails::with('user')
    //         ->orderBy('created_at', 'desc')
    //         ->paginate(10);
    //     return response()->json([
    //         'data' => $residences->items(),
    //         'links' => (string) $residences->links('vendor.pagination.bootstrap-5')
    //     ]);
    // }

    public function getUpdatedResidenceTable(Request $request)
    {
        if (!$request->ajax()) {
            return abort(403, 'Unauthorized action');
        }

        $searchResidenceRequest = $request->input('searchResidenceRequest');

        $residencePagination = ResidentDetails::with('user')
            ->when($searchResidenceRequest, function ($query, $searchResidenceRequest) {
                $query->whereHas('user', function ($q) use ($searchResidenceRequest) {
                    $q->where('email', 'like', '%' . $searchResidenceRequest . '%');
                })
                    ->orWhere('unit_no', 'like', '%' . $searchResidenceRequest . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Transform the paginator's collection
        $residencePagination->getCollection()->transform(function ($residence) {
            return [
                'id' => $residence->id,
                'user' => [
                    'email' => $residence->user->email ?? null,
                ],
                'resident_type' => $residence->resident_type,
                'section' => $residence->section,
                'unit_no' => $residence->unit_no,
                'status' => $residence->status,
                'remarks' => $residence->remarks,
                'created_at' => $residence->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $residence->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'data' => $residencePagination->items(),
            'links' => $residencePagination
                ->appends(['searchResidenceRequest' => $searchResidenceRequest])
                ->withPath('/admin/get-updated-residence-table')
                ->links('vendor.pagination.bootstrap-5')
                ->render()
        ]);
    }



    public function fetchUserEmails(Request $request)
    {
        $search = $request->input('q'); // this is what Select2 sends

        $users = User::select('id', 'email')
            ->when($search, function ($query, $search) {
                $query->where('email', 'LIKE', "%{$search}%");
            })
            ->orderBy('email')
            ->limit(10)
            ->get();

        return response()->json($users);
    }


    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:ACTIVE,DENIED',
            'remarks' => 'nullable|string|max:500',
        ]);

        $residenceRequest = ResidentDetails::findOrFail($id);
        $residenceRequest->status = $request->status;
        if ($request->status === 'DENIED') {
            $residenceRequest->remarks = $request->remarks;
        }

        $residenceRequest->save();

        return response()->json([
            'message' => 'Status updated to ' . $request->status,
        ]);
    }

    public function fetchResidence($id)
    {
        $residence = ResidentDetails::with('user')->find($id);

        if (!$residence) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        return response()->json([
            'user_id' => $residence->user->id ?? null,
            'user_email' => $residence->user->email ?? 'N/A',
            'resident_type' => $residence->resident_type,
            'residence_section' => $residence->section,
            'residence_unit_no' => $residence->unit_no,
            'residence_status' => $residence->status,
            'residence_remarks' => $residence->remarks,
        ]);
    }

    public function updateResidence(Request $request)
    {
        $residence = ResidentDetails::find($request->info_id);

        if (!$residence) {
            Log::warning('Attempted to update a residence that does not exist.', [
                'info_id' => $request->info_id
            ]);
            return response()->json(['message' => 'Residence not found'], 404);
        }

        $oldData = $residence->toArray();

        $residence->user_id = $request->user_id;
        $residence->resident_type = $request->resident_type;
        $residence->section = $request->section;
        $residence->unit_no = $request->unit_no;
        $residence->save();

        // Log the changes
        Log::info('Residence updated successfully.', [
            'residence_id' => $residence->id,
            'updated_by' => auth()->user()->id ?? 'system',
            'old_data' => $oldData,
            'new_data' => $residence->toArray()
        ]);

        return response()->json(['message' => 'Residence updated successfully.']);
    }

}
