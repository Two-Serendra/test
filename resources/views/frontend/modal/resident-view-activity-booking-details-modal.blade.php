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

<div class="modal fade" id="residentActivityBookingDetailsModal" data-bs-backdrop="static" data-bs-keyboard="false">
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
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label custom-label">Booking ID:</label>
                            <p id="detail-transaction-no" class="form-control-plaintext custom-p"></p>
                        </div>

                        <div class="mb-3 align-items-center">
                            <label class="form-label custom-label">Resident Type:</label>
                            <p id="detail-resident-type" class="form-control-plaintext custom-p"></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label custom-label">Unit:</label>
                            <p id="detail-unit" class="form-control-plaintext custom-p"></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label custom-label">Name:</label>
                            <p id="detail-name" class="form-control-plaintext custom-p"></p>
                        </div>



                        <div class="mb-3">
                            <label class="form-label custom-label">Contact:</label>
                            <p id="detail-contact" class="form-control-plaintext custom-p"></p>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label custom-label">Booking Status:</label>
                            <p id="detail-booking-status" class="form-control-plaintext custom-p"></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label custom-label">Booking Type:</label>
                            <p id="detail-booking-type" class="form-control-plaintext custom-p"></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label custom-label">Activity:</label>
                            <p id="detail-activity-name" class="form-control-plaintext custom-p"></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label custom-label">Booking Date:</label>
                            <p id="detail-booking-date" class="form-control-plaintext custom-p"></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label custom-label">Time:</label>
                            <p id="detail-start-time" class="form-control-plaintext custom-p"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-sm customBtn text-white" id="cancelAmenityBookingBtn">
                   Cancel
                </button>
            </div>
        </div>
    </div>
</div>