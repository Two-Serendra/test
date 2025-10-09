@extends('layouts.frontend')
@section('content')
    <div class="container my-3">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white text-center">
                <img src="{{ asset('assets/images/TWO SERENDRA LOGO PNG (White).png') }}"
                    style="height: 60px; width: auto; object-fit: contain;" alt="2serendra" />
            </div>

            <div class="card-body">

                <!-- Booking Info -->
                <div class="border-bottom pb-2 mb-3">
                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Transaction No:</div>
                        <div class="col-8">{{ $booking->transaction_no }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Name:</div>
                        <div class="col-8">{{ $booking->user->name ?? 'N/A' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Unit:</div>
                        <div class="col-8">{{ $booking->unit_no ?? 'N/A' }}</div>
                    </div>
                </div>

                <!-- Event Details -->
                <div class="border-bottom pb-2 mb-3">
                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Status:</div>
                        <div class="col-8">
                            @if($booking->booking_status == 0)
                                <span class="badge badge-forge bg-warning text-light">Waiting</span>
                            @elseif($booking->booking_status == 1)
                                <span class="badge badge-forge bg-success">Confirmed</span>
                            @else
                                <span class="badge badge-forge bg-danger">Cancelled</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Function Room:</div>
                        <div class="col-8">{{ $booking->functionRoom->function_room_name ?? 'N/A' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Purpose:</div>
                        <div class="col-8">{{ $booking->purpose_of_event }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Resident Type:</div>
                        <div class="col-8">
                            @if($booking->resident_type === 'TENANT')
                                <span class="badge badge-forge bg-danger">TENANT</span>
                            @elseif($booking->resident_type === 'OWNER')
                                <span class="badge badge-forge bg-primary">OWNER</span>
                            @else
                                <span class="badge bg-secondary">{{ $booking->resident_type }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Schedule -->
                <div class="border-bottom pb-2 mb-3">
                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Booking Date:</div>
                        <div class="col-8">
                            {{ \Carbon\Carbon::parse($booking->function_room_booking_date)->format('F d, Y') }}
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Time:</div>
                        <div class="col-8">
                            {{ \Carbon\Carbon::parse($booking->event_start_time)->format('g:i A') }} -
                            {{ \Carbon\Carbon::parse($booking->event_end_time)->format('g:i A') }}
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Pax:</div>
                        <div class="col-8">{{ $booking->pax }}</div>
                    </div>
                </div>

                @php

                    use Carbon\Carbon;

                    $start = Carbon::parse($booking->event_start_time);
                    $end = Carbon::parse($booking->event_end_time);

                    if ($end <= $start) {
                        $end->addDay();
                    }

                    $hours = round($end->diffInMinutes($start) / 60, 2);
                    if ($hours <= 0)
                        $hours = 1;

                    $ratePerHour = $booking->final_rate ?? $booking->functionRoom->function_room_rate ?? 0;
                    $baseRate = $booking->functionRoom->function_room_rate ?? $ratePerHour;
                    $roomLineTotal = round($hours * $ratePerHour, 2);
                @endphp

                <div class="border-bottom pb-2 mb-3">

                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Rate:</div>
                        <div class="col-8">
                            @if($baseRate > $ratePerHour)
                                <div>
                                    <small class="text-muted"><s>₱{{ number_format($baseRate, 2) }}/hr</s></small>
                                    &nbsp; → &nbsp;
                                    <small class="fw-bold">₱{{ number_format($ratePerHour, 2) }}/hr</small>
                                    &nbsp; × &nbsp;
                                    <small>{{ $hours }} hr{{ $hours > 1 ? 's' : '' }}</small>
                                    &nbsp; = &nbsp;
                                    <strong class="fw-bold">₱{{ number_format($roomLineTotal, 2) }}</strong>
                                </div>
                            @else
                                <div>
                                    <small class="text-muted">₱{{ number_format($ratePerHour, 2) }}/hr</small>
                                    &nbsp; × &nbsp;
                                    <small>{{ $hours }} hr{{ $hours > 1 ? 's' : '' }}</small>
                                    &nbsp; = &nbsp;
                                    <strong>₱{{ number_format($roomLineTotal, 2) }}</strong>
                                </div>
                            @endif
                        </div>
                    </div>

                      @php
        $discountValue = floatval($booking->discount ?? 0);
        $discountRemarks = $booking->discount_remarks ?? '';
    @endphp

    <div class="row mb-2">
        <div class="col-4 fw-bold">Discount:</div>
        <div class="col-8">
            @if($discountValue > 0)
                <div style="margin-top: 6px;">
                    <strong class="text-danger">
                        {{ rtrim(rtrim(number_format($discountValue, 2), '0'), '.') }}%
                    </strong>
                    @if(!empty($discountRemarks))
                        <span style="margin-left: 6px; color: #555;">{{ $discountRemarks }}</span>
                    @endif
                </div>
            @else
                <span class="text-muted" style="margin-top: 6px;">No discount</span>
            @endif
        </div>
    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Payment Mode:</div>
                        <div class="col-8">{{ ucfirst($booking->payment_mode) }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Contact:</div>
                        <div class="col-8">{{ $booking->contact_number ?? 'N/A' }}</div>
                    </div>
                </div>

                <!-- Suppliers & Authorization -->
                <div class="border-bottom pb-2 mb-3">
                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Suppliers:</div>
                        <div class="col-8">
                            @if($booking->suppliers->count() > 0)
                                <ul class="mb-0">
                                    @foreach($booking->suppliers as $supplier)
                                        <li>
                                            <strong>{{ $supplier->name ?? 'N/A' }}</strong> —
                                            @if($supplier->attachment)
                                                <a href="{{ asset($supplier->attachment) }}" target="_blank"
                                                    class="custom-link">View</a>
                                                <!-- <small class="text-muted">{{ basename($supplier->attachment) }}</small> -->
                                            @else
                                                N/A
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                N/A
                            @endif
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Authorization File:</div>
                        <div class="col-8">
                            @if($booking->authorization_file)
                                <a href="{{ asset($booking->authorization_file) }}" target="_blank" class="custom-link">View</a>
                                <!-- <small class="text-muted">{{ basename($booking->authorization_file) }}</small> -->
                            @else
                                N/A
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Breakdown / Add-Ons -->

                @php
                    // Calculate duration in hours
                    $start = Carbon::parse($booking->event_start_time);
                    $end = Carbon::parse($booking->event_end_time);
                    if ($end <= $start)
                        $end->addDay();
                    $hours = round($end->diffInMinutes($start) / 60, 2);
                    if ($hours <= 0)
                        $hours = 1;

                    // Rates
                    $ratePerHour = $booking->final_rate ?? $booking->functionRoom->function_room_rate ?? 0;
                    $baseRate = $booking->functionRoom->function_room_rate ?? $ratePerHour;
                    $roomTotal = round($hours * $ratePerHour, 2);

                    // Add-ons
                    $addOns = $booking->add_ons ?? [];
                    $addonsTotal = 0;

                    $breakdownTotal = $roomTotal;
                @endphp

                <h6 class="fw-bold">Breakdown</h6>
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Function Room Rate --}}
                            @if($ratePerHour > 0)
                                <tr>
                                    <td>
                                        {{ $booking->functionRoom->function_room_name ?? 'N/A' }} ({{ $hours }} hr{{ $hours > 1 ? 's' : '' }})
                                        <!-- @if($baseRate > $ratePerHour)
                                                                                    <br>
                                                                                    <small class="text-muted">
                                                                                        <s>₱{{ number_format($baseRate, decimals: 2) }}/hr</s>
                                                                                    </small>
                                                                                    &nbsp;→&nbsp;
                                                                                    <small class="text-success">₱{{ number_format($ratePerHour, 2) }}/hr</small>
                                                                                @endif -->
                                    </td>
                                    <td class="text-center">{{ $hours }}</td>
                                    <td class="text-end">₱{{ number_format($ratePerHour, 2) }}/hr</td>
                                    <td class="text-end">₱{{ number_format($roomTotal, 2) }}</td>
                                </tr>
                            @endif

                            {{-- Add-ons --}}{{ $booking->functionRoom->function_room_name ?? 'N/A' }}
                            @if($booking->addOns?->count() > 0)
                                @foreach($booking->addOns as $addon)
                                    @php
                                        $pivot = $addon->pivot ?? [];
                                        $qty = $pivot->quantity ?? $pivot->qty ?? $addon->qty ?? 0;
                                        $price = $pivot->price ?? $addon->price ?? 0;
                                        $lineTotal = round($qty * $price, 2);
                                        $addonsTotal += $lineTotal;
                                        $breakdownTotal += $lineTotal;
                                    @endphp
                                    <tr>
                                        <td>{{ $addon->item ?? $addon->name ?? 'Add-on' }}</td>
                                        <td class="text-center">{{ $qty }}</td>
                                        <td class="text-end">₱{{ number_format($price, 2) }}</td>
                                        <td class="text-end">₱{{ number_format($lineTotal, 2) }}</td>
                                    </tr>
                                @endforeach

                                <tr class="table-light fw-bold">
                                    <td colspan="3" class="text-end">Add-ons Subtotal</td>
                                    <td class="text-end">₱{{ number_format($addonsTotal, 2) }}</td>
                                </tr>
                            @endif

                            @if($breakdownTotal == 0)
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No charges</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- Grand Total --}}
                <div class="d-flex justify-content-end border-top pt-2">
                    <div class="fw-bold me-2">Grand Total:</div>
                    <div>₱{{ number_format($breakdownTotal, 2) }}</div>
                </div>
            </div>
        </div>
@endsection