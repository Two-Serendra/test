<div class="modal fade" id="modalActivity{{ $activity->id }}" tabindex="-1"
    aria-labelledby="modalLabel{{ $activity->id }}" data-bs-backdrop="static" aria-hidden="true"
    data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fs-3" id="modalLabel{{ $activity->id }}">
                    {{ strtoupper($activity->activity_name) }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="bookAmenityForm{{ $activity->id }}" action="{{ route('activities.new.booking') }}"
                    method="POST" class="bookAmenityForm needs-validation" novalidate>
                    @csrf
                    <input type="hidden" id="amenityId{{ $activity->id }}" name="amenity_id"
                        value="{{ $activity->amenity_id }}">
                    <input type="hidden" name="activity_id" value="{{ $activity->id }}">
                    <div class="row">
                        <input type="hidden" id="bookingType" name="booking_type">

                        <ul class="nav nav-tabs mb-3" id="bookingTabs">
                            <li class="nav-item">
                                <a class="nav-link active booking-tab" id="advanced-tab" data-bs-toggle="tab" href="#"
                                    data-value="Advanced Booking">Advanced Booking</a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link booking-tab" id="20hrs" data-bs-toggle="tab" href="#"
                                    data-value="20hrs">20 Hrs</a>
                            </li>

                            <!-- <li class="nav-item">
                                <a class="nav-link booking-tab" id="walkin-tab" data-bs-toggle="tab" href="#"
                                    data-value="Walk-in">Walk-in</a>
                            </li> --> 
                        </ul>

                        <div class="col-md-6">

                            <div class="mb-3 position-relative">
                                <label for="dateField" class="form-label">Date <span class="required">*</span></label>
                                <input type="text" id="dateField" class="form-control" name="booking_date" required>
                                <i class="fa-regular fa-calendar position-absolute"
                                    style="top: 73%; right: 8px; transform: translateY(-50%);"></i>
                                <div class="invalid-feedback">Required</div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <!-- Label on the left -->
                                    <label class="form-label mb-0">
                                        Select Residence <span class="required">*</span>
                                    </label>

                                    <!-- Unit status + info on the right -->
                                    <div class="d-flex align-items-center gap-1">
                                        <span id="unitStatus" class="text-muted">0/0</span>
                                        <span class="unitStatusInfo" style="position: relative; cursor: pointer;">
                                            <i class="bi bi-question-circle"></i>
                                            <!-- Tooltip -->
                                            <span class="tooltipText" style="visibility: hidden; width: 200px; background-color: #333; color: #fff; 
                           text-align: center; border-radius: 4px; padding: 5px;
                           position: absolute; z-index: 10; top: 125%; right: 0; font-size: 12px;">
                                                Shows the number of existing bookings vs maximum allowed for this unit.
                                            </span>
                                        </span>
                                    </div>
                                </div>

                                <div class="d-flex gap-2 mt-1">
                                    <select id="residentSelect" name="resident_email_id"
                                        class="form-select selectResidentType flex-grow-1" required>
                                        <option value="">-- Select Residence --</option>
                                        @foreach ($residences as $residence)
                                            <option value="{{ $residence->id }}"
                                                data-type="{{ strtolower($residence->resident_type) }}"
                                                data-unit="{{ $residence->unit_no }}">
                                                {{ ucfirst($residence->resident_type) }} - Unit {{ $residence->unit_no }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <button type="button"
                                        class="btn btn-secondary customBtn checkUnit text-white">Check</button>
                                </div>
                                <div class="invalid-feedback">Required</div>
                            </div>




                            <div class="mb-3">
                                <label for="contact" class="form-label">Contact No. </label>
                                <input type="number" class="form-control" id="contact_number{{ $activity->id }}"
                                    name="contact_number">

                            </div>

                        </div>
                        <div class="col-md-6">
                            <div class="mb-3 position-relative">
                                <label for="startTime" class="form-label">Start Time <span
                                        class="required">*</span></label>
                                <select class="form-control booking_start_time"
                                    id="booking_start_time{{ $activity->id }}" name="booking_start_time" required>
                                    <option>Select Date</option>
                                </select>
                                <i class="fa-regular fa-clock position-absolute"
                                    style="top: 73%; right: 8px; transform: translateY(-50%);"></i>
                                <div class="invalid-feedback">Required</div>
                            </div>
                            <div class="mb-3 position-relative">
                                <label for="endTime" class="form-label">Finish Time <span
                                        class="required">*</span></label>
                                <select class="form-control booking_end_time" id="booking_end_time{{ $activity->id }}"
                                    name="booking_end_time" required>
                                    <option>Select end time</option>
                                </select>
                                <i class="fa-regular fa-clock position-absolute"
                                    style="top: 73%; right: 8px; transform: translateY(-50%);"></i>
                                <div class="invalid-feedback">Required</div>
                            </div>
                            <input type="hidden" class="selected_slots_user" id="selectedSlotsInputUser"
                                name="selected_slots_user">


                            <div class="mb-3 position-relative">
                                <label for="" class="form-label">Slots <span class="required">*</span></label>
                                <div class="d-flex flex-wrap gap-2" style="height: 108;"
                                    id="userAvailableSlotsContainer{{ $activity->id }}">
                                </div>
                            </div>

                        </div>
                </form>
            </div>

            <div class="modal-footer d-flex align-items-center justify-content-end">
                <button type="button" class="btn btn-secondary customBtn text-white"
                    data-bs-dismiss="modal">Cancel</button>
                <button type="submit"
                    class="btn btn-primary activity-submit-btn customBtn d-flex align-items-center justify-content-center customBtn"
                    style="min-width: 100px; height: 38px;" form="bookAmenityForm{{ $activity->id }}"
                    id="submitButton{{ $activity->id }}">
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"
                        id="spinner{{ $activity->id }}"></span>
                    Submit
                </button>
            </div>

        </div>
    </div>
</div>