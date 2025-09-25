@extends('layouts.backend')

<style>
    table th,
    table td {
        text-align: center;
        vertical-align: middle;
    }
</style>
@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <form action="{{ route('search.function.room.booking.records') }}" method="GET"
                        id="searchFormFunctionRoomBookingRecords" class="d-flex align-items-center">
                        <div class="input-group" style="width: 200px;">
                            <span class="input-group-text">
                                <i class="fa-solid fa-magnifying-glass fa-sm"></i>
                            </span>
                            <input type="text" name="searchFunctionRoomBookingRecords"
                                value="{{ $searchFunctionRoomBookingRecords ?? '' }}"
                                id="searchInputFunctionRoomBookingRecords" class="form-control" placeholder="Unit"
                                autocomplete="off">
                        </div>
                    </form>

                    @if(in_array(auth()->user()->role_id, [1, 7,8]))
                        <div class="">
                            <button type="button" class="btn btn-primary badge DownloadFunctionRoomBookingRecords">
                               <i class="menu-icon tf-icons bx bx-download"></i>Download
                            </button>
                        </div>

                    @endif
                </div>
            </div>

            <div class="table-responsive">
                <table id="functionRoomTable" class="table table-bordered table-striped">
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
                            <th>Payment</th>
                            <th>Status</th>

                            @if(in_array(auth()->user()->role_id, [1, 3, 7]))
                                <th>Supplier</th>
                            @endif
                            {{-- Admin Approval --}}
                            @if(in_array(auth()->user()->role_id, [1, 3, 7]))
                                <th>Admin</th>
                                <th>By</th>
                                <th>At</th>
                            @endif

                            {{-- Finance Approval --}}
                            @if(in_array(auth()->user()->role_id, [1, 3, 5, 7]))
                                <th>Finance</th>
                                <th>By</th>
                                <th>At</th>
                            @endif

                            {{-- Engineer Approval --}}
                            @if(in_array(auth()->user()->role_id, [1, 3, 6, 7]))
                                <th>Engr</th>
                                <th>By</th>
                                <th>At</th>

                            @endif

                            @if(in_array(auth()->user()->role_id, [1, 3, 6, 7]))
                                {{-- Manger Approval --}}
                                <th>Manager</th>
                                <th>By</th>
                                <th>At</th>

                                <th>Created</th>
                                <th>Updated</th>
                                <th>Actions</th>
                            @endif


                            {{-- Other --}}

                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($functionRoomBookingRecords as $functionRoomBookingRecord)
                            <tr>
                                {{-- Booking Info --}}
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

                                <td>{{ $functionRoomBookingRecord->formatted_start_time }}</td>
                                <td>{{ $functionRoomBookingRecord->formatted_end_time }}</td>
                                {{-- Details --}}
                                <td>{{ $functionRoomBookingRecord->contact_number }}</td>
                                <td>{{ $functionRoomBookingRecord->pax }}</td>
                                <td>{{ $functionRoomBookingRecord->payment_mode }}</td>
                                <td>
                                    @if($functionRoomBookingRecord->booking_status == 1)
                                        <span class="badge bg-success">Completed</span>
                                    @elseif($functionRoomBookingRecord->booking_status == 2)
                                        <span class="badge bg-danger">Cancelled</span>
        
                                    @else
                                        <span class="badge bg-secondary">Unknown</span>
                                    @endif
                                </td>
                                @if(in_array(auth()->user()->role_id, [1, 3, 7]))
                                    <td>
                                        @if($functionRoomBookingRecord->has_suppliers)
                                            <span class="badge bg-success">Yes</span>
                                        @else
                                            <span class="badge bg-secondary">No</span>
                                        @endif
                                    </td>
                                @endif

                                {{-- Admin Approval--}}

                                @if(in_array(auth()->user()->role_id, [1, 3, 5, 7]))
                                    <td>
                                        @if(!$functionRoomBookingRecord->authorization_file)
                                            <span class="badge bg-secondary">N/A</span>
                                        @else
                                            @if($functionRoomBookingRecord->admin_approval)
                                                <span class="badge bg-success">Approved</span>
                                            @else
                                                <span class="badge bg-warning">Waiting</span>
                                            @endif
                                        @endif
                                    </td>

                                    <td>
                                        @if(!$functionRoomBookingRecord->authorization_file)
                                            <span class="badge bg-secondary">N/A</span>
                                        @elseif($functionRoomBookingRecord->admin_approved_by)
                                            <span class="">{{ $functionRoomBookingRecord->adminApprover->name ?? 'Unknown' }}</span>
                                        @else
                                            <span class="badge bg-warning">Waiting</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if(!$functionRoomBookingRecord->authorization_file)
                                            <span class="badge bg-secondary">N/A</span>
                                        @else
                                            @if($functionRoomBookingRecord->admin_approved_at)
                                                <span>
                                                    {{ $functionRoomBookingRecord->admin_approved_at }}</span>
                                            @else
                                                <span class="badge bg-warning">Waiting</span>
                                            @endif
                                        @endif
                                    </td>
                                @endif


                                {{-- Finance Approval--}}

                                @if(in_array(auth()->user()->role_id, [1, 3, 5, 7]))
                                    <td>
                                        @if($functionRoomBookingRecord->finance_approval)
                                            <span class="badge bg-success">Approved</span>
                                        @else
                                            <span class="badge bg-warning">Waiting</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($functionRoomBookingRecord->finance_approved_by)
                                            <span class="">{{ $functionRoomBookingRecord->financeApprover->name ?? 'Unknown' }}</span>
                                        @else
                                            <span class="badge bg-warning">Waiting</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($functionRoomBookingRecord->finance_approved_at)
                                            <span class="">{{ $functionRoomBookingRecord->finance_approved_at }}</span>
                                        @else
                                            <span class="badge bg-warning">Waiting</span>
                                        @endif
                                    </td>
                                @endif

                                @if(in_array(auth()->user()->role_id, [1, 3, 7]))
                                    {{-- Engineering Approval--}}
                                    <td>
                                        @if($functionRoomBookingRecord->suppliers && $functionRoomBookingRecord->suppliers->count() > 0)
                                            <span
                                                class="badge {{ $functionRoomBookingRecord->engineering_approval ? 'bg-success' : 'bg-warning' }}">
                                                {{ $functionRoomBookingRecord->engineering_approval ? 'Approved' : 'Waiting' }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if($functionRoomBookingRecord->suppliers && $functionRoomBookingRecord->suppliers->count() > 0)
                                            <span class=" {{ $functionRoomBookingRecord->engineeringApprover}}">
                                                {{ $functionRoomBookingRecord->engineeringApprover->name ?? 'Waiting' }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if($functionRoomBookingRecord->suppliers && $functionRoomBookingRecord->suppliers->count() > 0)
                                            @if($functionRoomBookingRecord->engineering_approved_at)
                                                <span>{{ $functionRoomBookingRecord->getOriginal('engineering_approved_at') }}</span>
                                            @else
                                                <span class="badge bg-warning">Waiting</span>
                                            @endif
                                        @else
                                            <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </td>



                                    {{-- Manager Approval--}}
                                    <td>
                                        @if($functionRoomBookingRecord->manager_approval)
                                            <span class="badge bg-success">Approved</span>
                                        @else
                                            <span class="badge bg-warning">Waiting</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($functionRoomBookingRecord->manager_approved_by)
                                            <span class="">{{ $functionRoomBookingRecord->managerApprover->name ?? 'Unknown' }}</span>
                                        @else
                                            <span class="badge bg-warning">Waiting</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($functionRoomBookingRecord->manager_approved_at)
                                            <span>{{ $functionRoomBookingRecord->getOriginal('manager_approved_at') }}</span>
                                        @else
                                            <span class="badge bg-warning">Waiting</span>
                                        @endif
                                    </td> 


                                @endif

                                <td>{{ $functionRoomBookingRecord->created_at }}</td>
                                <td>{{ $functionRoomBookingRecord->updated_at }}</td>
                                <td>
                                    <button class="btn btn-sm btn-info view-functionRoomBookingRecord-btn"
                                        data-id="{{ $functionRoomBookingRecord->id }}">
                                        View
                                    </button>
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

        <div class="pagination-container">
            {{ $functionRoomBookingRecords->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
     @include('backend.modal.admin-download-function-room-booking-records-modal')

@endsection