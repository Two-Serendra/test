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
                <th>Penalty</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
            @if($bookings->count())
                @foreach ($bookings as $b)
                    <tr>
                        <td>{{ $b->transaction_no }}</td>
                        <td>{{ \Carbon\Carbon::parse($b->booking_date)->format('F d, Y') }}</td>
                        <td>{{ $b->booking_time_slot ?? '-' }}</td>

                        <td>
                            @if ($b->charged_type === 1)
                                <span class="text-primary">Free</span>
                            @else
                                <span class="text-danger">₱{{ number_format(448, 2) }}</span>
                            @endif
                        </td>

                        <td>
                            @php
                                // Determine booking datetime
                                if ($b->booking_date) {
                                    if ($b->booking_time_slot) {
                                        // Use start time if time slot exists
                                        $startTime = trim(explode('-', $b->booking_time_slot)[0]);
                                        $bookingDateTime = \Carbon\Carbon::parse($b->booking_date . ' ' . $startTime);
                                    } else {
                                        // If no time slot, check just the date (end of day)
                                        $bookingDateTime = \Carbon\Carbon::parse($b->booking_date)->endOfDay();
                                    }
                                } else {
                                    $bookingDateTime = null;
                                }

                                $isPast = $bookingDateTime ? $bookingDateTime->lt(now()) : false;

                                // Determine status text
                                if ($b->booking_status == 2) {
                                    $statusText = '<span class="text-danger">Cancelled</span>';
                                } elseif ($b->booking_status == 1 && $isPast) {
                                    $statusText = '<span class="text-success">Completed</span>';
                                } elseif ($b->booking_status == 1) {
                                    $statusText = '<span class="text-primary">Confirmed</span>';
                                } else {
                                    $statusText = '<span class="text-warning">Pending</span>';
                                }

                                // Disable cancel button if past or cancelled
                                $disabled = ($b->booking_status == 2 || $isPast) ? 'disabled' : '';
                            @endphp

                            {!! $statusText !!}
                        </td>

                        <td>
                            @if ($b->has_penalty)
                                <span class="text-warning fw-bold">₱{{ number_format($b->penalty_amount, 2) }}</span>
                            @else
                                -
                            @endif
                        </td>

                        <td>
                            @if ($b->booking_status == 2)
                                <div data-bs-toggle="tooltip" title="Cancelled">
                                    <button class="btn btn-secondary text-white badge-forge grease-trap-booking-cancelled" disabled>
                                        <i class="fa-solid fa-ban"></i>
                                    </button>
                                </div>
                            @elseif ($b->booking_status == 1)
                                <div data-bs-toggle="tooltip" title="Cancel">
                                    <button class="btn btn-danger grease-trap-booking-cancel text-white badge-forge"
                                        data-id="{{ $b->id }}" {{ $disabled }}>
                                        <i class="fa-solid fa-ban"></i>
                                    </button>
                                </div>
                            @else
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