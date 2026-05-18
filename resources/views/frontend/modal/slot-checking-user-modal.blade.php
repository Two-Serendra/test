<div class="modal fade" id="SlotCheckingModalUser" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-3">Slot Checking</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="max-height: calc(100vh - 200px); overflow-y: auto;">
                <form action="{{ route('fetchAllSlotsUser') }}" id="SearchSlotUser" method="POST" class="SearchSlotUser"
                    enctype="multipart/form-data" novalidate>
                    @csrf
                    <div class="row mb-3">
                        <div class="col-12 col-md-4 mb-3 mb-md-0">
                            <label class="form-label">Activity</label>

                            {{-- Hidden values for form submission --}}
                            <input type="hidden" name="activity_id" value="{{ $activity->id }}">
                            <input type="hidden" name="amenity_id" value="{{ $activity->amenity_id }}">

                            {{-- Readonly display --}}
                            <input type="text" class="form-control" value="{{ strtoupper($activity->activity_name) }}"
                                readonly>

                            {{-- Optional hidden metadata --}}
                            <input type="hidden" id="activityMeta"
                                data-start-time="{{ \Carbon\Carbon::parse($activity->start_time)->format('H:i') }}"
                                data-end-time="{{ \Carbon\Carbon::parse($activity->end_time)->format('H:i') }}"
                                data-activity-space="{{ $activity->activity_space }}">
                        </div>

                        <!-- Date Field -->
                        <div class="col-12 col-md-4 mb-3 mb-md-0">
                            <div class="position-relative">
                                <label for="activityDateFieldSearchUser" class="form-label">Date *</label>
                                <input type="text" id="activityDateFieldSearchUser" class="form-control"
                                    name="activityDateFieldSearchUser" required>
                                <i class="fa-regular fa-calendar position-absolute"
                                    style="top: 73%; right: 8px; transform: translateY(-50%);"></i>
                            </div>
                        </div>

                        <div class="col-12 col-md-4 mb-3 mb-md-0 d-flex justify-content-start align-items-end">
                            <button type="submit" form="SearchSlotUser"
                                class="btn btn-primary customBtn searchBtn slot-checking-submit-btn"
                                style="min-width: 100px; height: 38px;">
                                <i class="fa-solid fa-search me-1"></i><span> Search</span>
                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"
                                    id="spinner"></span>
                            </button>
                        </div>
                        <!-- Search Button -->

                    </div>

                    <div class="all-slot-available-user">

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>