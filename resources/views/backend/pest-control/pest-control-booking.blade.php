@extends('layouts.backend')
@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-6  d-flex justify-content-between align-items-center flex-wrap">

                    <form action="{{ route('admin.search.pest.control.booking') }}" method="GET"
                        id="searchPesControlBookingForm" class="d-flex align-items-center" style="max-width: 250px;">
                        <div class="input-group text-dark w-100">
                            <span class="input-group-text">
                                <i class='bx bx-search-alt text-dark'></i>
                            </span>
                            <input type="text" name="searchPestControlBooking" value="{{ $searchBooking ?? '' }}"
                                id="searchInputPesControlBooking" class="form-control" placeholder="Name/Unit"
                                autocomplete="off">

                        </div>
                    </form>
                </div>

                <div class="col-6 d-flex justify-content-end align-items-center">
                    <form id="bookingImportFormPC" action="{{ route('pest.control.booking.import') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="file" id="PCbookingFileInput" name="file" accept=".csv,.xlsx" style="display:none;">
                    </form>

                    <!-- <button type="button" class="btn btn-primary badge me-2" id="uploadBookingBtnPC">
                                                                                    <i class='bx bx-upload'></i> Upload Bookings
                                                                                </button> -->


                    <button type="button" class="btn btn-primary badge AddPesControlBookingAdmin me-2">
                        <i class='bx bx-plus'></i> New Booking
                    </button>

                    <button type="button" class="btn btn-danger badge AddEmergencyPesControlBooking me-2">
                        <i class='bx bx-plus'></i> Emergency Booking
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <!-- <table id="userTable" class="table table-bordered table-hover table-striped"></table> -->
                <table id="pestControlBookingTable" class="table">
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
                            <th class="text-dark">Created By</th>
                            <th class="text-dark">Created At</th>
                            <th class="text-dark">Cancelled By</th>
                            <th class="text-dark">Cancelled At</th>
                            <th class="text-dark">Completed By</th>
                            <th class="text-dark">Completed At</th>
                            <th class="text-dark">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($pestControlBookings->isEmpty())
                            <tr>
                                <td colspan="12" class="text-center">No Record Found</td>
                            </tr>
                        @else
                            @foreach ($pestControlBookings as $pestControlBooking)
                                <tr>
                                    <td>{{ $pestControlBooking->transaction_no ?? 'N/A' }}</td>
                                    <td>{{ $pestControlBooking->srf_no ?? 'N/A' }}</td>
                                    <td>{{ $pestControlBooking->name ?? 'N/A' }}</td>
                                    <td>
                                        @php
                                            $resType = strtolower($pestControlBooking->resident_type ?? '');
                                        @endphp

                                        @if ($resType === 'tenant')
                                            <span class="badge bg-danger text-uppercase">{{ $pestControlBooking->resident_type }}</span>
                                        @elseif ($resType === 'owner')
                                            <span
                                                class="badge bg-primary text-uppercase">{{ $pestControlBooking->resident_type }}</span>
                                        @else
                                            <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </td>
                                    <td>{{ $pestControlBooking->unit_no ?? 'N/A' }}</td>


                                    <td>{{ $pestControlBooking->booking_date ?? 'N/A' }}</td>
                                    <td>{{ $pestControlBooking->booking_time_slot ?? 'N/A' }}</td>
                                    <td>
                                        @if ($pestControlBooking->charged_type === 1)
                                            <span class="badge bg-primary text-white badge-forge ">Free</span>
                                        @else
                                            <span class="badge bg-danger badge-forge ">Billable</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if ($pestControlBooking->emergency == 0)
                                            <span class="badge bg-secondary badge-forge ">No</span>
                                        @else
                                            <span class="badge bg-danger badge-forge ">Yes</span>
                                        @endif
                                    </td>

                                    <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                        data-bs-toggle="tooltip" title="{{ $pestControlBooking->remarks }}">
                                        {{ $pestControlBooking->remarks ?? 'N/A' }}
                                    </td>

                                    <td>
                                        <span class="badge bg-{{ $pestControlBooking->p_c_status['badge'] }} custom-badge">
                                            {{ $pestControlBooking->p_c_status['label'] }}
                                        </span>
                                    </td>
                                    <td>{{ isset($pestControlBooking->createdBy->name) ? strtoupper($pestControlBooking->createdBy->name) : 'N/A' }}
                                    </td>
                                    <td>{{ $pestControlBooking->created_at ?? 'N/A' }}</td>
                                    <td>{{ isset($pestControlBooking->cancelledBy->name) ? strtoupper($pestControlBooking->cancelledBy->name) : 'N/A' }}
                                    </td>
                                    <td>{{ $pestControlBooking->cancelled_at ?? 'N/A' }}</td>
                                    <td>{{ isset($pestControlBooking->completedBy->name) ? strtoupper($pestControlBooking->completedBy->name) : 'N/A' }}
                                    </td>
                                    <td>{{ $pestControlBooking->completed_at ?? 'N/A' }}</td>
                                    <td class="sticky-col sticky-col-color">
                                        <div class="d-flex gap-1 justify-content-center">
                                            @php
                                                $isCancelled = $pestControlBooking->booking_status == \App\Models\PestControlBooking::STATUS_CANCELLED;
                                                $isScheduled = $pestControlBooking->booking_status == \App\Models\PestControlBooking::STATUS_SCHEDULED;
                                                $isCompleted = $pestControlBooking->booking_status == \App\Models\PestControlBooking::STATUS_COMPLETED;
                                            @endphp

                                            <button type="button" class="btn btn-primary edit_pest_control_booking btn-sm btn-equal"
                                                data-bs-toggle="tooltip" data-bs-placement="left" title="View" est
                                                data-id="{{ $pestControlBooking->id }}">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>

                                            <button type="button"
                                                class="btn btn-success btn-sm btn-equal complete_pest_control_booking"
                                                data-bs-toggle="tooltip"
                                                title="{{ $isScheduled ? 'Complete Booking' : 'Already Completed' }}"
                                                data-id="{{ $pestControlBooking->id }}" {{ !$isScheduled ? 'disabled' : '' }}>

                                                <i class="fa-solid fa-check"></i>
                                            </button>

                                            <button type="button"
                                                class="btn btn-sm btn-equal {{ $isCancelled ? 'btn-secondary cancel-booking' : 'btn-danger admin-pest-control-booking-cancel' }}"
                                                data-bs-toggle="tooltip" data-bs-placement="right"
                                                title="{{ $isCancelled ? 'Cancelled' : 'Cancel' }}"
                                                data-id="{{ $pestControlBooking->id }}" {{ $isCancelled ? 'disabled' : '' }}>
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
            {{ $pestControlBookings->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>

    @include('backend.modal.pest-control.pest-control-booking-modal')
    @push('scripts')
        <script src="{{ asset('assets/backend/js/pest-control-booking.js') }}"></script>
    @endpush

@endsection