<div class="modal fade" id="ausiViewResultModal">
    <div class="modal-dialog modal-lg modal-fullscreen-sm-down">
        <div class="modal-content">


            <div class="modal-header bg-primary text-white">

                <h5 class="modal-title">
                    AUSI Booking Details
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal">
                </button>

            </div>


            <div class="modal-body">


                <div class="text-center mb-4">

                    <h5 class="fw-bold">
                        <span id="view_transaction_no"></span>
                    </h5>

                    <span id="view_booking_status"></span>

                </div>


                <div class="card shadow-sm border-0 mb-3">

                    <div class="card-body">

                        <h6 class="text-primary fw-bold">
                            <i class="fa-solid fa-user me-2"></i>
                            Resident Information
                        </h6>


                        <div class="info-row">
                            <span>Name</span>
                            <strong id="view_name"></strong>
                        </div>


                        <div class="info-row">
                            <span>Unit</span>
                            <strong id="view_unit"></strong>
                        </div>


                        <div class="info-row">
                            <span>Resident Type</span>
                            <span id="view_resident_type"></span>
                        </div>


                    </div>

                </div>



                <div class="card shadow-sm border-0 mb-3">

                    <div class="card-body">


                        <h6 class="text-primary fw-bold">
                            <i class="fa-solid fa-calendar me-2"></i>
                            Booking Information
                        </h6>


                        <div class="info-row">
                            <span>Date</span>
                            <strong id="view_booking_date"></strong>
                        </div>


                        <div class="info-row">
                            <span>Time</span>
                            <strong id="view_time_slot"></strong>
                        </div>


                    </div>

                </div>



                <div class="card shadow-sm border-0 mb-3">

                    <div class="card-body">


                        <h6 class="text-primary fw-bold">
                            <i class="fa-solid fa-clipboard-check me-2"></i>
                            Inspection Results
                        </h6>


                        <div id="viewInspectionResults"></div>


                    </div>

                </div>



                <div class="card shadow-sm border-0">

                    <div class="card-body">

                        <h6 class="text-primary fw-bold">
                            Remarks
                        </h6>

                        <div class="bg-light rounded p-3">

                            <span id="view_remarks">
                            </span>

                        </div>

                    </div>

                </div>


            </div>


        </div>
    </div>
</div>