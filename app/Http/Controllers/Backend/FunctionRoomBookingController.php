<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Events\DiscoverEvents;
use Illuminate\Http\Request;
use App\Models\FunctionRoomBooking;
use App\Models\FunctionRoom;
use App\Models\AddOn;
use App\Models\AddOnFunctionRoomBooking;
use App\Models\ResidentDetails;
use App\Models\FunctionRoomDiscount;
use App\Models\FunctionRoomDateBlocking;
use App\Models\FunctionRoomBookingSupplier;
use App\Mail\FunctionRoomBookingConfirmedNotification;
use App\Notifications\UserFunctionRoomBookingBellNotification;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Mail\UserFunctionRoomBookingNotification;
use App\Mail\FinanceFunctionRoomBookingNotification;
use App\Events\FunctionRoomBookingCreated;



class FunctionRoomBookingController extends Controller
{


    public function showFunctionRoomBookings(Request $request)
    {
        $roleId = auth()->user()->role_id;
        $baseQuery = FunctionRoomBooking::query();

        if (in_array($roleId, [1, 5, 6, 7])) {
            $baseQuery->whereIn('booking_status', [0, 1]);
        } elseif ($roleId == 2) {
            $baseQuery->whereIn('booking_status', [0, 1])
                ->whereNotNull('authorization_file');
        } elseif ($roleId == 3) {
            $baseQuery->whereIn('booking_status', [0, 1])
                ->where('has_suppliers', 1);
        }
        $firstBookingIds = $baseQuery
            ->selectRaw('MIN(id) as id')
            ->groupBy('transaction_no')
            ->pluck('id');

        $functionRoomBookings = FunctionRoomBooking::with(['user', 'functionRoom', 'addOns'])
            ->whereIn('id', $firstBookingIds)
            ->orderBy('id', 'desc')
            ->paginate(10);

        $residences = ResidentDetails::select('id', 'unit_no', 'resident_type', 'email')
            ->whereNotNull('unit_no')
            ->get();

        $functionRooms = FunctionRoom::where('function_room_status', 1)->get();
        $addOns = AddOn::where('status', 1)->get();

        return view('backend.admin-function-room-booking', compact(
            'functionRoomBookings',
            'functionRooms',
            'addOns',
            'residences'
        ));

    }


    public function checkUnitTenant($unitNo)
    {
        $hasTenant = DB::table('emails')
            ->where('unit_no', $unitNo)
            ->where('resident_type', 'TENANT')
            ->exists();

        return response()->json(['hasTenant' => $hasTenant]);
    }

    public function getAddonsAvailability(Request $request)
    {
        $date = $request->date;

        $addons = AddOn::where('status', 1)->get();

        $availability = [];

        foreach ($addons as $addon) {

            $reserved = AddOnFunctionRoomBooking::whereHas('booking', function ($q) use ($date) {
                $q->where('function_room_booking_date', $date)
                    ->whereIn('booking_status', [0, 1]);
            })
                ->where('add_on_id', $addon->id)
                ->sum('qty');

            $availability[$addon->id] = max(0, $addon->qty - $reserved);
        }

        return response()->json($availability);
    }

    public function FunctionRoomBookingApproval(Request $request)
    {

        $bookingId = $request->booking_id;
        $booking = FunctionRoomBooking::findOrFail($bookingId);
        $transactionNo = $booking->transaction_no;
        $groupBookings = FunctionRoomBooking::where('transaction_no', $transactionNo)->get();
        $user = auth()->user();

        switch ($user->role_id) {
            case 6: // Concierge
                $groupBookings->each(function ($b) use ($user) {
                    $b->update([
                        'concierge_approval' => 1,
                        'concierge_user_id' => $user->id,
                        'concierge_action_at' => now(),
                        'concierge_remarks' => null,
                    ]);
                });
                break;

            case 2: // Admin
                if ($booking->authorization_file && $booking->concierge_approval) {
                    $groupBookings->each(function ($b) use ($user) {
                        $b->update([
                            'admin_approval' => 1,
                            'admin_user_id' => $user->id,
                            'admin_action_at' => now(),
                            'admin_remarks' => null, // ✅ clear old remarks
                        ]);
                    });

                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot approve yet. Waiting for Concierge approval.',
                    ], 422);
                }
                break;

            case 5: // Finance
                $groupBookings->each(function ($b) use ($user) {
                    $b->update([
                        'finance_approval' => 1,
                        'finance_user_id' => $user->id,
                        'finance_action_at' => now(),
                        'finance_remarks' => null, // ✅ clear old remarks
                    ]);
                });
                break;

