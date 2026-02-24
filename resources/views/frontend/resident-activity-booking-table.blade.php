<div class="table-responsive">
    <table class="table table-bordered text-center">
        <thead class="table-light">
            <tr>
                <th>Booking ID</th>
                <th>Activity</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
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

        if ($bookingDate->lt($today)) {
            $statusText = 'Completed';
            $statusClass = 'badge bg-success badge-forge';
        } else {
            switch ($b->booking_status) {
                case 0:
                    $statusText = 'Waiting';
                    $statusClass = 'badge bg-warning text-white badge-forge';
                    break;
                case 1:
                    $statusText = 'Confirmed';
                    $statusClass = 'badge bg-success badge-forge';
                    break;
                default:
                    $statusText = 'Cancelled';
                    $statusClass = 'badge bg-danger badge-forge';
            }
        }
    @endphp
    <span class="{{ $statusClass }}">{{ $statusText }}</span>
</td>
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