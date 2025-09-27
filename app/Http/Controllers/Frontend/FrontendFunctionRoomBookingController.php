<?php

namespace App\Http\Controllers\Frontend;

use App\Models\FunctionRoomAuthorization;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\FunctionRoom;
use App\Models\FunctionRoomBooking;
use App\Models\Amenity;
use App\Models\AddOn;
use App\Models\ResidentDetails;
use App\Models\FunctionRoomBookingSupplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Mail\UserFunctionRoomBookingNotification;
use App\Mail\FinanceFunctionRoomBookingNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use App\Notifications\UserFunctionRoomBookingBellNotification;
use App\Models\FunctionRoomDateBlocking;
use App\Models\AddOnFunctionRoomBooking;
use App\Events\FunctionRoomBookingCreated;
use App\Events\FunctionRoomBookingCancellation;
use App\Mail\UserFunctionRoomBookingCancelled;
use App\Mail\FinanceFunctionRoomBookingCancellation;
use Carbon\Carbon;


class FrontendFunctionRoomBookingController extends Controller
{
    public function list(Request $request)
    {
        $category = $request->get('category', '');

        if ($category === 'function_room') {
            $items = FunctionRoom::with('firstImage')
                ->get()
                ->map(function ($item) {
                    $item->type = 'function_room';
                    $item->imageFolder = 'function-rooms';
                    return $item;
                });
        } elseif ($category === 'amenity') {
            $items = Amenity::with('firstImage')
                ->get()
                ->map(function ($item) {
                    $item->type = 'amenity';
                    $item->imageFolder = 'amenities'; // ✅ added
                    return $item;
                });
        } else {
            $functionRooms = FunctionRoom::with('firstImage')
                ->get()
                ->map(function ($item) {
                    $item->type = 'function_room';
                    $item->imageFolder = 'function-rooms';
                    return $item;
                });

            $amenities = Amenity::with('firstImage')
                ->get()
                ->map(function ($item) {
                    $item->type = 'amenity';
                    $item->imageFolder = 'amenities'; // ✅ added
                    return $item;
                });

            $items = $functionRooms->merge($amenities);
        }

        return view('frontend.booking-list', [
            'items' => $items,
            'category' => $category
        ]);
    }