            case 3: // Engineering
                if ($booking->suppliers && $booking->suppliers->count() > 0) {
                    $groupBookings->each(function ($b) use ($user) {
                        $b->update([
                            'engineering_approval' => 1,
                            'engineering_user_id' => $user->id,
                            'engineering_action_at' => now(),
                            'engineering_remarks' => null, // ✅ clear old remarks
                        ]);
                    });
                }
                break;
            case 7: // Manager
                $groupBookings->each(function ($b) use ($user) {
                    $b->update([
                        'manager_approval' => 1,
                        'manager_user_id' => $user->id,
                        'manager_action_at' => now(),
                        'manager_remarks' => null, // ✅ clear old remarks
                        'booking_status' => '1',
                    ]);
                });
                break;
        }

        if ($user->role_id !== 7 && $booking->isReadyForConfirmation()) {
            $groupBookings->each(function ($b) {
                $b->update(['booking_status' => 1]);
            });
        }

        // After updating all approvals
        if ($groupBookings->first()->booking_status == 1) {
            $mainBooking = $groupBookings->first(); // Pick the first booking as "main"

            // Send email once for the whole transaction
            Mail::to($mainBooking->user->email)
                ->queue(new FunctionRoomBookingConfirmedNotification($mainBooking, $groupBookings));

            // Send bell notification once
            $mainBooking->user->notify((new UserFunctionRoomBookingBellNotification($mainBooking))->delay(now()->addSeconds(1)));
        }

        return response()->json([
            'success' => true,
            'message' => 'Approval updated successfully',
            'booking_status' => $booking->booking_status
        ]);
    }

    public function FunctionRoomBookingReject(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:function_room_bookings,id',
            'remarks' => 'required|string|max:1000',
        ]);

        $booking = FunctionRoomBooking::findOrFail($request->booking_id);
        $transactionNo = $booking->transaction_no;
        $groupBookings = FunctionRoomBooking::where('transaction_no', $transactionNo)->get();
        $user = auth()->user();

        switch ($user->role_id) {
            case 6: // Concierge
                $groupBookings->each(function ($b) use ($user, $request) {
                    $b->update([
                        'concierge_approval' => 2, // rejected
                        'concierge_user_id' => $user->id,
                        'concierge_action_at' => now(),
                        'concierge_remarks' => $request->remarks,
                    ]);
                });

                break;

            case 2: // Admin
                $groupBookings->each(function ($b) use ($user, $request) {
                    $b->update([
                        'admin_approval' => 2,
                        'admin_user_id' => $user->id,
                        'admin_action_at' => now(),
                        'admin_remarks' => $request->remarks,
                    ]);
                });
                break;

            case 5: // Finance
                $groupBookings->each(function ($b) use ($user, $request) {
                    $b->update([
                        'finance_approval' => 2,
                        'finance_user_id' => $user->id,
                        'finance_action_at' => now(),
                        'finance_remarks' => $request->remarks,
                    ]);
                });
                break;

            case 3: // Engineering
                $groupBookings->each(function ($b) use ($user, $request) {
                    $b->update([
                        'engineering_approval' => 2,
                        'engineering_user_id' => $user->id,
                        'engineering_action_at' => now(),
                        'engineering_remarks' => $request->remarks,
                    ]);
                });
                break;

            case 7: // Manager

                $groupBookings->each(function ($b) use ($user, $request) {
                    $b->update([
                        'manager_approval' => 2,
                        'manager_user_id' => $user->id,
                        'manager_action_at' => now(),
                        'manager_remarks' => $request->remarks,
                        'booking_status' => 2, // rejected (global)
                    ]);
                });

                break;
        }

        return response()->json([
            'success' => true,
            'message' => 'Booking rejected successfully',
            'booking_status' => $booking->booking_status,
        ]);
    }

    public function getFunctionRoomBookingDetails($id)
    {
        $booking = FunctionRoomBooking::with(['user', 'functionRoom', 'suppliers', 'addOns', 'createdBy'])->findOrFail($id);
        $userRole = auth()->user()->role_id;
        $booking->event_start_time = $booking->event_start_time
            ? Carbon::parse($booking->event_start_time)->format('h:i A')
            : null;
        $booking->event_end_time = $booking->event_end_time
            ? Carbon::parse($booking->event_end_time)->format('h:i A')
            : null;
        $authorizationFileUrl = $booking->authorization_file ? asset($booking->authorization_file) : null;
        $booking->suppliers->transform(function ($supplier) {
            $supplier->attachment_url = $supplier->attachment ? asset($supplier->attachment) : null;
            return $supplier;
        });
        $booking->has_suppliers = $booking->suppliers->count() > 0;
        $approvalColumns = [
            6 => 'concierge_approval',
            2 => 'admin_approval',
            5 => 'finance_approval',
            3 => 'engineering_approval',
            7 => 'manager_approval',
        ];

        $userApprovalColumn = $approvalColumns[$userRole] ?? null;
        $userApprovalStatus = $booking->{$userApprovalColumn} ?? 0;

        $approvalOrder = ['concierge', 'admin', 'finance', 'engineering', 'manager'];

        $rejectedByPrevious = false;
        $rejectedByRole = null;
        $currentUserRejected = false;

        foreach ($approvalOrder as $role) {
            $status = $booking->{$role . '_approval'} ?? 0;

            if ($status == 2) {
                if ($role . '_approval' == $userApprovalColumn) {
                    $currentUserRejected = true;
                } else {
                    $rejectedByPrevious = true;
                    $rejectedByRole = ucfirst($role);
                }
                break;
            }
        }

        $showApproveButton = false;
        $showViewButton = false;
        $waitingReason = null;
        $isApproved = false;

        switch ($userRole) {
            case 6: // Concierge
                $showViewButton = true;
                if (!$booking->concierge_user_id && !$rejectedByPrevious)
                    $showApproveButton = true;
                elseif ($booking->concierge_approval == 1)
                    $isApproved = true;
                break;

            case 2: // Admin
                $showViewButton = true;
                if ($booking->authorization_file) {
                    if (!$booking->concierge_user_id)
                        $waitingReason = 'Waiting for Concierge';
                    elseif (!$booking->admin_user_id && !$rejectedByPrevious)
                        $showApproveButton = true;
                    else
                        $isApproved = true;
                }
                break;

            case 5: // FINANCE
                $showViewButton = true;
                if ($booking->concierge_approval != 1) {
                    $waitingReason = 'Waiting for Concierge';
                } elseif ($booking->authorization_file && $booking->admin_approval != 1) {
                    $waitingReason = 'Waiting for Admin';
                } elseif ($booking->finance_approval == 0 && !$rejectedByPrevious) {
                    $showApproveButton = true;
                }

                // 4. Already approved/rejected by Finance
                else {
                    $isApproved = true;
                }
                break;


            case 3: // Engineering
                $showViewButton = true;
                if (($booking->finance_approval ?? 0) != 1)
                    $waitingReason = 'Waiting for Finance';
                elseif (!$booking->engineering_user_id && !$rejectedByPrevious)
                    $showApproveButton = true;
                else
                    $isApproved = true;
                break;

            case 7: // Manager
                $showViewButton = true;
                if (!$booking->concierge_user_id)
                    $waitingReason = 'Waiting for Concierge';
                elseif ($booking->authorization_file && !$booking->admin_user_id)
                    $waitingReason = 'Waiting for Admin';
                elseif (!$booking->finance_user_id)
                    $waitingReason = 'Waiting for Finance';
                elseif ($booking->has_suppliers && !$booking->engineering_user_id)
                    $waitingReason = 'Waiting for Engineering';
                elseif (!$booking->manager_user_id && !$rejectedByPrevious)
                    $showApproveButton = true;
                else
                    $isApproved = true;
                break;
        }

        $booking->created_by_name = $booking->createdBy?->name;

        $transactionNo = $booking->transaction_no;
        $linkedBookings = FunctionRoomBooking::with('functionRoom')
            ->where('transaction_no', $transactionNo)
            ->orderBy('function_room_id')
            ->get();

        $roomsBreakdown = [];

        foreach ($linkedBookings as $linked) {
            $hours = $linked->duration_in_hours ?? 1;
            $rate = $linked->final_rate ?? $linked->functionRoom->function_room_rate;
            $roomTotal = $rate * $hours;

            $addons = [];
            $addonsTotal = 0;

            foreach ($linked->addOns as $addon) {
                $qty = $addon->pivot->quantity ?? 0;
                $price = $addon->pivot->price ?? 0;
                $lineTotal = $qty * $price;

                $addons[] = [
                    'item' => $addon->item,
                    'qty' => $qty,
                    'price' => $price,
                    'total' => $lineTotal,
                ];

                $addonsTotal += $lineTotal;
            }

            $roomsBreakdown[] = [
                'room_name' => $linked->functionRoom->function_room_name,
                'hours' => (float) $hours,
                'rate' => (float) $rate,
                'room_total' => (float) $roomTotal,
                'addons' => $addons,
                'addons_total' => (float) $addonsTotal,
            ];
        }


        return response()->json([
            'success' => true,
            'booking' => $booking,
            'authorization_file_url' => $authorizationFileUrl,
            'show_approve_button' => $showApproveButton,
            'show_view_button' => $showViewButton,
            'is_approved' => $isApproved,
            'waiting_reason' => $waitingReason,
            'rejectedByPrevious' => $rejectedByPrevious,
            'rejectedByRole' => $rejectedByRole,
            'current_user_status' => $userApprovalStatus,
            'current_user_rejected' => $currentUserRejected,
            'linked_bookings' => $linkedBookings,
            'rooms_breakdown' => $roomsBreakdown,
        ]);
    }

    public function showFunctionRoomBookingRecords(Request $request)
    {
        $today = Carbon::today();
        $roleId = auth()->user()->role_id;

        // Include all relationships that your booking view depends on
        $query = FunctionRoomBooking::with([
            'user',
            'functionRoom',
            'addOns',
            'suppliers',
            'adminApprover',
            'engineeringApprover'
        ])->whereDate('function_room_booking_date', '<', $today);

        // Role-based filters
        if (in_array($roleId, [1, 5, 6, 7])) {
            $query->whereIn('booking_status', [0, 1, 2]);
        } elseif ($roleId == 2) {
            $query->whereIn('booking_status', [0, 1, 2])
                ->whereNotNull('authorization_file');
        } elseif ($roleId == 3) {
            $query->whereIn('booking_status', [0, 1, 2])
                ->where('has_suppliers', 1);
        }

        $functionRoomBookingRecords = $query->latest()->paginate(10);

        $functionRooms = FunctionRoom::where('function_room_status', 1)->get();
        $addOns = AddOn::where('status', 1)->get();

        return view('backend.admin-function-room-booking-records', compact(
            'functionRoomBookingRecords',
            'functionRooms',
            'addOns'
        ));
    }


    public function searchFunctionRoomBookingRecords(Request $request)
    {
        $searchFunctionRoomBookingRecords = $request->input('searchFunctionRoomBookingRecords');
        $currentDate = now();

        $functionRoomBookingRecords = FunctionRoomBooking::with(['user', 'functionRoom'])
            ->where('function_room_booking_date', '<', $currentDate) // past bookings
            ->when($searchFunctionRoomBookingRecords, function ($query, $searchFunctionRoomBookingRecords) {
                $query->where('unit_no', 'LIKE', "%{$searchFunctionRoomBookingRecords}%"); // search only unit_no
            })
            ->orderBy('function_room_booking_date', 'desc')
            ->paginate(10);

        // Format times
        foreach ($functionRoomBookingRecords as $booking) {
            $booking->formatted_start_time = Carbon::parse($booking->booking_start_time)->format('h:i A');
            $booking->formatted_end_time = Carbon::parse($booking->booking_end_time)->format('h:i A');
        }

        $functionRoomBookingRecords->appends(['searchFunctionRoomBookingRecords' => $searchFunctionRoomBookingRecords]);

        return view('backend.admin-function-room-booking-records', compact('functionRoomBookingRecords', 'searchFunctionRoomBookingRecords'));
    }



    public function getUpdatedFunctionRoomBookingTable(Request $request)
    {
        $roleId = auth()->user()->role_id;

        // Base query for filtering (same logic as the blade view version)
        $baseQuery = FunctionRoomBooking::query();

        // Role-based filtering
        if (in_array($roleId, [1, 5, 7, 8])) {
            $baseQuery->whereIn('booking_status', [0, 1]);
        } elseif ($roleId == 2) {
            $baseQuery->whereIn('booking_status', [0, 1])
                ->whereNotNull('authorization_file');
        } elseif ($roleId == 3) {
            $baseQuery->whereIn('booking_status', [0, 1])
                ->where('has_suppliers', 1);
        }

        // 🔹 Get only FIRST booking per transaction_no
        $firstBookingIds = $baseQuery
            ->selectRaw('MIN(id) as id')
            ->groupBy('transaction_no')
            ->pluck('id');

        // Main query for display
        $query = FunctionRoomBooking::with([
            'user',
            'functionRoom',
            'adminApprover',
            'financeApprover',
            'engineeringApprover',
            'managerApprover'
        ])->whereIn('id', $firstBookingIds) // ← Apply grouping here
            ->orderBy('id', 'desc');

        // 🔹 Search
        $search = $request->input('searchFunctionRoomBooking');
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_no', 'like', "%{$search}%")
                    ->orWhere('unit_no', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $bookings = $query->paginate(10);

        $data = $bookings->getCollection()->map(function ($booking) {
            return [
                'id' => $booking->id,
                'transaction_no' => $booking->transaction_no,
                'unit_no' => $booking->unit_no,
                'user' => [
                    'name' => $booking->user->name ?? 'N/A',
                ],
                'resident_type' => $booking->resident_type ?? 'N/A',
                'function_room' => [
                    'function_room_name' => $booking->functionRoom->function_room_name ?? 'N/A',
                ],
                'purpose_of_event' => $booking->purpose_of_event ?? 'N/A',
                'function_room_booking_date' => $booking->function_room_booking_date,
                'event_start_time' => $booking->event_start_time ? Carbon::parse($booking->event_start_time)->format('h:i A') : null,
                'event_end_time' => $booking->event_end_time ? Carbon::parse($booking->event_end_time)->format('h:i A') : null,
                'contact_number' => $booking->contact_number ?? 'N/A',
                'pax' => $booking->pax ?? 'N/A',
                'base_rate' => $booking->base_rate ?? 'N/A',
                'discount' => $booking->discount ?? 0,
                'discount_remarks' => $booking->discount_remarks ?? 'N/A',
                'final_rate' => $booking->final_rate ?? 0,
                'payment_mode' => $booking->payment_mode ?? 'N/A',
                'booking_status' => $booking->booking_status,
                'has_suppliers' => $booking->has_suppliers,
                'authorization_file' => $booking->authorization_file,

                // Approvals
                'concierge_approval' => $booking->concierge_approval,
                'concierge_remarks' => $booking->concierge_remarks,
                'concierge_approver' => $booking->conciergeApprover->name ?? null,
                'concierge_action_at' => $booking->concierge_action_at,

                'admin_approval' => $booking->admin_approval,
                'admin_remarks' => $booking->admin_remarks,
                'admin_approver' => $booking->adminApprover->name ?? null,
                'admin_action_at' => $booking->admin_action_at,

                'finance_approval' => $booking->finance_approval,
                'finance_remarks' => $booking->finance_remarks,
                'finance_approver' => $booking->financeApprover->name ?? null,
                'finance_action_at' => $booking->finance_action_at,

                'engineering_approval' => $booking->engineering_approval,
                'engineering_remarks' => $booking->engineering_remarks,
                'engineering_approver' => $booking->engineeringApprover->name ?? null,
                'engineering_action_at' => $booking->engineering_action_at,

                'manager_approval' => $booking->manager_approval,
                'manager_remarks' => $booking->manager_remarks,
                'manager_approver' => $booking->managerApprover->name ?? null,
                'manager_action_at' => $booking->manager_action_at,

                'created_at' => $booking->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $booking->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'data' => $data,
            'links' => $bookings
                ->appends(['searchFunctionRoomBooking' => $search ?? ''])
                ->links('vendor.pagination.bootstrap-5')
                ->render()
        ]);
    }


    // public function editFunctionRoomBooking($id)
    // {
    //     $booking = FunctionRoomBooking::with(['user', 'functionRoom', 'addOns', 'suppliers'])->findOrFail($id);
    //     $residences = ResidentDetails::all();
    //     $addons = AddOn::where('status', 1)->get();

    //     $booking->suppliers->transform(function ($supplier) {
    //         $supplier->attachment_url = $supplier->attachment
    //             ? asset($supplier->attachment)
    //             : null;
    //         return $supplier;
    //     });

    //     return response()->json([
    //         'booking' => $booking,
    //         'residences' => $residences,
    //         'addons' => $addons,
    //         'authorization_file' => $booking->authorization_file
    //             ? asset($booking->authorization_file)
    //             : null,
    //     ]);
    // }

    public function editFunctionRoomBooking($id)
    {
        $booking = FunctionRoomBooking::with(['user', 'functionRoom', 'addOns', 'suppliers'])
            ->findOrFail($id);

        $residences = ResidentDetails::all();
        $addons = AddOn::where('status', 1)->get();

        // 🔗 Define linked rooms
        $linkedRooms = [
            5 => [6],
            6 => [5],
        ];

        $mainRoomId = $booking->function_room_id;

        // ⚡ Determine if current booking includes the linked room
        $roomsUnderSameTransaction = FunctionRoomBooking::where('transaction_no', $booking->transaction_no)
            ->pluck('function_room_id')
            ->toArray();

        $linkedRoomId = $linkedRooms[$mainRoomId][0] ?? null;

        $alreadyBookedLinkedRoom = false;
        $linkedRoomName = null;

        if ($linkedRoomId) {
            $linkedRoomName = FunctionRoom::where('id', $linkedRoomId)->value('function_room_name');

            if (in_array($linkedRoomId, $roomsUnderSameTransaction)) {
                $alreadyBookedLinkedRoom = true;
            }
        }

        // Add supplier URLs
        $booking->suppliers->transform(function ($supplier) {
            $supplier->attachment_url = $supplier->attachment
                ? asset($supplier->attachment)
                : null;
            return $supplier;
        });

        return response()->json([
            'booking' => $booking,
            'residences' => $residences,
            'addons' => $addons,
            'authorization_file' => $booking->authorization_file
                ? asset($booking->authorization_file)
                : null,

            // 🔥 Add these two (required by your JS)
            'linked_room_name' => $linkedRoomName,
            'already_booked_linked_room' => $alreadyBookedLinkedRoom,
        ]);
    }


    private function getUniqueFilename($destinationPath, $originalName)
    {
        $filename = pathinfo($originalName, PATHINFO_FILENAME);
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);

        $counter = 1;
        $newName = $originalName;

        while (file_exists($destinationPath . DIRECTORY_SEPARATOR . $newName)) {
            $newName = $filename . '(' . $counter . ').' . $extension;
            $counter++;
        }

        return $newName;
    }

    public function updateFunctionRoomBooking(Request $request)
    {
        $maxRetries = 3;
        $attempt = 0;

        while ($attempt < $maxRetries) {
            try {
                DB::beginTransaction();

                $booking = FunctionRoomBooking::with(['suppliers', 'addOns'])->findOrFail($request->booking_id);
                $mainRoom = FunctionRoom::findOrFail($request->function_room_id);

                $linkedRooms = [13 => [14], 14 => [13]];
                $sharedRooms = [
                    13 => [14],
                    14 => [13], //b&c fr1 and fr2 are shared rooms, so they block each other
                    11 => [9],
                    9 => [11]    //meranti tropical and culinary are shared rooms, so they block each other
                ];

                // Determine rooms to update/book  
                $roomsToBook = [$request->function_room_id];
                if ($request->boolean('book_linked_rooms') && isset($linkedRooms[$request->function_room_id])) {
                    $roomsToBook = array_merge($roomsToBook, $linkedRooms[$request->function_room_id]);
                }

                $sharedRoomIds = $sharedRooms[$request->function_room_id] ?? [];
                $conflictRoomIds = array_unique(array_merge($roomsToBook, $sharedRoomIds));


                $allBookings = FunctionRoomBooking::where('transaction_no', $booking->transaction_no)->get();
                $mainBookingId = $allBookings->min('id');

                foreach ($allBookings as $b) {
                    // Skip main booking and any room still selected
                    if ($b->id != $mainBookingId && !in_array($b->function_room_id, $roomsToBook)) {
                        // Delete supplier files
                        foreach ($b->suppliers as $sup) {
                            if ($sup->attachment && file_exists(public_path($sup->attachment))) {
                                unlink(public_path($sup->attachment));
                            }
                        }
                        $b->suppliers()->delete();

                        // Detach add-ons
                        $b->addOns()->detach();

                        // Delete authorization file
                        if ($b->authorization_file && file_exists(public_path($b->authorization_file))) {
                            unlink(public_path($b->authorization_file));
                        }

                        // Delete the linked booking
                        $b->delete();
                    }

                }

                // Check for conflicts  
                $isAlreadyBooked = FunctionRoomBooking::whereIn('function_room_id', $conflictRoomIds)
                    ->where('function_room_booking_date', $request->function_room_booking_date)
                    ->where('booking_status', '!=', 2)
                    ->where('id', '!=', $booking->id)
                    ->lockForUpdate()
                    ->exists();

                if ($isAlreadyBooked) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Sorry, this date has just been booked by another user in one of the linked/shared rooms.'
                    ], 409);
                }

                $authorizationPath = $booking->authorization_file;
                if ($request->hasFile('authorization_file')) {
                    if ($authorizationPath && file_exists(public_path($authorizationPath)))
                        unlink(public_path($authorizationPath));
                    $file = $request->file('authorization_file');
                    $filename = $this->getUniqueFilename(public_path('assets/frontend/uploads/function-room-bookings/authorizations'), $file->getClientOriginalName());
                    $file->move(public_path('assets/frontend/uploads/function-room-bookings/authorizations'), $filename);
                    $authorizationPath = 'assets/frontend/uploads/function-room-bookings/authorizations/' . $filename;
                } elseif ($request->remove_authorization == 1 && $authorizationPath) {
                    if (file_exists(public_path($authorizationPath)))
                        unlink(public_path($authorizationPath));
                    $authorizationPath = null;
                }

                $start = Carbon::parse($request->event_start_time);
                $end = Carbon::parse($request->event_end_time);
                if ($end->lte($start))
                    $end->addDay();
                $durationHours = $start->floatDiffInHours($end);

                // Prepare supplier updates  
                $existingSuppliers = $booking->suppliers()->pluck('id')->toArray();
                $newSupplierIds = [];

                if ($request->has('suppliers')) {
                    foreach ($request->suppliers as $index => $supplier) {
                        if (!empty($supplier['name'])) {
                            $supplierModel = isset($supplier['id']) ? FunctionRoomBookingSupplier::find($supplier['id']) : new FunctionRoomBookingSupplier();
                            $supplierPath = $supplierModel->attachment ?? null;

                            if ($request->hasFile("suppliers.$index.attachment")) {
                                if ($supplierPath && file_exists(public_path($supplierPath)))
                                    unlink(public_path($supplierPath));
                                $file = $request->file("suppliers.$index.attachment");
                                $filename = $this->getUniqueFilename(public_path('assets/frontend/uploads/function-room-bookings/suppliers'), $file->getClientOriginalName());
                                $file->move(public_path('assets/frontend/uploads/function-room-bookings/suppliers'), $filename);
                                $supplierPath = 'assets/frontend/uploads/function-room-bookings/suppliers/' . $filename;
                            } elseif (!empty($supplier['remove_attachment']) && $supplier['remove_attachment'] == 1) {
                                if ($supplierPath && file_exists(public_path($supplierPath)))
                                    unlink(public_path($supplierPath));
                                $supplierPath = null;
                            }

                            $supplierModel->fill([
                                'booking_id' => $booking->id,
                                'name' => $supplier['name'],
                                'attachment' => $supplierPath,
                            ])->save();

                            $newSupplierIds[] = $supplierModel->id;
                        }
                    }
                }
                foreach (array_diff($existingSuppliers, $newSupplierIds) as $deleteId) {
                    $sup = FunctionRoomBookingSupplier::find($deleteId);
                    if ($sup?->attachment && file_exists(public_path($sup->attachment)))
                        unlink(public_path($sup->attachment));
                    $sup?->delete();
                }

                // Handle add-ons  
                $addonsTotal = 0;
                $addonData = [];
                $booking->addOns()->detach();

                if ($request->has('addons')) {
                    foreach ($request->addons as $addonId => $addon) {
                        if (!empty($addon['selected'])) {
                            $addonModel = AddOn::where('id', $addonId)->lockForUpdate()->first();
                            if ($addonModel) {
                                $qtyRequested = max(1, $addon['qty'] ?? 1);
                                $reserved = AddOnFunctionRoomBooking::whereHas('booking', function ($q) use ($request, $booking) {
                                    $q->where('function_room_booking_date', $request->function_room_booking_date)
                                        ->whereIn('booking_status', [0, 1])
                                        ->where('id', '!=', $booking->id);
                                })->where('add_on_id', $addonId)->lockForUpdate()->sum('qty');

                                $available = $addonModel->qty - $reserved;
                                if ($qtyRequested > $available) {
                                    DB::rollBack();
                                    return response()->json([
                                        'success' => false,
                                        'message' => "Sorry, only {$available} of {$addonModel->item} left for this date."
                                    ], 422);
                                }

                                $addonsTotal += $qtyRequested * $addonModel->price;
                                $addonData[$addonId] = ['qty' => $qtyRequested, 'price' => $addonModel->price];
                            }
                        }
                    }

                    if (!empty($addonData)) {
                        $booking->addOns()->attach($addonData);
                    }
                }

                // Update main booking  
                $baseRate = $mainRoom->function_room_rate;
                $activeDiscount = FunctionRoomDiscount::where('function_room_id', $mainRoom->id)
                    ->whereDate('start_date', '<=', $request->function_room_booking_date)
                    ->whereDate('end_date', '>=', $request->function_room_booking_date)
                    ->orderByDesc('discount')->first();

                $appliedDiscount = $activeDiscount->discount ?? 0;
                $discountRemarks = $activeDiscount->remarks ?? null;
                $finalRate = $baseRate - ($baseRate * ($appliedDiscount / 100));
                $roomTotal = $finalRate * $durationHours;
                $totalAmount = $roomTotal + $addonsTotal;

                $booking->update([
                    'function_room_id' => $mainRoom->id,
                    'purpose_of_event' => $request->purpose_of_event,
                    'function_room_booking_date' => $request->function_room_booking_date,
                    'event_start_time' => $request->event_start_time,
                    'event_end_time' => $request->event_end_time,
                    'contact_number' => $request->contact_number,
                    'pax' => $request->pax,
                    'payment_mode' => $request->payment_mode,
                    'has_suppliers' => $request->boolean('has_suppliers'),
                    'authorization_file' => $authorizationPath,
                    'base_rate' => $baseRate,
                    'discount' => $appliedDiscount,
                    'discount_remarks' => $discountRemarks,
                    'final_rate' => $finalRate,
                    'room_total' => $roomTotal,
                    'addons_total' => $addonsTotal,
                    'total_amount' => $totalAmount,
                ]);

                // Update linked/shared rooms  
                foreach ($roomsToBook as $roomId) {
                    if ($roomId == $mainRoom->id)
                        continue;
                    $linkedRoom = FunctionRoom::findOrFail($roomId);
                    $linkedBooking = FunctionRoomBooking::where('transaction_no', $booking->transaction_no)
                        ->where('function_room_id', $roomId)
                        ->first();

                    $baseRate = $linkedRoom->function_room_rate;
                    $activeDiscount = FunctionRoomDiscount::where('function_room_id', $linkedRoom->id)
                        ->whereDate('start_date', '<=', $request->function_room_booking_date)
                        ->whereDate('end_date', '>=', $request->function_room_booking_date)
                        ->orderByDesc('discount')->first();

                    $appliedDiscount = $activeDiscount->discount ?? 0;
                    $discountRemarks = $activeDiscount->remarks ?? null;
                    $finalRate = $baseRate - ($baseRate * ($appliedDiscount / 100));
                    $roomTotal = $finalRate * $durationHours;
                    $totalAmount = $roomTotal;

                    $data = [
                        'purpose_of_event' => $request->purpose_of_event,
                        'function_room_booking_date' => $request->function_room_booking_date,
                        'event_start_time' => $request->event_start_time,
                        'event_end_time' => $request->event_end_time,
                        'contact_number' => $request->contact_number,
                        'pax' => $request->pax,
                        'payment_mode' => $request->payment_mode,
                        'has_suppliers' => $request->boolean('has_suppliers'),
                        'authorization_file' => $authorizationPath,
                        'base_rate' => $baseRate,
                        'discount' => $appliedDiscount,
                        'discount_remarks' => $discountRemarks,
                        'final_rate' => $finalRate,
                        'room_total' => $roomTotal,
                        'addons_total' => 0,
                        'total_amount' => $totalAmount,
                    ];

                    if (!$linkedBooking) {
                        $linkedBooking = FunctionRoomBooking::create(array_merge([
                            'transaction_no' => $booking->transaction_no,
                            'user_id' => $booking->user_id,
                            'unit_no' => $booking->unit_no,
                            'resident_type' => $booking->resident_type,
                            'function_room_id' => $roomId,
                            'created_by' => $booking->created_by,
                            'booking_status' => $booking->booking_status,
                            // include approval columns if any  
                        ], $data));
                    } else {
                        $linkedBooking->update($data);
                    }

                    // replicate suppliers to linked rooms  
                    $linkedBooking->suppliers()->delete();
                    foreach ($booking->suppliers as $sup) {
                        FunctionRoomBookingSupplier::create([
                            'booking_id' => $linkedBooking->id,
                            'name' => $sup->name,
                            'attachment' => $sup->attachment,
                        ]);
                    }
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Booking updated successfully',
                ]);

            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollBack();
                if (in_array($e->errorInfo[1], [1213, 1205])) {
                    $attempt++;
                    if ($attempt < $maxRetries) {
                        usleep(100000);
                        continue;
                    }
                }
                Log::error("Error updating booking", ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                return response()->json(['success' => false, 'message' => 'Something went wrong while updating the booking.'], 500);
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("Error updating booking", ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                return response()->json(['success' => false, 'message' => 'Something went wrong while updating the booking.'], 500);
            }
        }

        return response()->json(['success' => false, 'message' => "Could not update booking. Please try again in a few seconds."], 500);

    }


    public function downloadFunctionRoomBookingRecords(Request $request)
    {
        $request->validate([
            'download_start_date' => 'required|date',
            'download_end_date' => 'required|date|after_or_equal:download_start_date',
        ]);

        $startDate = $request->download_start_date;
        $endDate = $request->download_end_date;

        $data = DB::table('function_room_bookings as frb')
            ->join('users', 'frb.user_id', '=', 'users.id')
            ->join('function_rooms as fr', 'frb.function_room_id', '=', 'fr.id')
            ->leftJoin('function_room_booking_suppliers as frbs', 'frb.id', '=', 'frbs.booking_id')
            ->select(
                'frb.id',
                'frb.transaction_no',
                'users.name as user_name',
                'frb.unit_no',
                'frb.resident_type',
                'fr.function_room_name as function_room',
                'frb.purpose_of_event',
                'frb.function_room_booking_date',
                'frb.event_start_time',
                'frb.event_end_time',
                'frb.pax',
                'frb.payment_mode',
                'frb.base_rate',
                'frb.discount',
                'frb.final_rate',
                'frb.room_total',
                'frb.total_amount',
                'frb.booking_status',
                DB::raw('GROUP_CONCAT(DISTINCT frbs.name SEPARATOR ", ") as suppliers')
            )
            ->whereBetween('frb.function_room_booking_date', [$startDate, $endDate])
            ->groupBy(
                'frb.id',
                'users.name',
                'frb.unit_no',
                'frb.resident_type',
                'fr.function_room_name',
                'frb.purpose_of_event',
                'frb.function_room_booking_date',
                'frb.event_start_time',
                'frb.event_end_time',
                'frb.pax',
                'frb.payment_mode',
                'frb.base_rate',
                'frb.discount',
                'frb.final_rate',
                'frb.room_total',
                'frb.total_amount',
                'frb.booking_status'
            )
            ->get();

        $fileName = "2S_FunctionRoom_Bookings_" . Carbon::parse($startDate)->format('Ymd') . "_to_" . Carbon::parse($endDate)->format('Ymd') . ".csv";

        $response = new StreamedResponse(function () use ($data) {
            $handle = fopen('php://output', 'w');

            // CSV headers
            fputcsv($handle, [
                'Transaction No',
                'User Name',
                'Unit No',
                'Resident Type',
                'Function Room',
                'Purpose',
                'Booking Date',
                'Start Time',
                'End Time',
                'Booking Duration (hrs)',
                'Pax',
                'Payment Mode',
                'Function Room Base Rate',
                'Function Room Discount (%)',
                'Function Room Final Rate',
                'Room Total',
                'Add-Ons',
                'Add-Ons Qty',
                'Add-Ons Price',
                'Add-Ons Total',
                'Overall Total',
                'Booking Status',
                'Suppliers'
            ]);


            foreach ($data as $row) {
                // Fetch add-ons details for this booking
                $addons = DB::table('add_on_function_room_bookings as frba')
                    ->join('add_ons as ao', 'frba.add_on_id', '=', 'ao.id')
                    ->where('frba.function_room_booking_id', $row->id)
                    ->select('ao.item', 'frba.qty', 'frba.price')
                    ->get();

                // Separate columns
                $addonNames = $addons->pluck('item')->implode(', ');
                $addonQtys = $addons->pluck('qty')->implode(', ');
                $addonPrices = $addons->map(fn($a) => number_format($a->price, 2))->implode(', ');
                $addonsTotal = $addons->sum(fn($a) => $a->qty * $a->price);

                // Booking duration in hours
                $start = Carbon::parse($row->event_start_time);
                $end = Carbon::parse($row->event_end_time);
                $durationHours = $end->diffInHours($start); // e.g., 1, 2, etc.


                $bookingDate = $row->function_room_booking_date ? Carbon::parse($row->function_room_booking_date)->format('F j, Y') : '';
                $startTime = $row->event_start_time ? Carbon::parse($row->event_start_time)->format('h:i A') : '';
                $endTime = $row->event_end_time ? Carbon::parse($row->event_end_time)->format('h:i A') : '';
                $status = match ($row->booking_status) {
                    0 => 'Incomplete',
                    1 => 'Complete',
                    2 => 'Cancelled',
                };

                $discountPercent = $row->discount ? $row->discount . '%' : '0%';
                $overallTotal = $row->room_total + $addonsTotal;

                fputcsv($handle, [
                    $row->transaction_no,
                    $row->user_name,
                    $row->unit_no,
                    $row->resident_type,
                    $row->function_room,
                    $row->purpose_of_event,
                    $bookingDate,
                    $startTime,
                    $endTime,
                    $durationHours, // <-- Booking Duration in hours
                    $row->pax,
                    $row->payment_mode,
                    number_format($row->base_rate, 2), // Function Room Base Rate
                    $discountPercent,
                    number_format($row->final_rate, 2),
                    number_format($row->room_total, 2),
                    $addonNames,
                    $addonQtys,
                    $addonPrices,
                    number_format($addonsTotal, 2),
                    number_format($row->room_total + $addonsTotal, 2),
                    $status,
                    $row->suppliers
                ]);

                ob_flush();
                flush();
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;
    }

    public function getAdminAddonsAvailability(Request $request)
    {
        $date = $request->date;

        $addons = AddOn::where('status', 1)->get();

        $availability = [];

        foreach ($addons as $addon) {

            $reserved = AddOnFunctionRoomBooking::whereHas('booking', function ($q) use ($date) {
                $q->where('function_room_booking_date', $date)
                    ->whereIn('booking_status', [0, 1]);
            })
                ->where('add_on_id', $addon->id)
                ->sum('qty');

            $availability[$addon->id] = max(0, $addon->qty - $reserved);
        }

        return response()->json($availability);
    }


    public function getAdminFunctionRoomBookedDates($roomId)
    {
        // Define linked room pairs (example: 5 <-> 6)
        $linkedRooms = [

            9 => [11], //meranti tropical and culinary are shared rooms, so they block each other
            11 => [9],//meranti culinary and  tropical shared rooms, so they block each other

            13 => [14], //b&c fr1 is linked to fr2
            14 => [13],//b&c fr1 is linked to fr2

        ];

        // Include the linked room if applicable
        $relatedRoomIds = [$roomId];
        if (isset($linkedRooms[$roomId])) {
            $relatedRoomIds = array_merge($relatedRoomIds, $linkedRooms[$roomId]);
        }

        // Get booked dates for all related rooms
        $bookedDates = FunctionRoomBooking::whereIn('function_room_id', $relatedRoomIds)
            ->whereIn('booking_status', [0, 1])
            ->pluck('function_room_booking_date')
            ->toArray();

        // Get blocked dates for all related rooms
        $blockedDates = FunctionRoomDateBlocking::whereIn('function_room_id', $relatedRoomIds)
            ->get()
            ->flatMap(function ($block) {
                $dates = [];
                $start = Carbon::parse($block->date_blocking_start)->startOfDay();
                $end = Carbon::parse($block->date_blocking_end)->endOfDay();

                while ($start->lte($end)) {
                    $dates[] = $start->format('Y-m-d');
                    $start->addDay();
                }
                return $dates;
            })
            ->toArray();

        // Merge, remove duplicates, and reindex
        $disabledDates = array_values(array_unique(array_merge($bookedDates, $blockedDates)));

        return response()->json($disabledDates);
    }


    public function searchResidents(Request $request)
    {
        $search = $request->input('term'); // term is sent by Select2

        $residents = ResidentDetails::where('unit_no', 'like', "%{$search}%")
            ->orWhere('resident_type', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
            ->limit(20) // limit results for performance
            ->get();

        $results = $residents->map(function ($residence) {
            return [
                'id' => $residence->id,   // ✅ USE users.id
                'text' => ucfirst($residence->resident_type) . ' - Unit ' . $residence->unit_no . ' (' . $residence->email . ')',
                'unit_no' => $residence->unit_no,
                'resident_type' => strtolower($residence->resident_type),
            ];
        });

        return response()->json(['results' => $results]);
    }

    public function adminCheckUnitTenant($unitNo)
    {
        $hasTenant = DB::table('resident_details')
            ->where('unit_no', $unitNo)
            ->where('resident_type', 'TENANT')
            ->exists();

        return response()->json(['hasTenant' => $hasTenant]);
    }


    public function AdminStoreBooking(Request $request)
    {
        $maxRetries = 3;
        $attempt = 0;

        while ($attempt < $maxRetries) {
            try {
                DB::beginTransaction();
                $linkedRooms = [
                    13 => [14], //b&c fr1
                    14 => [13], //b&c fr2
                ];

                $roomsToBook = [$request->function_room_id];
                if ($request->boolean('book_linked_rooms') && isset($linkedRooms[$request->function_room_id])) {
                    $roomsToBook = array_merge($roomsToBook, $linkedRooms[$request->function_room_id]);
                }

                $sharedRooms = [
                    11 => [9], //meranti tropical garden
                    9 => [11], //meranti culinary room
                ];
                $sharedRoomIds = [];
                if (isset($sharedRooms[$request->function_room_id])) {
                    $sharedRoomIds = $sharedRooms[$request->function_room_id];
                }

                $relatedRoomIds = array_unique(array_merge($roomsToBook, $sharedRoomIds));
                $bookingDate = Carbon::parse($request->function_room_booking_date);

                $fullLinkedSet = isset($linkedRooms[$request->function_room_id])
                    ? array_merge([$request->function_room_id], $linkedRooms[$request->function_room_id])
                    : [$request->function_room_id];

                // Always include full shared room group for conflict checking
                $fullSharedSet = isset($sharedRooms[$request->function_room_id])
                    ? $sharedRooms[$request->function_room_id]
                    : [];

                // Final conflict group (complete group)
                $conflictRoomIds = array_unique(array_merge($fullLinkedSet, $fullSharedSet));


                $isAlreadyBooked = FunctionRoomBooking::whereIn('function_room_id', $conflictRoomIds)
                    ->where('function_room_booking_date', $request->function_room_booking_date)
                    ->where('booking_status', '!=', 2) // only cancelled allowed
                    ->lockForUpdate()
                    ->exists();

                if ($isAlreadyBooked) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Sorry, this date has just been booked by another user in one of the linked/shared rooms.'
                    ], 409);
                }

                $isBlocked = FunctionRoomDateBlocking::whereIn('function_room_id', $relatedRoomIds)
                    ->where('blocking_status', 1)
                    ->where(function ($query) use ($request) {
                        $query->whereBetween('date_blocking_start', [$request->function_room_booking_date, $request->function_room_booking_date])
                            ->orWhereBetween('date_blocking_end', [$request->function_room_booking_date, $request->function_room_booking_date])
                            ->orWhere(function ($query) use ($request) {
                                $query->where('date_blocking_start', '<=', $request->function_room_booking_date)
                                    ->where('date_blocking_end', '>=', $request->function_room_booking_date);
                            });
                    })
                    ->lockForUpdate()
                    ->exists();

                if ($isBlocked) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Sorry, this date has been blocked in one of the linked/shared rooms. Please choose another date.'
                    ], 409);
                }


                $lastId = FunctionRoomBooking::max('id') + 1;
                $transactionNo = '2SFR-' . str_pad($lastId, 5, '0', STR_PAD_LEFT);


                $resident = ResidentDetails::find($request->user_id);

                $userId = null;
                if ($resident && $resident->email) {
                    $existingUser = \App\Models\User::where('email', $resident->email)->first();
                    if ($existingUser) {
                        $userId = $existingUser->id;
                    }
                }

                $unitNo = $resident?->unit_no;
                $authorizationPath = null;
                if ($request->hasFile('admin_authorization_file')) {
                    $file = $request->file('admin_authorization_file');
                    $originalName = $file->getClientOriginalName();
                    $destinationPath = public_path('assets/frontend/uploads/function-room-bookings/authorizations');

                    if (!file_exists($destinationPath))
                        mkdir($destinationPath, 0777, true);

                    $filename = $this->getUniqueFilename($destinationPath, $originalName);
                    $file->move($destinationPath, $filename);
                    $authorizationPath = 'assets/frontend/uploads/function-room-bookings/authorizations/' . $filename;
                }

                $start = Carbon::parse($request->event_start_time);
                $end = Carbon::parse($request->event_end_time);
                if ($end->lte($start))
                    $end->addDay();
                $durationHours = $start->floatDiffInHours($end);


                $bookings = [];
                $totalAmountAllRooms = 0;

                foreach ($roomsToBook as $roomId) {
                    $room = FunctionRoom::findOrFail($roomId);

                    $activeDiscount = FunctionRoomDiscount::where('function_room_id', $room->id)
                        ->whereDate('start_date', '<=', $bookingDate)
                        ->whereDate('end_date', '>=', $bookingDate)
                        ->orderByDesc('discount')
                        ->first();

                    $appliedDiscount = $activeDiscount->discount ?? 0;
                    $discountRemarks = $activeDiscount->remarks ?? null;
                    $finalRate = $room->function_room_rate;

                    if ($appliedDiscount > 0) {
                        $finalRate = $finalRate - ($finalRate * ($appliedDiscount / 100));
                    }

                    $roomTotal = $finalRate * $durationHours;
                    $totalAmountAllRooms += $roomTotal;

                    $bookings[$roomId] = FunctionRoomBooking::create([
                        'transaction_no' => $transactionNo,
                        'user_id' => $userId,
                        'unit_no' => $unitNo,
                        'resident_type' => $resident?->resident_type,
                        'function_room_id' => $room->id,
                        'purpose_of_event' => $request->purpose_of_event,
                        'function_room_booking_date' => $request->function_room_booking_date,
                        'event_start_time' => $request->event_start_time,
                        'event_end_time' => $request->event_end_time,
                        'contact_number' => $request->contact_number,
                        'pax' => $request->pax,
                        'payment_mode' => $request->admin_payment_mode,
                        'has_suppliers' => $request->boolean('admin_has_suppliers'),
                        'authorization_file' => $authorizationPath,
                        'base_rate' => $room->function_room_rate,
                        'discount' => $appliedDiscount,
                        'discount_remarks' => $discountRemarks,
                        'final_rate' => $finalRate,
                        'room_total' => $roomTotal,
                        'addons_total' => 0,
                        'total_amount' => $roomTotal,
                        'created_by' => auth()->id(),
                    ]);
                }
                $mainBooking = $bookings[$request->function_room_id] ?? reset($bookings);
                $addonsTotal = 0;
                $addonData = [];

                if ($request->has('addons')) {
                    foreach ($request->addons as $addonId => $addon) {
                        if (isset($addon['selected']) && $addon['selected'] == 1) {
                            $addonModel = AddOn::where('id', $addonId)->lockForUpdate()->first();
                            if ($addonModel) {
                                $qtyRequested = max(1, $addon['qty'] ?? 1);

                                $reserved = AddOnFunctionRoomBooking::whereHas('booking', function ($q) use ($request) {
                                    $q->where('function_room_booking_date', $request->function_room_booking_date)
                                        ->whereIn('booking_status', [0, 1]);
                                })
                                    ->where('add_on_id', $addonId)
                                    ->lockForUpdate()
                                    ->sum('qty');

                                $available = $addonModel->qty - $reserved;
                                if ($qtyRequested > $available) {
                                    DB::rollBack();
                                    return response()->json([
                                        'success' => false,
                                        'message' => "Sorry, only {$available} of {$addonModel->item} left for this date."
                                    ], 422);
                                }

                                $addonsTotal += $qtyRequested * $addonModel->price;
                                $addonData[$addonId] = ['qty' => $qtyRequested, 'price' => $addonModel->price];
                            }
                        }
                    }

                    if (!empty($addonData)) {
                        $mainBooking->addOns()->attach($addonData);
                        $mainBooking->update([
                            'addons_total' => $addonsTotal,
                            'total_amount' => $mainBooking->room_total + $addonsTotal,
                        ]);
                    }

                    foreach ($bookings as $booking) {
                        if ($booking->id !== $mainBooking->id) {
                            $booking->update([
                                'addons_total' => 0,
                                'total_amount' => $booking->room_total,
                            ]);
                        }
                    }
                }
                $supplierUploads = [];
                if ($request->has('suppliers')) {
                    foreach ($request->suppliers as $index => $supplier) {
                        if (!empty($supplier['name'])) {
                            $supplierPath = null;
                            $file = $request->file("suppliers.$index.attachment");
                            if ($file) {
                                $originalName = $file->getClientOriginalName();
                                $destinationPath = public_path('assets/frontend/uploads/function-room-bookings/suppliers');
                                if (!file_exists($destinationPath))
                                    mkdir($destinationPath, 0777, true);
                                $filename = $this->getUniqueFilename($destinationPath, $originalName);
                                $file->move($destinationPath, $filename);
                                $supplierPath = 'assets/frontend/uploads/function-room-bookings/suppliers/' . $filename;
                            }

                            $supplierUploads[$index] = [
                                'name' => $supplier['name'],
                                'attachment' => $supplierPath,
                            ];
                        }
                    }
                    foreach ($bookings as $booking) {
                        foreach ($supplierUploads as $data) {
                            FunctionRoomBookingSupplier::create([
                                'booking_id' => $booking->id,
                                'name' => $data['name'],
                                'attachment' => $data['attachment'],
                            ]);
                        }
                    }
                }

                $mainBooking->load(['user', 'functionRoom']);
                $recipientEmail = null;

                if ($mainBooking->user) {
                    $recipientEmail = $mainBooking->user->email;
                } else {
                    $residentEmail = ResidentDetails::where('id', $request->user_id)->value('email');
                    if ($residentEmail) {
                        $recipientEmail = $residentEmail;
                        Log::warning("User not found in users table, fallback to resident_details email: {$residentEmail}");
                    }
                }

                if ($recipientEmail) {
                    Mail::to($recipientEmail)->queue(new UserFunctionRoomBookingNotification($mainBooking, $bookings));
                } else {
                    Log::warning("No email found for resident ID {$request->user_id}");
                }

                Mail::to('itdept@twoserendra.com')->queue(new FinanceFunctionRoomBookingNotification($mainBooking, $bookings));
                event(new FunctionRoomBookingCreated($mainBooking));
                if ($mainBooking->user) {
                    $mainBooking->user->notify(new UserFunctionRoomBookingBellNotification($mainBooking));
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => "Booking saved successfully! Transaction No: {$transactionNo}",
                    'transaction_no' => $transactionNo,
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollBack();

                // deadlock / lock wait
                if (in_array($e->errorInfo[1], [1213, 1205])) {
                    $attempt++;
                    if ($attempt < $maxRetries) {
                        usleep(100000);
                        continue;
                    }
                }

                Log::error("Error saving admin booking", [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong while saving the booking.'
                ], 500);
            } catch (\Throwable $e) {
                DB::rollBack();

                Log::error("Error saving admin booking", [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong while saving the booking.'
                ], 500);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Could not complete booking. Please try again.'
        ], 500);
    }



}
