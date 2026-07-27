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
                    @if(in_array(auth()->user()->role_id, [1, 6]))
                            <div class="">
                                <button type="button" class="btn btn-primary badge AdminAddFunctionRoomBooking"
                                    id="addFunctionRoom">
                                    <i class='bx bx-plus'></i> Booking
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
                            <th>Discount Remarks</th>
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
                        @forelse ($functionRoomBookings as $booking)
                            <tr>
                                {{-- Booking Info --}}
                                <td>{{ $booking->transaction_no }}</td>
                                <td>{{ $booking->unit_no }}</td>
                                <td>{{ $booking->user->name ?? 'N/A' }}</td>
                                <td> @if($booking->resident_type === 'TENANT')
                                    <span class="badge bg-danger">TENANT</span>
                                @elseif($booking->resident_type === 'OWNER')
                                        <span class="badge bg-primary">OWNER</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $booking->resident_type }}</span>
                                    @endif
                                </td>
                                <td>{{ $booking->functionRoom->function_room_name }}</td>
                                <td>{{ $booking->purpose_of_event }}</td>
                                {{-- Schedule --}}
                                <td>{{ $booking->function_room_booking_date }}</td>
                                <td>{{\Carbon\Carbon::parse($booking->event_start_time)->format('h:i A') }}</td>
                                <td>{{\Carbon\Carbon::parse($booking->event_end_time)->format('h:i A') }}</td>
                                {{-- Details --}}
                                <td>{{ $booking->contact_number }}</td>
                                <td>{{ $booking->pax }}</td>
                                <td>
                                    ₱{{ number_format($booking->base_rate, 2) }}
                                </td>
                                <td>
                                    @if($booking->discount > 0)
                                        <span
                                            class="badge bg-success">{{ rtrim(rtrim(number_format($booking->discount, 2), '0'), '.') }}%</span>
                                    @else
                                        <span class="badge bg-secondary">0%</span>
                                    @endif
                                </td>
                                <td> @if ( $booking->discount_remarks)
                                    {{ $booking->discount_remarks }}</td>
                                    @else
                                    <span class="badge bg-secondary">N/A</span>
                                    @endif

                                <td>{{ $booking->final_rate }}</td>
                                <td>{{ $booking->payment_mode }}</td>
                                <td>
                                    @if($booking->booking_status == 1)
                                        <span class="badge bg-success">Confirmed</span>
                                    @elseif($booking->booking_status == 2)
                                        <span class="badge bg-danger">Cancelled</span>

                                    @else
                                        <span class="badge bg-warning">Waiting</span>
                                    @endif
                                </td>
                                @if(in_array(auth()->user()->role_id, [1, 3, 7, 6]))
                                    <td>
                                        @if($booking->has_suppliers)
                                            <span class="badge bg-success">Yes</span>
                                        @else
                                            <span class="badge bg-secondary">No</span>
                                        @endif
                                    </td>
                                @endif

                                {{-- Concierge Approval --}}
                                @if(in_array(auth()->user()->role_id, [1, 6]))
                                    <td>
                                        @if($booking->concierge_approval === 1)
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($booking->concierge_approval === 2)
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-warning">Waiting</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($booking->concierge_remarks)
                                            {{ $booking->concierge_remarks }}
                                        @else
                                            <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($booking->conciergeApprover && $booking->conciergeApprover->name)
                                            {{ $booking->conciergeApprover->name }}
                                        @else
                                            <span class="badge bg-warning">Waiting</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($booking->concierge_action_at)
                                            {{ $booking->concierge_action_at }}
                                        @else
                                            <span class="badge bg-warning">Waiting</span>
                                        @endif
                                    </td>
                                @else
                                    <td>
                                        @if($booking->concierge_approval === 1)
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($booking->concierge_approval === 2)
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-warning">Waiting</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($booking->concierge_remarks)
                                            {{ $booking->concierge_remarks }}
                                        @else
                                            <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </td>
                                @endif


                                {{-- Admin Approval --}}
                                @if(in_array(auth()->user()->role_id, [1, 6]))
                                    {{-- Admin columns for super roles --}}
                                    @if(!$booking->authorization_file)
                                        {{-- No authorization file: Admin not needed --}}
                                        <td><span class="badge bg-secondary">N/A</span></td>
                                        <td>
                                            @if($booking->admin_remarks)
                                                {{ $booking->admin_remarks }}
                                            @else
                                                <span class="badge bg-secondary">N/A</span>
                                            @endif
                                        </td>
                                        <td><span class="badge bg-secondary">N/A</span></td>
                                        <td><span class="badge bg-secondary">N/A</span></td>
                                    @else
                                        {{-- With authorization file: normal approval flow --}}
                                        <td>
                                            @if($booking->admin_approval === 1)
                                                <span class="badge bg-success">Approved</span>
                                            @elseif($booking->admin_approval === 2)
                                                <span class="badge bg-danger">Rejected</span>
                                            @else
                                                <span class="badge bg-warning">Waiting</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($booking->admin_remarks)
                                                {{ $booking->admin_remarks }}
                                            @else
                                                <span class="badge bg-secondary">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($booking->adminApprover && $booking->adminApprover->name)
                                                {{ $booking->adminApprover->name }}
                                            @else
                                                <span class="badge bg-warning">Waiting</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($booking->admin_action_at)
                                                {{ $booking->admin_action_at }}
                                            @else
                                                <span class="badge bg-warning">Waiting</span>
                                            @endif
                                        </td>
                                    @endif
                                @else
                                    {{-- For other roles --}}
                                    @if(!$booking->authorization_file)
                                        <td><span class="badge bg-secondary">N/A</span></td>
                                        <td>
                                            @if($booking->admin_remarks)
                                                {{ $booking->admin_remarks }}
                                            @else
                                                <span class="badge bg-secondary">N/A</span>
                                            @endif
                                        </td>
                                    @else
                                        <td>
                                            @if($booking->admin_approval === 1)
                                                <span class="badge bg-success">Approved</span>
                                            @elseif($booking->admin_approval === 2)
                                                <span class="badge bg-danger">Rejected</span>
                                            @else
                                                <span class="badge bg-warning">Waiting</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($booking->admin_remarks)
                                                {{ $booking->admin_remarks }}
                                            @else
                                                <span class="badge bg-secondary">N/A</span>
                                            @endif
                                        </td>
                                    @endif
                                @endif



                                {{-- Finance Approval --}}
                                @if(in_array(auth()->user()->role_id, [1, 6]))
                                    <td>
                                        @if($booking->finance_approval === 1)
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($booking->finance_approval === 2)
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-warning">Waiting</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($booking->finance_remarks)
                                            {{ $booking->finance_remarks }}
                                        @else
                                            <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($booking->financeApprover && $booking->financeApprover->name)
                                            {{ $booking->financeApprover->name }}
                                        @else
                                            <span class="badge bg-warning">Waiting</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($booking->finance_action_at)
                                            {{ $booking->finance_action_at }}
                                        @else
                                            <span class="badge bg-warning">Waiting</span>
                                        @endif
                                    </td>
                                @else
                                    <td>
                                        @if($booking->finance_approval === 1)
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($booking->finance_approval === 2)
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-warning">Waiting</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($booking->finance_remarks)
                                            {{ $booking->finance_remarks }}
                                        @else
                                            <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </td>
                                @endif


                                {{-- Engineering Approval --}}
                                @if(in_array(auth()->user()->role_id, [1, 6]))
                                    {{-- Engineering columns for super roles --}}
                                    @if(!$booking->has_suppliers)
                                        {{-- No suppliers: Engineering not needed --}}
                                        <td><span class="badge bg-secondary">N/A</span></td>
                                        <td>
                                            @if($booking->engineering_remarks)
                                                {{ $booking->engineering_remarks }}
                                            @else
                                                <span class="badge bg-secondary">N/A</span>
                                            @endif
                                        </td>
                                        <td><span class="badge bg-secondary">N/A</span></td>
                                        <td><span class="badge bg-secondary">N/A</span></td>
                                    @else
                                        {{-- With suppliers: normal approval flow --}}
                                        <td>
                                            @if($booking->engineering_approval === 1)
                                                <span class="badge bg-success">Approved</span>
                                            @elseif($booking->engineering_approval === 2)
                                                <span class="badge bg-danger">Rejected</span>
                                            @else
                                                <span class="badge bg-warning">Waiting</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($booking->engineering_remarks)
                                                {{ $booking->engineering_remarks }}
                                            @else
                                                <span class="badge bg-secondary">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($booking->engineeringApprover && $booking->engineeringApprover->name)
                                                {{ $booking->engineeringApprover->name }}
                                            @else
                                                <span class="badge bg-warning">Waiting</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($booking->engineering_action_at)
                                                {{ $booking->engineering_action_at }}
                                            @else
                                                <span class="badge bg-warning">Waiting</span>
                                            @endif
                                        </td>
                                    @endif
                                @else
                                    {{-- For other roles --}}
                                    @if(!$booking->has_suppliers)
                                        <td><span class="badge bg-secondary">N/A</span></td>
                                        <td>
                                            @if($booking->engineering_remarks)
                                                {{ $booking->engineering_remarks }}
                                            @else
                                                <span class="badge bg-secondary">N/A</span>
                                            @endif
                                        </td>
                                    @else
                                        <td>
                                            @if($booking->engineering_approval === 1)
                                                <span class="badge bg-success">Approved</span>
                                            @elseif($booking->engineering_approval === 2)
                                                <span class="badge bg-danger">Rejected</span>
                                            @else
                                                <span class="badge bg-warning">Waiting</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($booking->engineering_remarks)
                                                {{ $booking->engineering_remarks }}
                                            @else
                                                <span class="badge bg-secondary">N/A</span>
                                            @endif
                                        </td>
                                    @endif
                                @endif

                                {{-- Manager Approval --}}
                                @if(in_array(auth()->user()->role_id, [1, 6]))
                                    <td>
                                        @if($booking->manager_approval === 1)
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($booking->manager_approval === 2)
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-warning">Waiting</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($booking->manager_remarks)
                                            {{ $booking->manager_remarks }}
                                        @else
                                            <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($booking->managerApprover && $booking->managerApprover->name)
                                            {{ $booking->managerApprover->name }}
                                        @else
                                            <span class="badge bg-warning">Waiting</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($booking->manager_action_at)
                                            {{ $booking->manager_action_at }}
                                        @else
                                            <span class="badge bg-warning">Waiting</span>
                                        @endif
                                    </td>
                                @else
                                    <td>
                                        @if($booking->manager_approval === 1)
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($booking->manager_approval === 2)
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-warning">Waiting</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($booking->manager_remarks)
                                            {{ $booking->manager_remarks }}
                                        @else
                                            <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </td>
                                @endif

                                <td>{{ $booking->created_at }}</td>
                                <td>{{ $booking->updated_at }}</td>
                                <td class="sticky-action-col">
                                    <button class="btn btn-sm btn-info view-booking-btn mb-2" data-id="{{ $booking->id }}"
                                        style="width: 60px;">
                                        View
                                    </button>

                                    @if(auth()->user()->role_id == 6)
                                        <button class="btn btn-sm btn-warning edit-booking-btn" data-id="{{ $booking->id }}"
                                            style="width: 60px;">
                                            Edit
                                        </button>
                                    @endif
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
            {{ $functionRoomBookings->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>

    <div id="global-loading">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    
    @include('backend.modal.admin-view-function-room-booking-details-modal')
    @include('backend.modal.admin-edit-function-room-booking-details-modal')
    @include('backend.modal.admin-new-function-room-booking-modal')
     @push('scripts')
        <script>
        const USER_ROLE = {{ auth()->user()->role_id }};
        </script>
        <script src="{{ asset('assets/backend/js/function-room-booking.js')}}"></script>

    @endpush

@endsection