<div class="modal fade" id="viewFitnessHubRecordModal" data-bs-backdrop="static" data-bs-keyboard="false">
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
                                <span class="text-muted">Fitness Hub</span>
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

<div class="modal fade" id="fitnessHubCalendarModal" tabindex="-1" aria-hidden="true">
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
                    <small class="text-muted">Fitness Hub</small>
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