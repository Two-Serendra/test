<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Activity;
use App\Models\ActivityBooking;
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
use App\Models\FunctionRoomDiscount;
use App\Events\FunctionRoomBookingCreated;
use App\Events\FunctionRoomBookingCancellation;
use App\Mail\UserFunctionRoomBookingCancelled;
use App\Mail\FinanceFunctionRoomBookingCancellation;
use Carbon\Carbon;


class FrontendFunctionRoomBookingController extends Controller
{
    public function list(Request $request)
    {
        $category = $request->get('category', 'amenity');
        $items = collect();        // default
        $residences = collect();

        if ($category === 'function_room') {
            $items = FunctionRoom::with(['firstImage', 'discounts'])
                ->get()
                ->map(function ($item) {
                    $item->type = 'function_room';
                    $item->imageFolder = 'function-rooms';

                    // get active discount
                    $activeDiscount = $item->discounts
                        ->where('start_date', '<=', now())
                        ->where('end_date', '>=', now())
                        ->first();

                    if ($activeDiscount) {
                        $item->discount = $activeDiscount->discount;
                        $item->discounted_rate = $item->function_room_rate - ($item->function_room_rate * ($activeDiscount->discount / 100));
                    } else {
                        $item->discount = 0;
                        $item->discounted_rate = $item->function_room_rate;
                    }

                    return $item;
                });
        } elseif ($category === 'amenity') {
            $items = Activity::where('activity_status', '1')
                ->orderBy('id', 'asc') // sort by id ascending
                ->get()
                ->map(function ($item) {
                    $item->type = 'amenity';
                    return $item;
                });
        } elseif ($category === 'grease_trap') {

            $residences = auth()->check()
                ? DB::table('resident_details')
                    ->where('email', auth()->user()->email)
                    ->select('id', 'unit_no', 'resident_type')
                    ->get()
                : collect();
            $items = collect();




        } elseif ($category === 'pest_control') {

            $residences = auth()->check()
                ? DB::table('resident_details')
                    ->where('email', auth()->user()->email)
                    ->select('id', 'unit_no', 'resident_type')
                    ->get()
                : collect();
            $items = collect();



        } else {
            $functionRooms = FunctionRoom::with(['firstImage', 'discounts'])
                ->get()
                ->map(function ($item) {
                    $item->type = 'function_room';
                    $item->imageFolder = 'function-rooms';

                    // get active discount
                    $activeDiscount = $item->discounts
                        ->where('start_date', '<=', now())
                        ->where('end_date', '>=', now())
                        ->first();

                    if ($activeDiscount) {
                        $item->discount = $activeDiscount->discount;
                        $item->discounted_rate = $item->function_room_rate - ($item->function_room_rate * ($activeDiscount->discount / 100));
                    } else {
                        $item->discount = 0;
                        $item->discounted_rate = $item->function_room_rate;
                    }

                    return $item;
                });

            $activity = Activity::where('activity_status', '1')->get()
                ->map(function ($item) {
                    $item->type = 'amenity';
                    return $item;
                });

            $items = $functionRooms->merge($activity);
        }

        return view('frontend.booking-list', [
            'items' => $items,
            'category' => $category,
            'residences' => $residences
        ]);
    }


