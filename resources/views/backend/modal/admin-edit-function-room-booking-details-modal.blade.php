<!-- Admin Edit Booking Modal -->
<div class="modal fade" id="adminEditBookingModal" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <form id="adminEditBookingForm" action="{{ route('admin.update.function.room.booking') }}" method="POST"
                enctype="multipart/form-data" class="needs-validation" novalidate>
                @csrf
                <input type="hidden" name="booking_id" id="booking_id">
                <input type="hidden" name="function_room_id" id="function_room_id">

                <div class="modal-header">
                    <h5 class="modal-title text-primary" id="editFunctionRoomName"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-7 border-end pe-4">
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-secondary">Transaction No.</label>
                                    <p id="editTransactionNo" class="form-control-plaintext mb-3 text-dark"></p>
                                </div>

                                <input type="hidden" name="book_linked_rooms" id="editLinkedRoomInput" value="0">
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-secondary">Resident</label>
                                    <p id="editResidentDisplay" class="form-control-plaintext text-dark"></p>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Date <span class="required">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class='bx bx-calendar'></i></span>
                                        <input type="text" class="form-control bg-white text-dark"
                                            id="editFunctionRoomBookingDate" name="function_room_booking_date" required>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div id="editLinkedRoomWrapper" class="form-check mb-3 d-none">
                                        <input type="checkbox" id="editLinkedRoomCheckbox" class="form-check-input">
                                        <label id="editLinkedRoomLabel"
                                            class="form-check-label fw-bold text-primary"></label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Purpose of Event <span class="required">*</span></label>
                                    <input type="text" class="form-control" name="purpose_of_event"
                                        id="editPurposeOfEvent" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Discount (%)</label>
                                    <input type="number" class="form-control" name="discount" id="editDiscount" min="0"
                                        max="100" step="0.01" placeholder="">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Discount Remarks</label>
                                    <input type="text" class="form-control" name="discount_remarks"
                                        id="editDiscountRemarks">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Start Time <span class="required">*</span></label>
                                    <input type="time" name="event_start_time" id="editStartTime" class="form-control"
                                        step="3600" min="00:00" max="23:00" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">No. of Pax <span class="required">*</span></label>
                                    <input type="number" class="form-control" name="pax" id="editPaxInput" min="1"
                                        required>
                                    <small class="text-danger d-none" id="capacityError">Max capacity is <span
                                            id="editRoomCapacity">0</span></small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">End Time <span class="required">*</span></label>
                                    <input type="time" name="event_end_time" id="editEndTime" class="form-control"
                                        step="3600" min="00:00" max="23:00" required>
                                    <small class="text-danger d-none" id="timeError">End time must be later than start
                                        time.</small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Contact Number <span class="required">*</span></label>
                                    <input type="number" class="form-control" name="contact_number"
                                        id="editContactNumber" required>
                                </div>
                            </div>

                            <label class="form-label">Payment Mode <span class="required">*</span></label>
                            <div class="mb-3" id="editPaymentModeWrapper">
                                <!-- Radio buttons populated via AJAX -->
                            </div>

                            <div id="editAuthorizationUploadWrapper" class="col-md-12 d-none mb-4">
                                <label id="editAuthorizationLabel" class="form-label text-danger fw-bold"></label>

                                <!-- Preview / Existing File -->
                                <div id="authorizationPreview" class="mb-2 d-none">
                                    <a href="#" target="_blank" class="btn btn-sm btn-outline-primary"
                                        id="authorizationViewLink">
                                        View Current Authorization
                                    </a>
                                    <small class="text-muted d-block mt-2">You can upload a new file to replace
                                        this.</small>
                                </div>

                                <!-- Upload New File -->
                                <input type="file" name="authorization_file" class="form-control" accept="image/*,.pdf">
                                <small id="editAuthorizationNote" class="text-muted"></small>
                            </div>


                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="hasSuppliers" name="has_suppliers">
                                <label class="form-check-label" for="hasSuppliers">I have Catering / Other
                                    Supplier</label>
                            </div>
                            <div id="supplierSection" class="col-12 mt-2 d-none">
                                <h6 class="fw-bold">Supplier(s)</h6>
                                <div id="suppliersWrapper">
                                    <div class="row g-2 supplier-item mb-2">
                                        <div class="col-md-4">
                                            <input type="text" name="suppliers[0][name]" class="form-control"
                                                placeholder="Name">
                                        </div>
                                        <div class="col-md-6">
                                            <input type="file" name="suppliers[0][attachment]" class="form-control"
                                                accept="image/*,.pdf">
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-primary mt-2 customBtn" id="addSupplier">+
                                    Supplier</button>
                            </div>
                        </div>

                        <div class="col-lg-5 ps-4">
                            <h6 class="fw-bold text-secondary mb-3">Add-ons</h6>
                            <div class="row g-3" id="editAddonsWrapper">
                                <!-- Add-ons populated via AJAX -->
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" form="adminEditBookingForm" id="updateFunctionRoomBookingBtn"
                        class="btn btn-primary d-flex align-items-center justify-content-center"
                        style="min-width: 100px; height: 38px;">
                        <span class="btn-text">Update</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>