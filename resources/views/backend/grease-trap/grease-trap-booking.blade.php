@extends('layouts.backend')
@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-6  d-flex justify-content-between align-items-center flex-wrap">

                    <form action="{{ route('admin.search.grease.trap.booking') }}" method="GET"
                        id="searchGreaseTrapBookingForm" class="d-flex align-items-center" style="max-width: 250px;">
                        <div class="input-group text-dark w-100">
                            <span class="input-group-text">
                                <i class='bx bx-search-alt text-dark'></i>
                            </span>
                            <input type="text" name="searchGreaseTrapBooking" value="{{ $searchGreaseTrapBooking ?? '' }}"
                                id="searchInputGreaseTrapBooking" class="form-control" placeholder="Name/Unit"
                                autocomplete="off">


                        </div>
                    </form>
                </div>

                <div class="col-6 d-flex justify-content-end align-items-center">

                    <form id="bookingImportFormGT" action="{{ route('grease.trap.booking.import') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="file" id="GTbookingFileInput" name="file" accept=".csv,.xlsx" style="display:none;">
                    </form>
<!-- 
                    <button type="button" class="btn btn-primary badge me-2" id="uploadBookingBtnGT">
                        <i class='bx bx-upload'></i> Upload Bookings
                    </button> -->

                     <button type="button" class="btn btn-primary badge AddGreaseTrapBookingAdmin me-2">
                         <i class='bx bx-search-alt'></i> Check Unit
                    </button>


                    <button type="button" class="btn btn-primary badge AddGreaseTrapBookingAdmin me-2">
                        <i class='bx bx-plus'></i> New Booking
                    </button>

                    <button type="button" class="btn btn-danger badge AddEmergencyGreaseTrapBooking me-2">
                        <i class='bx bx-plus'></i> Emergency Booking
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <!-- <table id="userTable" class="table table-bordered table-hover table-striped"></table> -->
                <table id="greaseTrapBookingTable" class="table">
                    <thead>
                        <tr>
                            <th class="text-dark">Transaction No</th>
                            <th class="text-dark">SRF No</th>
                            <th class="text-dark">Name</th>
                            <th class="text-dark">Resident Type</th>
                            <th class="text-dark">Unit</th>
                            <th class="text-dark">Date</th>
                            <th class="text-dark">Time Slot</th>
                            <th class="text-dark">Charged_Type</th>
                            <th class="text-dark">Emergency</th>
                            <th class="text-dark">Remarks</th>
                            <th class="text-dark">Status</th>
                            <th class="text-dark">Penalty</th>
                            <th class="text-dark">Created by</th>
                            <th class="text-dark">Created at</th>
                            <th class="text-dark">Cancelled_by</th>
                            <th class="text-dark">Cancelled_at</th>
                            <th class="text-dark">Action</th>


                        </tr>
                    </thead>
                    <tbody>
                        @if($greaseTrapBookings->isEmpty())
                            <tr>
                                <td colspan="12" class="text-center">No Record Found</td>
                            </tr>
                        @else
                            @foreach ($greaseTrapBookings as $greaseTrapBooking)
                                            <tr>
                                                <td>{{ $greaseTrapBooking->transaction_no ?? 'N/A' }}</td>
                                                <td>{{ !empty(trim($greaseTrapBooking->srf_no)) ? $greaseTrapBooking->srf_no : 'N/A' }}</td>
                                                <td>{{ !empty(trim($greaseTrapBooking->name)) ? $greaseTrapBooking->name : 'N/A' }}</td>

                                                <td>
                                                    @php
                                                        $resType = strtolower($greaseTrapBooking->resident_type ?? '');
                                                    @endphp

                                                    @if ($resType === 'tenant')
                                                        <span class="badge bg-danger text-uppercase">{{ $greaseTrapBooking->resident_type }}</span>
                                                    @elseif ($resType === 'owner')
                                                        <span class="badge bg-primary text-uppercase">{{ $greaseTrapBooking->resident_type }}</span>
                                                    @else
                                                        <span class="badge bg-secondary">N/A</span>
                                                    @endif
                                                </td>
                                                 <td>{{ !empty(trim($greaseTrapBooking->unit_no)) ? $greaseTrapBooking->unit_no : 'N/A' }}</td>


                                                <td>{{ $greaseTrapBooking->booking_date ?? 'N/A' }}</td>
                                                <td>{{ $greaseTrapBooking->booking_time_slot ?? 'N/A' }}</td>
                                                <td>
                                                    @if ($greaseTrapBooking->charged_type === 1)
                                                        <span class="badge bg-primary text-white badge-forge ">FREE</span>
                                                    @else
                                                        <span class="badge bg-danger badge-forge ">BILLABLE</span>
                                                    @endif
                                                </td>

                                                <td>
                                                    @if ($greaseTrapBooking->emergency == 0)
                                                        <span class="badge bg-secondary badge-forge ">No</span>
                                                    @else
                                                        <span class="badge bg-danger badge-forge ">Yes</span>
                                                    @endif
                                                </td>

                                             <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                                data-bs-toggle="tooltip"
                                                title="{{ $greaseTrapBooking->remarks }}">
                                                {{ !empty(trim($greaseTrapBooking->remarks)) ? $greaseTrapBooking->remarks : 'N/A' }}
                                            </td>

                                                <td>
                                                    @if ($greaseTrapBooking->booking_status == 1)
                                                        <span class="badge bg-primary custom-badge">BOOKED</span>
                                                    @else
                                                        <span class="badge bg-danger custom-badge">CANCELLED</span>
                                                    @endif
                                                </td>
                                                 <td>
                                                    @if ($greaseTrapBooking->has_penalty)
                                                        <span
                                                            class="text-warning fw-bold">₱{{ number_format($greaseTrapBooking->penalty_amount, 2) }}</span>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                 <td>{{ isset($greaseTrapBooking->createdBy->name) ? strtoupper($greaseTrapBooking->createdBy->name) : 'N/A' }}            
                                            </td>
                                                 <td>{{ $greaseTrapBooking->created_at ?? 'N/A' }}</td>
                                             
                                                <td>{{ isset($greaseTrapBooking->cancelledBy->name) ? strtoupper($greaseTrapBooking->cancelledBy->name) : 'N/A' }}
                                                </td>
                                                <td>{{ $greaseTrapBooking->cancelled_at ?? 'N/A' }}</td>




                                                <td class="sticky-col sticky-col-color">
                                                    <div class="d-flex gap-1 justify-content-center">
                                                        @php
                                                            $isCancelled = $greaseTrapBooking->booking_status == 2;
                                                           $bookingDateTime = $greaseTrapBooking->getBookingDateTime();
                                                          $isPast = $bookingDateTime instanceof \Carbon\Carbon
                                                            && $bookingDateTime->isValid()
                                                            && $bookingDateTime->lt(now());
                                                        @endphp

                                                        <button type="button" class="btn btn-primary edit_grease_trap_booking btn-sm btn-equal"
                                                            data-bs-toggle="tooltip" data-bs-placement="left" title="View"
                                                            data-id="{{ $greaseTrapBooking->id }}">
                                                            <i class="fa-solid fa-eye"></i>
                                                        </button>


                                                        <button type="button"
                                                            class="btn btn-sm btn-equal 
                                {{ ($isCancelled || $isPast) ? 'btn-secondary cancel-booking' : 'btn-danger admin-grease-trap-booking-cancel' }}"
                                                            data-bs-toggle="tooltip" data-bs-placement="right"
                                                            title="{{ $isCancelled ? 'Cancelled' : ($isPast ? 'Past Booking' : 'Cancel') }}"
                                                            data-id="{{ $greaseTrapBooking->id }}" {{ ($isCancelled || $isPast) ? 'disabled' : '' }}>
                                                            <i class="fa-solid fa-ban"></i>
                                                        </button>
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
            {{ $greaseTrapBookings->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>

    @include('backend.modal.grease-trap.grease-trap-booking-modal')

@endsection