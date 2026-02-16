<div class="modal fade" id="userViewfunctionRoomBookingDetailsModal" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header bg-primary d-flex justify-content-center position-relative">
                <h5 class="modal-title m-0">
                    <img src="{{ asset('assets/images/TWO SERENDRA LOGO PNG (White).png') }}"
                        style="height: 60px; width: auto; object-fit: contain;" alt="2serendra" />
                </h5>
                <button type="button" class="btn-close btn-close-white position-absolute end-0 me-3"
                    data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <!-- Booking Info -->
                <div class="modal-body">

                    <!-- Booking Info -->
                    <div class="border-bottom pb-2 mb-3">
                        
                        <div class="row mb-2">
                            <div class="col-4 fw-bold">Transaction No:</div>
                            <div class="col-8" id="detail-transaction-no"></div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-4 fw-bold">Name:</div>
                            <div class="col-8" id="detail-name"></div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-4 fw-bold">Unit:</div>
                            <div class="col-8" id="detail-unit"></div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-4 fw-bold">Resident Type:</div>
                            <div class="col-8" id="detail-resident-type"></div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-4 fw-bold">Function Room(s):</div>
                            <div class="col-8" id="detail-function-rooms"></div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-4 fw-bold">Status:</div>
                            <div class="col-8" id="detail-status"></div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-4 fw-bold">Purpose:</div>
                            <div class="col-8" id="detail-purpose"></div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-4 fw-bold">Payment Mode:</div>
                            <div class="col-8" id="detail-payment-mode"></div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-4 fw-bold">Contact:</div>
                            <div class="col-8" id="detail-contact"></div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-4 fw-bold">Authorization File:</div>
                            <div class="col-8" id="detail-authorization"></div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-4 fw-bold">Suppliers:</div>
                            <div class="col-8" id="detail-suppliers"></div>
                        </div>

                    </div>

                    <div id="detail-room-list"></div> <!-- Breakdown Table -->
                    <h6 class="fw-bold">Breakdown</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th class="text-center">Qty/Hrs</th>
                                    <th class="text-end">Rate</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody id="detail-breakdown">
                                <tr>
                                    <td colspan="4" class="text-muted text-center">No charges</td>
                                </tr>
                            </tbody>
                            <tfoot id="detail-breakdown-footer"></tfoot>
                        </table>
                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm customBtn text-white" data-bs-dismiss="modal">
                        Close
                    </button>
                    <button type="button" id="cancel-booking-btn"
                        class="btn btn-danger btn-sm customBtn text-white d-none">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>