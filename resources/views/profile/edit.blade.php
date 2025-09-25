@extends('layouts.frontend')

@section('content')
    <div class="container py-5">
        {{-- Success alert for profile update --}}
        <div class="card mb-3 shadow-sm" style="border-radius: 4px;">
            <h5 class="card-header">Profile Information</h5>
            <div class="card-body">
                @if (session('status') === 'profile-updated')
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Profile updated successfully.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- Profile Update Form --}}
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('patch')
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" name="email" type="email" class="form-control" value="{{ auth()->user()->email }}"
                            disabled>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', auth()->user()->name) }}" disabled>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !auth()->user()->hasVerifiedEmail())
                        <div class="mb-3 text-warning">
                            Your email address is unverified.
                            <button form="send-verification" class="btn btn-link p-0">Click here to resend verification
                                email.</button>
                        </div>
                    @endif

                    <!-- <button type="submit" class="btn btn-primary">Save Changes</button> -->
                </form>
            </div>
        </div>


        <div class="card mb-3 shadow-sm" style="border-radius: 4px;">
            <h5 class="card-header">Residence</h5>
            <div class="card-body">
                @if ($residences->count())
                    <div class="table-responsive">
                        <table class="table table-bordered" id="userResidenceTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Unit No</th>
                                    <th>Resident Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($residences as $residence)
                                    <tr>
                                        <td>{{ $residence->unit_no }}</td>
                                        <td>
                                            @if (strtolower($residence->resident_type) === 'owner')
                                                <span
                                                    class="badge bg-primary badge-forge">{{ ucfirst($residence->resident_type) }}</span>
                                            @elseif (strtolower($residence->resident_type) === 'tenant')
                                                <span
                                                    class="badge bg-danger badge-forge">{{ ucfirst($residence->resident_type) }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($residence->resident_type) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                       {{ $residences->links('pagination::bootstrap-5') }}
                    </div>
                @else
                    <div class="text-muted">No residence records available.</div>
                @endif
            </div>
        </div>


        <div class="card mb-3 shadow-sm" style="border-radius: 4px;">
            <h5 class="card-header">My Bookings</h5>
            <div class="card-body">

                @if ($allResidences->count())
                    <!-- Residence Dropdown -->
                    <form method="GET" action="{{ route('profile.edit') }}" class="mb-3">
                        <label for="unit_no" class="form-label">Select Unit</label>
                        <select name="unit_no" id="unit_no" class="form-select" onchange="this.form.submit()">
                            @foreach ($allResidences as $allResidence)
                                <option value="{{ $allResidence->unit_no }}" {{ $selectedUnit == $allResidence->unit_no ? 'selected' : '' }}>
                                    {{ $allResidence->unit_no }} ({{ ucfirst($allResidence->resident_type) }})
                                </option>
                            @endforeach
                        </select>
                    </form>
                @endif

                @if ($bookings->count())
                    <div class="table-responsive">
                        <table class="table table-bordered text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Function Room</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bookings as $booking)
                                    <tr>
                                        <td>{{ $booking->transaction_no }}</td>
                                        <td>{{ $booking->functionRoom->function_room_name ?? 'N/A' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($booking->function_room_booking_date)->format('F d, Y') }}</td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($booking->event_start_time)->format('g:i A') }} -
                                            {{ \Carbon\Carbon::parse($booking->event_end_time)->format('g:i A') }}
                                        </td>
                                        <td>
                                            @if ($booking->booking_status == 0)
                                                <span class="badge bg-warning text-white badge-forge">Waiting</span>
                                            @elseif ($booking->booking_status == 1)
                                                <span class="badge bg-success badge-forge">Confirmed</span>
                                            @else
                                                <span class="badge bg-danger badge-forge">Cancelled</span>
                                            @endif
                                        </td>
                                         <td>
                                         <button class="btn btn-sm btn-info function-room-booking-details badge-forge text-white" data-id="{{ $booking->id }}">
                                             View
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div> 
                    <div class="pagination-container">
                    {{ $bookings->appends(['unit_no' => $selectedUnit])->links('vendor.pagination.bootstrap-4') }}
                    </div>
                @else
                    <div class="text-muted">No bookings found for this unit.</div>
                @endif
            </div>
        </div>


        {{-- Password Update Section --}}
        <div class="card shadow-sm" style="border-radius: 4px;">
            <h5 class="card-header">Update Password</h5>
            <div class="card-body">
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    @method('put')

                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input id="current_password" name="current_password" type="password"
                            class="form-control @error('current_password', 'updatePassword') is-invalid @enderror">
                        @error('current_password', 'updatePassword')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input id="password" name="password" type="password"
                            class="form-control @error('password', 'updatePassword') is-invalid @enderror">
                        @error('password', 'updatePassword')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password"
                            class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror">
                        @error('password_confirmation', 'updatePassword')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary btn-forge">Update Password</button>

                    @if (session('status') === 'password-updated')
                        <div class="alert alert-success mt-3 mb-0">
                            Password updated successfully.
                        </div>
                    @endif
                </form>
            </div>
        </div>

        <form id="send-verification" method="POST" action="{{ route('verification.send') }}">
            @csrf
        </form>
    </div>

     <div id="global-loading">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    @include('frontend.modal.user-view-function-room-booking-details-modal')
@endsection