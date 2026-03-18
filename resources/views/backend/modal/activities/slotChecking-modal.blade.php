<div class="modal fade" id="SlotCheckingModal" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-3">Slot Checking</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="max-height: calc(100vh - 200px); overflow-y: auto;">
                <form action="{{ route('fetchAllSlotsAdmin') }}" id="SearchSlotAdmin" method="POST"
                    class="SearchSlotAdmin" enctype="multipart/form-data" novalidate>
                    @csrf
                    <div class="row mb-3">
                        <div class="col-4">
                            <label for="activitySelectBookingSearchAdmin" class="form-label">Select Activity *</label>
                            <input type="hidden" id="amenityIdBooking" name="amenity_id">
                            <select class="form-select" id="activitySelectBookingSearchAdmin" name="activity_id" required>
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

                        <!-- Date Field -->
                        <div class="col-4">
                            <div class="position-relative">
                                <label for="activityDateFieldSearchAdmin" class="form-label">Date *</label>
                                <input type="text" id="activityDateFieldSearchAdmin" class="form-control"
                                    name="activityDateFieldSearchAdmin" required>
                                <i class="fa-regular fa-calendar position-absolute"
                                    style="top: 73%; right: 8px; transform: translateY(-50%);"></i>
                            </div>
                        </div>

                        <div class="col-4 d-flex justify-content-start align-items-end">
                            <button type="submit" form="SearchSlotAdmin" class="btn btn-primary searchBtn">
                                <i class="fa-solid fa-search me-1"></i><span> Search</span>
                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"
                                    id="spinner"></span>
                            </button>
                        </div>
                        <!-- Search Button -->

                    </div>

                    <div class="all-slot-available-admin">

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
