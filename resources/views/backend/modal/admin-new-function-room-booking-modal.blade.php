<!-- Booking Modal -->
<div class="modal fade" id="adminFunctionRoomBookingModal" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.booking.store') }}" method="POST" id="adminFunctionRoomNewBooking"
                enctype="multipart/form-data" class="needs-validation" novalidate>
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title text-primary">Function Room Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-7 border-end pe-4">
                            <div class="row g-3 mb-4">
                                <div class="col-md-12">
                                    <label class="form-label">Select Function Room <span
                                            class="required">*</span></label>
                                    <select id="functionRoomSelect" name="function_room_id" class="form-select"
                                        required>
                                        <option value="">-- Select Function Room --</option>
                                        @foreach ($functionRooms as $room)
                                            <option value="{{ $room->id }}"
                                                data-capacity="{{ $room->function_room_capacity }}">
                                                {{ $room->function_room_name }} (Capacity:
                                                {{ $room->function_room_capacity }})
                                            </option>
                                        @endforeach
                                    </select>


                                    <div id="linkedRoomOptionWrapper" class="mt-2 d-none">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="adminBookLinkedRoom"
                                                name="book_linked_rooms" value="1">
                                            <label class="form-check-label fw-bold text-primary"
                                                id="linkedRoomLabel"></label>
                                        </div>
                                    </div>

                                </div>

                                <input type="hidden" id="roomCapacity" value="0">

                                <div class="col-md-12">
                                    <label class="form-label">Select Resident <span class="required">*</span></label>
                                    <select id="residentSelectAdmin" name="user_id" class="form-select select2" required
                                        style="width: 100%;">
                                        <option value="">-- Search Resident --</option>
                                    </select>
                                </div>


                                <input type="hidden" name="unit_no" id="unitNoAdmin">
                                <input type="hidden" name="resident_type" id="residentTypeAdmin">

                                <div class="col-md-6">
                                    <label class="form-label">Date <span class="required">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class='bx bx-calendar'></i></span>
                                        <input type="text" class="form-control bg-white text-dark"
                                            id="adminFunctionRoomBookingDate" name="function_room_booking_date"
                                            required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Purpose of Event <span class="required">*</span></label>
                                    <input type="text" class="form-control" name="purpose_of_event" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Start Time <span class="required">*</span></label>
                                    <input type="time" name="event_start_time" id="startTime" class="form-control"
                                        step="3600" min="00:00" max="23:00" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">No. of Pax <span class="required">*</span></label>
                                    <input type="number" class="form-control" name="pax" min="1" id="paxInput" required>
                                    <small class="text-danger d-none" id="capacityError">
                                        Max capacity is <span id="maxCapacityText">0</span>
                                    </small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">End Time <span class="required">*</span></label>
                                    <input type="time" name="event_end_time" id="endTime" class="form-control"
                                        step="3600" min="00:00" max="23:00" required>
                                    <small class="text-danger d-none" id="timeError">End time must be later than start
                                        time.</small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Contact Number <span class="required">*</span></label>
                                    <input type="number" class="form-control" name="contact_number" required>
                                </div>
                            </div>

                            <label class="form-label">Payment Mode <span class="required">*</span></label>
                            <div class="mb-3">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="admin_payment_mode"
                                        id="Charge to Account" value="Charge to Account" required>
                                    <label class="form-check-label" for="Charge to Account">Charge to Account</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="admin_payment_mode"
                                        id="Advance Payment" value="Advance Payment" required>
                                    <label class="form-check-label" for="Advance Payment">Advance Payment</label>
                                </div>
                            </div>

                            <div id="adminAuthorizationUploadWrapper" class="col-md-12 d-none mb-4">
                                <label id="adminAuthorizationLabel" class="form-label text-danger fw-bold"></label>
                                <input type="file" name="admin_authorization_file" class="form-control"
                                    accept="image/*,.pdf">
                                <small id="adminAuthorizationNote" class="text-muted"></small>
                            </div>

                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="adminHasSuppliers"
                                    name="admin_has_suppliers">
                                <label class="form-check-label" for="adminHasSuppliers">I have Catering / Delivery
                                    Permit</label>
                            </div>
                            <div id="adminSupplierSection" class="col-12 mt-2 d-none">
                                <h6 class="fw-bold">Supplier(s)</h6>
                                <div id="adminSuppliersWrapper">
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
                                <button type="button" class="btn btn-sm btn-primary mt-2 customBtn"
                                    id="adminAddSupplier">+
                                    Supplier</button>
                            </div>
                        </div>

                        <div class="col-lg-5 ps-4">
                            <h6 class="fw-bold text-secondary mb-3">Add-ons</h6>
                            <div class="row g-3">
                                @foreach($addOns as $addOn)
                                    <div class="col-12">
                                        <div class="border rounded p-2 h-100">
                                            <div class="form-check">
                                                <input type="hidden" name="addons[{{ $addOn->id }}][selected]" value="0">
                                                <input type="checkbox" class="form-check-input adminAddOnsFields"
                                                    name="addons[{{ $addOn->id }}][selected]" value="1"
                                                    id="addon{{ $addOn->id }}" data-max="{{ $addOn->qty }}">
                                                <label class="form-check-label fw-bold" for="addon{{ $addOn->id }}">
                                                    {{ $addOn->item }} (₱{{ number_format($addOn->price, 2) }})
                                                </label>
                                            </div>
                                            <div class="mt-2">
                                                <label class="form-label small mb-1">Quantity</label>
                                                <input type="number" name="addons[{{ $addOn->id }}][qty]"
                                                    class="form-control form-control-sm addonQty" min="1" value="0"
                                                    max="{{ $addOn->qty }}" data-addon-id="{{ $addOn->id }}" disabled>
                                            </div>
                                            <small class="text-muted d-block mt-2" id="addonAvailable{{ $addOn->id }}">
                                                Available: {{ $addOn->qty }}
                                            </small>
                                        </div>
                                    </div>
                                @endforeach

                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary customBtn text-white"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="adminFunctionRoomNewBooking" id="saveUserFunctionRoomBtn"
                        class="btn btn-primary d-flex align-items-center justify-content-center customBtn"
                        style="min-width: 100px; height: 38px;">
                        <span class="btn-text">Submit</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>