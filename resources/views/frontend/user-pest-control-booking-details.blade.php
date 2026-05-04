@extends('layouts.frontend')

@section('content')
    <div class="container my-4">
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">

            <!-- Plain Header -->
            <div class="card-header text-center py-3 border-bottom">
                <h5 class="mb-1 fw-bold text-dark">Pest Control Booking Details</h5>
                <small class="text-muted">Transaction Reference & Booking Summary</small>
            </div>

            <div class="card-body p-4">

                <!-- Transaction & Status -->
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                    <div>
                        <div class="text-muted small">Transaction No</div>
                        <div class="fw-bold fs-5">{{ $booking->transaction_no }}</div>
                    </div>

                    @php
                        use Carbon\Carbon;

                        $currentDateTime = Carbon::now();

                        // Extract only the start time from the range
                        $startTime = $booking->booking_time_slot
                            ? trim(explode('-', $booking->booking_time_slot)[0])
                            : null;

                        $bookingDateTime = ($booking->booking_date && $startTime)
                            ? Carbon::parse($booking->booking_date . ' ' . $startTime)
                            : null;

                        // Determine status dynamically
                        if ($booking->booking_status == 2) {
                            $statusText = 'Cancelled';
                            $statusClass = 'bg-danger';
                        } elseif ($booking->booking_status == 1 && $bookingDateTime && $bookingDateTime->isPast()) {
                            $statusText = 'Completed';
                            $statusClass = 'bg-secondary';
                        } elseif ($booking->booking_status == 1) {
                            $statusText = 'Confirmed';
                            $statusClass = 'bg-primary';
                        } else {
                            $statusText = 'N/A';
                            $statusClass = 'bg-secondary';
                        }
                    @endphp

                    <span class="badge rounded-pill px-3 py-2 {{ $statusClass }}">
                        {{ $statusText }}
                    </span>


                </div>

                <!-- Info Sections -->
                <div class="row g-4">
                    <!-- Resident Info -->
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-4 h-100">
                            <h6 class="fw-bold mb-3 text-primary">
                                <i class="bi bi-person-circle me-2"></i> Resident Information
                            </h6>

                            <div class="mb-3">
                                <div class="text-muted small">Name</div>
                                <div class="fw-semibold">{{ $booking->name ?? 'N/A' }}</div>
                            </div>

                            <div class="mb-3">
                                <div class="text-muted small">Unit</div>
                                <div class="fw-semibold">{{ $booking->unit_no ?? 'N/A' }}</div>
                            </div>

                            <div class="mb-0">
                                <div class="text-muted small">Resident Type</div>
                                <div class="fw-semibold">
                                    @if($booking->resident_type === 'TENANT')
                                        <span class="badge bg-danger">TENANT</span>
                                    @elseif($booking->resident_type === 'OWNER')
                                        <span class="badge bg-primary">OWNER</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $booking->resident_type ?? 'N/A' }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Booking Info -->
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-4 h-100">
                            <h6 class="fw-bold mb-3 text-primary">
                                <i class="bi bi-calendar-event me-2"></i> Booking Information
                            </h6>

                            <div class="mb-3">
                                <div class="text-muted small">Charge Type</div>
                                @php
                                    $chargeText = match ($booking->charged_type) {
                                        1 => 'Free',
                                        2 => 'Billable',
                                        default => 'N/A',
                                    };
                                    $chargeClass = match ($booking->charged_type) {
                                        1 => 'bg-primary',
                                        2 => 'bg-danger',
                                        default => 'bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $chargeClass }}">{{ $chargeText }}</span>
                            </div>

                            <div class="mb-3">
                                <div class="text-muted small">Booking Date</div>
                                <div class="fw-semibold">
                                    {{ $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('F j, Y') : 'N/A' }}
                                </div>
                            </div>



                            <div class="mb-0">
                                <div class="text-muted small">Scheduled Time</div>
                                <div class="fw-semibold">{{ $booking->booking_time_slot ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>



@endsection