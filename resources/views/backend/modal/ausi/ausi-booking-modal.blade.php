<div class="modal fade" id="ausiAddBookingAdminModal" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-3" id="staticBackdropLabel">ADD NEW AUSI BOOKING</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <form action="{{ route('admin.ausi.booking.store') }}" id="ausiBookingFormAdmin" method="POST"
                    enctype="multipart/form-data" class="AdminNewAusiBooking needs-validation" novalidate>

                    @csrf

                    <div class="row g-4">

                        <!-- LEFT SIDE : BOOKING DETAILS -->
                        <div class="col-md-6">

                            <div class="card shadow-sm border-0">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0 text-primary">
                                        <i class="bx bx-calendar-check me-1"></i>
                                        Booking Details
                                    </h5>
                                </div>

                                <div class="card-body">

                                    <div class="mb-3">
                                        <label for="unitAusi" class="form-label">Unit *</label>
                                        <input type="text" class="form-control" id="unitAusi" name="unit" oninput="
                                            this.value = this.value
                                                .toUpperCase()
                                                .replace(/[^0-9A-I]/g,'');
                                        " required>

                                        <div class="invalid-feedback">
                                            Format: 304A, 1203B, 2501H
                                        </div>
                                    </div>


                                    <div class="mb-3">
                                        <label for="ausiBookingDate" class="form-label">
                                            Date *
                                        </label>

                                        <div class="input-group">
                                            <span class="input-group-text bg-white">
                                                <i class="bx bx-calendar"></i>
                                            </span>

                                            <input type="text" class="form-control bg-white text-dark"
                                                id="ausiBookingDateAdmin" name="booking_date" required>
                                        </div>

                                        <div class="invalid-feedback">
                                            Required
                                        </div>
                                    </div>


                                    <div class="mb-3">
                                        <button type="button" id="checkAusiSlots" class="btn btn-outline-primary w-100">

                                            <i class="bx bx-search"></i>
                                            Check Available Slots
                                        </button>
                                    </div>


                                    <label class="form-label d-flex justify-content-between align-items-center">

                                        <span>
                                            Select Time Slot *
                                        </span>

                                        <span id="slotStatusBadge" class="badge bg-secondary">
                                            Not checked
                                        </span>

                                    </label>


                                    <div>
                                        @php
                                            $slots = [
                                                '8:00 AM - 8:30 AM',
                                                '8:30 AM - 9:00 AM',
                                                '9:00 AM - 9:30 AM',
                                                '9:30 AM - 10:00 AM',
                                                '10:00 AM - 10:30 AM',
                                                '10:30 AM - 11:00 AM',
                                                '11:00 AM - 11:30 AM',
                                                '11:30 AM - 12:00 NN',

                                                '1:00 PM - 1:30 PM',
                                                '1:30 PM - 2:00 PM',
                                                '2:00 PM - 2:30 PM',
                                                '2:30 PM - 3:00 PM',
                                                '3:00 PM - 3:30 PM',
                                                '3:30 PM - 4:00 PM',
                                                '4:00 PM - 4:30 PM',
                                                '4:30 PM - 5:00 PM',
                                            ];
                                        @endphp


                                        <div class="row g-2">

                                            @foreach ($slots as $slot)

                                                <div class="col-lg-3 col-md-4 col-6">

                                                    <input type="radio" class="btn-check booking-slot-admin-ausi"
                                                        name="booking_time_slot" id="slot{{ $loop->index }}"
                                                        value="{{ $slot }}" data-slot="{{ $slot }}" required>


                                                    <label class="btn btn-outline-primary w-100 py-2"
                                                        for="slot{{ $loop->index }}">
                                                        {{ $slot }}
                                                    </label>

                                                </div>

                                            @endforeach

                                        </div>

                                    </div>


                                </div>
                            </div>

                        </div>



                        <!-- RIGHT SIDE : RESIDENT INFORMATION -->
                        <div class="col-md-6">


                            <div class="card shadow-sm border-0">

                                <div class="card-header bg-light">
                                    <h5 class="mb-0 text-primary">
                                        <i class="bx bx-user me-1"></i>
                                        Resident Information
                                    </h5>
                                </div>


                                <div class="card-body">


                                    <div class="mb-3">

                                        <label for="name" class="form-label">
                                            Name *
                                        </label>

                                        <input type="text" class="form-control" id="name" name="name" required>

                                        <div class="invalid-feedback">
                                            Required
                                        </div>

                                    </div>



                                    <div class="mb-3">

                                        <label for="selectResidentType" class="form-label">
                                            Resident Type *
                                        </label>

                                        <select class="form-select" id="selectResidentType" name="selectResidentType"
                                            required>

                                            <option value="Owner">
                                                Owner
                                            </option>

                                            <option value="Tenant">
                                                Tenant
                                            </option>

                                        </select>


                                        <div class="invalid-feedback">
                                            Required
                                        </div>

                                    </div>


                                </div>

                            </div>


                        </div>


                    </div>


                </form>

            </div>


            <div class="modal-footer">
                <button type="submit" form="ausiBookingFormAdmin" id="saveAdminAusiBtn"
                    class="btn btn-primary d-flex align-items-center justify-content-center"
                    style="min-width: 100px; height: 38px;">
                    <span class="btn-text">Create</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="calendarModalAusi" tabindex="-1" aria-hidden="true">
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
                    <small class="text-muted">AUSI</small>
                    <div id="calendar_transaction_no" class="fs-5 fw-bold text-primary"></div>
                </div>

                <!-- GRID -->
                <div class="row g-4">

                    <!-- LEFT CARD (Resident Info) -->
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

                            </div>
                        </div>
                    </div>

                    <!-- RIGHT CARD (Schedule Info) -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body p-4">

                                <h6 class="text-uppercase text-muted small mb-3">Schedule</h6>

                                <div class="mb-3">
                                    <small class="text-muted">SRF No</small>
                                    <div class="fw-semibold" id="calendar_srf_no">N/A</div>
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

