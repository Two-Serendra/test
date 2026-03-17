@extends('layouts.frontend')

@section('content')
    <div class="container my-4">
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">


            <div class="card-header bg-primary text-white text-center py-4">
                <img src="{{ asset('assets/images/TWO SERENDRA LOGO PNG (White).png') }}"
                    style="height: 60px; width: auto; object-fit: contain;" alt="2serendra" />
                <h5 class="mt-3 mb-0 fw-semibold text-white">Amenity Booking Details</h5>
                <small class="text-white">Transaction Reference & Booking Summary</small>
            </div>

            <div class="card-body p-4">

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                    <div>
                        <div class="text-muted small">Transaction No</div>
                        <div class="fw-bold fs-5">{{ $booking->transaction_no }}</div>
                    </div>

                    <div>
                        @php
                            $statusText = match ((int) $booking->booking_status) {
                                1 => 'Confirmed',
                                2 => 'Cancelled',
                                3 => 'Penalty',
                                default => 'N/A',
                            };

                            $statusClass = match ((int) $booking->booking_status) {
                                1 => 'bg-success',
                                2 => 'bg-danger',
                                3 => 'bg-warning text-dark',
                                default => 'bg-secondary',
                            };
                        @endphp

                        <span class="badge rounded-pill px-3 py-2 {{ $statusClass }}">
                            {{ $statusText }}
                        </span>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-4 h-100">

                            <h6 class="fw-bold mb-3 text-primary">
                                <i class="bi bi-person-circle me-2"></i> Resident Information
                            </h6>

                            <div class="mb-3">
                                <div class="text-muted small">Name</div>
                                <div class="fw-semibold">
                                    {{ $booking->user->name ?? $booking->name ?? 'N/A' }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="text-muted small">Unit</div>
                                <div class="fw-semibold">
                                    {{ $booking->unit ?? 'N/A' }}
                                </div>
                            </div>

                            <div class="mb-0">
                                <div class="text-muted small">Resident Type</div>
                                <div class="fw-semibold">
                                    @if($booking->resident_type === 'TENANT')
                                        <span class="badge badge-forge bg-danger">TENANT</span>
                                    @elseif($booking->resident_type === 'OWNER')
                                        <span class="badge badge-forge bg-primary">OWNER</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $booking->resident_type ?? 'N/A' }}</span>
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-4 h-100">

                            <h6 class="fw-bold mb-3 text-primary">
                                <i class="bi bi-calendar-event me-2"></i> Booking Information
                            </h6>

                            <div class="mb-3">
                                <div class="text-muted small">Amenity</div>
                                <div class="fw-semibold">
                                    {{ $booking->activity->activity_name ?? 'N/A' }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="text-muted small">Booking Date</div>
                                <div class="fw-semibold">
                                    {{ $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('F j, Y') : 'N/A' }}
                                </div>
                            </div>

                            <div class="mb-0">
                                <div class="text-muted small">Scheduled Time</div>
                                <div class="fw-semibold">
                                    {{ $booking->booking_start_time ? \Carbon\Carbon::parse($booking->booking_start_time)->format('g:i A') : 'N/A' }}
                                    -
                                    {{ $booking->booking_end_time ? \Carbon\Carbon::parse($booking->booking_end_time)->format('g:i A') : 'N/A' }}
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                @if((int) $booking->booking_status === 3)
                    <div class="alert alert-warning mt-4 rounded-4 mb-0">
                        <div class="fw-bold mb-1">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> Penalty Notice
                        </div>
                        <div>
                            This booking was cancelled less than <b>12 hours</b> prior to the schedule.
                            A <b>₱1000 penalty</b> will be applied.
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection