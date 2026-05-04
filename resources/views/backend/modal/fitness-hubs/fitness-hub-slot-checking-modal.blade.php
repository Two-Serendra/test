<div class="modal fade" id="fitnessHubSlotCheckingModal" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-3">Slot Checking</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="max-height: calc(100vh - 200px); overflow-y: auto;">
                <form action="{{ route('admin.fetch.available.slot.fitness.hub') }}" id="SearchSlotAdminFitnessHub" method="POST"
                    class="SearchSlotAdminFitnessHub" enctype="multipart/form-data" novalidate>
                    @csrf
                    <div class="row mb-3">
                        <div class="col-4">
                            <label for="fitnessHubSelectBookingSearchAdmin" class="form-label">Select Activity *</label>
                            <input type="text" id="fitnessHubIdBooking" name="fitness_hub_id">
                            <select class="form-select" id="fitnessHubSelectBookingSearchAdmin" name="fitnessHub_id"
                                required>
                                <option value="" disabled selected>Fitness Hub</option>
                                @foreach($FitnessHubs as $FitnessHub)
                                    <option value="{{ $FitnessHub->id }}" data-fitness-hub-id="{{ $FitnessHub->id }}"
                                        data-start-time="{{ \Carbon\Carbon::parse($FitnessHub->start_time)->format('H:i') }}"
                                        data-end-time="{{ \Carbon\Carbon::parse($FitnessHub->end_time)->format('H:i') }}"
                                        data-FitnessHub-space="{{ $FitnessHub->fitnessHub_space }}">
                                        {{ strtoupper($FitnessHub->fitness_hub_name) }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Please select an fitness hub</div>
                        </div>

                        <!-- Date Field -->
                        <div class="col-4">
                            <div class="position-relative">
                                <label for="fitnessHubDateFieldSearchAdmin" class="form-label">Date *</label>
                                <input type="text" id="fitnessHubDateFieldSearchAdmin" class="form-control"
                                    name="fitnessHubDateFieldSearchAdmin" required>
                                <i class="fa-regular fa-calendar position-absolute"
                                    style="top: 73%; right: 8px; transform: translateY(-50%);"></i>
                            </div>
                        </div>

                        <div class="col-4 d-flex justify-content-start align-items-end">
                            <button type="submit" form="SearchSlotAdminFitnessHub"
                                class="btn btn-primary searchBtn slot-checking-submit-btn-admin"
                                style="min-width: 100px; height: 38px;">
                                <i class="fa-solid fa-search me-1"></i><span>Search</span>
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