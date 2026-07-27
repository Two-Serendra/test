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

    <div class="row mt-4">
        <div class="col-12 d-flex justify-content-start align-items-center">
            <h2>Report</h2>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-6 d-flex justify-content-start align-items-center">
                    <form action="{{ route('search-history') }}" method="GET" id="searchFormHistory"
                        class="d-flex align-items-center">
                        <div class="input-group" style="width: 200px;">
                            <span class="input-group-text">
                                <i class="fa-solid fa-magnifying-glass fa-sm"></i>
                            </span>
                            <input type="text" name="searchHistory" value="{{ $searchHistory ?? '' }}"
                                id="searchInputHistory" class="form-control" placeholder="Name/Unit" autocomplete="off">
                        </div>
                    </form>
                </div>
                <div class="col-6 d-flex justify-content-end align-items-center">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#DownloadHistory"
                        id="exportButtonBelize">
                        <i class="fa-solid fa-download me-1"></i> Download
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table id="historyTable" class="display table">
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
                        </tr>
                    </thead>
                    <tbody>
                        @if($activity_bookings->isEmpty())
                            <tr>
                                <td colspan="11" class="text-center">No Records Found</td>
                            </tr>
                        @else
                            @foreach ($activity_bookings as $activity_booking)
                                <tr>
                                    <td>{{ strtoupper($activity_booking->lobby ?? 'N/A') }}</td>
                                    <td>{{ strtoupper($activity_booking->transaction_no ?? 'N/A') }}</td>
                                    <td>{{ strtoupper($activity_booking->activity->activity_name ?? 'N/A') }}</td>
                                    <td>{{ strtoupper($activity_booking->unit ?? 'N/A') }}</td>
                                    <td>
                                        @if (strtoupper($activity_booking->resident_type) == 'OWNER')
                                            <span class="text-success">{{ strtoupper($activity_booking->resident_type) }}</span>
                                        @elseif (strtoupper($activity_booking->resident_type) == 'TENANT')
                                            <span class="text-danger">{{ strtoupper($activity_booking->resident_type) }}</span>
                                        @else
                                            {{ strtoupper($activity_booking->resident_type) ?: 'N/A' }}
                                        @endif
                                    </td>
                                    <td>{{ strtoupper($activity_booking->name ?? 'N/A') }}</td>
                                    <td>{{ strtoupper($activity_booking->contact_number ?? 'N/A') }}</td>
                                    <td>{{ strtoupper($activity_booking->booking_type ?? 'N/A') }}</td>

                                    <td>
                                        @if ($activity_booking->booking_status == 1)
                                            <span class="badge bg-success custom-badge">Completed</span>
                                        @else
                                            <span class="badge bg-danger custom-badge">Cancelled</span>
                                        @endif
                                    </td>
                                    <td>{{ strtoupper($activity_booking->booking_date ?? 'N/A') }}</td>
                                    <td>{{ strtoupper($activity_booking->booking_start_time ?? 'N/A') }}</td>
                                    <td>{{ strtoupper($activity_booking->booking_end_time ?? 'N/A') }}</td>
                                    <td>{{ strtoupper($activity_booking->created_at ?? 'N/A') }}</td>
                                    <td>{{ strtoupper($activity_booking->updated_at ?? 'N/A') }}</td>

                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        <div class="pagination-container">
            {{ $activity_bookings->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
    @include('backend.modal.downloadHistory-modal')
    @push('scripts')
        <script src="{{ asset('assets/backend/js/history.js') }}"></script>
    @endpush
@endsection