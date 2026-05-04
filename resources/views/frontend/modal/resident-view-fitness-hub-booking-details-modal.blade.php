<style>
    .custom-label {
        font-size: 1rem;
        font-weight: 500;
        color: #222;
    }

    .custom-p {
        font-size: 1rem;
        /* Increase font size */
        font-weight: 600;
        /* Make it bolder */
        color: #222;
        /* Darker text */
    }
</style>

<div class="modal fade" id="residentFitnessHubBookingDetailsModal" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header bg-primary text-white justify-content-center position-relative">
                <h5 class="modal-title m-0">
                    <img src="{{ asset('assets/images/TWO SERENDRA LOGO PNG (White).png') }}"
                        style="height: 60px; width: auto;" alt="2serendra" />
                </h5>
                <button type="button" class="btn-close btn-close-white position-absolute end-0 me-3"
                    data-bs-dismiss="modal"></button>
            </div>

            <!-- Body -->
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
                                <span class="text-muted">Name:</span>
                                <span id="detail-name" class="fw-semibold text-end"></span>
                            </div>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Unit:</span>
                                <span id="detail-unit" class="fw-semibold"></span>
                            </div>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Resident Type:</span>
                                <span id="detail-resident-type"></span>
                            </div>

                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Contact:</span>
                                <span id="detail-contact" class="fw-semibold"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Booking Info Card -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <h6 class="fw-semibold text-primary mb-3">Booking Information</h6>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Fitness Hub:</span>
                                <span id="detail-fitness-hub-name" class="fw-semibold text-end"></span>
                            </div>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Booking Type:</span>
                                <span id="detail-booking-type" class="fw-semibold"></span>
                            </div>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Date</span>
                                <span id="detail-booking-date" class="fw-semibold"></span>
                            </div>

                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Time:</span>
                                <span id="detail-start-time" class="fw-semibold"></span>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-sm customBtn text-white" id="cancelFitnessHubBookingBtn">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>