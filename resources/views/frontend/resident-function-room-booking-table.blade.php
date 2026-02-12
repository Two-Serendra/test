<div class="table-responsive">
    <table class="table table-bordered text-center">
        <thead class="table-light">
            <tr>
                <th>Booking ID</th>
                <th>Function Room</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @if($bookings->count())
                @foreach ($bookings as $b)
                    @php
                        $booking = \App\Models\FunctionRoomBooking::with('functionRoom')->find($b->id);
                    @endphp
                    <tr>
                        <td>{{ $booking->transaction_no }}</td>
                        <td>{{ $booking->functionRoom->function_room_name ?? 'N/A' }}</td>
                        <td>{{ \Carbon\Carbon::parse($booking->function_room_booking_date)->format('F d, Y') }}</td>
                        <td>
                            {{ \Carbon\Carbon::parse($booking->event_start_time)->format('g:i A') }} -
                            {{ \Carbon\Carbon::parse($booking->event_end_time)->format('g:i A') }}
                        </td>
                        <td>
                            @if ($booking->booking_status == 0)
                                <span class="badge bg-warning text-white badge-forge">Waiting</span>
                            @elseif ($booking->booking_status == 1)
                                <span class="badge bg-success badge-forge">Confirmed</span>
                            @else
                                <span class="badge bg-danger badge-forge">Cancelled</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-sm btn-info function-room-booking-details badge-forge text-white"
                                data-id="{{ $booking->id }}">
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