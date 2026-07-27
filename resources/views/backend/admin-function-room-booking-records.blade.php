@extends('layouts.backend')

<style>
    /* Center table content */
    table th,
    table td {
        text-align: center;
        vertical-align: middle;
        background: #fff;
        /* Ensure all cells have solid background */
    }

    /* Scrollable wrapper */
    .table-responsive {
        overflow-x: auto;
        position: relative;
        /* Needed for proper stacking */
    }

    /* Sticky header row */
    #functionRoomBookingsTable thead th {
        position: sticky;
        top: 0;
        background: #f8f9fa !important;
        /* Force light gray header */
        z-index: 200;
        /* Make sure header is above everything else */
    }

    #functionRoomBookingsTable td.sticky-action-col {
        position: sticky;
        right: 0;
        background: #fff !important;
        z-index: 150;
        border-left: 2px solid #dee2e6;
        box-shadow: -4px 0 6px rgba(0, 0, 0, 0.15);
    }

    /* Sticky Actions column (header) */
    #functionRoomBookingsTable th.sticky-action-col {
        position: sticky;
        right: 0;
        background: #f8f9fa !important;
        z-index: 250;
        border-left: 2px solid #dee2e6;
        box-shadow: -4px 0 6px rgba(0, 0, 0, 0.2);
    }