<div class="modal fade" id="ausiEdit" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <!-- HEADER (same style as Fitness Hub) -->
            <div class="modal-header bg-primary text-white position-relative">

                <div class="w-100 d-flex align-items-center justify-content-center">
                    <img src="{{ asset('assets/images/TWO SERENDRA LOGO PNG (White).png') }}"
                        style="height: 60px; width: auto;" alt="2serendra" />
                </div>

                <button type="button" class="btn-close position-absolute end-0 top-50 translate-middle-y me-2"
                    data-bs-dismiss="modal" aria-label="Close">
                </button>

            </div>

            <div class="modal-body px-4">

                <!-- TITLE -->
                <div class="text-center mb-4">
                    <h5 class="fw-bold mb-1">
                        AUSI Booking
                    </h5>

                    <span class="text-muted">
                        Reference:
                        <strong id="display_transaction_no"></strong>
                    </span>
                </div>


                <!-- BOOKING DETAILS -->
                <div class="row g-3">

                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">

                                <h6 class="text-primary fw-bold mb-3">
                                    <i class="fa-solid fa-user me-2"></i>
                                    Resident Information
                                </h6>


                                <div class="info-row">
                                    <span>Name</span>
                                    <strong id="display_name"></strong>
                                </div>

                                <div class="info-row">
                                    <span>Unit</span>
                                    <strong id="display_unit"></strong>
                                </div>

                                <div class="info-row">
                                    <span>Resident Type</span>
                                    <span id="display_resident_type"></span>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">

                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">

                                <h6 class="text-primary fw-bold mb-3">
                                    <i class="fa-solid fa-calendar-check me-2"></i>
                                    Booking Information
                                </h6>

                                <div class="info-row">
                                    <span>Date</span>
                                    <strong id="display_booking_date"></strong>
                                </div>

                                <div class="info-row">
                                    <span>Time Slot</span>
                                    <strong id="display_time_slot"></strong>
                                </div>

                                <div class="info-row">
                                    <span>Status</span>
                                    <span id="display_booking_status"></span>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- INSPECTION RESULTS -->
                <div class="card border-0 shadow-sm mt-3">

                    <div class="card-body">

                        <h6 class="text-primary fw-bold mb-3">
                            <i class="fa-solid fa-clipboard-check me-2"></i>
                            Inspection Results
                        </h6>

                        <div id="inspectionResultsContainer">
                        </div>
                    </div>
                </div>

                <!-- REMARKS -->
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-body">

                        <h6 class="text-primary fw-bold">
                            <i class="fa-solid fa-note-sticky me-2"></i>
                            Remarks
                        </h6>

                        <div class="mt-2 p-3 bg-light rounded">
                            <span id="remarks_ausi" class="">
                                No remarks provided.
                            </span>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>


