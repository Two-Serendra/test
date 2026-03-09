<!-- Booking Modal -->
<div class="modal fade" id="functionRoomBookingModal" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('booking.store') }}" method="POST" id="userFunctionRoomNewBooking"
                enctype="multipart/form-data" class="needs-validation" novalidate>
                @csrf
                <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                <input type="hidden" name="function_room_id" value="{{ $item->id }}">
                <input type="hidden" name="type" value="{{ $type }}">
                <input type="hidden" id="roomCapacity" value="{{ $item->function_room_capacity ?? 0 }}">

                <div class="modal-header">
                    <h5 class="modal-title text-primary">{{ $item->function_room_name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-7 border-end pe-4">
                            <div class="row g-3 mb-">
                                <div class="col-md-6"> 
                                    <label class="form-label">Select Residence <span class="required">*</span></label>
                                    <select id="residentSelect" name="resident_email_id" class="form-select" required>
                                        <option value="">-- Select Residence --</option>
                                        @foreach ($residences as $residence)
                                            <option value="{{ $residence->id }}"
                                                data-type="{{ strtolower($residence->resident_type) }}"
                                                data-unit="{{ $residence->unit_no }}">
                                                {{ ucfirst($residence->resident_type) }} - Unit {{ $residence->unit_no }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Date <span class="required">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class='bx bx-calendar'></i></span>
                                        <input type="text" class="form-control bg-white text-dark"
                                            id="functionRoomBookingDate" name="function_room_booking_date" required>
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
                                        Max capacity is {{ $item->function_room_capacity }}
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

                                <div class="col-md-12 d-flex align-items-center mb-2">
                                    <div id="linkedRoomContainer"></div>
                                    
                                </div>


                            </div>

                            <label class="form-label">Payment Mode <span class="required">*</span></label>
                            <div class="mb-3">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="payment_mode"
                                        id="Charge to Account" value="Charge to Account" required>
                                    <label class="form-check-label" for="Charge to Account">Charge to Account</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="payment_mode"
                                        id="Advance Payment" value="Advance Payment" required>
                                    <label class="form-check-label" for="Advance Payment">Advance Payment</label>
                                </div>
                            </div>

                            <div id="authorizationUploadWrapper" class="col-md-12 d-none mb-4">
                                <label id="authorizationLabel" class="form-label text-danger fw-bold"></label>
                                <input type="file" name="authorization_file" class="form-control" accept="image/*,.pdf">
                                <small id="authorizationNote" class="text-muted"></small>
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
                            <div class="row g-3">
                                @foreach($addons as $addon)
                                    <div class="col-12">
                                        <div class="border rounded p-2 h-100">
                                            <div class="form-check">
                                                <input type="hidden" name="addons[{{ $addon->id }}][selected]" value="0">
                                                <input type="checkbox" class="form-check-input addOnsFields"
                                                    name="addons[{{ $addon->id }}][selected]" value="1"
                                                    id="addon{{ $addon->id }}" data-max="{{ $addon->qty }}">
                                                <label class="form-check-label fw-bold" for="addon{{ $addon->id }}">
                                                    {{ $addon->item }} (₱{{ number_format($addon->price, 2) }})
                                                </label>
                                            </div>
                                            <div class="mt-2">
                                                <label class="form-label small mb-1">Quantity</label>
                                                <input type="number" name="addons[{{ $addon->id }}][qty]"
                                                    class="form-control form-control-sm addonQty" min="1" value="0"
                                                    max="{{ $addon->qty }}" data-addon-id="{{ $addon->id }}" disabled>
                                            </div>
                                            <small class="text-muted d-block mt-2" id="addonAvailable{{ $addon->id }}">
                                                Available: {{ $addon->qty }}
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
                    <button type="submit" form="userFunctionRoomNewBooking" id="saveUserFunctionRoomBtn"
                        class="btn btn-primary d-flex align-items-center justify-content-center customBtn"
                        style="min-width: 100px; height: 38px;">
                        <span class="btn-text">SUBMIT</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>