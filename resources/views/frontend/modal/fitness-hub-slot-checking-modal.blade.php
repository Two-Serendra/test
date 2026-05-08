<div class="modal fade" id="FitnessHubSlotCheckingModalUser" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-3">Slot Checking</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="max-height: calc(100vh - 200px); overflow-y: auto;">
                <form action="{{ route('user.fetch.available.slot.fitness.hub') }}" id="SearchSlotUserFitnessHub"
                    method="POST" class="SearchSlotUserFitnessHub" enctype="multipart/form-data" novalidate>
                    @csrf
                    <div class="row mb-3">
                        <div class="col-12 col-md-4 mb-3 mb-md-0">
                            <label for="fitnessHubSelectBookingSearchUser" class="form-label">Select Fitness Hub
                                *</label>
                            <select class="form-select" id="fitnessHubSelectBookingSearchUser" name="fitness_hub_id"
                                required>
                                <option value="" disabled selected>Fitness Hub</option>
                                @foreach($all_fitness_hubs as $all_fitness_hub)
                                    <option value="{{ $all_fitness_hub->id }}"
                                        data-start-time="{{ \Carbon\Carbon::parse($all_fitness_hub->start_time)->format('H:i') }}"
                                        data-end-time="{{ \Carbon\Carbon::parse($all_fitness_hub->end_time)->format('H:i') }}">
                                        {{ strtoupper($all_fitness_hub->fitness_hub_name) }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Please select a fitness hub</div>
                        </div>

                        <div class="col-12 col-md-4 mb-3 mb-md-0">
                            <div class="position-relative">
                                <label for="fitnessHubDateFieldSearchUser" class="form-label">Date *</label>
                                <input type="text" id="fitnessHubDateFieldSearchUser" class="form-control"
                                    name="fitnessHubDateFieldSearchUser" required>
                                <i class="fa-regular fa-calendar position-absolute"
                                    style="top: 73%; right: 8px; transform: translateY(-50%);"></i>
                            </div>
                        </div>

                        <div class="col-12 col-md-4 mb-3 mb-md-0 d-flex justify-content-start align-items-end">
                            <button type="submit" form="SearchSlotUserFitnessHub"
                                class="btn btn-primary customBtn slot-checking-submit-btn-fitness-hub"
                                style="min-width: 100px; height: 38px;">
                                <i class="fa-solid fa-search me-1"></i><span> Search</span>
                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"
                                    id="spinner"></span>
                            </button>
                        </div>


                    </div>

                    <div class="all-slot-available-user-fitness-hub">

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>