<div class="modal fade" id="ausiEditReport" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <!-- HEADER (same style as Fitness Hub) -->
            <div class="modal-header bg-primary text-white position-relative">

                <div class="w-100 d-flex align-items-center justify-content-center">
                    <img src="{{ asset('assets/images/TWO SERENDRA LOGO PNG (White).png') }}"
                        style="height: 60px; width: auto;" alt="2serendra" />
                </div>

                <button type="button" class="btn-close position-absolute end-0 top-50 translate-middle-y me-2"
                    data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>

            <div class="modal-body px-4">

                <!-- TITLE -->
                <div class="text-center mb-4">
                    <h5 class="fw-bold mb-1">
                        AUSI Booking Report
                    </h5>

                    <span class="text-muted">
                        Reference:
                        <strong id="display_report_transaction_no"></strong>
                    </span>
                </div>


                <!-- BOOKING DETAILS -->
                <div class="row g-3">

                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">

                                <h6 class="text-primary fw-bold mb-3">
                                    <i class="fa-solid fa-user me-2"></i>
                                    Resident Information
                                </h6>


                                <div class="info-row">
                                    <span>Name</span>
                                    <strong id="display_report_name"></strong>
                                </div>

                                <div class="info-row">
                                    <span>Unit</span>
                                    <strong id="display_report_unit"></strong>
                                </div>

                                <div class="info-row">
                                    <span>Resident Type</span>
                                    <span id="display_report_resident_type"></span>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">

                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">

                                <h6 class="text-primary fw-bold mb-3">
                                    <i class="fa-solid fa-calendar-check me-2"></i>
                                    Booking Information
                                </h6>

                                <div class="info-row">
                                    <span>Date</span>
                                    <strong id="display_report_booking_date"></strong>
                                </div>

                                <div class="info-row">
                                    <span>Time Slot</span>
                                    <strong id="display_report_time_slot"></strong>
                                </div>

                                <div class="info-row">
                                    <span>Status</span>
                                    <span id="display_report_booking_status"></span>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- INSPECTION RESULTS -->
                <div class="card border-0 shadow-sm mt-3">

                    <div class="card-body">

                        <h6 class="text-primary fw-bold mb-3">
                            <i class="fa-solid fa-clipboard-check me-2"></i>
                            Inspection Results
                        </h6>


                        <div id="inspectionResultsContainerReport">

                        </div>
                    </div>
                </div>

                <!-- REMARKS -->
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-body">

                        <h6 class="text-primary fw-bold">
                            <i class="fa-solid fa-note-sticky me-2"></i>
                            Remarks
                        </h6>

                        <div class="mt-2 p-3 bg-light rounded">
                            <span id="remarks_report_ausi" class="">
                                No remarks provided.
                            </span>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="DownloadAusiBookingReports" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-3" id="staticBackdropLabel">Download AUSI Reports</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('download.ausi.booking.reports')}}" id="download-ausi-booking-reports"
                    method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf
                    <div class="row">
                        <div class="col-6 ">
                            <div class="mb-3 position-relative">
                                <label for="DownloadStartDatePC" class="form-label">Start Date *</label>
                                <input type="text" id="DownloadStartDatePC" class="form-control"
                                    name="download_start_date_ausi">
                                <i class="fa-regular fa-calendar position-absolute"
                                    style="top: 73%; right: 8px; transform: translateY(-50%);"></i>
                            </div>
                        </div>

                        <div class="col-6 ">
                            <div class="mb-3 position-relative">
                                <label for="DownloadEndDatePC" class="form-label">End Date *</label>
                                <input type="text" id="DownloadEndDatePC" class="form-control"
                                    name="download_end_date_ausi">
                                <i class="fa-regular fa-calendar position-absolute"
                                    style="top: 73%; right: 8px; transform: translateY(-50%);"></i>
                            </div>

                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="download-ausi-booking-reports" id="download-ausi-booking-reports"
                    class="btn btn-primary d-flex align-items-center justify-content-center"
                    style="min-width: 100px; height: 38px;">
                    <span class="btn-text">Download</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="NewInspectionItemModal" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-3" id="staticBackdropLabel">Add Inspection Item</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('store.inspection.item')}}" id="storeInspectionItem" method="POST"
                    enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf
                    <div class="row">
                        <div class="col-12 ">
                            <div class="mb-3 position-relative">
                                <label for="itemName" class="form-label">Item Name *</label>
                                <input type="text" id="itemName" class="form-control" name="item_name">
                            </div>

                            <div class="mb-3 position-relative">
                                <label for="option1" class="form-label">Option 1 *</label>
                                <input type="text" id="option1" class="form-control" name="option_1">
                            </div>

                            <div class="mb-3 position-relative">
                                <label for="option2" class="form-label">Option 2 *</label>
                                <input type="text" id="option2" class="form-control" name="option_2">
                            </div>

                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="storeInspectionItem" id="storeInspectionItemBtn"
                    class="btn btn-primary d-flex align-items-center justify-content-center"
                    style="min-width: 100px; height: 38px;">
                    <span class="btn-text">Create</span>
                </button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="EditInspectionItemModal" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-3" id="staticBackdropLabel">Edit Inspection Item</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.update.inspection.item') }}" id="updateInspectionItemForm" method="POST"
                    enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf
                    <div class="row">
                        <input type="hidden" id="edit_item_id" name="id">
                        <div class="col-12 ">
                            <div class="mb-3 position-relative">
                                <label for="itemName" class="form-label">Item Name *</label>
                                <input type="text" id="edit_item_name" name="item_name" class="form-control" required>
                            </div>

                            <div class="mb-3 position-relative">
                                <label for="option1" class="form-label">Option 1 *</label>
                                <input type="text" id="edit_option_1" name="option_1" class="form-control" required>
                            </div>

                            <div class="mb-3 position-relative">
                                <label for="option2" class="form-label">Option 2 *</label>
                                <input type="text" id="edit_option_2" name="option_2" class="form-control" required>
                            </div>

                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="submit" form="updateInspectionItemForm" id="UpdateInspectionItemBtn"
                    class="btn btn-primary d-flex align-items-center justify-content-center"
                    style="min-width: 100px; height: 38px;">
                    <span class="btn-text">Update</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="UnitAusiInspectionModal" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">

                <button type="button" class="btn-close" data-bs-dismiss="modal">
                </button>
            </div>

            <div class="modal-body">
                <div class="border rounded p-3 mb-3 bg-light">

                    <div class="row align-items-center">

                        <div class="col-md-6">
                            <div class="text-muted small">
                                Unit No.
                            </div>

                            <div class="fw-bold fs-5 text-dark">
                                <i class="fa-solid fa-building me-2 text-primary"></i>
                                <span id="inspection_unit">N/A</span>
                            </div>
                        </div>

                        <div class="col-md-6 text-md-end mt-3 mt-md-0">

                            <div class="text-muted small">
                                Inspection Type
                            </div>

                            <h6 class="fw-bold text-primary mb-0">
                                <i class="fa-solid fa-clipboard-check me-2"></i>
                                Inspection Checklist
                            </h6>
                        </div>
                    </div>
                </div>
                <input type="hidden" id="inspection_booking_id">
                <div class="inspection-container">

                    <div id="inspectionChecklist" class="inspection-list">
                    </div>

                </div>

                <div class="card border rounded p-3 mt-3">

                    <h6 class="fw-bold text-primary mb-3">
                        <i class="fa-solid fa-note-sticky me-2"></i>
                        Inspection Remarks
                    </h6>

                    <textarea class="form-control" id="inspection_remarks" name="inspection_remarks"
                        rows="4">
                    </textarea>

                </div>


            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Close
                </button>


                <button type="button" id="saveInspectionBtn" class="btn btn-primary"
                    class="btn btn-primary d-flex align-items-center justify-content-center"
                    style="min-width: 100px; height: 38px;">
                    Complete Inspection
                </button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="ReportUnitAusiInspectionModal" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">

                <button type="button" class="btn-close" data-bs-dismiss="modal">
                </button>
            </div>

            <div class="modal-body">
                <div class="border rounded p-3 mb-3 bg-light">

                    <div class="row align-items-center">

                        <div class="col-md-6">
                            <div class="text-muted small">
                                Unit No.
                            </div>

                            <div class="fw-bold fs-5 text-dark">
                                <i class="fa-solid fa-building me-2 text-primary"></i>
                                <span id="report_inspection_unit">N/A</span>
                            </div>
                        </div>

                        <div class="col-md-6 text-md-end mt-3 mt-md-0">

                            <div class="text-muted small">
                                Inspection Type
                            </div>

                            <h6 class="fw-bold text-primary mb-0">
                                <i class="fa-solid fa-clipboard-check me-2"></i>
                                Inspection Checklist
                            </h6>
                        </div>
                    </div>
                </div>
                <input type="hidden" id="report_inspection_booking_id">
                <div class="inspection-container">

                    <div id="report_inspectionChecklist" class="inspection-list">
                    </div>

                </div>

                <div class="card border rounded p-3 mt-3">

                    <h6 class="fw-bold text-primary mb-3">
                        <i class="fa-solid fa-note-sticky me-2"></i>
                        Inspection Remarks
                    </h6>

                    <textarea class="form-control" id="report_inspection_remarks" name="report_inspection_remarks"
                        rows="4">
                    </textarea>

                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Close
                </button>

                <button type="button" id="saveInspectionReportBtn" class="btn btn-primary"
                    class="btn btn-primary d-flex align-items-center justify-content-center"
                    style="min-width: 100px; height: 38px;">
                    Complete Inspection
                </button>
            </div>
        </div>
    </div>
</div>