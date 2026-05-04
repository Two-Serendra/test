<div class="modal fade" id="NewFitnessHubBookingModal" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-3">New Booking</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.new.booking.fitness.hub') }}" id="AdminNewBookingFitnessHub" method="POST"
                    enctype="multipart/form-data" class="AdminNewBookingFitnessHub needs-validation" novalidate>
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
                                <label for="fitnessHubSelectBooking" class="form-label">Select Fitness Hub *</label>
                                <select class="form-select" id="fitnessHubSelectBooking" name="fitness_hub_id" required>
                                    <option value="" disabled selected>Fitness Hub</option>
                                    @foreach($FitnessHubs as $fitnessHub)

                                        @php
                                            $isFitnessHubInactive = ($fitnessHub->fitness_hub_status ?? null) == 0;                                        
                                        @endphp

                                        <option value="{{ $fitnessHub->id }}"
                                            data-start-time="{{ \Carbon\Carbon::parse($fitnessHub->start_time)->format('H:i') }}"
                                            data-end-time="{{ \Carbon\Carbon::parse($fitnessHub->end_time)->format('H:i') }}"
                                            data-fitnessHub-space="{{ $fitnessHub->fitnessHub_space }}" {{ $isFitnessHubInactive ? 'disabled' : '' }}>

                                            {{ strtoupper($fitnessHub->fitness_hub_name) }}
                                            {{ $isFitnessHubInactive ? ' (Unavailable - ' . ($fitnessHub->fitness_hub_remarks ?? 'Maintenance') . ')' : '' }}

                                        </option>

                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Please select a fitness hub</div>
                            </div>

                            <div class="mb-3 position-relative">
                                <label for="dateFieldBookingFH" class="form-label">Date *</label>
                                <input type="text" id="dateFieldBookingFH" class="form-control" name="booking_date"
                                    required>
                                <i class="fa-regular fa-calendar position-absolute"
                                    style="top: 73%; right: 8px; transform: translateY(-50%);"></i>
                            </div>

                            <div class="mb-3 d-flex align-items-end gap-2">
                                <div class="flex-grow-1">
                                    <label for="unitNumber" class="form-label">Unit *</label>
                                    <input type="text" class="form-control" id="unitNumberFH" name="unit" oninput="
                                this.value = this.value
                                    .toUpperCase()
                                    .replace(/[^0-9A-I]/g,'');
                                " required>
                                    <div class="invalid-feedback">Format: 304A</div>
                                </div>
                                <button type="button" class="btn btn-outline-secondary checkUnitFH">Check</button>

                                <span id="unitStatus" class="mt-1 text-muted">0/0</span>
                            </div>

                            <div class="mb-3 position-relative">
                                <label for="booking_start_time_FH" class="form-label">Time Start *</label>
                                <select class="form-control" id="booking_start_time_FH" name="booking_start_time"
                                    required>

                                </select>
                                <i class="fa-regular fa-clock position-absolute"
                                    style="top: 73%; right: 8px; transform: translateY(-50%);"></i>
                            </div>

                            <div class="mb-3 position-relative">
                                <label for="booking_end_time_FH" class="form-label">Time Finish *</label>
                                <select class="form-control" id="booking_end_time_FH" name="booking_end_time" required>

                                </select>
                                <i class="fa-regular fa-clock position-absolute"
                                    style="top: 73%; right: 8px; transform: translateY(-50%);"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label for="selectResidentTypeFH" class="form-label">Resident Type *</label>
                                <select class="form-select" id="selectResidentTypeFH" name="selectResidentType"
                                    required>
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
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">

                <span id="submitWrapper" style="display: inline-block;">
                    <button type="submit" form="AdminNewBookingFitnessHub" id="saveFitnessHubBookingBtn"
                        class="btn btn-primary d-flex align-items-center justify-content-center"
                        style="min-width: 100px; height: 38px;">

                        <span class="btn-text">Submit</span>
                    </button>
                </span>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewFitnessHubBookingModal" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white position-relative">

                <!-- Centered Logo -->
                <div class="w-100 d-flex align-items-center justify-content-center">
                    <img src="{{ asset('assets/images/TWO SERENDRA LOGO PNG (White).png') }}"
                        style="height: 60px; width: auto;" alt="2serendra" />
                </div>

                <!-- Close Button (force right alignment) -->
                <button type="button" class="btn-close position-absolute end-0 top-50 translate-middle-y me-2"
                    data-bs-dismiss="modal" aria-label="Close">
                </button>

            </div>
            <div class="modal-body px-4 py-3">

                <!-- Header -->
                <div class="text-center mb-4">
                    <h5 class="fw-bold mb-1">Booking Details</h5>
                    <small class="text-muted">Reference #: <span id="detail-transaction-no"></span></small>
                </div>

                <!-- STATUS + PENALTY (Highlight Section) -->
                <div class="d-flex justify-content-between align-items-center mb-4 p-3 rounded bg-light">
                    <div>
                        <small class="text-muted d-block">Status</small>
                        <span id="detail-booking-status"></span>
                    </div>

                    <div class="text-end">
                        <small class="text-muted d-block">Penalty</small>
                        <span id="detail-penalty-display" class="fw-semibold"></span>
                    </div>
                </div>

                <div class="row g-3">

                    <!-- Guest Info Card -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <h6 class="fw-semibold text-primary mb-3">Guest Information</h6>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Name</span>
                                <span id="detail-name" class="fw-semibold text-end"></span>
                            </div>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Unit</span>
                                <span id="detail-unit" class="fw-semibold"></span>
                            </div>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Resident Type</span>
                                <span id="detail-resident-type"></span>
                            </div>

                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Contact</span>
                                <span id="detail-contact" class="fw-semibold"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Booking Info Card -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <h6 class="fw-semibold text-primary mb-3">Booking Information</h6>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Fitness Hub</span>
                                <span id="detail-fitness-hub-name" class="fw-semibold text-end"></span>
                            </div>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Booking Type</span>
                                <span id="detail-booking-type" class="fw-semibold"></span>
                            </div>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Date</span>
                                <span id="detail-booking-date" class="fw-semibold"></span>
                            </div>

                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Time</span>
                                <span id="detail-start-time" class="fw-semibold"></span>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>