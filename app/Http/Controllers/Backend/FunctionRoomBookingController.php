<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FunctionRoomBooking;
use App\Models\FunctionRoom;
use App\Models\AddOn;
use App\Models\AddOnFunctionRoomBooking;
use App\Models\ResidentDetails;
use App\Models\FunctionRoomBookingSupplier;
use App\Mail\FunctionRoomBookingConfirmedNotification;
use App\Notifications\UserFunctionRoomBookingBellNotification;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FunctionRoomBookingController extends Controller
{

    public function showFunctionRoomBookings(Request $request)
    {
        $roleId = auth()->user()->role_id;

        $query = FunctionRoomBooking::with(['user', 'functionRoom', 'addOns']);

        if (in_array($roleId, [1, 5, 6, 7])) {
            // Role 1, 5, 7: Show pending and confirmed bookings
            $query->whereIn('booking_status', [0, 1,]);

        } elseif ($roleId == 2) {
            // Role 2: Pending or confirmed bookings with authorization file
            $query->whereIn('booking_status', [0, 1])
                ->whereNotNull('authorization_file');

        } elseif ($roleId == 3) {
            // Role 3: Pending or confirmed bookings with supplier checked
            $query->whereIn('booking_status', [0, 1])
                ->where('has_suppliers', 1); // Assuming supplier is a boolean/int column
        }

        $functionRoomBookings = $query->latest()->paginate(perPage: 10);

        $functionRooms = FunctionRoom::where('function_room_status', 1)->get();
        $addOns = AddOn::where('status', 1)->get();
        return view('backend.admin-function-room-booking', compact('functionRoomBookings', 'functionRooms', 'addOns'));
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

    // public function FunctionRoomBookingApproval(Request $request)
    // {
    //     $bookingId = $request->booking_id;
    //     $booking = FunctionRoomBooking::findOrFail($bookingId);
    //     $user = auth()->user();

    //     switch ($user->role_id) {
    //         case 2:
    //             if ($booking->authorization_file) {
    //                 $booking->update([
    //                     'admin_approval' => true,
    //                     'admin_approved_by' => $user->id,
    //                     'admin_approved_at' => now(),
    //                 ]);
    //             }
    //             break;

    //         case 5:
    //             $booking->update([
    //                 'finance_approval' => true,
    //                 'finance_approved_by' => $user->id,
    //                 'finance_approved_at' => now(),
    //             ]);
    //             break;

    //         case 3: 
    //             $booking->update([
    //                 'engineering_approval' => true,
    //                 'engineering_approved_by' => $user->id,
    //                 'engineering_approved_at' => now(),
    //             ]);
    //             break;

    //         case 7: 
    //             $booking->update([
    //                 'manager_approval' => true,
    //                 'manager_approved_by' => $user->id,
    //                 'manager_approved_at' => now(),
    //                 'booking_status' => '1', 
    //             ]);
    //             break;
    //     }
    //     if ($user->role_id !== 7 && $booking->isReadyForConfirmation()) {
    //         $booking->update(['booking_status' => '1']);
    //     }

    //     if ($booking->booking_status == 1) {
    //         Mail::to($booking->user->email)->queue(new FunctionRoomBookingConfirmedNotification($booking));
    //         $booking->user->notify(new UserFunctionRoomBookingBellNotification($booking));
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Approval updated successfully',
    //         'booking_status' => $booking->booking_status
    //     ]);
    // }

    public function FunctionRoomBookingApproval(Request $request)
    {
        $bookingId = $request->booking_id;
        $booking = FunctionRoomBooking::findOrFail($bookingId);
        $user = auth()->user();

        switch ($user->role_id) {
            case 6: // Concierge
                $booking->update([
                    'concierge_approval' => 1,
                    'concierge_user_id' => $user->id,
                    'concierge_action_at' => now(),
                    'concierge_remarks' => null, // ✅ clear old remarks
                ]);
                break;

            case 2: // Admin
                if ($booking->authorization_file && $booking->concierge_approval) {
                    $booking->update([
                        'admin_approval' => 1,
                        'admin_user_id' => $user->id,
                        'admin_action_at' => now(),
                        'admin_remarks' => null, // ✅ clear old remarks
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot approve yet. Waiting for Concierge approval.',
                    ], 422);
                }
                break;

            case 5: // Finance
                $booking->update([
                    'finance_approval' => 1,
                    'finance_user_id' => $user->id,
                    'finance_action_at' => now(),
                    'finance_remarks' => null, // ✅ clear old remarks
                ]);
                break;

            case 3: // Engineering
                if ($booking->suppliers && $booking->suppliers->count() > 0) {
                    $booking->update([
                        'engineering_approval' => 1,
                        'engineering_user_id' => $user->id,
                        'engineering_action_at' => now(),
                        'engineering_remarks' => null, // ✅ clear old remarks
                    ]);
                }
                break;

            case 7: // Manager
                $booking->update([
                    'manager_approval' => 1,
                    'manager_user_id' => $user->id,
                    'manager_action_at' => now(),
                    'manager_remarks' => null, // ✅ clear old remarks
                    'booking_status' => '1',
                ]);
                break;
        }

        if ($user->role_id !== 7 && $booking->isReadyForConfirmation()) {
            $booking->update(['booking_status' => '1']);
        }

        if ($booking->booking_status == 1) {
            // Dispatch in background
            Mail::to($booking->user->email)
                ->queue(new FunctionRoomBookingConfirmedNotification($booking));

            // Queue the notification
            $booking->user->notify((new UserFunctionRoomBookingBellNotification($booking))->delay(now()->addSeconds(1)));
        }

        return response()->json([
            'success' => true,
            'message' => 'Approval updated successfully',
            'booking_status' => $booking->booking_status
        ]);
    }

    // public function FunctionRoomBookingRejection(Request $request)
    // {
    //     $request->validate([
    //         'booking_id' => 'required|exists:function_room_bookings,id',
    //         'remarks' => 'required|string|max:500',
    //     ]);

    //     $bookingId = $request->booking_id;
    //     $booking = FunctionRoomBooking::findOrFail($bookingId);
    //     $user = auth()->user();

    //     switch ($user->role_id) {
    //         case 6: // Concierge
    //             $booking->update([
    //                 'concierge_approval' => false, // mark rejected
    //                 'concierge_user_id' => $user->id,
    //                 'concierge_action_at' => now(),
    //                 'concierge_remarks' => $request->remarks,
    //                 'booking_status' => '2', // rejected
    //             ]);
    //             break;

    //         case 2: // Admin
    //             $booking->update([
    //                 'admin_approval' => false,
    //                 'admin_user_id' => $user->id,
    //                 'admin_action_at' => now(),
    //                 'admin_remarks' => $request->remarks,
    //                 'booking_status' => '2',
    //             ]);
    //             break;

    //         case 5: // Finance
    //             $booking->update([
    //                 'finance_approval' => false,
    //                 'finance_user_id' => $user->id,
    //                 'finance_action_at' => now(),
    //                 'finance_remarks' => $request->remarks,
    //                 'booking_status' => '2',
    //             ]);
    //             break;

    //         case 3: // Engineering
    //             $booking->update([
    //                 'engineering_approval' => false,
    //                 'engineering_user_id' => $user->id,
    //                 'engineering_action_at' => now(),
    //                 'engineering_remarks' => $request->remarks,
    //                 'booking_status' => '2',
    //             ]);
    //             break;

    //         case 7: // Manager
    //             $booking->update([
    //                 'manager_approval' => false,
    //                 'manager_user_id' => $user->id,
    //                 'manager_action_at' => now(),
    //                 'manager_remarks' => $request->remarks,
    //                 'booking_status' => '2',
    //             ]);
    //             break;
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Booking rejected successfully',
    //         'booking_status' => $booking->booking_status,
    //     ]);
    // }


    public function FunctionRoomBookingReject(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:function_room_bookings,id',
            'remarks' => 'required|string|max:1000',
        ]);

        $booking = FunctionRoomBooking::findOrFail($request->booking_id);
        $user = auth()->user();

        switch ($user->role_id) {
            case 6: // Concierge
                $booking->update([
                    'concierge_approval' => 2, // rejected
                    'concierge_user_id' => $user->id,
                    'concierge_action_at' => now(),
                    'concierge_remarks' => $request->remarks,
                ]);
                break;

            case 2: // Admin
                $booking->update([
                    'admin_approval' => 2,
                    'admin_user_id' => $user->id,
                    'admin_action_at' => now(),
                    'admin_remarks' => $request->remarks,
                ]);
                break;

            case 5: // Finance
                $booking->update([
                    'finance_approval' => 2,
                    'finance_user_id' => $user->id,
                    'finance_action_at' => now(),
                    'finance_remarks' => $request->remarks,
                ]);
                break;

            case 3: // Engineering
                $booking->update([
                    'engineering_approval' => 2,
                    'engineering_user_id' => $user->id,
                    'engineering_action_at' => now(),
                    'engineering_remarks' => $request->remarks,
                ]);
                break;

            case 7: // Manager
                $booking->update([
                    'manager_approval' => 2,
                    'manager_user_id' => $user->id,
                    'manager_action_at' => now(),
                    'manager_remarks' => $request->remarks,
                    'booking_status' => 2, // rejected (global)
                ]);
                break;
        }

        return response()->json([
            'success' => true,
            'message' => 'Booking rejected successfully',
            'booking_status' => $booking->booking_status,
        ]);
    }







    // public function getFunctionRoomBookingDetails($id)
    // {
    //     $booking = FunctionRoomBooking::with(['user', 'functionRoom', 'suppliers', 'addOns'])->findOrFail($id);
    //     $userRole = auth()->user()->role_id;
    //     $booking->event_start_time = $booking->event_start_time
    //         ? \Carbon\Carbon::parse($booking->event_start_time)->format('h:i A')
    //         : null;
    //     $booking->event_end_time = $booking->event_end_time
    //         ? \Carbon\Carbon::parse($booking->event_end_time)->format('h:i A')
    //         : null;

    //     $authorizationFileUrl = $booking->authorization_file ? asset($booking->authorization_file) : null;
    //     $booking->suppliers->transform(function ($supplier) {
    //         $supplier->attachment_url = $supplier->attachment ? asset($supplier->attachment) : null;
    //         return $supplier;
    //     });

    //     $booking->has_suppliers = $booking->suppliers->count() > 0;

    //     $showViewButton = false;
    //     $showApproveButton = false;
    //     $waitingReason = null;
    //     $isApproved = false;

    //     if ($userRole == 6) { 
    //         $showViewButton = true;
    //         if (empty($booking->concierge_user_id)) {
    //             $showApproveButton = true;
    //         } else {
    //             $isApproved = true;
    //         }
    //     }

    //     if ($userRole == 2 && $booking->authorization_file) {
    //         $showViewButton = true;

    //         if (empty($booking->concierge_user_id)) {
    //             $waitingReason = 'Waiting for Concierge';
    //             $showApproveButton = false; 
    //         } elseif (empty($booking->admin_user_id)) {
    //             $showApproveButton = true;
    //         } else {
    //             $isApproved = true;
    //         }
    //     }

    //     if ($userRole == 5) { 
    //         $showViewButton = true;

    //         if ($booking->authorization_file) {
    //             if (empty($booking->admin_user_id)) {
    //                 $waitingReason = 'Waiting for Admin';
    //             } elseif (empty($booking->finance_user_id)) {
    //                 $showApproveButton = true;
    //             } else {
    //                 $isApproved = true;
    //             }
    //         } else {
    //             if (empty($booking->concierge_user_id)) {
    //                 $waitingReason = 'Waiting for Concierge';
    //             } elseif (empty($booking->finance_user_id)) {
    //                 $showApproveButton = true;
    //             } else {
    //                 $isApproved = true;
    //             }
    //         }
    //     }


    //     if ($userRole == 3 && $booking->has_suppliers) {
    //         $showViewButton = true;
    //         if (empty($booking->finance_user_id)) {
    //             $waitingReason = 'Waiting for Finance';
    //         } elseif (empty($booking->engineering_user_id)) {
    //             $showApproveButton = true;
    //         } else {
    //             $isApproved = true;
    //         }
    //     }

    //     if ($userRole == 7) { 
    //         $showViewButton = true;
    //         if (empty($booking->concierge_user_id)) {
    //             $waitingReason = 'Waiting for Concierge';
    //         } elseif ($booking->authorization_file && empty($booking->admin_user_id)) {
    //             $waitingReason = 'Waiting for Admin';
    //         } elseif (empty($booking->finance_user_id)) {
    //             $waitingReason = 'Waiting for Finance';
    //         } elseif ($booking->has_suppliers && empty($booking->engineering_user_id)) {
    //             $waitingReason = 'Waiting for Engineering';
    //         } elseif (empty($booking->manager_user_id)) {
    //             $showApproveButton = true;
    //         } else {
    //             $isApproved = true;
    //         }
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'booking' => $booking,
    //         'authorization_file_url' => $authorizationFileUrl,
    //         'show_approve_button' => $showApproveButton,
    //         'show_view_button' => $showViewButton,
    //         'is_approved' => $isApproved,
    //         'waiting_reason' => $waitingReason,
    //     ]);
    // }

    // public function getFunctionRoomBookingDetails($id)
    // {
    //     $booking = FunctionRoomBooking::with(['user', 'functionRoom', 'suppliers', 'addOns'])->findOrFail($id);
    //     $userRole = auth()->user()->role_id;

    //     // Format times
    //     $booking->event_start_time = $booking->event_start_time
    //         ? \Carbon\Carbon::parse($booking->event_start_time)->format('h:i A')
    //         : null;
    //     $booking->event_end_time = $booking->event_end_time
    //         ? \Carbon\Carbon::parse($booking->event_end_time)->format('h:i A')
    //         : null;

    //     // Authorization URL
    //     $authorizationFileUrl = $booking->authorization_file ? asset($booking->authorization_file) : null;

    //     // Add supplier URLs
    //     $booking->suppliers->transform(function ($supplier) {
    //         $supplier->attachment_url = $supplier->attachment ? asset($supplier->attachment) : null;
    //         return $supplier;
    //     });

    //     $booking->has_suppliers = $booking->suppliers->count() > 0;

    //     // Default states
    //     $showViewButton = false;
    //     $showApproveButton = false;
    //     $waitingReason = null;
    //     $isApproved = false;

    //     // ----- Strict Approval Flow -----
    //     if ($userRole == 6) { // Concierge first
    //         $showViewButton = true;
    //         if (empty($booking->concierge_user_id)) {
    //             $showApproveButton = true;
    //         } else {
    //             $isApproved = true;
    //         }
    //     }

    //     if ($userRole == 2 && $booking->authorization_file) {
    //         $showViewButton = true;

    //         if (empty($booking->concierge_user_id)) {
    //             $waitingReason = 'Waiting for Concierge';
    //             $showApproveButton = false;
    //         } elseif (empty($booking->admin_user_id)) {
    //             $showApproveButton = true;
    //         } else {
    //             $isApproved = true;
    //         }
    //     }

    //     // ----- Strict Approval Flow -----
    //     if ($userRole == 5) { // Finance
    //         $showViewButton = true;

    //         // If previous approver rejected
    //         if ($booking->admin_approval == 2) {
    //             $waitingReason = 'Waiting for Admin';
    //             $showApproveButton = false;
    //             $isApproved = false;
    //         } else {
    //             if ($booking->authorization_file) {
    //                 if (empty($booking->admin_user_id)) {
    //                     $waitingReason = 'Waiting for Admin';
    //                 } elseif (empty($booking->finance_user_id)) {
    //                     $showApproveButton = true;
    //                 } else {
    //                     $isApproved = true;
    //                 }
    //             } else {
    //                 if (empty($booking->concierge_user_id)) {
    //                     $waitingReason = 'Waiting for Concierge';
    //                 } elseif (empty($booking->finance_user_id)) {
    //                     $showApproveButton = true;
    //                 } else {
    //                     $isApproved = true;
    //                 }
    //             }
    //         }
    //     }


    //     if ($userRole == 3) {
    //         $showViewButton = true;

    //         switch ($booking->engineering_approval ?? 0) {
    //             case 0:
    //                 if (empty($booking->finance_user_id)) {
    //                     $waitingReason = 'Waiting for Finance';
    //                 } else {
    //                     $showApproveButton = true;
    //                 }
    //                 break;

    //             case 1:
    //                 $isApproved = true;
    //                 break;

    //             case 2: // rejected
    //                 $rejected = true;
    //                 break;
    //         }
    //     }


    //     if ($userRole == 7) {
    //         $showViewButton = true;
    //         if (empty($booking->concierge_user_id)) {
    //             $waitingReason = 'Waiting for Concierge';
    //         } elseif ($booking->authorization_file && empty($booking->admin_user_id)) {
    //             $waitingReason = 'Waiting for Admin';
    //         } elseif (empty($booking->finance_user_id)) {
    //             $waitingReason = 'Waiting for Finance';
    //         } elseif ($booking->has_suppliers && empty($booking->engineering_user_id)) {
    //             $waitingReason = 'Waiting for Engineering';
    //         } elseif (empty($booking->manager_user_id)) {
    //             $showApproveButton = true;
    //         } else {
    //             $isApproved = true;
    //         }
    //     }

    //     $userApprovalStatus = 0; // 0 = waiting, 1 = approved, 2 = rejected

    //     switch ($userRole) {
    //         case 6:
    //             $userApprovalStatus = $booking->concierge_approval ?? 0;
    //             break;
    //         case 2:
    //             $userApprovalStatus = $booking->admin_approval ?? 0;
    //             break;
    //         case 5:
    //             $userApprovalStatus = $booking->finance_approval ?? 0;
    //             break;
    //         case 3:
    //             $userApprovalStatus = $booking->engineering_approval ?? 0;
    //             break;
    //         case 7:
    //             $userApprovalStatus = $booking->manager_approval ?? 0;
    //             break;
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'booking' => $booking,
    //         'authorization_file_url' => $authorizationFileUrl,
    //         'show_approve_button' => $showApproveButton,
    //         'show_view_button' => $showViewButton,
    //         'is_approved' => $isApproved,
    //         'waiting_reason' => $waitingReason,
    //         'status' => [
    //             'concierge' => $booking->concierge_approval ?? 0,
    //             'admin' => $booking->admin_approval ?? 0,
    //             'finance' => $booking->finance_approval ?? 0,
    //             'engineering' => $booking->engineering_approval ?? 0,
    //             'manager' => $booking->manager_approval ?? 0,
    //         ],
    //         'current_user_status' => $userApprovalStatus,
    //     ]);
    // }


    public function getFunctionRoomBookingDetails($id)
    {
        $booking = FunctionRoomBooking::with(['user', 'functionRoom', 'suppliers', 'addOns'])->findOrFail($id);
        $userRole = auth()->user()->role_id;

        // Format times
        $booking->event_start_time = $booking->event_start_time
            ? \Carbon\Carbon::parse($booking->event_start_time)->format('h:i A')
            : null;
        $booking->event_end_time = $booking->event_end_time
            ? \Carbon\Carbon::parse($booking->event_end_time)->format('h:i A')
            : null;

        // Authorization URL
        $authorizationFileUrl = $booking->authorization_file ? asset($booking->authorization_file) : null;

        // Supplier attachments
        $booking->suppliers->transform(function ($supplier) {
            $supplier->attachment_url = $supplier->attachment ? asset($supplier->attachment) : null;
            return $supplier;
        });
        $booking->has_suppliers = $booking->suppliers->count() > 0;

        // Approval columns mapping
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
                    $currentUserRejected = true; // This user rejected
                } else {
                    $rejectedByPrevious = true;
                    $rejectedByRole = ucfirst($role);
                }
                break;
            }
        }

        // Initialize buttons
        $showApproveButton = false;
        $showViewButton = false;
        $waitingReason = null;
        $isApproved = false;

        // Approval logic per role
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

            case 5: // Finance
                $showViewButton = true;
                if ($booking->admin_approval == 2)
                    $waitingReason = 'Waiting for Admin';
                elseif ($booking->authorization_file && !$booking->finance_user_id && !$rejectedByPrevious)
                    $showApproveButton = true;
                elseif (!$booking->authorization_file && !$booking->finance_user_id && !$rejectedByPrevious)
                    $showApproveButton = true;
                else
                    $isApproved = true;
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
        ]);
    }





    public function showFunctionRoomBookingRecords(Request $request)
    {
        $roleId = auth()->user()->role_id;
        $query = FunctionRoomBooking::with(['user', 'functionRoom']);

        // Apply role-based filters
        if (in_array($roleId, [1, 5, 7])) {
            $query->whereIn('booking_status', [1, 2]);
        } elseif ($roleId == 2) {
            $query->whereIn('booking_status', [1, 2])
                ->whereNotNull('authorization_file');
        } elseif ($roleId == 3) {
            $query->whereIn('booking_status', [1, 2])
                ->where('has_suppliers', 1);
        }

        // Get results and format times
        $functionRoomBookingRecords = $query->latest()->paginate(10);

        $functionRoomBookingRecords->getCollection()->transform(function ($booking) {
            $booking->formatted_start_time = Carbon::parse($booking->event_start_time)->format('h:i A');
            $booking->formatted_end_time = Carbon::parse($booking->event_end_time)->format('h:i A');
            return $booking;
        });

        return view('backend.admin-function-room-booking-records', compact('functionRoomBookingRecords'));
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
        // dd($request->all());
        $roleId = auth()->user()->role_id;

        $query = FunctionRoomBooking::with([
            'user',
            'functionRoom',
            'adminApprover',
            'financeApprover',
            'engineeringApprover',
            'managerApprover'
        ])->latest();

        // 🔹 Role-based filtering
        if (in_array($roleId, [1, 5, 7, 8])) {
            $query->whereIn('booking_status', [0, 1]);
        } elseif ($roleId == 2) {
            $query->whereIn('booking_status', [0, 1])
                ->whereNotNull('authorization_file');
        } elseif ($roleId == 3) {
            $query->whereIn('booking_status', [0, 1])
                ->where('has_suppliers', 1);
        }

        // 🔹 Search (Transaction No, Unit No, User Name)
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
                'event_start_time' => $booking->event_start_time
                    ? Carbon::parse($booking->event_start_time)->format('h:i A')
                    : null,
                'event_end_time' => $booking->event_end_time
                    ? Carbon::parse($booking->event_end_time)->format('h:i A')
                    : null,
                'contact_number' => $booking->contact_number ?? 'N/A',
                'pax' => $booking->pax ?? 'N/A',
                'payment_mode' => $booking->payment_mode ?? 'N/A',
                'booking_status' => $booking->booking_status,
                'has_suppliers' => $booking->has_suppliers,
                'authorization_file' => $booking->authorization_file,

                // Approvals + remarks + approver + action_at
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
                ->withPath('/admin/admin-get-updated-function-room-bookings-table')
                ->links('vendor.pagination.bootstrap-5')
                ->render()
        ]);
    }

    public function editFunctionRoomBooking($id)
    {
        $booking = FunctionRoomBooking::with(['user', 'functionRoom', 'addOns', 'suppliers'])->findOrFail($id);
        $residences = ResidentDetails::all();
        $addons = AddOn::where('status', 1)->get();

        return response()->json([
            'booking' => $booking,
            'residences' => $residences,
            'addons' => $addons,
            'authorization_file' => $booking->authorization_file
                ? asset($booking->authorization_file)
                : null,

        ]);
    }

    // public function updateFunctionRoomBooking(Request $request)
    // {
    //     DB::beginTransaction();

    //     try {
    //         $booking = FunctionRoomBooking::with(['suppliers', 'addOns'])
    //             ->findOrFail($request->booking_id); // ✅ get id from hidden input

    //         $room = FunctionRoom::findOrFail($request->function_room_id);

    //         // === Prevent double booking ===
    //         $isAlreadyBooked = FunctionRoomBooking::where('function_room_id', $request->function_room_id)
    //             ->where('function_room_booking_date', $request->function_room_booking_date)
    //             ->whereIn('booking_status', [0, 1])
    //             ->where('id', '!=', $booking->id) // exclude current booking
    //             ->lockForUpdate()
    //             ->exists();

    //         if ($isAlreadyBooked) {
    //             DB::rollBack();
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Sorry, another user has already booked this room for that date.'
    //             ], 409);
    //         }

    //         // === Handle authorization file ===
    //         $authorizationPath = $booking->authorization_file;

    //         if ($request->hasFile('authorization_file')) {
    //             if ($authorizationPath && file_exists(public_path($authorizationPath))) {
    //                 unlink(public_path($authorizationPath));
    //             }

    //             $file = $request->file('authorization_file');
    //             $originalName = $file->getClientOriginalName();
    //             $destinationPath = public_path('assets/frontend/uploads/function-room-bookings/authorizations');

    //             if (!file_exists($destinationPath)) {
    //                 mkdir($destinationPath, 0777, true);
    //             }

    //             $filename = $this->getUniqueFilename($destinationPath, $originalName);
    //             $file->move($destinationPath, $filename);

    //             $authorizationPath = 'assets/frontend/uploads/function-room-bookings/authorizations/' . $filename;
    //         } elseif ($request->remove_authorization == 1 && $authorizationPath) {
    //             if (file_exists(public_path($authorizationPath))) {
    //                 unlink(public_path($authorizationPath));
    //             }
    //             $authorizationPath = null;
    //         }

    //         // === Update suppliers ===
    //         $existingSuppliers = $booking->suppliers()->pluck('id')->toArray();
    //         $newSupplierIds = [];

    //         if ($request->has('suppliers')) {
    //             foreach ($request->suppliers as $index => $supplier) {
    //                 if (!empty($supplier['name'])) {
    //                     $supplierModel = isset($supplier['id'])
    //                         ? FunctionRoomBookingSupplier::find($supplier['id'])
    //                         : new FunctionRoomBookingSupplier();

    //                     $supplierPath = $supplierModel->attachment;

    //                     if ($request->hasFile("suppliers.$index.attachment")) {
    //                         if ($supplierPath && file_exists(public_path($supplierPath))) {
    //                             unlink(public_path($supplierPath));
    //                         }

    //                         $file = $request->file("suppliers.$index.attachment");
    //                         $originalName = $file->getClientOriginalName();
    //                         $destinationPath = public_path('assets/frontend/uploads/function-room-bookings/suppliers');

    //                         if (!file_exists($destinationPath)) {
    //                             mkdir($destinationPath, 0777, true);
    //                         }

    //                         $filename = $this->getUniqueFilename($destinationPath, $originalName);
    //                         $file->move($destinationPath, $filename);

    //                         $supplierPath = 'assets/frontend/uploads/function-room-bookings/suppliers/' . $filename;
    //                     } elseif (isset($supplier['remove_attachment']) && $supplier['remove_attachment'] == 1) {
    //                         if ($supplierPath && file_exists(public_path($supplierPath))) {
    //                             unlink(public_path($supplierPath));
    //                         }
    //                         $supplierPath = null;
    //                     }

    //                     $supplierModel->fill([
    //                         'booking_id' => $booking->id,
    //                         'name' => $supplier['name'],
    //                         'attachment' => $supplierPath,
    //                     ])->save();

    //                     $newSupplierIds[] = $supplierModel->id;
    //                 }
    //             }
    //         }

    //         $toDelete = array_diff($existingSuppliers, $newSupplierIds);
    //         foreach ($toDelete as $deleteId) {
    //             $sup = FunctionRoomBookingSupplier::find($deleteId);
    //             if ($sup && $sup->attachment && file_exists(public_path($sup->attachment))) {
    //                 unlink(public_path($sup->attachment));
    //             }
    //             $sup?->delete();
    //         }

    //         // === Update Addons ===
    //         $addonsTotal = 0;
    //         $addonData = [];

    //         $booking->addOns()->detach();

    //         if ($request->has('addons')) {
    //             foreach ($request->addons as $addonId => $addon) {
    //                 if (isset($addon['selected']) && $addon['selected'] == 1) {
    //                     $addonModel = AddOn::where('id', $addonId)->lockForUpdate()->first();

    //                     if ($addonModel) {
    //                         $qtyRequested = max(1, $addon['qty'] ?? 1);

    //                         // 🔹 Check reserved excluding THIS booking
    //                         $reserved = AddOnFunctionRoomBooking::whereHas('booking', function ($q) use ($request, $booking) {
    //                             $q->where('function_room_booking_date', $request->function_room_booking_date)
    //                                 ->whereIn('booking_status', [0, 1])
    //                                 ->where('id', '!=', $booking->id); // exclude current booking
    //                         })
    //                             ->where('add_on_id', $addonId)
    //                             ->lockForUpdate()
    //                             ->sum('qty');

    //                         $available = $addonModel->qty - $reserved;

    //                         if ($qtyRequested > $available) {
    //                             DB::rollBack();
    //                             return response()->json([
    //                                 'success' => false,
    //                                 'message' => "Sorry, only {$available} of {$addonModel->item} left for this date."
    //                             ], 422);
    //                         }

    //                         $addonsTotal += $qtyRequested * $addonModel->price;

    //                         $addonData[$addonId] = [
    //                             'qty' => $qtyRequested,
    //                             'price' => $addonModel->price,
    //                         ];
    //                     }
    //                 }
    //             }

    //             if (!empty($addonData)) {
    //                 $booking->addOns()->attach($addonData);
    //             }
    //         }


    //         // === Calculate totals ===
    //         $start = Carbon::parse($request->event_start_time);
    //         $end = Carbon::parse($request->event_end_time);
    //         if ($end->lte($start)) {
    //             $end->addDay();
    //         }

    //         $durationHours = $start->floatDiffInHours($end);
    //         $roomTotal = $room->discounted_rate * $durationHours;

    //         // === Update booking record ===
    //         $booking->update([
    //             'function_room_id' => $room->id,
    //             'purpose_of_event' => $request->purpose_of_event,
    //             'function_room_booking_date' => $request->function_room_booking_date,
    //             'event_start_time' => $request->event_start_time,
    //             'event_end_time' => $request->event_end_time,
    //             'contact_number' => $request->contact_number,
    //             'pax' => $request->pax,
    //             'payment_mode' => $request->payment_mode,
    //             'has_suppliers' => $request->boolean('has_suppliers'),
    //             'authorization_file' => $authorizationPath,
    //             'room_total' => $roomTotal,
    //             'addons_total' => $addonsTotal,
    //             'total_amount' => $roomTotal + $addonsTotal,
    //         ]);

    //         DB::commit();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Booking updated successfully',
    //         ]);

    //     } catch (\Throwable $e) {
    //         DB::rollBack();

    //         Log::error("Error updating booking", [
    //             'message' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString(),
    //         ]);

    //         return response()->json([
    //             'success' => false,
    //             'message' => "Something went wrong while updating the booking."
    //         ], 500);
    //     }
    // }

    public function updateFunctionRoomBooking(Request $request)
    {
        $maxRetries = 3;
        $attempt = 0;

        while ($attempt < $maxRetries) {
            try {
                DB::beginTransaction();

                $booking = FunctionRoomBooking::with(['suppliers', 'addOns'])
                    ->findOrFail($request->booking_id);

                $room = FunctionRoom::findOrFail($request->function_room_id);

                // === Prevent double booking of function room ===
                $isAlreadyBooked = FunctionRoomBooking::where('function_room_id', $request->function_room_id)
                    ->where('function_room_booking_date', $request->function_room_booking_date)
                    ->whereIn('booking_status', [0, 1])
                    ->where('id', '!=', $booking->id)
                    ->lockForUpdate()
                    ->exists();

                if ($isAlreadyBooked) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Sorry, another user has already booked this room for that date.'
                    ], 409);
                }

                // === Handle authorization file ===
                $authorizationPath = $booking->authorization_file;

                if ($request->hasFile('authorization_file')) {
                    if ($authorizationPath && file_exists(public_path($authorizationPath))) {
                        unlink(public_path($authorizationPath));
                    }

                    $file = $request->file('authorization_file');
                    $originalName = $file->getClientOriginalName();
                    $destinationPath = public_path('assets/frontend/uploads/function-room-bookings/authorizations');

                    if (!file_exists($destinationPath))
                        mkdir($destinationPath, 0777, true);

                    $filename = $this->getUniqueFilename($destinationPath, $originalName);
                    $file->move($destinationPath, $filename);

                    $authorizationPath = 'assets/frontend/uploads/function-room-bookings/authorizations/' . $filename;
                } elseif ($request->remove_authorization == 1 && $authorizationPath) {
                    if (file_exists(public_path($authorizationPath)))
                        unlink(public_path($authorizationPath));
                    $authorizationPath = null;
                }

                // === Update suppliers ===
                $existingSuppliers = $booking->suppliers()->pluck('id')->toArray();
                $newSupplierIds = [];

                if ($request->has('suppliers')) {
                    foreach ($request->suppliers as $index => $supplier) {
                        if (!empty($supplier['name'])) {
                            $supplierModel = isset($supplier['id'])
                                ? FunctionRoomBookingSupplier::find($supplier['id'])
                                : new FunctionRoomBookingSupplier();

                            $supplierPath = $supplierModel->attachment;

                            if ($request->hasFile("suppliers.$index.attachment")) {
                                if ($supplierPath && file_exists(public_path($supplierPath))) {
                                    unlink(public_path($supplierPath));
                                }

                                $file = $request->file("suppliers.$index.attachment");
                                $originalName = $file->getClientOriginalName();
                                $destinationPath = public_path('assets/frontend/uploads/function-room-bookings/suppliers');

                                if (!file_exists($destinationPath))
                                    mkdir($destinationPath, 0777, true);

                                $filename = $this->getUniqueFilename($destinationPath, $originalName);
                                $file->move($destinationPath, $filename);

                                $supplierPath = 'assets/frontend/uploads/function-room-bookings/suppliers/' . $filename;
                            } elseif (isset($supplier['remove_attachment']) && $supplier['remove_attachment'] == 1) {
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

                $toDelete = array_diff($existingSuppliers, $newSupplierIds);
                foreach ($toDelete as $deleteId) {
                    $sup = FunctionRoomBookingSupplier::find($deleteId);
                    if ($sup && $sup->attachment && file_exists(public_path($sup->attachment))) {
                        unlink(public_path($sup->attachment));
                    }
                    $sup?->delete();
                }

                // === Update Add-ons with stock check ===
                $addonsTotal = 0;
                $addonData = [];

                $booking->addOns()->detach();

                if ($request->has('addons')) {
                    foreach ($request->addons as $addonId => $addon) {
                        if (isset($addon['selected']) && $addon['selected'] == 1) {
                            $addonModel = AddOn::where('id', $addonId)->lockForUpdate()->first();
                            if ($addonModel) {
                                $qtyRequested = max(1, $addon['qty'] ?? 1);

                                // 🔹 Reserved qty excluding this booking
                                $reserved = AddOnFunctionRoomBooking::whereHas('booking', function ($q) use ($request, $booking) {
                                    $q->where('function_room_booking_date', $request->function_room_booking_date)
                                        ->whereIn('booking_status', [0, 1])
                                        ->where('id', '!=', $booking->id);
                                })
                                    ->where('add_on_id', $addonId)
                                    ->lockForUpdate()
                                    ->sum('qty');

                                $available = $addonModel->qty - $reserved;

                                if ($qtyRequested > $available) {
                                    DB::rollBack();
                                    return response()->json([
                                        'success' => false,
                                        'message' => "Sorry, another resident booked faster. Only {$available} of {$addonModel->item} left for this date."
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

                // === Calculate totals ===
                $start = Carbon::parse($request->event_start_time);
                $end = Carbon::parse($request->event_end_time);
                if ($end->lte($start))
                    $end->addDay();
                $durationHours = $start->floatDiffInHours($end);
                $roomTotal = $room->discounted_rate * $durationHours;

                // === Update booking ===
                $booking->update([
                    'function_room_id' => $room->id,
                    'purpose_of_event' => $request->purpose_of_event,
                    'function_room_booking_date' => $request->function_room_booking_date,
                    'event_start_time' => $request->event_start_time,
                    'event_end_time' => $request->event_end_time,
                    'contact_number' => $request->contact_number,
                    'pax' => $request->pax,
                    'payment_mode' => $request->payment_mode,
                    'has_suppliers' => $request->boolean('has_suppliers'),
                    'authorization_file' => $authorizationPath,
                    'room_total' => $roomTotal,
                    'addons_total' => $addonsTotal,
                    'total_amount' => $roomTotal + $addonsTotal,
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Booking updated successfully',
                ]);

            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollBack();

                // 🔹 Deadlock / lock wait timeout → retry
                if (in_array($e->errorInfo[1], [1213, 1205])) {
                    $attempt++;
                    if ($attempt < $maxRetries) {
                        usleep(100000); // small delay 0.1s
                        continue; // retry
                    }
                }

                Log::error("Error updating booking", [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong while updating the booking.'
                ], 500);

            } catch (\Throwable $e) {
                DB::rollBack();

                Log::error("Error updating booking", [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong while updating the booking.'
                ], 500);
            }
        }

        // Fallback if all retries fail
        return response()->json([
            'success' => false,
            'message' => "Could not update booking. Please try again in a few seconds."
        ], 500);
    }

    public function downloadFunctionRoomBookingRecords(Request $request)
    {
        $request->validate([
            'download_start_date' => 'required|date',
            'download_end_date' => 'required|date|after_or_equal:download_start_date',
        ]);

        $startDate = $request->download_start_date;
        $endDate = $request->download_end_date;

        Log::info("Download function room booking request received", [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        $data = DB::table('function_room_bookings as frb')
            ->join('users', 'frb.user_id', '=', 'users.id')
            ->join('function_rooms as fr', 'frb.function_room_id', '=', 'fr.id')
            ->leftJoin('add_on_function_room_bookings as frba', 'frb.id', '=', 'frba.function_room_booking_id')
            ->leftJoin('add_ons as ao', 'frba.add_on_id', '=', 'ao.id')
            ->leftJoin('function_room_booking_suppliers as frbs', 'frb.id', '=', 'frbs.booking_id')
            ->select(
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
                'frb.total_amount',
                'frb.booking_status',
                DB::raw('GROUP_CONCAT(DISTINCT CONCAT(ao.item, " x", frba.qty) SEPARATOR ", ") as addons'),
                DB::raw('GROUP_CONCAT(DISTINCT frbs.name SEPARATOR ", ") as suppliers')
            )
            ->whereIn('frb.booking_status', [1, 2])
            ->whereBetween('frb.function_room_booking_date', [$startDate, $endDate])
            ->groupBy('frb.id', 'users.name', 'frb.unit_no', 'frb.resident_type', 'fr.function_room_name', 'frb.purpose_of_event', 'frb.function_room_booking_date', 'frb.event_start_time', 'frb.event_end_time', 'frb.pax', 'frb.payment_mode', 'frb.total_amount', 'frb.booking_status')
            ->get();

        $fileName = "2S_FunctionRoom_Bookings_" . Carbon::parse($startDate)->format('Ymd') . "_to_" . Carbon::parse($endDate)->format('Ymd') . ".csv";

        $response = new StreamedResponse(function () use ($data) {
            $handle = fopen('php://output', 'w');

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
                'Pax',
                'Payment Mode',
                'Total Amount',
                'Booking Status',
                'Add-Ons',
                'Suppliers'
            ]);

            foreach ($data as $row) {
                $bookingDate = Carbon::parse($row->function_room_booking_date)->format('F j, Y');
                $startTime = Carbon::parse($row->event_start_time)->format('h:i A');
                $endTime = Carbon::parse($row->event_end_time)->format('h:i A');
                $status = $row->booking_status == 1 ? 'Confirmed' : 'Cancelled';

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
                    $row->pax,
                    $row->payment_mode,
                    number_format($row->total_amount, 2),
                    $status,
                    $row->addons,
                    $row->suppliers
                ]);

                ob_flush();
                flush();
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        Log::info("Function room booking CSV generated", ['filename' => $fileName, 'records' => count($data)]);

        return $response;
    }
}
