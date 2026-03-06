<div class="table-responsive">
    <table class="table table-bordered text-center">
        <thead class="table-light">
            <tr>
                <th>Booking ID</th>
                <!-- <th>SRF No</th> -->
                <th>Date</th>
                <th>Time</th>
                <th>Charge Type</th>
                <!-- <th>Emergency</th> -->
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
            @if($bookings->count())
                @foreach ($bookings as $b)
                    <tr>
                        <td>{{ $b->transaction_no }}</td>
                        <!-- <td>{{ $b->srf_no ?? 'N/A' }}</td> -->
                        <td>{{ \Carbon\Carbon::parse($b->booking_date)->format('F d, Y') }}</td>
                        <td>{{ $b->booking_time_slot }}</td>

                        <td>
                            @if ($b->charged_type === 1)
                                <span class="text-primary">
                                    Free
                                </span>
                            @else
                                <span class="text-danger">
                                    ₱{{ number_format(350, 2) }}
                                </span>
                            @endif

                        <td>
                            @php
                                $startTime = $b->booking_time_slot ? trim(explode('-', $b->booking_time_slot)[0]) : null;
                                $bookingDateTime = ($b->booking_date && $startTime)
                                    ? \Carbon\Carbon::parse($b->booking_date . ' ' . $startTime)
                                    : null;
                                $isPast = $bookingDateTime ? $bookingDateTime->lt(now()) : false;
                            @endphp


                            @if ($b->booking_status == 2)
                                <span class="text-danger">Cancelled</span>
                            @elseif ($b->booking_status == 1 && $isPast)
                                <span class="text-success">Completed</span>
                            @elseif ($b->booking_status == 1)
                                <span class="text-primary">Confirmed</span>
                            @else
                                <span class="text-warning">Pending</span>
                            @endif
                        </td>

                        @php
                            $timeRange = explode(' - ', $b->booking_time_slot);
                            $startTime = $timeRange[0] ?? null;

                            $bookingDateTime = $startTime
                                ? \Carbon\Carbon::parse($b->booking_date . ' ' . $startTime)
                                : \Carbon\Carbon::parse($b->booking_date);

                            $now = \Carbon\Carbon::now();
                        @endphp

                        <td>
                            @php
                                $disabled = $isPast ? 'disabled' : '';
                            @endphp

                            @if ($b->booking_status == 2)
                                {{-- Cancelled: disabled button --}}
                                <div data-bs-toggle="tooltip" title="Cancelled">
                                    <button class="btn btn-secondary text-white badge-forge pest-control-booking-cancelled" disabled>
                                        <i class="fa-solid fa-ban"></i>
                                    </button>
                                </div>

                            @elseif ($b->booking_status == 1)
                                {{-- Active: wrap button in div so tooltip works even if disabled --}}
                                <div data-bs-toggle="tooltip" title="Cancel">
                                    <button class="btn btn-danger pest-control-booking-cancel text-white badge-forge"
                                        data-id="{{ $b->id }}" {{ $disabled }}>
                                        <i class="fa-solid fa-ban"></i>
                                    </button>
                                </div>

                            @else
                                {{-- Pending --}}
                                <div data-bs-toggle="tooltip" title="Pending">
                                    <button class="btn btn-sm btn-warning badge-forge" data-id="{{ $b->id }}" {{ $disabled }}>
                                        <i class="bi bi-clock"></i>
                                    </button>
                                </div>
                            @endif
                        </td>


                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="8" class="text-center text-muted">No bookings found for this unit.</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
<div class="pagination-container">
    {{ $bookings->links('vendor.pagination.bootstrap-4') }}
</div>
@include('frontend.modal.resident-view-activity-booking-details-modal')