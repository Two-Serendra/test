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
                                        <option value="{{ $activity->id }}" data-amenity-id="{{ $activity->amenity_id }}"
                                            data-start-time="{{ \Carbon\Carbon::parse($activity->start_time)->format('H:i') }}"
                                            data-end-time="{{ \Carbon\Carbon::parse($activity->end_time)->format('H:i') }}"
                                            data-activity-space="{{ $activity->activity_space }}">
                                            {{ strtoupper($activity->activity_name) }}

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
                <button type="submit" form="AdminNewBooking" id="saveActivityBookingBtn"
                    class="btn btn-primary d-flex align-items-center justify-content-center"
                    style="min-width: 100px; height: 38px;">
                    <span class="btn-text">Submit</span>
                </button>
            </div>
        </div>
    </div>
</div>


<!-- EDIT BOOKING -->
<!-- <div class="modal fade" id="bookingEdit" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-3" id="staticBackdropLabel">EDIT BOOKING</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateBooking" method="POST" enctype="multipart/form-data" class="needs-validation"
                    novalidate>
                    @csrf
                    <div class="row">
                        <input type="hidden" id="booking_id" name="booking_id" required>
                        <div class="col-6">

                            <input type="hidden" id="edit_bookingType" name="booking_type">

                            <ul class="nav nav-tabs mb-3" id="bookingTabs">
                                <li class="nav-item">
                                    <a class="nav-link" id="advanced-tab" data-bs-toggle="tab" href="#"
                                        data-value="ADVANCED BOOKING">Advanced Booking</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link " id="walkin-tab" data-bs-toggle="tab" href="#"
                                        data-value="WALK-IN">Walk-in</a>
                                </li>
                            </ul>

                            <div class="mb-3">
                                <select class="form-select" id="edit_booking_select" name="edit_amenity_id_activity">
                                    <option value="" disabled selected>Select Activity</option>
                                    @foreach($activities as $activity)
                                        <option value="{{ $activity->id }}">{{ strtoupper($activity->activity_name) }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Please select an activity</div>
                            </div>

                            <div class="mb-3">
                                <label for="" class="form-label">Unit *</label>
                                <input type="text" class="form-control" id="edit_booking_unit" name="booking_unit"
                                    required>
                                <div class="invalid-feedback">
                                    Required
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="selectResidentType" class="form-label">Resident Type *</label>
                                <select class="form-select" id="edit_selectResidentType" name="edit_selectResidentType"
                                    required>
                                    <option value="Owner">Owner</option>
                                    <option value="Tenant">Tenant</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="" class="form-label">Name *</label>
                                <input type="text" class="form-control" id="edit_booking_name" name="booking_name"
                                    required>
                                <div class="invalid-feedback">
                                    Required
                                </div>
                            </div>
                        </div>

                        <div class="col-6">

                            <div class="mb-3">
                                <label for="" class="form-label">Contact </label>
                                <input type="text" class="form-control" id="edit_contact_number" name="contact_number">
                            </div>



                            <div class="mb-3 position-relative">
                                <label for="" class="form-label">Date *</label>
                                <input type="date" id="edit_booking_date" class="form-control" name="booking_date">
                            </div>

                            <div class="mb-3 position-relative">
                                <label for="edit_booking_start_time" class="form-label">Time Start *</label>
                                <span id="current_start_time" class="text-muted d-inline ms-2"></span>
                                <select class="form-control" id="edit_booking_start_time" name="booking_start_time"
                                    required disabled>
                                </select>
                                <i class="fa-regular fa-clock position-absolute"
                                    style="top: 73%; right: 8px; transform: translateY(-50%);"></i>

                            </div>

                            <div class="mb-3 position-relative">
                                <label for="endTime" class="form-label">Time Finish *</label>
                                <span id="current_end_time" class="text-muted d-inline ms-2"></span>
                                <select class="form-control" id="edit_booking_end_time" name="booking_end_time" required
                                    disabled>
                                </select>
                                <i class="fa-regular fa-clock position-absolute"
                                    style="top: 73%; right: 8px; transform: translateY(-50%);"></i>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="submit" form="updateBooking" class="btn btn-primary">Update</button>
            </div>
        </div>
    </div>
</div> -->

<div class="modal fade" id="bookingEdit" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-3 booking_type_text" id="staticBackdropLabel"></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <input type="hidden" id="booking_id">

                <div class="row">

                    <!-- LEFT -->
                    <div class="col-md-6">

                        <div class="mb-3">
                            <label class="form-label custom-label">Transaction No:</label>
                            <p id="edit_transaction_no" class="form-control-plaintext custom-p"></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label custom-label">Resident Type:</label>
                            <p id="edit_selectResidentType_text" class="form-control-plaintext custom-p"></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label custom-label">Unit:</label>
                            <p id="edit_booking_unit_text" class="form-control-plaintext custom-p"></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label custom-label">Name:</label>
                            <p id="edit_booking_name_text" class="form-control-plaintext custom-p"></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label custom-label">Contact:</label>
                            <p id="edit_contact_number_text" class="form-control-plaintext custom-p"></p>
                        </div>

                    </div>

                    <!-- RIGHT -->
                    <div class="col-md-6">

                        <div class="mb-3">
                            <label class="form-label custom-label">Booking Status:</label>
                            <p id="booking-status" class="form-control-plaintext custom-p"></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label custom-label">Booking Type:</label>
                            <p class="form-control-plaintext custom-p booking_type_text"></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label custom-label">Activity:</label>
                            <p id="edit_booking_select_text" class="form-control-plaintext custom-p"></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label custom-label">Booking Date:</label>
                            <p id="edit_booking_date_text" class="form-control-plaintext custom-p"></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label custom-label">Time:</label>
                            <p class="form-control-plaintext custom-p">
                                <span id="edit_booking_start_time_text"></span> -
                                <span id="edit_booking_end_time_text"></span>
                            </p>
                        </div>

                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="activityCalendarModal" tabindex="-1" aria-labelledby="editProductModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fs-3" id="exampleModalLabel">Calendar Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                    id="modalClose"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row mb-2">
                        <input type="hidden" class="form-control" name="schedule_id" id="edit_id">

                        <div class="col-12 mb-2">
                            <label for="" class="form-label"><b>Activity</b></label>
                            <p id="calendar_activity_name" class="form-control-static calendar-value"></p>
                            <!-- Display as text -->
                        </div>

                        <div class="col-6">
                            <div class="mb-2">
                                <label for="" class="form-label"><b>Unit</b></label>
                                <p id="calendar_unit" class="form-control-static calendar-value"></p>
                                <!-- Display as text -->
                            </div>

                            <div class="mb-2">
                                <label for="" class="form-label"><b>Name</b></label>
                                <p id="calendar_name" class="form-control-static calendar-value"></p>
                                <!-- Display as text -->
                            </div>

                            <div class="mb-2">
                                <label for="" class="form-label"><b>Contact</b></label>
                                <p id="calendar_contact_number" class="form-control-static calendar-value"></p>
                                <!-- Display as text -->
                            </div>

                        </div>

                        <div class="col-6">
                            <div class="mb-2">
                                <label for="" class="form-label"><b>Date</b></label>
                                <p id="calendar_booking_date" class="form-control-static calendar-value"></p>
                                <!-- Display as text -->
                            </div>

                            <div class="mb-2">
                                <label for="" class="form-label"><b>Start</b></label>
                                <p id="calendar_booking_start_time" class="form-control-static calendar-value"></p>
                                <!-- Display as text -->
                            </div>

                            <div class="mb-2">
                                <label for="" class="form-label"><b>End</b></label>
                                <p id="calendar_booking_end_time" class="form-control-static calendar-value"></p>
                                <!-- Display as text -->
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer p-2">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>