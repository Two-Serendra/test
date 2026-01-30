@extends('layouts.backend')
@section('content')
    <style>
        #resident-type-dropdown {
            inset: 36px auto auto 0px !important;
        }

        /* Additional styles for hiding the column */
        .hide-column {
            display: none;
        }
    </style>

    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-6 d-flex justify-content-start align-items-center">
                    <!-- Search Form -->
                    <form action="{{ route('admin.search.booking') }}" method="GET" id="searchFormBooking"
                        class="d-flex align-items-center">
                        <div class="input-group" style="width: 200px;">
                            <span class="input-group-text">
                                <i class="fa-solid fa-magnifying-glass fa-sm"></i>
                            </span>
                            <input type="text" name="searchBooking" value="{{ $searchBooking ?? '' }}"
                                id="searchInputBooking" class="form-control" placeholder="Name/Unit" autocomplete="off">
                        </div>
                    </form>
                </div>
                <div class="col-6 d-flex justify-content-end align-items-center">
                    <button type="button" class="btn btn-primary badge AddBookingAdmin me-2">
                        <i class='bx bx-plus'></i> New Booking
                    </button>

                    <button type="button" class="btn badge SlotChecking" style="color: #fff;
                                background-color: #6c757d;
                                border-color: #6c757d;">
                        <i class="bx bxs-check-square"></i> Slot Checking
                    </button>
                </div>
            </div>

            <div class="table-responsive" style="overflow-x: auto;">
                <table id="bookingTable" class="display table">
                    <thead>
                        <tr>
                            <th class="table-custom sticky-th">LOBBY</th>
                            <th class="table-custom sticky-th">TRANS NO.</th>
                            <th class="table-custom sticky-th">ACTIVITY</th>
                            <th class="table-custom sticky-th">UNIT</th>
                            <th class="table-custom sticky-th">RESIDENT TYPE</th>
                            <th class="table-custom sticky-th">NAME</th>
                            <th class="table-custom sticky-th">CONTACT</th>
                            <th class="table-custom sticky-th">BOOKING TYPE</th>
                            <th class="table-custom sticky-th">STATUS</th>
                            <th class="table-custom sticky-th">DATE</th>
                            <th class="table-custom sticky-th">START TIME</th>
                            <th class="table-custom sticky-th">END TIME</th>
                            <th class="table-custom sticky-th">CREATED AT</th>
                            <th class="table-custom sticky-th">UPDATED AT</th>
                            <th class="table-custom sticky-th sticky-col">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($bookings->isEmpty())
                            <tr>
                                <td colspan="11" class="text-center">No Records Found</td>
                            </tr>
                        @else
                            @foreach ($bookings as $booking)
                                <tr>
                                    <td>{{ strtoupper($booking->lobby ?? 'N/A') }}</td>
                                    <td>{{ strtoupper($booking->transaction_no ?? 'N/A') }}</td>
                                    <td>{{ strtoupper($booking->activity->activity_name ?? 'N/A') }}</td>
                                    <td>{{ strtoupper($booking->unit ?? 'N/A') }}</td>
                                    <td>
                                        @if (strtoupper($booking->resident_type) == 'OWNER')
                                            <span class="text-success">{{ strtoupper($booking->resident_type) }}</span>
                                        @elseif (strtoupper($booking->resident_type) == 'TENANT')
                                            <span class="text-danger">{{ strtoupper($booking->resident_type) }}</span>
                                        @else
                                            {{ strtoupper($booking->resident_type) ?: 'N/A' }}
                                        @endif
                                    </td>
                                    <td>{{ strtoupper($booking->name ?? 'N/A') }}</td>
                                    <td>{{ strtoupper($booking->contact_number ?? 'N/A') }}</td>
                                    <td>{{ strtoupper($booking->booking_type ?? 'N/A') }}</td>
                                    <td>
                                        @if ($booking->booking_status == 1)
                                            <span class="badge bg-success border-success custom-badge">Booked</span>
                                        @else
                                            <span class="badge bg-danger border-danger custom-badge">Cancelled</span>
                                        @endif
                                    </td>
                                    <td>{{ strtoupper($booking->booking_date ?? 'N/A') }}</td>
                                    <td>{{ strtoupper($booking->booking_start_time ?? 'N/A') }}</td>
                                    <td>{{ strtoupper($booking->booking_end_time ?? 'N/A') }}</td>
                                    <td>{{ strtoupper($booking->created_at ?? 'N/A') }}</td>
                                    <td>{{ strtoupper($booking->updated_at ?? 'N/A') }}</td>
                                    <td class="sticky-col sticky-col-color">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <button type="button" class="btn btn-primary editInfo_id_booking btn-sm btn-equal"
                                                data-bs-toggle="tooltip" data-bs-placement="left" title="View"
                                                data-id="{{ $booking->id }}">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>

                                            @if ($booking->booking_status == 0)
                                                <button type="button" class="btn btn-secondary cancel-booking btn-sm btn-equal"
                                                    data-bs-toggle="tooltip" data-bs-placement="right" title="Cancelled"
                                                    data-id="{{ $booking->id }}" disabled>
                                                    <i class="fa-solid fa-ban"></i>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-danger cancel-booking btn-sm btn-equal"
                                                    data-bs-toggle="tooltip" data-bs-placement="right" title="Cancel"
                                                    data-id="{{ $booking->id }}">
                                                    <i class="fa-solid fa-ban"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>

                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        <div class="pagination-container">
            {{ $bookings->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
    @include('backend.modal.activities.activities-booking-modal')
    @include('backend.modal.activities.slotChecking-modal')
@endsection