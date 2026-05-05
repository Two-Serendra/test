@extends('layouts.backend')
@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-6  d-flex justify-content-between align-items-center flex-wrap">

                    <form action="{{ route('admin.search.pest.control.report') }}" method="GET"
                        id="searchpestControlBookingForm" class="d-flex align-items-center" style="max-width: 250px;">
                        <div class="input-group text-dark w-100">
                            <span class="input-group-text">
                                <i class='bx bx-search-alt text-dark'></i>
                            </span>
                            <input type="text" name="searchPestControlReport" value="{{ $searchPestControlReport ?? '' }}"
                                id="searchInputpestControlReport" class="form-control" placeholder="Name/Unit"
                                autocomplete="off">
                        </div>
                    </form>
                </div>

                <div class="col-6 d-flex justify-content-end align-items-center">
                    <button type="button" class="btn btn-primary badge DownloadPestControlBookingReports me-2">
                        <i class='bx bx-download'></i> Download
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

                                    <td>{{ $pestControlBooking->remarks ?? 'N/A' }}</td>

                                    <td>
                                        @if ($pestControlBooking->booking_status == 1)
                                            <span class="badge bg-primary custom-badge">Completed</span>
                                        @else
                                            <span class="badge bg-danger custom-badge">Cancelled</span>
                                        @endif
                                    </td>

                                    <td class="sticky-col sticky-col-color">
                                        <div class="d-flex gap-1 justify-content-center">
                                            @php
                                                $isCancelled = $pestControlBooking->booking_status == 2;
                                            @endphp

                                            <button type="button" class="btn btn-primary view_pest_control_booking btn-sm btn-equal"
                                                data-bs-toggle="tooltip" data-bs-placement="left" title="View"
                                                data-id="{{ $pestControlBooking->id }}">
                                                <i class="fa-solid fa-eye"></i>
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

@endsection