</style>
@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <form action="" method="GET" id="searchFormFunctionRoomBookings" class="d-flex align-items-center mb-0"
                        style="max-width: 250px;">
                        <div class="input-group text-dark w-100">
                            <span class="input-group-text">
                                <i class='bx bx-search-alt text-dark'></i>

                            </span>
                            <input type="text" name="searchFunctionRoomBooking"
                                value="{{ $searchFunctionRoomBooking ?? '' }}" id="searchInputFunctionRoomBooking"
                                class="form-control" placeholder="Transaction/Unit/Name" autocomplete="off">
                        </div>
                    </form>
                    @if(in_array(auth()->user()->role_id, [1, 6, 7]))
                            <div class="">
                                <button type="button" class="btn btn-primary badge DownloadFunctionRoomBookingRecords"
                                    id="addFunctionRoom">
                                    <i class="menu-icon tf-icons bx bx-download"></i>Download
                                </button>
                            </div>
                        </div>
                    @endif
            </div>

            <div class="table-responsive mt-3">
                <table id="functionRoomBookingsTable" class="table table-bordered table-striped">
                    <thead class="table-light text-center">
                        <tr>
                            {{-- Booking Info --}}
                            <th>Transac No</th>
                            <th>Unit No</th>
                            <th>Name</th>
                            <th>Resident Type</th>
                            <th>Function Room</th>
                            <th>Purpose of Event</th>

                            {{-- Schedule --}}
                            <th>Date</th>
                            <th>Start</th>
                            <th>End</th>

                            {{-- Details --}}
                            <th>Contact</th>
                            <th>Pax</th>
                            <th>Base Rate</th>
                            <th>Discount</th>
                            <th>Final Rate</th>
                            <th>Payment</th>
                            <th>Status</th>

                            @if(in_array(auth()->user()->role_id, [1, 3, 7, 6]))
                                <th>Supplier</th>
                            @endif
                            {{-- Concierge Approval --}}
                            @if(in_array(auth()->user()->role_id, [1, 6]))
                                <th>Concierge Status</th>
                                <th>Concierge Remarks</th>
                                <th>Concierge By</th>
                                <th>Concierge At</th>
                            @else
                                <th>Concierge</th>
                                <th>Concierge Remarks</th>
                            @endif

                            {{-- Admin Approval --}}
                            @if(in_array(auth()->user()->role_id, [1, 6]))
                                <th>Admin Status</th>
                                <th>Admin Remarks</th>
                                <th>Admin By</th>
                                <th>Admin At</th>
                            @else
                                <th>Admin</th>
                                <th>Admin Remarks</th>
                            @endif
                            {{-- Finance Approval --}}
                            @if(in_array(auth()->user()->role_id, [1, 6]))
                                <th>Finance Status</th>
                                <th>Finance Remarks</th>
                                <th>Finance By</th>
                                <th>Finance At</th>
                            @else
                                <th>Finance</th>
                                <th>Finance Remarks</th>
                            @endif
                            {{-- Engineer Approval --}}
                            @if(in_array(auth()->user()->role_id, [1, 6]))
                                <th>Engineering Status</th>
                                <th>Engineering Remarks</th>
                                <th>Engineering By</th>
                                <th>Engineering At</th>
                            @else
                                <th>Engineering</th>
                                <th>Engineering Remarks</th>
                            @endif


                            @if(in_array(auth()->user()->role_id, [1, 6]))
                                <th>Manager Status</th>
                                <th>Manager Remarks</th>
                                <th>Manager By</th>
                                <th>Manager At</th>
                            @else
                                <th>Manager</th>
                                <th>Manager Remarks</th>
                            @endif



                            <th>Created</th>
                            <th>Updated</th>
                            <th class="sticky-action-col text-center">Actions</th>



                            {{-- Other --}}

                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($functionRoomBookingRecords as $functionRoomBookingRecord)
                            <tr>
                                {{-- functionRoomBookingRecord Info --}}
                                <td>{{ $functionRoomBookingRecord->transaction_no }}</td>
                                <td>{{ $functionRoomBookingRecord->unit_no }}</td>
                                <td>{{ $functionRoomBookingRecord->user->name }}</td>
                                <td> @if($functionRoomBookingRecord->resident_type === 'TENANT')
                                    <span class="badge bg-danger">TENANT</span>
                                @elseif($functionRoomBookingRecord->resident_type === 'OWNER')
                                        <span class="badge bg-primary">OWNER</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $functionRoomBookingRecord->resident_type }}</span>
                                    @endif
                                </td>
                                <td>{{ $functionRoomBookingRecord->functionRoom->function_room_name }}</td>
                                <td>{{ $functionRoomBookingRecord->purpose_of_event }}</td>
                                {{-- Schedule --}}
                                <td>{{ $functionRoomBookingRecord->function_room_booking_date }}</td>
                                <td>{{\Carbon\Carbon::parse($functionRoomBookingRecord->event_start_time)->format('h:i A') }}
                                </td>
                                <td>{{\Carbon\Carbon::parse($functionRoomBookingRecord->event_end_time)->format('h:i A') }}</td>
                                {{-- Details --}}
                                <td>{{ $functionRoomBookingRecord->contact_number }}</td>
                                <td>{{ $functionRoomBookingRecord->pax }}</td>
                                <td>
                                    ₱{{ number_format($functionRoomBookingRecord->base_rate, 2) }}
                                </td>
                                <td>
                                    @if($functionRoomBookingRecord->discount > 0)
                                        <span
                                            class="badge bg-success">{{ rtrim(rtrim(number_format($functionRoomBookingRecord->discount, 2), '0'), '.') }}%</span>
                                    @else
                                        <span class="badge bg-secondary">0%</span>
                                    @endif
                                </td>
                                <td>{{ $functionRoomBookingRecord->final_rate }}</td>
                                <td>{{ $functionRoomBookingRecord->payment_mode }}</td>
                                <td>
                                    @if ($functionRoomBookingRecord->booking_status == 0)
                                        <span class="badge bg-warning">Incomplete</span>
                                    @elseif ($functionRoomBookingRecord->booking_status == 1)
                                        <span class="badge bg-success">Completed</span>
                                    @elseif ($functionRoomBookingRecord->booking_status == 2)
                                        <span class="badge bg-danger">Cancelled</span>
                                    @else
                                        <span class="badge bg-secondary">Unknown</span>
                                    @endif
                                </td>
                                @if(in_array(auth()->user()->role_id, [1, 3, 7, 6]))
                                    <td>
                                        @if($functionRoomBookingRecord->has_suppliers)
                                            <span class="badge bg-success">Yes</span>
                                        @else
                                            <span class="badge bg-secondary">No</span>
                                        @endif
                                    </td>
                                @endif

                                {{-- Concierge Approval --}}
                                @if(in_array(auth()->user()->role_id, [1, 6]))
                                    <td>
                                        @if($functionRoomBookingRecord->concierge_approval === 1)
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($functionRoomBookingRecord->concierge_approval === 2)
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-warning">Incomplete</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($functionRoomBookingRecord->concierge_remarks)
                                            {{ $functionRoomBookingRecord->concierge_remarks }}
                                        @else
                                            <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($functionRoomBookingRecord->conciergeApprover && $functionRoomBookingRecord->conciergeApprover->name)
                                            {{ $functionRoomBookingRecord->conciergeApprover->name }}
                                        @else
                                            <span class="badge bg-warning">Incomplete</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($functionRoomBookingRecord->concierge_action_at)
                                            {{ $functionRoomBookingRecord->concierge_action_at }}
                                        @else
                                            <span class="badge bg-warning">Incomplete</span>
                                        @endif
                                    </td>
                                @else
                                    <td>
                                        @if($functionRoomBookingRecord->concierge_approval === 1)
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($functionRoomBookingRecord->concierge_approval === 2)
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-warning">Incomplete</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($functionRoomBookingRecord->concierge_remarks)
                                            {{ $functionRoomBookingRecord->concierge_remarks }}
                                        @else
                                            <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </td>
                                @endif


                                {{-- Admin Approval --}}
                                @if(in_array(auth()->user()->role_id, [1, 6]))
                                    {{-- Admin columns for super roles --}}
                                    @if(!$functionRoomBookingRecord->authorization_file)
                                        {{-- No authorization file: Admin not needed --}}
                                        <td><span class="badge bg-secondary">N/A</span></td>
                                        <td>
                                            @if($functionRoomBookingRecord->admin_remarks)
                                                {{ $functionRoomBookingRecord->admin_remarks }}
                                            @else
                                                <span class="badge bg-secondary">N/A</span>
                                            @endif
                                        </td>
                                        <td><span class="badge bg-secondary">N/A</span></td>
                                        <td><span class="badge bg-secondary">N/A</span></td>
                                    @else
                                        {{-- With authorization file: normal approval flow --}}
                                        <td>
                                            @if($functionRoomBookingRecord->admin_approval === 1)
                                                <span class="badge bg-success">Approved</span>
                                            @elseif($functionRoomBookingRecord->admin_approval === 2)
                                                <span class="badge bg-danger">Rejected</span>
                                            @else
                                                <span class="badge bg-warning">Incomplete</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($functionRoomBookingRecord->admin_remarks)
                                                {{ $functionRoomBookingRecord->admin_remarks }}
                                            @else
                                                <span class="badge bg-secondary">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($functionRoomBookingRecord->adminApprover && $functionRoomBookingRecord->adminApprover->name)
                                                {{ $functionRoomBookingRecord->adminApprover->name }}
                                            @else
                                                <span class="badge bg-warning">Incomplete</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($functionRoomBookingRecord->admin_action_at)
                                                {{ $functionRoomBookingRecord->admin_action_at }}
                                            @else
                                                <span class="badge bg-warning">Incomplete</span>
                                            @endif
                                        </td>
                                    @endif
                                @else
                                    {{-- For other roles --}}
                                    @if(!$functionRoomBookingRecord->authorization_file)
                                        <td><span class="badge bg-secondary">N/A</span></td>
                                        <td>
                                            @if($functionRoomBookingRecord->admin_remarks)
                                                {{ $functionRoomBookingRecord->admin_remarks }}
                                            @else
                                                <span class="badge bg-secondary">N/A</span>
                                            @endif
                                        </td>
                                    @else
                                        <td>
                                            @if($functionRoomBookingRecord->admin_approval === 1)
                                                <span class="badge bg-success">Approved</span>
                                            @elseif($functionRoomBookingRecord->admin_approval === 2)
                                                <span class="badge bg-danger">Rejected</span>
                                            @else
                                                <span class="badge bg-warning">Incomplete</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($functionRoomBookingRecord->admin_remarks)
                                                {{ $functionRoomBookingRecord->admin_remarks }}
                                            @else
                                                <span class="badge bg-secondary">N/A</span>
                                            @endif
                                        </td>
                                    @endif
                                @endif



                                {{-- Finance Approval --}}
                                @if(in_array(auth()->user()->role_id, [1, 6]))
                                    <td>
                                        @if($functionRoomBookingRecord->finance_approval === 1)
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($functionRoomBookingRecord->finance_approval === 2)
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-warning">Incomplete</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($functionRoomBookingRecord->finance_remarks)
                                            {{ $functionRoomBookingRecord->finance_remarks }}
                                        @else
                                            <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($functionRoomBookingRecord->financeApprover && $functionRoomBookingRecord->financeApprover->name)
                                            {{ $functionRoomBookingRecord->financeApprover->name }}
                                        @else
                                            <span class="badge bg-warning">Incomplete</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($functionRoomBookingRecord->finance_action_at)
                                            {{ $functionRoomBookingRecord->finance_action_at }}
                                        @else
                                            <span class="badge bg-warning">Incomplete</span>
                                        @endif
                                    </td>
                                @else
                                    <td>
                                        @if($functionRoomBookingRecord->finance_approval === 1)
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($functionRoomBookingRecord->finance_approval === 2)
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-warning">Incomplete</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($functionRoomBookingRecord->finance_remarks)
                                            {{ $functionRoomBookingRecord->finance_remarks }}
                                        @else
                                            <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </td>
                                @endif


                                {{-- Engineering Approval --}}
                                @if(in_array(auth()->user()->role_id, [1, 6]))
                                    {{-- Engineering columns for super roles --}}
                                    @if(!$functionRoomBookingRecord->has_suppliers)
                                        {{-- No suppliers: Engineering not needed --}}
                                        <td><span class="badge bg-secondary">N/A</span></td>
                                        <td>
                                            @if($functionRoomBookingRecord->engineering_remarks)
                                                {{ $functionRoomBookingRecord->engineering_remarks }}
                                            @else
                                                <span class="badge bg-secondary">N/A</span>
                                            @endif
                                        </td>
                                        <td><span class="badge bg-secondary">N/A</span></td>
                                        <td><span class="badge bg-secondary">N/A</span></td>
                                    @else
                                        {{-- With suppliers: normal approval flow --}}
                                        <td>
                                            @if($functionRoomBookingRecord->engineering_approval === 1)
                                                <span class="badge bg-success">Approved</span>
                                            @elseif($functionRoomBookingRecord->engineering_approval === 2)
                                                <span class="badge bg-danger">Rejected</span>
                                            @else
                                                <span class="badge bg-warning">Incomplete</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($functionRoomBookingRecord->engineering_remarks)
                                                {{ $functionRoomBookingRecord->engineering_remarks }}
                                            @else
                                                <span class="badge bg-secondary">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($functionRoomBookingRecord->engineeringApprover && $functionRoomBookingRecord->engineeringApprover->name)
                                                {{ $functionRoomBookingRecord->engineeringApprover->name }}
                                            @else
                                                <span class="badge bg-warning">Incomplete</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($functionRoomBookingRecord->engineering_action_at)
                                                {{ $functionRoomBookingRecord->engineering_action_at }}
                                            @else
                                                <span class="badge bg-warning">Incomplete</span>
                                            @endif
                                        </td>
                                    @endif
                                @else
                                    {{-- For other roles --}}
                                    @if(!$functionRoomBookingRecord->has_suppliers)
                                        <td><span class="badge bg-secondary">N/A</span></td>
                                        <td>
                                            @if($functionRoomBookingRecord->engineering_remarks)
                                                {{ $functionRoomBookingRecord->engineering_remarks }}
                                            @else
                                                <span class="badge bg-secondary">N/A</span>
                                            @endif
                                        </td>
                                    @else
                                        <td>
                                            @if($functionRoomBookingRecord->engineering_approval === 1)
                                                <span class="badge bg-success">Approved</span>
                                            @elseif($functionRoomBookingRecord->engineering_approval === 2)
                                                <span class="badge bg-danger">Rejected</span>
                                            @else
                                                <span class="badge bg-warning">Incomplete</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($functionRoomBookingRecord->engineering_remarks)
                                                {{ $functionRoomBookingRecord->engineering_remarks }}
                                            @else
                                                <span class="badge bg-secondary">N/A</span>
                                            @endif
                                        </td>
                                    @endif
                                @endif

                                {{-- Manager Approval --}}
                                @if(in_array(auth()->user()->role_id, [1, 6]))
                                    <td>
                                        @if($functionRoomBookingRecord->manager_approval === 1)
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($functionRoomBookingRecord->manager_approval === 2)
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-warning">Incomplete</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($functionRoomBookingRecord->manager_remarks)
                                            {{ $functionRoomBookingRecord->manager_remarks }}
                                        @else
                                            <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($functionRoomBookingRecord->managerApprover && $functionRoomBookingRecord->managerApprover->name)
                                            {{ $functionRoomBookingRecord->managerApprover->name }}
                                        @else
                                            <span class="badge bg-warning">Incomplete</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($functionRoomBookingRecord->manager_action_at)
                                            {{ $functionRoomBookingRecord->manager_action_at }}
                                        @else
                                            <span class="badge bg-warning">Incomplete</span>
                                        @endif
                                    </td>
                                @else
                                    <td>
                                        @if($functionRoomBookingRecord->manager_approval === 1)
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($functionRoomBookingRecord->manager_approval === 2)
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-warning">Incomplete</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($functionRoomBookingRecord->manager_remarks)
                                            {{ $functionRoomBookingRecord->manager_remarks }}
                                        @else
                                            <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </td>
                                @endif

                                <td>{{ $functionRoomBookingRecord->created_at }}</td>
                                <td>{{ $functionRoomBookingRecord->updated_at }}</td>
                                <td class="sticky-action-col">
                                    <button class="btn btn-sm btn-info view-records-booking-btn mb-2"
                                        data-id="{{ $functionRoomBookingRecord->id }}" style="width: 60px;">
                                        View
                                    </button>

                                    <!-- @if(auth()->user()->role_id == 6)
                                                        <button class="btn btn-sm btn-warning edit-booking-btn" data-id="{{ $functionRoomBookingRecord->id }}"
                                                            style="width: 60px;">
                                                            Edit
                                                        </button>
                                                    @endif -->
                                </td>


                            </tr>
                        @empty
                            <tr>
                                <td colspan="25" class="text-center">No Bookings Found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="pagination-container-function-room-booking">
            {{ $functionRoomBookingRecords->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>

    <div id="global-loading">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    <script>
        const USER_ROLE = {{ auth()->user()->role_id }};
    </script>

    @include('backend.modal.admin-view-function-room-details-records-modal')
    @include('backend.modal.admin-download-function-room-booking-records-modal')
    @push('scripts')
        <script src="{{ asset('assets/backend/js/records.js')}}"></script>
    @endpush
@endsection