@extends('layouts.backend')
@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-6  d-flex justify-content-between align-items-center flex-wrap">

                    <form action="{{ route('admin.search.ausi.booking') }}" method="GET" id="searchPesControlBookingForm"
                        class="d-flex align-items-center" style="max-width: 250px;">
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
                    <form id="bookingImportFormAusi" action="{{ route('ausi.booking.import') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="file" id="AusibookingFileInput" name="file" accept=".csv,.xlsx" style="display:none;">
                    </form>

                    <button type="button" class="btn btn-primary badge me-2" id="uploadBookingBtnAusi">
                        <i class='bx bx-upload'></i> Upload Bookings
                    </button>


                    <button type="button" class="btn btn-primary badge AddAusiBookingAdmin me-2">
                        <i class='bx bx-plus'></i> New Booking
                    </button>

                    <!-- <button type="button" class="btn btn-danger badge AddEmergencyAusiBooking me-2">
                                                                                                <i class='bx bx-plus'></i> Emergency Booking
                                                                                            </button> -->
                </div>
            </div>
            <div class="table-responsive">
                <!-- <table id="userTable" class="table table-bordered table-hover table-striped"></table> -->
                <table id="ausiBookingTable" class="table">
                    <thead>
                        <tr>
                            <th class="text-dark">Transaction No</th>
                            <!-- <th class="text-dark">SRF No</th> -->
                            <th class="text-dark">Name</th>
                            <th class="text-dark">Resident Type</th>
                            <th class="text-dark">Unit</th>
                            <th class="text-dark">Date</th>
                            <th class="text-dark">Time Slot</th>
                            <!-- <th class="text-dark">Emergency</th> -->
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
                        @if($ausiBookings->isEmpty())
                            <tr>
                                <td colspan="16" class="text-center">No Record Found</td>
                            </tr>
                        @else
                            @foreach ($ausiBookings as $ausiBooking)
                                <tr>
                                    <td>{{ $ausiBooking->transaction_no ?? 'N/A' }}</td>
                                    <!-- <td>{{ $ausiBooking->srf_no ?? 'N/A' }}</td> -->
                                    <td>{{ $ausiBooking->name ?? 'N/A' }}</td>
                                    <td>
                                        @php
                                            $resType = strtolower($ausiBooking->resident_type ?? '');
                                        @endphp

                                        @if ($resType === 'tenant')
                                            <span class="badge bg-danger text-uppercase">{{ $ausiBooking->resident_type }}</span>
                                        @elseif ($resType === 'owner')
                                            <span class="badge bg-primary text-uppercase">{{ $ausiBooking->resident_type }}</span>
                                        @else
                                            <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </td>
                                    <td>{{ $ausiBooking->unit_no ?? 'N/A' }}</td>


                                    <td>{{ $ausiBooking->booking_date ?? 'N/A' }}</td>
                                    <td>{{ $ausiBooking->booking_time_slot ?? 'N/A' }}</td>


                                    <!-- <td>
                                                                                                    @if ($ausiBooking->emergency == 0)
                                                                                                        <span class="badge bg-secondary badge-forge ">No</span>
                                                                                                    @else
                                                                                                        <span class="badge bg-danger badge-forge ">Yes</span>
                                                                                                    @endif
                                                                                                </td> -->

                                    <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                        data-bs-toggle="tooltip" title="{{ $ausiBooking->remarks }}">
                                        {{ $ausiBooking->remarks ?? 'N/A' }}
                                    </td>

                                    <td>
                                        <span class="badge bg-{{ $ausiBooking->status_badge }} custom-badge">
                                            {{ strtoupper($ausiBooking->display_status) }}
                                        </span>
                                    </td>
                                    <td>{{ isset($ausiBooking->createdBy->name) ? strtoupper($ausiBooking->createdBy->name) : 'N/A' }}
                                    </td>
                                    <td>{{ $ausiBooking->created_at ?? 'N/A' }}</td>
                                    <td>{{ isset($ausiBooking->cancelledBy->name) ? strtoupper($ausiBooking->cancelledBy->name) : 'N/A' }}
                                    </td>
                                    <td>{{ $ausiBooking->cancelled_at ?? 'N/A' }}</td>
                                    <td>{{ isset($ausiBooking->completedBy->name) ? strtoupper($ausiBooking->completedBy->name) : 'N/A' }}
                                    <td>{{ $ausiBooking->completed_at ?? 'N/A' }}</td>
                                    <td class="sticky-col sticky-col-color">
                                        <div class="d-flex gap-1 justify-content-center">

                                            @php
                                                $disableActions = in_array($ausiBooking->booking_status, [0, 2]);
                                                $disableInspect = $disableActions;
                                            @endphp



                                            <button type="button" class="btn btn-primary edit_ausi_booking btn-sm btn-equal"
                                                data-bs-toggle="tooltip" data-bs-placement="left" title="View"
                                                data-id="{{ $ausiBooking->id }}">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>

                                            <button type="button"
                                                class="btn btn-sm {{ $disableInspect ? 'btn-secondary' : 'btn-success' }} inspection_ausi_booking"
                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="{{ $disableInspect ? 'Inspection Unavailable' : 'Inspect' }}"
                                                data-id="{{ $ausiBooking->id }}" {{ $disableInspect ? 'disabled' : '' }}>
                                                <i class="fa-solid fa-clipboard-check"></i>
                                            </button>


                                            <button type="button"
                                                class="btn btn-sm btn-equal {{ $disableActions ? 'btn-secondary cancel-booking' : 'btn-danger admin-ausi-booking-cancel' }}"
                                                data-bs-toggle="tooltip" data-bs-placement="right"
                                                title="{{ $disableActions ? $ausiBooking->display_status : 'Cancel' }}"
                                                data-id="{{ $ausiBooking->id }}" {{ $disableActions ? 'disabled' : '' }}>
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
            {{ $ausiBookings->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>

    @include('backend.modal.ausi.ausi-booking-modal')

    @push('scripts')
        <script src="{{ asset('assets/backend/js/ausi-booking.js') }}"></script>
    @endpush
@endsection