@extends    ('layouts.frontend')
@section('content')
    <div class="container py-5">
        <div class="card mb-3 shadow-sm" style="border-radius: 4px;">
            <h5 class="card-header">My Bookings</h5>
            <div class="card-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Booking Type</label>
                        <select id="booking_type" class="form-select">
                            <option value="function_room" selected>Function Room</option>
                            <option value="amenity">Amenities</option>

                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Select Unit</label>
                        <select id="unit_no" class="form-select">
                            @foreach ($allResidences as $allResidence)
                                <option value="{{ $allResidence->unit_no }}" {{ $selectedUnit == $allResidence->unit_no ? 'selected' : '' }}>
                                    {{ $allResidence->unit_no }} ({{ ucfirst($allResidence->resident_type) }})
                                </option>
                            @endforeach
                        </select>
                    </div>


                </div>

                <div id="bookingTableContainer">
                    @if($bookingType === 'function_room')
                        @include('frontend.resident-function-room-booking-table')
                    @elseif($bookingType === 'amenity')
                        @include('frontend.resident-activity-booking-table')
                    @endif


                </div>
                @include('frontend.modal.user-view-function-room-booking-details-modal')
               
            </div>
        </div>
    </div>
@endsection