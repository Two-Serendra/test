<div class="modal fade" id="greastrapAdd" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-3" id="staticBackdropLabel">ADD NEW GREASE TRAP BOOKING</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.grease.trap.booking.store') }}" id="greaseTrapBookingFormAdmin"
                    method="POST" enctype="multipart/form-data" class="AdminNewGreaseTrapBooking needs-validation"
                    novalidate>
                    @csrf

                    <div class="row g-3">

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                                <div class="invalid-feedback">Required</div>

                            </div>

                            <div class="mb-3">
                                <label for="unit" class="form-label">Unit *</label>
                                <input type="text" class="form-control" id="unit" name="unit" oninput="
                                this.value = this.value
                                    .toUpperCase()
                                    .replace(/[^0-9A-I]/g,'');
                                " required>
                                <div class="invalid-feedback">Format: 304A, 1203B, 2501H</div>
                            </div>

                            <div class="mb-3">
                                <label for="selectResidentType" class="form-label">Resident Type *</label>
                                <select class="form-select" id="selectResidentType" name="selectResidentType" required>
                                    <option value="Owner">Owner</option>
                                    <option value="Tenant">Tenant</option>
                                </select>
                                <div class="invalid-feedback">Required</div>

                            </div>

                            <div class="">
                                <label for="additionalDetails" class="form-label">Remarks</label>
                                <textarea name="remarks" id="additionalDetails" class="form-control" rows="6"
                                    ></textarea>
                                <div class="invalid-feedback">Required</div>

                            </div>
                        </div>

                        <!-- Time Slots -->
                        <div class="col-6">
                            <div class="mb-3">
                                <label for="GreaseTrapBookingDate" class="form-label">Date</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bx bx-calendar"></i></span>
                                    <input type="text" class="form-control bg-white text-dark"
                                        id="GreaseTrapBookingDateAdmin" name="booking_date" required>
                                </div>
                                <div class="invalid-feedback">Required</div>
                            </div>


                            <label class="form-label">Select Time Slot *</label>
                            <div class="row g-2">
                                @php
                                    $slots = [
                                        '09:00 AM - 10:00 AM',
                                        '10:00 AM - 11:00 AM',
                                        '11:00 AM - 12:00 NN',
                                        '01:00 PM - 02:00 PM',
                                        '02:00 PM - 03:00 PM',
                                        '03:00 PM - 04:00 PM',
                                        '04:00 PM - 05:00 PM',
                                    ];
                                @endphp

                                @foreach ($slots as $slot)
                                    <div class="col-12">
                                        <input type="radio" class="btn-check booking-slot-admin" name="booking_time_slot"
                                            id="slot{{ $loop->index }}" value="{{ $slot }}" data-slot="{{ $slot }}"
                                            required>
                                        <label class="btn btn-outline-primary w-100" for="slot{{ $loop->index }}">
                                            {{ $slot }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="submit" form="greaseTrapBookingFormAdmin" id="saveAdminGreaseTrapBtn"
                    class="btn btn-primary d-flex align-items-center justify-content-center"
                    style="min-width: 100px; height: 38px;">
                    <span class="btn-text">Create</span>
                </button>
            </div>


        </div>

    </div>
</div>


<div class="modal fade" id="emergencyGreaseTrapBooking" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-3" id="staticBackdropLabel">ADD EMERGENCY GREASE TRAP BOOKING</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.grease.trap.emergency.booking.store') }}"
                    id="greaseTrapBookingEmergencyForm" method="POST" enctype="multipart/form-data"
                    class="needs-validation" novalidate>
                    @csrf

                    <div class="row g-3">

                        <div class="col-md-6">

                            <div class="mb-3">
                                <label for="unit" class="form-label">Unit *</label>
                                <input type="text" class="form-control" id="unit" name="unit" oninput="
                                this.value = this.value
                                    .toUpperCase()
                                    .replace(/[^0-9A-I]/g,'');
                                " required>
                                <div class="invalid-feedback">Format: 304A, 1203B, 2501H</div>
                            </div>

                            <div class="mb-3">
                                <label for="name" class="form-label">Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                                <div class="invalid-feedback">Required</div>

                            </div>

                            <div class="mb-3">
                                <label for="GreaseTrapBookingDate" class="form-label">Date</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bx bx-calendar"></i></span>
                                    <input type="text" class="form-control bg-white text-dark"
                                        id="GreaseTrapBookingDateAdminEmergency" name="booking_date" required>
                                </div>
                                <div class="invalid-feedback">Required</div>
                            </div>

                            <div class="mb-3">
                                @php
                                    use Carbon\Carbon;

                                    $slots = [];
                                    $start = Carbon::createFromTime(6, 0); // 6:00 AM
                                    $end = Carbon::createFromTime(22, 0);  // 10:00 PM

                                    while ($start->lt($end)) {
                                        $slotStart = $start->format('h:i A');
                                        $slotEnd = $start->copy()->addHour()->format('h:i A');
                                        $slots[] = "{$slotStart} - {$slotEnd}";
                                        $start->addHour();
                                    }
                                @endphp

                                <div class="mb-3">
                                    <label for="booking_time_slot" class="form-label">Select Time Slot</label>
                                    <select name="booking_time_slot" id="booking_time_slot" class="form-select"
                                        required>

                                        <option value="" disabled selected>Select a slot</option>
                                        @foreach ($slots as $slot)
                                            <option value="{{ $slot }}">{{ $slot }}</option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>
                        </div>

                        <div class="col-6">
                            <div class="mb-3">
                                <label for="selectResidentType" class="form-label">Resident Type *</label>
                                <select class="form-select" id="selectResidentType" name="selectResidentType" required>
                                    <option value="Owner">Owner</option>
                                    <option value="Tenant">Tenant</option>
                                </select>
                                <div class="invalid-feedback">Required</div>

                            </div>

                            <div class="mb-3">
                                <label for="remarks" class="form-label">Additional Details *</label>
                                <textarea name="remarks" id="remarks" class="form-control" rows="6" required></textarea>
                                <div class="invalid-feedback">Required</div>

                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="submit" form="greaseTrapBookingEmergencyForm" id="saveEmergencyGreaseTrapBtn"
                    class="btn btn-primary d-flex align-items-center justify-content-center"
                    style="min-width: 100px; height: 38px;">
                    <span class="btn-text">Create</span>
                </button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="greastrapEdit" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-3" id="staticBackdropLabel">EDIT GREASE TRAP BOOKING</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.grease.trap.booking.update') }}" id="updateGreaseTrapBookingFormAdmin"
                    method="POST" enctype="multipart/form-data" class="AdminNewGreaseTrapBooking needs-validation"
                    novalidate>
                    @csrf
                    <input type="hidden" id="info_id" name="id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <p class="form-control-plaintext fw-bold" id="display_name"></p>
                            </div>


                            <div class="mb-3">
                                <label class="form-label">Unit</label>
                                <p class="form-control-plaintext" id="display_unit"></p>
                            </div>


                            <div class="mb-3">
                                <label class="form-label">Resident Type</label>
                                <p class="form-control-plaintext" id="display_resident_type"></p>
                            </div>


                            <div class="mb-3">
                                <label class="form-label">Booking Date</label>
                                <p class="form-control-plaintext" id="display_booking_date"></p>
                            </div>

                        </div>

                        <!-- Time Slots -->
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label">Transaction No</label>
                                <p class="form-control-plaintext" id="display_transaction_no"></p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Charged Type</label>
                                <p class="form-control-plaintext" id="display_charged_type"></p>
                            </div>


                            <div class="mb-3">
                                <label class="form-label">Time Slot</label>
                                <p class="form-control-plaintext" id="display_time_slot"></p>
                            </div>


                            <div class="mb-3">
                                <label class="form-label">SRF No *</label>
                                <input type="text" class="form-control" id="srf_no" name="srf_no">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Remarks</label>
                                <textarea class="form-control" id="remarks_grease_trap" name="remarks"
                                    rows="4"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="submit" form="updateGreaseTrapBookingFormAdmin" id="UpdateGreaseTrapBookingBtn"
                    class="btn btn-primary d-flex align-items-center justify-content-center"
                    style="min-width: 100px; height: 38px;">
                    <span class="btn-text">Update</span>
                </button>
            </div>


        </div>

    </div>
</div>


{{-- Calendar Modal --}}
<div class="modal fade" id="calendarModalGreaseTrap" tabindex="-1" aria-labelledby="editProductModalLabel"
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


                        <div class="col-6">

                            <div class="mb-2">
                                <label for="" class="form-label"><b>Transaction No</b></label>
                                <p id="calendar_transaction_no" class="form-control-static calendar-value"></p>
                            </div>

                            <div class="mb-2">
                                <label for="" class="form-label"><b>Unit</b></label>
                                <p id="calendar_unit" class="form-control-static calendar-value"></p>
                            </div>


                            <div class="mb-3">
                                <label class="form-label"><b>Resident Type</b></label>
                                <p class="form-control-plaintext" id="display_resident_type_calendar"></p>
                            </div>


                            <div class="mb-2">
                                <label for="" class="form-label"><b>Name</b></label>
                                <p id="calendar_name" class="form-control-static calendar-value"></p>
                                <!-- Display as text -->
                            </div>

                        </div>

                        <div class="col-6">
                            <div class="mb-2">
                                <label for="" class="form-label"><b>SRF No</b></label>
                                <p id="calendar_srf_no" class="form-control-static calendar-value"></p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><b>Charged Type</b></label>
                                <p class="form-control-plaintext" id="display_charged_type_calendar"></p>
                            </div>

                            <div class="mb-2">
                                <label for="" class="form-label"><b>Date</b></label>
                                <p id="calendar_booking_date" class="form-control-static calendar-value"></p>
                                <!-- Display as text -->
                            </div>

                            <div class="mb-2">
                                <label for="" class="form-label"><b>Time Slot</b></label>
                                <p id="calendar_time_slot" class="form-control-static calendar-value"></p>
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

<div class="modal fade" id="DownloadGreaseTrapBookingRecords" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-3" id="staticBackdropLabel">Download Grease Trap Reports</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('download.grease.trap.booking.reports')}}"
                    id="download-grease-trap-booking-reports" method="POST" enctype="multipart/form-data"
                    class="needs-validation" novalidate>
                    @csrf
                    <div class="row">
                        <div class="col-6 ">
                            <div class="mb-3 position-relative">
                                <label for="DownloadStartDateGT" class="form-label">Start Date *</label>
                                <input type="text" id="DownloadStartDateGT" class="form-control"
                                    name="download_start_date_gt">
                                <i class="fa-regular fa-calendar position-absolute"
                                    style="top: 73%; right: 8px; transform: translateY(-50%);"></i>
                            </div>
                        </div>

                        <div class="col-6 ">
                            <div class="mb-3 position-relative">
                                <label for="DownloadEndDateGT" class="form-label">End Date *</label>
                                <input type="text" id="DownloadEndDateGT" class="form-control" name="download_end_date_gt">
                                <i class="fa-regular fa-calendar position-absolute"
                                    style="top: 73%; right: 8px; transform: translateY(-50%);"></i>
                            </div>

                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="download-grease-trap-booking-reports"
                    id="download-grease-trap-booking-reports"
                    class="btn btn-primary d-flex align-items-center justify-content-center"
                    style="min-width: 100px; height: 38px;">
                    <span class="btn-text">Download</span>
                </button>
            </div>
        </div>
    </div>
</div>