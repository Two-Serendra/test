<div class="modal fade" id="ausiViewResultModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">

            <!-- Header -->
            <div class="modal-header bg-primary text-white">
                <div>
                    <h5 class="mb-1 text-white">
                        <i class="fa-solid fa-clipboard-check me-2"></i>
                        AUSI Booking Details
                    </h5>

                    <small class="opacity-75">
                        Transaction #
                        <span id="view_transaction_no"></span>
                    </small>
                </div>

                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <!-- Status -->
                <div class="text-center mb-4">
                    <span id="view_booking_status"></span>
                </div>

                <!-- Resident Card -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-light fw-bold">
                        <i class="fa-solid fa-user me-2 text-primary"></i>
                        Resident Information
                    </div>

                    <div class="card-body">

                        <div class="row g-3">

                            <!-- <div class="col-md-6">
                                <small class="text-muted d-block">Resident</small>
                                <div class="fw-semibold fs-6" id="view_name"></div>
                            </div> -->

                            <div class="col-md-3">
                                <small class="text-muted d-block">Unit</small>
                                <div class="fw-semibold" id="view_unit"></div>
                            </div>

                            <div class="col-md-3">
                                <small class="text-muted d-block">Resident Type</small>
                                <div id="view_resident_type" class="fw-semibold"></div>
                            </div>

                        </div>

                    </div>
                </div>

                <!-- Booking -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-light fw-bold">
                        <i class="fa-solid fa-calendar-days me-2 text-primary"></i>
                        Booking Schedule
                    </div>

                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-6">
                                <small class="text-muted d-block">
                                    Date
                                </small>

                                <div class="fw-semibold" id="view_booking_date"></div>
                            </div>

                            <div class="col-md-6">
                                <small class="text-muted d-block">
                                    Time Slot
                                </small>

                                <div class="fw-semibold" id="view_time_slot"></div>
                            </div>

                        </div>

                    </div>
                </div>

                <!-- Inspection -->
                <div class="card border-0 shadow-sm mb-3">

                    <div class="card-header bg-light fw-bold">
                        <i class="fa-solid fa-list-check me-2 text-primary"></i>
                        Inspection Results
                    </div>

                    <div class="card-body">

                        <div id="viewInspectionResults"></div>

                    </div>

                </div>

                <!-- Remarks -->
                <div class="card border-0 shadow-sm">

                    <div class="card-header bg-light fw-bold">
                        <i class="fa-solid fa-comment-dots me-2 text-primary"></i>
                        Remarks
                    </div>

                    <div class="card-body">

                        <div class="border-start border-4 border-primary bg-light rounded p-3 fst-italic">
                            <span id="view_remarks"></span>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </div>
</div>