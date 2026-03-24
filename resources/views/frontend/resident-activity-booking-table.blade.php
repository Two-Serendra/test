<div class="table-responsive">
    <table class="table table-bordered text-center">
        <thead class="table-light">
            <tr>
                <th>Booking ID</th>
                <th>Activity</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <!-- <th>Penalty</th>
                <th>Waived</th> -->
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @if($bookings->count())
                @foreach ($bookings as $b)
                    <tr>
                        <td>{{ $b->transaction_no }}</td>
                        <td>{{ $b->activity->activity_name ?? 'N/A' }}</td>
                        <td>{{ \Carbon\Carbon::parse($b->booking_date)->format('F d, Y') }}</td>
                        <td>
                            {{ \Carbon\Carbon::parse($b->booking_start_time)->format('g:i A') }}
                            {{ \Carbon\Carbon::parse($b->booking_end_time)->format('g:i A') }}
                        </td>
                        <td>
                            @php
                                $bookingDate = \Carbon\Carbon::parse($b->booking_date);
                                $today = \Carbon\Carbon::today();

                                switch ($b->booking_status) {
                                    case 0:
                                        $statusText = 'Waiting';
                                        $statusClass = 'badge bg-warning text-white badge-forge';
                                        break;

                                    case 1:
                                        if ($bookingDate->lt($today)) {
                                            $statusText = 'Completed';
                                            $statusClass = 'badge bg-success badge-forge';
                                        } else {
                                            $statusText = 'Confirmed';
                                            $statusClass = 'badge bg-primary badge-forge';
                                        }
                                        break;

                                    case 2:
                                        $statusText = 'Cancelled';
                                        $statusClass = 'badge bg-danger badge-forge';
                                        break;

                                    case 3:
                                        $statusText = 'Late Cancel';
                                        $statusClass = 'badge bg-warning badge-forge';
                                        break;

                                    case 4:
                                        $statusText = 'No Show';
                                        $statusClass = 'badge bg-dark badge-forge';
                                        break;

                                    default:
                                        if ($bookingDate->lt($today)) {
                                            $statusText = 'Completed';
                                            $statusClass = 'badge bg-success badge-forge';
                                        } else {
                                            $statusText = 'N/A';
                                            $statusClass = 'badge bg-secondary badge-forge';
                                        }
                                }
                            @endphp

                            <span class="{{ $statusClass }}">{{ $statusText }}</span>
                        </td>
                        <!-- <td class="{{ ($b->has_penalty && ($b->penalty_amount ?? 0) > 0) ? 'text-danger fw-semibold' : '' }}">
                            @if(!$b->has_penalty)
                                -
                            @else
                                ₱{{ number_format($b->penalty_amount ?? 0, 2) }}
                            @endif
                        </td>
                        <td>
                            @if($b->has_penalty == 0 && $b->penalty_amount == 0)
                                <span>-</span>
                            @elseif($b->has_penalty == 1 && $b->penalty_waived)
                                <span class="text-primary">Yes</span>
                            @elseif($b->has_penalty == 1 && !$b->penalty_waived)
                                <span class="text-danger">No</span>
                            @endif
                        </td> -->


                        <td>
                            <button class="btn btn-sm btn-info activity-booking-details badge-forge text-white"
                                data-id="{{ $b->id }}">
                                View
                            </button>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="6" class="text-center text-muted">No bookings found for this unit.</td>
                </tr>
            @endif
        </tbody>

    </table>
</div>
<div class="pagination-container">
    {{ $bookings->appends(['unit_no' => $selectedUnit])->links('vendor.pagination.bootstrap-4') }}
</div>
@include('frontend.modal.resident-view-activity-booking-details-modal')