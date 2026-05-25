@extends('layouts.backend')
@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-6  d-flex justify-content-between align-items-center flex-wrap">

                    <form action="{{ route('admin.search.ausi.report') }}" method="GET"
                        id="searchAusiBookingForm" class="d-flex align-items-center" style="max-width: 250px;">
                        <div class="input-group text-dark w-100">
                            <span class="input-group-text">
                                <i class='bx bx-search-alt text-dark'></i>
                            </span>
                            <input type="text" name="searchAusiReport" value="{{ $searchAusiReport ?? '' }}"
                                id="searchInputAusiReport" class="form-control" placeholder="Name/Unit"
                                autocomplete="off">
                        </div>
                    </form>
                </div>

                <div class="col-6 d-flex justify-content-end align-items-center">
                    <button type="button" class="btn btn-primary badge DownloadAusiBookingReports me-2">
                        <i class='bx bx-download'></i> Download
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <!-- <table id="userTable" class="table table-bordered table-hover table-striped"></table> -->
                <table id="ausiBookingReportTable" class="table">
                    <thead>
                        <tr>
                            <th class="text-dark">Transaction No</th>
                            <th class="text-dark">SRF No</th>
                            <th class="text-dark">Name</th>
                            <th class="text-dark">Resident Type</th>
                            <th class="text-dark">Unit</th>
                            <th class="text-dark">Date</th>
                            <th class="text-dark">Time Slot</th>
                            <th class="text-dark">Emergency</th>
                            <th class="text-dark">Remarks</th>
                            <th class="text-dark">Status</th>
                            <th class="text-dark">Action</th>


                        </tr>
                    </thead>
                    <tbody>
                        @if($ausiBookings->isEmpty())
                            <tr>
                                <td colspan="12" class="text-center">No Record Found</td>
                            </tr>
                        @else
                            @foreach ($ausiBookings as $ausiBooking)
                                <tr>
                                    <td>{{ $ausiBooking->transaction_no ?? 'N/A' }}</td>
                                    <td>{{ $ausiBooking->srf_no ?? 'N/A' }}</td>
                                    <td>{{ $ausiBooking->name ?? 'N/A' }}</td>
                                    <td>
                                        @php
                                            $resType = strtolower($ausiBooking->resident_type ?? '');
                                        @endphp

                                        @if ($resType === 'tenant')
                                            <span class="badge bg-danger text-uppercase">{{ $ausiBooking->resident_type }}</span>
                                        @elseif ($resType === 'owner')
                                            <span
                                                class="badge bg-primary text-uppercase">{{ $ausiBooking->resident_type }}</span>
                                        @else
                                            <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </td>
                                    <td>{{ $ausiBooking->unit_no ?? 'N/A' }}</td>


                                    <td>{{ $ausiBooking->booking_date ?? 'N/A' }}</td>
                                    <td>{{ $ausiBooking->booking_time_slot ?? 'N/A' }}</td>
                                    <td>
                                        @if ($ausiBooking->emergency == 0)
                                            <span class="badge bg-secondary badge-forge ">No</span>
                                        @else
                                            <span class="badge bg-danger badge-forge ">Yes</span>
                                        @endif
                                    </td>

                                    <td>{{ $ausiBooking->remarks ?? 'N/A' }}</td>

                                    <td>
                                        @if ($ausiBooking->booking_status == 1)
                                            <span class="badge bg-primary custom-badge">Completed</span>
                                        @else
                                            <span class="badge bg-danger custom-badge">Cancelled</span>
                                        @endif
                                    </td>

                                    <td class="sticky-col sticky-col-color">
                                        <div class="d-flex gap-1 justify-content-center">
                                            @php
                                                $isCancelled = $ausiBooking->booking_status == 2;
                                            @endphp

                                            <button type="button" class="btn btn-primary view_ausi_booking btn-sm btn-equal"
                                                data-bs-toggle="tooltip" data-bs-placement="left" title="View"
                                                data-id="{{ $ausiBooking->id }}">
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
            {{ $ausiBookings->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>

    @include('backend.modal.ausi.ausi-booking-modal')

@endsection