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
                                <textarea name="remarks" id="additionalDetails" class="form-control"
                                    rows="6"></textarea>
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
                                        '9:00 AM - 10:00 AM',
                                        '10:00 AM - 11:00 AM',
                                        '11:00 AM - 12:00 NN',
                                        '1:00 PM - 2:00 PM',
                                        '2:00 PM - 3:00 PM',
                                        '3:00 PM - 4:00 PM',
                                        '4:00 PM - 5:00 PM',
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
                                        $slotStart = $start->format('g:i A');
                                        $slotEnd = $start->copy()->addHour()->format('g:i A');

                                        // Optional: convert 12:00 PM to 12:00 NN
                                        $slotEnd = str_replace('12:00 PM', '12:00 NN', $slotEnd);

                                        $slots[] = "{$slotStart} - {$slotEnd}";
                                        $start->addHour();
                                    }
                                @endphp
                                <label for="booking_time_slot" class="form-label">Select Time Slot</label>
                                <div class="slot-container border rounded p-2">
                                    <select name="booking_time_slot" id="booking_time_slot" class="form-select border-0 shadow-none"  size="8"
                                        required>
                                       
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <!-- HEADER -->
            <div class="modal-header bg-primary text-white position-relative">

                <div class="w-100 d-flex align-items-center justify-content-center">
                    <img src="{{ asset('assets/images/TWO SERENDRA LOGO PNG (White).png') }}"
                        style="height: 60px; width: auto;" alt="logo" />
                </div>

                <button type="button" class="btn-close position-absolute end-0 top-50 translate-middle-y me-2"
                    data-bs-dismiss="modal">
                </button>

            </div>

            <!-- BODY -->
            <div class="modal-body px-4 py-3">

                <!-- TITLE -->
                <div class="text-center mb-4">
                    <h5 class="fw-bold mb-1">Grease Trap Booking Details</h5>
                    <small class="text-muted">
                        Reference #: <span id="display_transaction_no"></span>
                    </small>
                </div>

                <!-- STATUS STRIP -->
                <div class="d-flex justify-content-between align-items-center mb-4 p-3 rounded bg-light">
                    <div>
                        <small class="text-muted d-block">Charged Type</small>
                        <span id="display_charged_type" class="fw-semibold"></span>
                    </div>

                    <div class="text-end">
                        <small class="text-muted d-block">Time Slot</small>
                        <span id="display_time_slot" class="fw-semibold"></span>
                    </div>
                </div>

                <form action="{{ route('admin.grease.trap.booking.update') }}" id="updateGreaseTrapBookingFormAdmin"
                    method="POST" enctype="multipart/form-data" novalidate>
                    @csrf

                    <input type="hidden" id="info_id" name="id">

                    <div class="row g-3">

                        <!-- LEFT CARD -->
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">

                                <h6 class="fw-semibold text-primary mb-3">
                                    Resident Information
                                </h6>

                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Name</span>
                                    <span id="display_name" class="fw-semibold text-end"></span>
                                </div>

                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Unit</span>
                                    <span id="display_unit" class="fw-semibold"></span>
                                </div>

                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Resident Type</span>
                                    <span id="display_resident_type"></span>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Booking Date</span>
                                    <span id="display_booking_date" class="fw-semibold"></span>
                                </div>

                            </div>
                        </div>

                        <!-- RIGHT CARD -->
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">

                                <h6 class="fw-semibold text-primary mb-3">
                                    Update Information
                                </h6>

                                <div class="mb-3">
                                    <label class="form-label">SRF No *</label>
                                    <input type="text" class="form-control" id="grease_trap_srf_no" name="srf_no">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Remarks</label>
                                    <textarea class="form-control" id="remarks_grease_trap" name="remarks"
                                        rows="4"></textarea>
                                </div>

                            </div>
                        </div>

                    </div>

                </form>

            </div>

            <!-- FOOTER -->
            <div class="modal-footer">
                <button type="submit" form="updateGreaseTrapBookingFormAdmin" id="UpdateGreaseTrapBookingBtn"
                    class="btn btn-primary btn-sm">
                    Update
                </button>
            </div>

        </div>
    </div>
</div>


