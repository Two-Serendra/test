<div class="modal fade" id="AddBookingAdmin" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-3">New Booking</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.new.booking.activities') }}" id="AdminNewBooking" method="POST"
                    enctype="multipart/form-data" class="AdminNewBooking needs-validation" novalidate>
                    @csrf
                    <div class="row">
                        <input type="hidden" id="bookingType" name="booking_type">

                        <ul class="nav nav-tabs mb-3" id="bookingTabs">
                            <li class="nav-item">
                                <a class="nav-link active booking-tab" id="advanced-tab" data-bs-toggle="tab" href="#"
                                    data-value="Advanced Booking">Advanced Booking</a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link booking-tab" id="24hrs" data-bs-toggle="tab" href="#"
                                    data-value="24hrs">24 Hrs</a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link booking-tab" id="walkin-tab" data-bs-toggle="tab" href="#"
                                    data-value="Walk-in">Walk-in</a>
                            </li>
                        </ul>
                        <div class="col-6">
                            <div class="mb-3">
                                <label for="activitySelectBooking" class="form-label">Select Activity *</label>
                                <input type="hidden" id="amenityIdBooking" name="amenity_id">
                                <select class="form-select" id="activitySelectBooking" name="activity_id" required>
                                    <option value="" disabled selected>Activity</option>
                                    @foreach($activities as $activity)

                                        @php
                                            $isAmenityInactive = optional($activity->amenity)->amenity_status == 0;
                                        @endphp

                                        <option value="{{ $activity->id }}" data-amenity-id="{{ $activity->amenity_id }}"
                                            data-start-time="{{ \Carbon\Carbon::parse($activity->start_time)->format('H:i') }}"
                                            data-end-time="{{ \Carbon\Carbon::parse($activity->end_time)->format('H:i') }}"
                                            data-activity-space="{{ $activity->activity_space }}" {{ $isAmenityInactive ? 'disabled' : '' }}>

                                            {{ strtoupper($activity->activity_name) }}
                                            {{ $isAmenityInactive ? ' (Unavailable - ' . ($activity->amenity->amenity_remarks ?? 'Maintenance') . ')' : '' }}

                                        </option>

                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Please select an amenity</div>
                            </div>

                            <div class="mb-3 position-relative">
                                <label for="dateFieldBooking" class="form-label">Date *</label>
                                <input type="text" id="dateFieldBooking" class="form-control" name="booking_date"
                                    required>
                                <i class="fa-regular fa-calendar position-absolute"
                                    style="top: 73%; right: 8px; transform: translateY(-50%);"></i>
                            </div>

                            <div class="mb-3 d-flex align-items-end gap-2">
                                <div class="flex-grow-1">
                                    <label for="unitNumber" class="form-label">Unit *</label>
                                    <input type="text" class="form-control" id="unitNumber" name="unit" oninput="
                                this.value = this.value
                                    .toUpperCase()
                                    .replace(/[^0-9A-I]/g,'');
                                " required>
                                    <div class="invalid-feedback">Format: 304A</div>
                                </div>
                                <button type="button" class="btn btn-outline-secondary checkUnit">Check</button>

                                <span id="unitStatus" class="mt-1 text-muted">0/0</span>
                            </div>

                            <div class="mb-3 position-relative">
                                <label for="booking_start_time" class="form-label">Time Start *</label>
                                <select class="form-control" id="booking_start_time" name="booking_start_time" required>

                                </select>
                                <i class="fa-regular fa-clock position-absolute"
                                    style="top: 73%; right: 8px; transform: translateY(-50%);"></i>
                            </div>

                            <div class="mb-3 position-relative">
                                <label for="booking_end_time" class="form-label">Time Finish *</label>
                                <select class="form-control" id="booking_end_time" name="booking_end_time" required>

                                </select>
                                <i class="fa-regular fa-clock position-absolute"
                                    style="top: 73%; right: 8px; transform: translateY(-50%);"></i>
                            </div>
                        </div>


                        <div class="col-6">

                            <div class="mb-3">
                                <label for="selectResidentType" class="form-label">Resident Type *</label>
                                <select class="form-select" id="selectResidentType" name="selectResidentType" required>
                                    <option value="Owner">Owner</option>
                                    <option value="Tenant">Tenant</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="name" class="form-label">Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                                <div class="invalid-feedback">Required</div>
                            </div>

                            <div class="mb-3">
                                <label for="contact_number" class="form-label">Contact</label>
                                <input type="number" class="form-control" id="contact_number" name="contact_number">
                                <div class="invalid-feedback">Required</div>
                            </div>


                            <input type="hidden" class="selected_slots" id="selectedSlotsInput" name="selected_slots">


                            <div class="mb-3 position-relative">
                                <label for="" class="form-label">Slots *</label>
                                <div id="availableSlotsContainer" class="d-flex flex-wrap gap-2">
                                </div>

                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                @php
                    use Carbon\Carbon;

                    $now = Carbon::now();
                    $isFriday10AM = $now->isFriday() && $now->format('H:i') >= '10:00';
                    $isRole6 = auth()->user()->role_id == 6;

                    $isDisabled = $isRole6 && !$isFriday10AM;
                @endphp
                
                <span id="submitWrapper" data-bs-toggle="tooltip"
                    title="{{ $isDisabled ? 'Available every Friday at 10:00 AM' : '' }}"
                    style="display: inline-block;">
                    <button type="submit" form="AdminNewBooking" id="saveActivityBookingBtn"
                        class="btn btn-primary d-flex align-items-center justify-content-center"
                        style="min-width: 100px; height: 38px;" {{ $isDisabled ? 'disabled' : '' }}>

                        <span class="btn-text">Submit</span>
                    </button>
                </span>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="bookingEdit" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white position-relative">

                <!-- Centered Logo -->
                <div class="w-100 d-flex align-items-center justify-content-center">
                    <img src="{{ asset('assets/images/TWO SERENDRA LOGO PNG (White).png') }}"
                        style="height: 60px; width: auto;" alt="2serendra" />
                </div>

                <!-- Close Button (force right alignment) -->
                <button type="button" class="btn-close position-absolute end-0 top-50 translate-middle-y me-2"
                    data-bs-dismiss="modal" aria-label="Close">
                </button>

            </div>
            <div class="modal-body px-4 py-3">

                <!-- Header -->
                <div class="text-center mb-4">
                    <h5 class="fw-bold mb-1">Booking Details</h5>
                    <small class="text-muted">Reference #: <span id="detail-transaction-no"></span></small>
                </div>

                <!-- STATUS + PENALTY (Highlight Section) -->
                <div class="d-flex justify-content-between align-items-center mb-4 p-3 rounded bg-light">
                    <div>
                        <small class="text-muted d-block">Status</small>
                        <span id="detail-booking-status"></span>
                    </div>

                    <div class="text-end">
                        <small class="text-muted d-block">Penalty</small>
                        <span id="detail-penalty-display" class="fw-semibold"></span>
                    </div>
                </div>

                <div class="row g-3">

                    <!-- Guest Info Card -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <h6 class="fw-semibold text-primary mb-3">Guest Information</h6>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Name</span>
                                <span id="detail-name" class="fw-semibold text-end"></span>
                            </div>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Unit</span>
                                <span id="detail-unit" class="fw-semibold"></span>
                            </div>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Resident Type</span>
                                <span id="detail-resident-type"></span>
                            </div>

                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Contact</span>
                                <span id="detail-contact" class="fw-semibold"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Booking Info Card -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <h6 class="fw-semibold text-primary mb-3">Booking Information</h6>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Activity</span>
                                <span id="detail-activity-name" class="fw-semibold text-end"></span>
                            </div>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Booking Type</span>
                                <span id="detail-booking-type" class="fw-semibold"></span>
                            </div>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Date</span>
                                <span id="detail-booking-date" class="fw-semibold"></span>
                            </div>

                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Time</span>
                                <span id="detail-start-time" class="fw-semibold"></span>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="activityCalendarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

            <!-- HEADER -->
            <div class="modal-header bg-primary text-white position-relative py-3">

                <div class="w-100 text-center">
                    <img src="{{ asset('assets/images/TWO SERENDRA LOGO PNG (White).png') }}" style="height: 50px;"
                        alt="logo">
                </div>

                <button type="button" class="btn-close position-absolute end-0 top-50 translate-middle-y me-2"
                    data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>

            <!-- BODY -->
            <div class="modal-body bg-light p-4">

                <input type="hidden" id="edit_id">

                <!-- TITLE CARD -->
                <div class="bg-white p-3 rounded-4 shadow-sm border-start border-4 border-primary mb-4">
                    <small class="text-muted">Activity</small>
                    <div id="calendar_activity_name" class="fs-5 fw-bold text-primary"></div>
                </div>

                <!-- GRID -->
                <div class="row g-4">

                    <!-- LEFT CARD -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body p-4">

                                <h6 class="text-uppercase text-muted small mb-3">Resident Info</h6>

                                <div class="mb-3">
                                    <small class="text-muted">Unit</small>
                                    <div class="fw-semibold" id="calendar_unit">N/A</div>
                                </div>

                                <div class="mb-3">
                                    <small class="text-muted">Name</small>
                                    <div class="fw-semibold" id="calendar_name">N/A</div>
                                </div>

                                <div class="mb-0">
                                    <small class="text-muted">Contact</small>
                                    <div class="fw-semibold" id="calendar_contact_number">N/A</div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- RIGHT CARD -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body p-4">

                                <h6 class="text-uppercase text-muted small mb-3">Schedule</h6>

                                <div class="mb-3">
                                    <small class="text-muted">Date</small>
                                    <div class="fw-semibold" id="calendar_booking_date">N/A</div>
                                </div>

                                <div class="mb-3">
                                    <small class="text-muted">Start Time</small>
                                    <div class="fw-semibold" id="calendar_booking_start_time">N/A</div>
                                </div>

                                <div class="mb-0">
                                    <small class="text-muted">End Time</small>
                                    <div class="fw-semibold" id="calendar_booking_end_time">N/A</div>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </div>
</div>