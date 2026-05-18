<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="POST" action="{{ route('pest.control.booking.store') }}" enctype="multipart/form-data"
            id="userPestControlNewBooking" class="needs-validation" novalidate>
            @csrf

            <div class="row mb-3">
                <div class="col-md-6 mb-3 mb-md-0">
                    <label class="form-label">Select Residence <span class="required">*</span></label>
                    <select name="resident_id_pest_control" class="form-select" required>
                        <option value="">-- Select Residence --</option>
                        @foreach ($residences as $residence)
                            <option value="{{ $residence->id }}">
                                {{ ucfirst($residence->resident_type) }} - Unit {{ $residence->unit_no }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Date <span class="required">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class='bx bx-calendar'></i></span>
                        <input type="text" class="form-control bg-white text-dark" id="PestControlBookingDate"
                            name="booking_date" required>
                    </div>
                </div>
            </div>

            <div class="row g-2 mb-3">
                <label class="form-label">Select Time Slot <span class="required">*</span></label>
                @php
                    $slots = [
                        '8:00 AM - 9:00 AM',
                        '9:00 AM - 10:00 AM',
                        '10:00 AM - 11:00 AM',
                        '11:00 AM - 12:00 NN',
                        '1:00 PM - 2:00 PM',
                        '2:00 PM - 3:00 PM',
                        '3:00 PM - 4:00 PM',
                        '4:00 PM - 5:00 PM',
                    ];
                @endphp

                @foreach ($slots as $slot)
                    <div class="col-md-12 col-sm-12">
                        <input type="radio" class="btn-check pest-control-booking-slot" name="booking_time_slot"
                            id="slot{{ $loop->index }}" value="{{ $slot }}" data-slot="{{ $slot }}" required>
                        <label class="btn btn-outline-primary w-100" for="slot{{ $loop->index }}">
                            {{ $slot }}
                        </label>
                    </div>
                @endforeach
            </div>

            <div class="d-grid">
                <button type="submit" form="userPestControlNewBooking" id="saveUserPestControlBtn"
                    class="btn btn-primary d-flex align-items-center justify-content-center customBtn"
                    style="min-width: 100px; height: 38px;">
                    <span class="btn-text">SUBMIT</span>
                </button>

            </div>
        </form>
    </div>
</div>