<div class="modal fade" id="calendarModalGreaseTrap" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

            <!-- HEADER -->
            <div class="modal-header bg-primary text-white position-relative py-3">

                <div class="w-100 text-center">
                    <img src="{{ asset('assets/images/TWO SERENDRA LOGO PNG (White).png') }}" style="height: 50px;"
                        alt="logo">
                </div>

                <button type="button" class="btn-close position-absolute end-0 top-50 translate-middle-y me-2"
                    data-bs-dismiss="modal">
                </button>
            </div>

            <!-- BODY -->
            <div class="modal-body bg-light p-4">

                <input type="hidden" id="edit_id">

                <!-- TITLE CARD -->
                <div class="bg-white p-3 rounded-4 shadow-sm border-start border-4 border-primary mb-4">
                    <small class="text-muted">Grease Trap</small>
                    <div id="calendar_transaction_no" class="fs-5 fw-bold text-primary"></div>
                </div>

                <!-- GRID -->
                <div class="row g-4">

                    <!-- LEFT CARD -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body p-4">

                                <h6 class="text-uppercase text-muted small mb-3">Resident Info</h6>

                                <div class="mb-3">
                                    <small class="text-muted">Unit</small>
                                    <div class="fw-semibold" id="calendar_unit">N/A</div>
                                </div>

                                <div class="mb-3">
                                    <small class="text-muted">Name</small>
                                    <div class="fw-semibold" id="calendar_name">N/A</div>
                                </div>

                                <div class="mb-3">
                                    <small class="text-muted">Resident Type</small>
                                    <div class="fw-semibold" id="display_resident_type_calendar">N/A</div>
                                </div>

                                <div class="mb-0">
                                    <small class="text-muted">SRF No</small>
                                    <div class="fw-semibold" id="calendar_srf_no">N/A</div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- RIGHT CARD -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body p-4">

                                <h6 class="text-uppercase text-muted small mb-3">Schedule</h6>

                                <div class="mb-3">
                                    <small class="text-muted">Charged Type</small>
                                    <div class="fw-semibold" id="display_charged_type_calendar">N/A</div>
                                </div>

                                <div class="mb-3">
                                    <small class="text-muted">Date</small>
                                    <div class="fw-semibold" id="calendar_booking_date">N/A</div>
                                </div>

                                <div class="mb-0">
                                    <small class="text-muted">Time Slot</small>
                                    <div class="fw-semibold" id="calendar_time_slot">N/A</div>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>

                <!-- FOOTER -->
                <div class="text-end mt-4">
                    <button type="button" class="btn btn-danger btn-sm px-4" data-bs-dismiss="modal">
                        Close
                    </button>
                </div>

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
                                <input type="text" id="DownloadEndDateGT" class="form-control"
                                    name="download_end_date_gt">
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

<div class="modal fade" id="viewGreaseTrapBooking" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <!-- HEADER -->
            <div class="modal-header bg-primary text-white position-relative">

                <div class="w-100 d-flex align-items-center justify-content-center">
                    <img src="{{ asset('assets/images/TWO SERENDRA LOGO PNG (White).png') }}"
                        style="height: 60px; width: auto;" alt="logo" />
                </div>

                <button type="button" class="btn-close position-absolute end-0 top-50 translate-middle-y me-2"
                    data-bs-dismiss="modal">
                </button>

            </div>

            <!-- BODY -->
            <div class="modal-body px-4 py-3">

                <!-- TITLE -->
                <div class="text-center mb-4">
                    <h5 class="fw-bold mb-1">Grease Trap Booking Details</h5>
                    <small class="text-muted">
                        Reference #: <span id="display_transaction_no_reports"></span>
                    </small>
                </div>

                <!-- STATUS STRIP -->
                <div class="d-flex justify-content-between align-items-center mb-4 p-3 rounded bg-light">
                    <div>
                        <small class="text-muted d-block">Charged Type</small>
                        <span id="display_charged_type_reports" class="fw-semibold"></span>
                    </div>

                    <div class="text-end">
                        <small class="text-muted d-block">Time Slot</small>
                        <span id="display_time_slot_reports" class="fw-semibold"></span>
                    </div>
                </div>

                <form action="{{ route('admin.grease.trap.booking.update') }}" id="updateGreaseTrapBookingFormAdmin"
                    method="POST" enctype="multipart/form-data" novalidate>
                    @csrf

                    <input type="hidden" id="info_id" name="id">

                    <div class="row g-3">

                        <!-- LEFT CARD -->
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">

                                <h6 class="fw-semibold text-primary mb-3">
                                    Resident Information
                                </h6>

                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Name</span>
                                    <span id="display_name_reports" class="fw-semibold text-end"></span>
                                </div>

                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Unit</span>
                                    <span id="display_unit_reports" class="fw-semibold"></span>
                                </div>

                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Resident Type</span>
                                    <span id="display_resident_type_reports" class="fw-semibold"></span>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Booking Date</span>
                                    <span id="display_booking_date_reports" class="fw-semibold"></span>
                                </div>

                            </div>
                        </div>

                        <!-- RIGHT CARD -->
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">

                                <h6 class="fw-semibold text-primary mb-3">
                                    Update Information
                                </h6>

                                <div class="d-flex justify-content-between mb-3">
                                    <span class="text-muted">SRF No</span>
                                    <span id="srf_no_reports" class="fw-semibold"></span>
                                </div>

                                <div class="d-flex justify-content-between mb-3">
                                    <span class="text-muted">Remarks</span>
                                    <span id="remarks_grease_trap_reports" class="fw-semibold"></span>
                                </div>

                            </div>
                        </div>

                    </div>

                </form>

            </div>

            <!-- FOOTER -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Close</button>
            </div>


        </div>
    </div>
</div>