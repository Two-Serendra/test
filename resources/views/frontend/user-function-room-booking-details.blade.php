@extends('layouts.frontend')
@section('content')
    <div class="container my-3">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white text-center">
                <img src="{{ asset('assets/images/TWO SERENDRA LOGO PNG (White).png') }}"
                    style="height: 60px; width: auto; object-fit: contain;" alt="2serendra" />
            </div>

            <div class="card-body">
                @php $mainBooking = $bookings->first(); @endphp

                <div class="border-bottom pb-2 mb-3">
                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Transaction No:</div>
                        <div class="col-8">{{ $mainBooking->transaction_no }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Name:</div>
                        <div class="col-8">{{ $mainBooking->user->name ?? 'N/A' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Unit:</div>
                        <div class="col-8">{{ $mainBooking->unit_no ?? 'N/A' }}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Resident Type:</div>
                        <div class="col-8">
                            @if($mainBooking->resident_type === 'TENANT')
                                <span class="badge badge-forge bg-danger">TENANT</span>
                            @elseif($mainBooking->resident_type === 'OWNER')
                                <span class="badge badge-forge bg-primary">OWNER</span>
                            @else
                                <span class="badge bg-secondary">{{ $mainBooking->resident_type }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Function Room(s):</div>
                        <div class="col-8">
                            @foreach($bookings as $b)
                                <span class="badge bg-primary badge-forge">{{ $b->functionRoom->function_room_name }}</span>
                            @endforeach
                        </div>
                    </div>


                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Status:</div>
                        <div class="col-8">
                            @php
                                $status = $bookings->every(fn($b) => $b->booking_status == 1) ? 1 :
                                    ($bookings->every(fn($b) => $b->booking_status == 2) ? 2 : 0);
                            @endphp
                            @if($status == 0)
                                <span class="badge badge-forge bg-warning text-light">Waiting</span>
                            @elseif($status == 1)
                                <span class="badge badge-forge bg-success">Confirmed</span>
                            @else
                                <span class="badge badge-forge bg-danger">Cancelled</span>
                            @endif
                        </div>
                    </div>

                    <!-- Function Room Names -->

                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Purpose:</div>
                        <div class="col-8">{{ $mainBooking->purpose_of_event }}</div>
                    </div>



                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Payment Mode:</div>
                        <div class="col-8">{{ $mainBooking->payment_mode ?? 'N/A' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Contact:</div>
                        <div class="col-8">{{ $mainBooking->contact_number ?? 'N/A' }}</div>
                    </div>


                    <!-- Authorization File -->
                    @if(!empty($mainBooking->authorization_file))
                        <div class="row mb-2">
                            <div class="col-4 fw-bold">Authorization File:</div>
                            <div class="col-8">
                                <a href="{{ asset($mainBooking->authorization_file) }}" target="_blank"
                                    class="text-decoration-none">
                                    View File
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- Suppliers -->
                    @if($mainBooking->suppliers->isNotEmpty())
                        <div class="row mb-2">
                            <div class="col-4 fw-bold">Suppliers:</div>
                            <div class="col-8">
                                @foreach($mainBooking->suppliers as $supplier)
                                    <div class="mb-1">
                                        {{ $supplier->name }}
                                        @if(!empty($supplier->attachment))
                                            - <a href="{{ asset($supplier->attachment) }}" target="_blank"
                                                class="text-decoration-none">View</a>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif


                </div>

                <!-- Per-room Details (keep visible) -->
                @foreach($bookings as $booking)
                    @php
                        $start = \Carbon\Carbon::parse($booking->event_start_time);
                        $end = \Carbon\Carbon::parse($booking->event_end_time);
                        if ($end <= $start)
                            $end->addDay();
                        $hours = round($end->diffInMinutes($start) / 60, 2);
                        if ($hours <= 0)
                            $hours = 1;

                        $ratePerHour = $booking->final_rate ?? $booking->functionRoom->function_room_rate ?? 0;
                        $baseRate = $booking->functionRoom->function_room_rate ?? $ratePerHour;
                        $roomTotal = round($hours * $ratePerHour, 2);

                        $discountValue = floatval($booking->discount ?? 0);
                        $discountRemarks = $booking->discount_remarks ?? '';
                    @endphp

                    <div class="border-bottom pb-2 mb-3">
                        <h6 class="fw-bold">{{ $booking->functionRoom->function_room_name }}</h6>

                        <div class="row mb-2">
                            <div class="col-4 fw-bold">Booking Date:</div>
                            <div class="col-8">
                                {{ \Carbon\Carbon::parse($booking->function_room_booking_date)->format('F d, Y') }}
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4 fw-bold">Time:</div>
                            <div class="col-8">{{ $start->format('g:i A') }} - {{ $end->format('g:i A') }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4 fw-bold">Pax:</div>
                            <div class="col-8">{{ $booking->pax }}</div>
                        </div>

                        <!-- Rate & Discount -->
                        <div class="row mb-2">
                            <div class="col-4 fw-bold">Rate:</div>
                            <div class="col-8">
                                @if($baseRate > $ratePerHour)
                                    <small class="text-muted"><s>₱{{ number_format($baseRate, 2) }}/hr</s></small>
                                    &nbsp; → &nbsp;
                                    <small class="fw-bold">₱{{ number_format($ratePerHour, 2) }}/hr</small>
                                    &nbsp; × &nbsp;
                                    <small>{{ $hours }} hr{{ $hours > 1 ? 's' : '' }}</small>
                                    &nbsp; = &nbsp;
                                    <strong>₱{{ number_format($roomTotal, 2) }}</strong>
                                @else
                                    <small>₱{{ number_format($ratePerHour, 2) }}/hr</small>
                                    &nbsp; × &nbsp;
                                    <small>{{ $hours }} hr{{ $hours > 1 ? 's' : '' }}</small>
                                    &nbsp; = &nbsp;
                                    <strong>₱{{ number_format($roomTotal, 2) }}</strong>
                                @endif
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-4 fw-bold">Discount:</div>
                            <div class="col-8">
                                @if($discountValue > 0)
                                    <strong
                                        class="text-danger">{{ rtrim(rtrim(number_format($discountValue, 2), '0'), '.') }}%</strong>
                                    @if(!empty($discountRemarks))
                                        <span style="margin-left: 6px; color: #555;">{{ $discountRemarks }}</span>
                                    @endif
                                @else
                                    <span class="text-muted">No discount</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- Unified Function Rooms Breakdown Table (with discount display) -->
                @php $functionRoomsTotal = 0; @endphp
                <h6 class="fw-bold">Breakdown</h6>
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th class="text-center">Qty/Hrs</th>
                                <th class="text-end">Rate</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>

                        <tbody>
                            @php
                                $functionRoomsTotal = 0;
                                $addonsTotal = 0;
                            @endphp

                            {{-- FUNCTION ROOM ENTRIES --}}
                            @foreach($bookings as $booking)
                                @php
                                    $start = \Carbon\Carbon::parse($booking->event_start_time);
                                    $end = \Carbon\Carbon::parse($booking->event_end_time);
                                    if ($end <= $start)
                                        $end->addDay();

                                    $hours = round($end->diffInMinutes($start) / 60, 2);
                                    if ($hours <= 0)
                                        $hours = 1;

                                    $ratePerHour = $booking->final_rate ?? $booking->functionRoom->function_room_rate ?? 0;
                                    $baseRate = $booking->functionRoom->function_room_rate ?? $ratePerHour;

                                    $roomTotal = round($hours * $ratePerHour, 2);
                                    $functionRoomsTotal += $roomTotal;
                                @endphp

                                <tr>
                                    <td>{{ $booking->functionRoom->function_room_name }}</td>

                                    <td class="text-center">{{ $hours }} hr{{ $hours > 1 ? 's' : '' }}</td>

                                    <td class="text-end">
                                        @if($baseRate > $ratePerHour)
                                            <small class="text-muted"><s>₱{{ number_format($baseRate, 2) }}</s></small>
                                            &nbsp;→&nbsp;
                                            <small class="fw-bold">₱{{ number_format($ratePerHour, 2) }}</small>
                                        @else
                                            ₱{{ number_format($ratePerHour, 2) }}
                                        @endif
                                    </td>

                                    <td class="text-end">₱{{ number_format($roomTotal, 2) }}</td>
                                </tr>
                            @endforeach

                            {{-- ADD-ONS ENTRIES (same table) --}}
                            @foreach($bookings as $booking)
                                @foreach($booking->addOns as $addon)
                                    @php
                                        $pivot = $addon->pivot ?? [];
                                        $qty = $pivot->quantity ?? $pivot->qty ?? $addon->qty ?? 0;
                                        $price = $pivot->price ?? $addon->price ?? 0;
                                        $lineTotal = round($qty * $price, 2);
                                        $addonsTotal += $lineTotal;
                                    @endphp

                                    <tr>
                                        <td>{{ $addon->item ?? $addon->name }}</td>
                                        <td class="text-center">{{ $qty }}</td>
                                        <td class="text-end">₱{{ number_format($price, 2) }}</td>
                                        <td class="text-end">₱{{ number_format($lineTotal, 2) }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>

                        {{-- SUBTOTALS + GRAND TOTAL --}}
                        <tfoot>
                            <tr class="table-light fw-bold">
                                <td colspan="3" class="text-end">Function Rooms Subtotal</td>
                                <td class="text-end">₱{{ number_format($functionRoomsTotal, 2) }}</td>
                            </tr>

                            @if($addonsTotal > 0)
                                <tr class="table-light fw-bold">
                                    <td colspan="3" class="text-end">Add-Ons Subtotal</td>
                                    <td class="text-end">₱{{ number_format($addonsTotal, 2) }}</td>
                                </tr>
                            @endif

                            <tr class="table-dark fw-bold">
                                <td colspan="3" class="text-end">Grand Total</td>
                                <td class="text-end">₱{{ number_format($functionRoomsTotal + $addonsTotal, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>


@endsection