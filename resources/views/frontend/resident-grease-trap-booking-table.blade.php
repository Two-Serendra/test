<div class="table-responsive">
    <table class="table table-bordered text-center">
        <thead class="table-light">
            <tr>
                <th>Booking ID</th>
                <!-- <th>SRF No</th> -->
                <th>Date</th>
                <th>Time</th>
                <th>Charge Type</th>
                <th>Emergency</th>
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
                                    <span class="badge bg-primary text-white badge-forge ">Free</span>
                                @else
                                    <span class="badge bg-danger badge-forge ">Billable</span>
                                @endif
                            </td>

                            <td>
                                @if ($b->emergency == 0)
                                    <span class="badge bg-secondary badge-forge ">No</span>
                                @else
                                    <span class="badge bg-danger badge-forge ">Yes</span>
                                @endif
                            </td>

                            <td>
                                @php
                                    $startTime = $b->booking_time_slot ? trim(explode('-', $b->booking_time_slot)[0]) : null;
                                    $bookingDateTime = ($b->booking_date && $startTime)
                                        ? \Carbon\Carbon::parse($b->booking_date . ' ' . $startTime)
                                        : null;
                                    $isPast = $bookingDateTime ? $bookingDateTime->lt(now()) : false;
                                @endphp

                                @if ($b->booking_status == 2)
                                    <span class="badge bg-danger badge-forge">Cancelled</span>
                                @elseif ($b->booking_status == 1 && $isPast)
                                    <span class="badge bg-success badge-forge">Completed</span>
                                @elseif ($b->booking_status == 1)
                                    <span class="badge badge-forge bg-primary">Confirmed</span>
                                @else
                                    <span class="badge bg-warning badge-forge">Pending</span>
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
                                @if ($b->booking_status == 0)
                                    <span class="badge bg-secondary badge-forge activity-booking-cancel" data-id="{{ $b->id }}">
                                        Cancel
                                    </span>

                                @elseif ($b->booking_status == 1)
                                    <button class="btn btn-sm btn-danger grease-trap-booking-cancel text-white badge-forge"
                                        data-id="{{ $b->id }}" @if(\Carbon\Carbon::parse($b->booking_date . ' ' . explode('-', $b->booking_time_slot)[0])->lt(now())) disabled @endif>
                                        Cancel
                                    </button>

                                @else
                                    <span class="badge bg-warning badge-forge">Pending</span>
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
    {{ $bookings->appends(['unit_no' => $selectedUnit])->links('vendor.pagination.bootstrap-4') }}
</div>
@include('frontend.modal.resident-view-activity-booking-details-modal')