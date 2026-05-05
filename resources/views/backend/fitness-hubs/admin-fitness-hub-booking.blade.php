@extends('layouts.backend')
@section('content')
    <style>
        #resident-type-dropdown {
            inset: 36px auto auto 0px !important;
        }

        .hide-column {
            display: none;
        }
    </style>

    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-6 d-flex justify-content-start align-items-center">
                    <!-- Search Form -->
                    <form action="{{ route('admin.search.booking.fitness.hub') }}" method="GET" id="searchFormBooking"
                        class="d-flex align-items-center">
                        <div class="input-group" style="width: 200px;">
                            <span class="input-group-text">
                                <i class="fa-solid fa-magnifying-glass fa-sm"></i>
                            </span>
                            <input type="text" name="searchBookingFitnessHub"
                                value="{{ $searchTerm ?? '' }}"
                                id="searchInputBooking"
                                class="form-control"
                                placeholder="Name/Unit"
                                autocomplete="off">
                        </div>
                    </form>
                </div>
                <div class="col-6 d-flex justify-content-end align-items-center">
                    <form id="fitnessHubBookingImportForm" action="{{ route('fitness.hub.booking.import') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="file" id="fitnessHubBookingFileInput" name="file" accept=".csv,.xlsx"
                            style="display:none;">
                    </form>

                    <button type="button" class="btn btn-primary badge me-2" id="uploadBookingBtn">
                        <i class='bx bx-upload'></i> Upload Bookings
                    </button>

                    <button type="button" class="btn btn-primary badge addFitnessHubBookingBtn me-2">
                        <i class='bx bx-plus'></i> New Booking
                    </button>

                    <button type="button" class="btn badge SlotCheckingFitnessHubBtn"
                        style="color:#fff;background-color:#6c757d;border-color:#6c757d;">
                        <i class="bx bxs-check-square"></i> Slot Checking
                    </button>
                </div>
            </div>

            <div class="table-responsive" style="overflow-x: auto;">
                <table id="fitnessHubBookingTable" class="display table">
                    <thead>
                        <tr>
                            <!-- <th class="table-custom sticky-th">LOBBY</th> -->
                            <th class="table-custom sticky-th">TRANS NO.</th>
                            <th class="table-custom sticky-th">FITNESS HUB</th>
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
                            <th class="table-custom sticky-th sticky-col">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($FitnessHubBookings->isEmpty())
                            <tr>
                                <td colspan="11" class="text-center">No Records Found</td>
                            </tr>
                        @else
                            @foreach ($FitnessHubBookings as $fitnessHubBooking)
                                        <tr>

                                            <td>{{ strtoupper($fitnessHubBooking->transaction_no ?? 'N/A') }}</td>
                                            <td>{{ strtoupper($fitnessHubBooking->fitnessHub->fitness_hub_name ?? 'N/A') }}</td>
                                            <td>{{ strtoupper($fitnessHubBooking->unit ?? 'N/A') }}</td>
                                            <td> @if($fitnessHubBooking->resident_type === 'TENANT')
                                                <span class="badge bg-danger border-danger custom-badge">TENANT</span>
                                            @elseif($fitnessHubBooking->resident_type === 'OWNER')
                                                    <span class="badge bg-primary border-primary custom-badge">OWNER</span>

                                                @endif
                                            </td>
                                            <td>{{ strtoupper($fitnessHubBooking->name ?? 'N/A') }}</td>
                                            <td>{{ $fitnessHubBooking->contact_number ? trim($fitnessHubBooking->contact_number) : 'N/A' }}
                                            </td>
                                            <td>{{ strtoupper($fitnessHubBooking->booking_type ?? 'N/A') }}</td>
                                            <td>
                                                @if($fitnessHubBooking->booking_status == 1)
                                                    <span class="badge bg-primary">BOOKED</span>

                                                @elseif($fitnessHubBooking->booking_status == 2)
                                                    <span class="badge bg-danger">CANCELLED</span>

                                                @elseif($fitnessHubBooking->booking_status == 3)
                                                    <span class="badge bg-warning">LATE CANCEL</span>

                                                @elseif($fitnessHubBooking->booking_status == 4)
                                                    <span class="badge bg-dark">NO SHOW</span>
                                                @endif
                                            </td>


                                            <td>{{ strtoupper($fitnessHubBooking->booking_date ?? 'N/A') }}</td>
                                            <td>
                                                {{ $fitnessHubBooking->booking_start_time
                                ? \Carbon\Carbon::createFromFormat('H:i:s', $fitnessHubBooking->booking_start_time)->format('h:i A')
                                : 'N/A' }}
                                            </td>

                                            <td>
                                                {{ $fitnessHubBooking->booking_end_time
                                ? \Carbon\Carbon::createFromFormat('H:i:s', $fitnessHubBooking->booking_end_time)->format('h:i A')
                                : 'N/A' }}
                                            </td>
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

                                            <td class="sticky-col sticky-col-color">
                                                <div class="d-flex gap-1 justify-content-center">

                                                    <button type="button"
                                                        class="btn btn-primary viewFitnessHubBookingDetailsBtn btn-sm btn-equal"
                                                        data-bs-toggle="tooltip" data-bs-placement="left" title="View"
                                                        data-id="{{ $fitnessHubBooking->id }}">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </button>

                                                    @if ($fitnessHubBooking->booking_status == 2 || $fitnessHubBooking->booking_status == 3 || $fitnessHubBooking->booking_status == 4)
                                                        <button type="button"
                                                            class="btn btn-secondary cancelled-fitnessHubBooking btn-sm btn-equal"
                                                            data-bs-toggle="tooltip" data-bs-placement="right" title="Cancelled"
                                                            data-id="{{ $fitnessHubBooking->id }}" disabled>
                                                            <i class="fa-solid fa-ban"></i>
                                                        </button>
                                                    @else
                                                        <button type="button" class="btn btn-danger cancel-fitnessHubBooking btn-sm btn-equal"
                                                            data-bs-toggle="tooltip" data-bs-placement="right" title="Cancel"
                                                            data-id="{{ $fitnessHubBooking->id }}">
                                                            <i class="fa-solid fa-ban"></i>
                                                        </button>
                                                    @endif

                                                    @php
                                                        $fitnessHubBookingDateTime = \Carbon\Carbon::parse($fitnessHubBooking->booking_date . ' ' . $fitnessHubBooking->booking_start_time);
                                                    @endphp

                                                    @if ($fitnessHubBooking->booking_status == 2 || $fitnessHubBooking->booking_status == 3 || $fitnessHubBooking->booking_status == 4 || $fitnessHubBookingDateTime->isFuture())

                                                        <button type="button" class="btn btn-secondary marked-as-no-show btn-sm btn-equal"
                                                            data-bs-toggle="tooltip" title="No show" disabled>
                                                            <i class="fa-solid fa-user-slash"></i>
                                                        </button>
                                                    @else

                                                        <button type="button" class="btn btn-warning mark-as-no-show-fitness-hub btn-sm btn-equal"
                                                            data-bs-toggle="tooltip" data-bs-placement="right" title="Mark as no show"
                                                            data-id="{{ $fitnessHubBooking->id }}">
                                                            <i class="fa-solid fa-user-slash"></i>
                                                        </button>

                                                    @endif


                                                    @if ($fitnessHubBooking->penalty_amount > 0 && !$fitnessHubBooking->penalty_waived)

                                                        <button type="button" class="btn btn-success manage-penalty-fitness-hub btn-sm btn-equal"
                                                            data-action="waive" data-id="{{ $fitnessHubBooking->id }}" data-bs-toggle="tooltip"
                                                            title="Waive Penalty">
                                                            <i class="fa-solid fa-hand-holding-dollar"></i>
                                                        </button>
                                                    @else
                                                        {{-- No penalty or waived → allow apply --}}
                                                        <button type="button" class="btn btn-dark manage-penalty-fitness-hub btn-sm btn-equal"
                                                            data-action="apply" data-id="{{ $fitnessHubBooking->id }}" data-bs-toggle="tooltip"
                                                            title="Apply Penalty">
                                                            <i class="fa-solid fa-coins"></i>
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
            {{ $FitnessHubBookings->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div> 

    <script>
        const userRole = {{ auth()->user()->role_id }};
    </script>
    @include('backend.modal.fitness-hubs.fitness-hubs-booking-modal')
    @include('backend.modal.fitness-hubs.fitness-hub-slot-checking-modal')

@endsection