    public function fullDetails($type, $id)
    {
        if ($type === 'function_room') {
            $item = FunctionRoom::with(['images', 'discounts'])->findOrFail($id);

            $linkedToThisRoom = [
                13 => [14], //b&c fr1
                14 => [13], //b&c fr2
            ];


            $activeDiscount = $item->discounts
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->first();

            if ($activeDiscount) {
                $item->discount = $activeDiscount->discount;
                $item->discounted_rate = $item->function_room_rate - ($item->function_room_rate * ($activeDiscount->discount / 100));
                $item->discount_start = $activeDiscount->start_date;
                $item->discount_end = $activeDiscount->end_date;

            } else {
                $item->discount = 0;
                $item->discounted_rate = $item->function_room_rate;
            }

            // Suggestions only from Function Rooms
            $suggestions = FunctionRoom::with(['images', 'discounts'])
                ->where('id', '!=', $id)
                ->inRandomOrder()
                ->take(4)
                ->get()
                ->map(function ($suggestion) {
                    $activeDiscount = $suggestion->discounts
                        ->where('start_date', '<=', now())
                        ->where('end_date', '>=', now())
                        ->first();

                    if ($activeDiscount) {
                        $suggestion->discount = $activeDiscount->discount;
                        $suggestion->discounted_rate = $suggestion->function_room_rate - ($suggestion->function_room_rate * ($activeDiscount->discount / 100));

                        $suggestion->discount_start = $activeDiscount ? $activeDiscount->start_date : null;
                        $suggestion->discount_end = $activeDiscount ? $activeDiscount->end_date : null;

                    } else {
                        $suggestion->discount = 0;
                        $suggestion->discounted_rate = $suggestion->function_room_rate;
                    }
                    return $suggestion;
                });

        } elseif ($type === 'amenity') {
            $item = Amenity::with('images')->findOrFail($id);

            // Suggestions only from Amenities
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

        return view('frontend.booking-full-details', compact('item', 'type', 'residences', 'suggestions', 'addons', 'linkedToThisRoom'));
    }

    public function fullDetailsActivity($type, $activity_id)
    {
        $activity = Activity::with('ActivityBooking')->findOrFail($activity_id);
        $activities = Activity::where('activity_status', 1)->get();

        $suggestions = Activity::with('amenity')
            ->where('id', '!=', $activity->id)
            ->inRandomOrder()
            ->take(4)
            ->get();

        $residences = auth()->check()
            ? DB::table('resident_details')
                ->where('email', auth()->user()->email)
                ->select('id', 'unit_no', 'resident_type')
                ->get()
            : collect();

        return view('frontend.booking-full-details-activity', compact(
            'activity',
            'suggestions',
            'residences',
            'activities'
        ));
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
            $eventDateTime = Carbon::parse($booking->function_room_booking_date . ' ' . $booking->event_start_time);
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
        $hasTenant = DB::table('resident_details')
            ->where('unit_no', $unitNo)
            ->where('resident_type', 'TENANT')
            ->exists();

        return response()->json(['hasTenant' => $hasTenant]);
    }

    public function showFunctionRoomBookingDetails($id)
    {
        $booking = FunctionRoomBooking::with(['user', 'functionRoom', 'suppliers', 'addOns'])
            ->findOrFail($id);

        // Load all bookings under the same transaction number
        $bookings = FunctionRoomBooking::where('transaction_no', $booking->transaction_no)
            ->with(['user', 'functionRoom', 'suppliers', 'addOns'])
            ->orderBy('function_room_id')
            ->get();

        return view('frontend.user-function-room-booking-details', compact('bookings'));
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



    public function store(Request $request)
    {
        $maxRetries = 3;
        $attempt = 0;

        while ($attempt < $maxRetries) {
            try {
                DB::beginTransaction();

                $linkedRooms = [
                    13 => [14], //b&c fr1
                    14 => [13],//b&c fr2
                ];

                $roomsToBook = [$request->function_room_id];
                if ($request->boolean('book_linked_rooms') && isset($linkedRooms[$request->function_room_id])) {
                    $roomsToBook = array_merge($roomsToBook, $linkedRooms[$request->function_room_id]);
                }

                $sharedRooms = [
                    11 => [9],  // meranti tropical → culinary
                    9 => [11],  // culinary → meranti tropical
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
                        'message' => 'Sorry, the selected function room has been blocked by admin for this date.'
                    ], 409);
                }


                $lastId = FunctionRoomBooking::max('id') + 1;
                $transactionNo = '2SFR-' . str_pad($lastId, 5, '0', STR_PAD_LEFT);

                $resident = ResidentDetails::where('email', auth()->user()->email)->first();
                $unitNo = $resident ? $resident->unit_no : null;


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
                        'discount' => $appliedDiscount,
                        'discount_remarks' => $discountRemarks,
                        'final_rate' => $finalRate,
                        'room_total' => $roomTotal,
                        'addons_total' => 0,
                        'total_amount' => $roomTotal,
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

                if ($request->has('suppliers')) {
                    $supplierUploads = [];
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

                Mail::to($mainBooking->user->email)
                    ->queue(new UserFunctionRoomBookingNotification($mainBooking, $bookings));
                Mail::to('itdept@twoserendra.com')
                    ->queue(new FinanceFunctionRoomBookingNotification($mainBooking, $bookings));

                event(new FunctionRoomBookingCreated($mainBooking));
                $mainBooking->user->notify(new UserFunctionRoomBookingBellNotification($mainBooking));

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => "Booking saved! Transaction No: {$transactionNo}",
                    'transaction_no' => $transactionNo
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
                Log::error("Error creating booking", ['message' => $e->getMessage()]);
                return response()->json([
                    'success' => false,
                    'message' => $e->errorInfo[1] == 23000 ? 'Sorry, other user booked it just now.' : 'Something went wrong while saving the booking.'
                ], 500);
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("Error creating booking", ['message' => $e->getMessage()]);
                return response()->json([
                    'success' => false,
                    'message' => "Something went wrong while saving the booking."
                ], 500);
            }
        }

        return response()->json([
            'success' => false,
            'message' => "Could not complete booking. Please try again in a few seconds."
        ], 500);
    }


    public function getFunctionRoomBookedDates($roomId)
    {
        $linkedRooms = [
            13 => [14], //b&c fr1
            14 => [13], //b&c fr2

            11 => [9], //meranti tropical - meranti culinary
            9 => [11], //meranti culinary - meranti tropical
        ];

        $relatedRoomIds = [$roomId];
        if (isset($linkedRooms[$roomId])) {
            $relatedRoomIds = array_merge($relatedRoomIds, $linkedRooms[$roomId]);
        }
        $bookedDates = FunctionRoomBooking::whereIn('function_room_id', $relatedRoomIds)
            ->whereIn('booking_status', [0, 1])
            ->pluck('function_room_booking_date')
            ->toArray();
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

        $disabledDates = array_values(array_unique(array_merge($bookedDates, $blockedDates)));

        return response()->json($disabledDates);
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

        $authorizationFileUrl = $booking->authorization_file
            ? asset($booking->authorization_file)
            : null;

        $booking->suppliers->transform(function ($supplier) {
            $supplier->attachment_url = $supplier->attachment ? asset($supplier->attachment) : null;
            return $supplier;
        });
        $booking->duration_hours = $booking->duration_in_hours;
        $booking->has_suppliers = $booking->suppliers->count() > 0;


        $showViewButton = false;
        $showButton = false;
        $waitingReason = null;

        $isApproved = false;
        if ($userRole == 2 && $booking->authorization_file) {
            $showViewButton = true;
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


        if ($userRole == 2 && $booking->authorization_file) {
            $showViewButton = true;
        }

        if ($userRole == 3 && $booking->has_suppliers) {
            $showViewButton = true;
            if (!$financeApproved) {
                $waitingReason = 'Waiting for Finance';
            } elseif (!$booking->engineering_approved_by) {
                $showButton = true;
            }
        }

        if ($userRole == 5 && !$booking->finance_approved_by) {
            if (($booking->authorization_file && $booking->admin_approved_by) || !$booking->authorization_file) {
                $showButton = true;
            }
        }

        if ($userRole == 7 && !$booking->manager_approved_by && $financeApproved) {
            if (($booking->has_suppliers && $booking->engineering_approved_by) || !$booking->has_suppliers) {
                $showButton = true;
            } else {
                $waitingReason = 'Waiting for Engineering';
            }
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
            'show_approve_button' => $showButton,
            'show_view_button' => $showViewButton,
            'is_approved' => $isApproved,
            'waiting_reason' => $waitingReason,
            'linked_bookings' => $linkedBookings,
            'rooms_breakdown' => $roomsBreakdown,
        ]);
    }




}