    public function fullDetails($type, $id)
    {
        if ($type === 'function_room') {
            $item = FunctionRoom::with('images')->findOrFail($id);

            // Show suggestions only from Function Rooms
            $suggestions = FunctionRoom::with('images')
                ->where('id', '!=', $id) // Exclude current one
                ->inRandomOrder()
                ->take(4)
                ->get();

        } elseif ($type === 'amenity') {
            $item = Amenity::with('images')->findOrFail($id);

            // Show suggestions only from Amenities
            $suggestions = Amenity::with('images')
                ->where('id', '!=', $id)
                ->inRandomOrder()
                ->take(4)
                ->get();
        } else {
            abort(404);
        }

        $addons = AddOn::all();
        $residences = auth()->check()
            ? DB::table('resident_details')
                ->where('email', auth()->user()->email)
                ->select('id', 'unit_no', 'resident_type')
                ->get()
            : collect();

        return view('frontend.booking-full-details', compact('item', 'type', 'residences', 'suggestions', 'addons'));
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

    public function store(Request $request)
    {
        $maxRetries = 3;
        $attempt = 0;

        while ($attempt < $maxRetries) {
            try {
                DB::beginTransaction();

                // === Prevent double booking for function room ===
                $isAlreadyBooked = FunctionRoomBooking::where('function_room_id', $request->function_room_id)
                    ->where('function_room_booking_date', $request->function_room_booking_date)
                    ->whereIn('booking_status', [0, 1])
                    ->lockForUpdate()
                    ->exists();

                if ($isAlreadyBooked) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Sorry, other user has booked it just now. Kindly try another date'
                    ], 409);
                }

                // === Generate transaction number ===
                $lastId = FunctionRoomBooking::max('id') + 1;
                $transactionNo = '2SFR-' . str_pad($lastId, 5, '0', STR_PAD_LEFT);

                $resident = ResidentDetails::where('email', auth()->user()->email)->first();
                $unitNo = $resident ? $resident->unit_no : null;

                $room = FunctionRoom::findOrFail($request->function_room_id);

                // === Authorization file upload ===
                $authorizationPath = null;
                if ($request->hasFile('authorization_file')) {
                    $file = $request->file('authorization_file');
                    $originalName = $file->getClientOriginalName();
                    $destinationPath = public_path('assets/frontend/uploads/function-room-bookings/authorizations');

                    if (!file_exists($destinationPath))
                        mkdir($destinationPath, 0777, true);

                    $filename = $this->getUniqueFilename($destinationPath, $originalName);
                    $file->move($destinationPath, $filename);
                    $authorizationPath = 'assets/frontend/uploads/function-room-bookings/authorizations/' . $filename;
                }

                // === Booking duration & rate ===
                $start = Carbon::parse($request->event_start_time);
                $end = Carbon::parse($request->event_end_time);
                if ($end->lte($start))
                    $end->addDay();
                $durationHours = $start->floatDiffInHours($end);
                $roomTotal = $room->discounted_rate * $durationHours;

                // === Create booking ===
                $booking = FunctionRoomBooking::create([
                    'transaction_no' => $transactionNo,
                    'user_id' => auth()->id(),
                    'unit_no' => $unitNo,
                    'resident_type' => $resident?->resident_type,
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
                    'base_rate' => $room->function_room_rate,
                    'discount' => $room->discount,
                    'final_rate' => $room->discounted_rate,
                    'room_total' => $roomTotal,
                    'addons_total' => 0,
                    'total_amount' => $roomTotal,
                ]);

                // === Save suppliers ===
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

                            FunctionRoomBookingSupplier::create([
                                'booking_id' => $booking->id,
                                'name' => $supplier['name'],
                                'attachment' => $supplierPath,
                            ]);
                        }
                    }
                }

                // === Add-ons with stock check ===
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
                        $booking->addOns()->attach($addonData);
                    }
                }

                // === Update totals ===
                $booking->update([
                    'addons_total' => $addonsTotal,
                    'total_amount' => $roomTotal + $addonsTotal,
                ]);

                // === Notifications ===
                Log::info('Queuing user email', [
                    'email' => $booking->user->email,
                    'user' => $booking->user->toArray()
                ]);
                $booking->load(['user', 'functionRoom']);
                Mail::to($booking->user->email)->queue(new UserFunctionRoomBookingNotification($booking));
                Mail::to('itdept@twoserendra.com')->queue(new FinanceFunctionRoomBookingNotification($booking));
                event(new FunctionRoomBookingCreated($booking));
                $booking->user->notify(new UserFunctionRoomBookingBellNotification($booking));

                Log::info("Booking created", [
                    'transaction_no' => $transactionNo,
                    'booking_id' => $booking->id,
                    'user_id' => auth()->id(),
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => "Booking saved! Transaction No: {$transactionNo}",
                    'transaction_no' => $transactionNo
                ]);

            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollBack();

                // 🔹 Deadlock or lock wait timeout detected → retry
                if (in_array($e->errorInfo[1], [1213, 1205])) {
                    $attempt++;
                    if ($attempt < $maxRetries) {
                        usleep(100000); // small delay 0.1s before retry
                        continue; // retry the transaction
                    }
                }

                Log::error("Error creating booking", [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $e->errorInfo[1] == 23000
                        ? 'Sorry, other user booked it just now.'
                        : 'Something went wrong while saving the booking.'
                ], 500);
            } catch (\Throwable $e) {
                DB::rollBack();

                Log::error("Error creating booking", [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => "Something went wrong while saving the booking."
                ], 500);
            }
        }

        // Fallback if all retries fail
        return response()->json([
            'success' => false,
            'message' => "Could not complete booking. Please try again in a few seconds."
        ], 500);
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

    public function cancel($id)
    {
        $booking = FunctionRoomBooking::with('user', 'functionRoom')->findOrFail($id);

        if ($booking->booking_status == 0) {
            $eventDateTime = \Carbon\Carbon::parse($booking->function_room_booking_date . ' ' . $booking->event_start_time);
            $hoursDiff = now()->diffInHours($eventDateTime, false);
            $penalty = 0;
            if ($hoursDiff < 24) {
                $penalty = 1000;
                $booking->penalty_fee = $penalty;
            }

            $booking->booking_status = 2;
            $booking->save();
            Mail::to($booking->user->email)->queue(new UserFunctionRoomBookingCancelled($booking));
            Mail::to('itdept@twoserendra.com')->queue(new FinanceFunctionRoomBookingCancellation($booking));

            $booking->user->notify(new UserFunctionRoomBookingBellNotification($booking));

            event(new FunctionRoomBookingCancellation($booking));

            return response()->json(['success' => true, 'penalty' => $penalty]);
        }

        return response()->json(['success' => false, 'message' => 'Booking cannot be cancelled']);
    }


    public function checkUnitTenant($unitNo)
    {
        $hasTenant = DB::table('emails')
            ->where('unit_no', $unitNo)
            ->where('resident_type', 'TENANT')
            ->exists();

        return response()->json(['hasTenant' => $hasTenant]);
    }

    public function showFunctionRoomBookingDetails(FunctionRoomBooking $booking)
    {
        $booking->load(['user', 'functionRoom', 'suppliers', 'addOns']);
        return view('frontend.user-function-room-booking-details', compact('booking'));
    }

    public function FunctionRoommNotificationMarkAsRead($id, Request $request)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'booking_id' => $notification->data['booking_id'] ?? null,
        ]);
    }

    public function getFunctionRoomBookedDates($roomId)
    {
        $bookedDates = FunctionRoomBooking::where('function_room_id', $roomId)
            ->whereIn('booking_status', [0, 1])
            ->pluck('function_room_booking_date')
            ->toArray();
        $blockedDates = FunctionRoomDateBlocking::where('function_room_id', $roomId)
            ->get()
            ->flatMap(function ($block) {
                $dates = [];
                $start = \Carbon\Carbon::parse($block->date_blocking_start)->startOfDay();
                $end = \Carbon\Carbon::parse($block->date_blocking_end)->endOfDay();

                while ($start->lte($end)) {
                    $dates[] = $start->format('Y-m-d');
                    $start->addDay();
                }
                return $dates;
            })
            ->toArray();

        $disabledDates = array_values(array_unique(array_merge($bookedDates, $blockedDates)));

        return response()->json($disabledDates);
    }

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
        $authorizationFileUrl = $booking->authorization_file
            ? asset($booking->authorization_file)
            : null;

        // Add supplier URLs
        $booking->suppliers->transform(function ($supplier) {
            $supplier->attachment_url = $supplier->attachment ? asset($supplier->attachment) : null;
            return $supplier;
        });
        $booking->duration_hours = $booking->duration_in_hours;
        $booking->has_suppliers = $booking->suppliers->count() > 0;

        // Default states
        $showViewButton = false;
        $showButton = false;
        $waitingReason = null;

        // Role-specific approval status
        $isApproved = false;
        if ($userRole == 2 && $booking->authorization_file) {
            $showViewButton = true;

            // Allow Admin to approve if not yet approved
            if (empty($booking->admin_approved_by)) {
                $showButton = true;
            }
        }
        if ($userRole == 5)
            $isApproved = !empty($booking->finance_approved_by);
        if ($userRole == 3)
            $isApproved = !empty($booking->engineering_approved_by);
        if ($userRole == 7)
            $isApproved = !empty($booking->manager_approved_by);

        $financeApproved = !empty($booking->finance_approved_by);

        /**
         * Logic
         */

        // Admin view button
        if ($userRole == 2 && $booking->authorization_file) {
            $showViewButton = true;
        }

        // Engineering
        if ($userRole == 3 && $booking->has_suppliers) {
            $showViewButton = true;
            if (!$financeApproved) {
                $waitingReason = 'Waiting for Finance';
            } elseif (!$booking->engineering_approved_by) {
                $showButton = true;
            }
        }

        // Finance
        if ($userRole == 5 && !$booking->finance_approved_by) {
            if (($booking->authorization_file && $booking->admin_approved_by) || !$booking->authorization_file) {
                $showButton = true;
            }
        }

        // Manager
        if ($userRole == 7 && !$booking->manager_approved_by && $financeApproved) {
            if (($booking->has_suppliers && $booking->engineering_approved_by) || !$booking->has_suppliers) {
                $showButton = true;
            } else {
                $waitingReason = 'Waiting for Engineering';
            }
        }

        return response()->json([
            'success' => true,
            'booking' => $booking,
            'authorization_file_url' => $authorizationFileUrl,
            'show_approve_button' => $showButton,
            'show_view_button' => $showViewButton,
            'is_approved' => $isApproved,
            'waiting_reason' => $waitingReason,
        ]);
    }




}
