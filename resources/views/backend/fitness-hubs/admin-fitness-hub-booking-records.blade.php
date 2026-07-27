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
                        <i class='bx bx-download me-1'></i> Download
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table id="historyFitnessHubTable" class="display table">
                    <thead>
                        <tr>
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
                            <th class="table-custom sticky-th">CANCELLED BY</th>
                            <th class="table-custom sticky-th">CANCELLED AT</th>
                            <th class="table-custom sticky-th">PENALTY</th>
                            <th class="table-custom sticky-th">PENALTY WAIVED</th>
                            <th class="table-custom sticky-th">WAIVED BY</th>
                            <th class="table-custom sticky-th">WAIVED AT</th>
                            <th class="table-custom sticky-th">APPLIED BY</th>
                            <th class="table-custom sticky-th">APPLIED AT</th>
                            <th class="table-custom sticky-th">CREATED BY</th>
                            <th class="table-custom sticky-th">CREATED AT</th>
                            <th class="table-custom sticky-th">UPDATED AT</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($fitnessHubBookings->isEmpty())
                            <tr>
                                <td colspan="21" class="text-center">No Records Found</td>
                            </tr>
                        @else
                            @foreach ($fitnessHubBookings as $fitnessHubBooking)
                                <tr>

                                    <td>{{ strtoupper($fitnessHubBooking->transaction_no ?? 'N/A') }}</td>
                                    <td>{{ strtoupper($fitnessHubBooking->fitnessHub->fitness_hub_name ?? 'N/A') }}</td>
                                    <td>{{ strtoupper($fitnessHubBooking->unit ?? 'N/A') }}</td>

                                    <td>
                                        @if($fitnessHubBooking->resident_type === 'TENANT')
                                            <span class="badge bg-danger">TENANT</span>
                                        @else
                                            <span class="badge bg-primary">OWNER</span>
                                        @endif
                                    </td>

                                    <td>{{ strtoupper($fitnessHubBooking->name ?? 'N/A') }}</td>
                                    <td>{{ $fitnessHubBooking->contact_number ?? 'N/A' }}</td>
                                    <td>{{ strtoupper($fitnessHubBooking->booking_type ?? 'N/A') }}</td>

                                    <td>
                                        @if ($fitnessHubBooking->booking_status == 1)
                                            <span class="badge bg-primary">Completed</span>
                                        @elseif ($fitnessHubBooking->booking_status == 2)
                                            <span class="badge bg-danger">Cancelled</span>
                                        @elseif ($fitnessHubBooking->booking_status == 3)
                                            <span class="badge bg-warning">Late Cancel</span>
                                        @elseif ($fitnessHubBooking->booking_status == 4)
                                            <span class="badge bg-dark">No Show</span>
                                        @endif
                                    </td>

                                    <td>{{ strtoupper($fitnessHubBooking->booking_date ?? 'N/A') }}</td>
                                    <td>{{ strtoupper($fitnessHubBooking->booking_start_time ?? 'N/A') }}</td>
                                    <td>{{ strtoupper($fitnessHubBooking->booking_end_time ?? 'N/A') }}</td>

                                    <td>{{ strtoupper($fitnessHubBooking->cancelledBy->name ?? 'N/A') }}</td>
                                    <td>{{ strtoupper($fitnessHubBooking->cancelled_at ?? 'N/A') }}</td>

                                    <td
                                        class="{{ ($fitnessHubBooking->penalty_amount ?? 0) > 0 ? 'text-danger fw-semibold' : '' }}">
                                        ₱{{ number_format($fitnessHubBooking->penalty_amount ?? 0, 2) }}
                                    </td>

                                    <td>
                                        @if($fitnessHubBooking->penalty_waived)
                                            <span class="badge bg-primary">YES</span>
                                        @else
                                            <span class="badge bg-danger">NO</span>
                                        @endif
                                    </td>

                                    <td>
                                        {{ $fitnessHubBooking->waivedBy->name ?? 'N/A' }}
                                    </td>
                                    <td>
                                        {{ $fitnessHubBooking->penalty_waived_at ?? 'N/A' }}
                                    </td>

                                    <td>

                                        {{ $fitnessHubBooking->penaltyAppliedBy->name ?? 'N/A' }}
                                    </td>

                                    <td>
                                        {{ $fitnessHubBooking->penalty_applied_at ?? 'N/A' }}
                                    </td>

                                    <td>{{ strtoupper(optional($fitnessHubBooking->createdBy)->name ?? 'N/A') }}</td>
                                    <td>{{ strtoupper($fitnessHubBooking->created_at ?? 'N/A') }}</td>
                                    <td>{{ strtoupper($fitnessHubBooking->updated_at ?? 'N/A') }}</td>

                                    {{-- ACTION COLUMN (simplified) --}}
                                    <td class="sticky-col sticky-col-color">
                                        <div class="d-flex gap-1 justify-content-center">

                                            <button type="button" class="btn btn-primary viewFitnessHubRecordDetailsBtn btn-sm"
                                                title="View" data-id="{{ $fitnessHubBooking->id }}">
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
            {{ $fitnessHubBookings->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
    @include('backend.modal.fitness-hubs.booking-history-fitness-hub-modal')
    @push('scripts')
        <script src="{{ asset('assets/backend/js/fitness-hub-records.js') }}"></script>
    @endpush
@endsection