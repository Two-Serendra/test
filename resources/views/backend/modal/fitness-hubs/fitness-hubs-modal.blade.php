<!-- ADD ACTIVITIES -->
<div class="modal fade" id="addFitnessHubModal" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-3" id="staticBackdropLabel">ADD NEW FITNESS HUB</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.store.fitness.hub') }}" id="fitnessHubsForm" method="POST"
                    enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf

                    <div class="row">
                        <!-- LEFT COLUMN -->
                        <div class="col-md-6">
                            <label for="fitnessHubName" class="form-label">Fitness Hub *</label>
                            <input type="text" class="form-control mb-3" id="fitnessHubName" name="fitness_hub_name"
                                required>
                            <div class="invalid-feedback">Required</div>

                            <label for="fitnessHubDescription" class="form-label">Description *</label>
                            <textarea class="form-control mb-3" id="fitnessHubDescription"
                                name="fitness_hub_description" required></textarea>
                            <div class="invalid-feedback">Required</div>
                        </div>

                        <!-- RIGHT COLUMN -->
                        <div class="col-md-6">

                            <label for="fitnessHubImage" class="form-label">Image *</label>
                            <input type="file" class="form-control w-100" id="fitnessHubImage" name="fitness_hub_image"
                                accept="image/*" required>

                            <div id="imagePreviewContainer"
                                style="width: 130px; height: 130px; margin-top: 10px; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center; color: #888;">
                                <span>Image Preview Here</span>
                                <img id="imagePreviewFitnessHub" src="#" alt="Image Preview"
                                    style="display: none; width: 100%; height: 100%; object-fit: cover;">
                            </div>

                            <div class="invalid-feedback">Required</div>

                            <label class="form-label mt-3">Max Booking Per HR Per Unit Per Week *</label>
                            <div class="d-flex align-items-center ms-3 mb-3 gap-3 flex-wrap">
                                @foreach([1, 2, 3, 4] as $num)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="fitness_hub_max_booking"
                                            id="booking{{ $num }}" value="{{ $num }}" required>
                                        <label class="form-check-label" for="booking{{ $num }}">{{ $num }}</label>
                                    </div>
                                @endforeach
                            </div>

                            <label class="form-label">Start Time *</label>
                            <select class="form-select mb-3" id="StartTime" name="start_time" required>
                                <option value="">Select Start Time</option>
                                @for ($hour = 5; $hour <= 22; $hour++)
                                    @php
                                        $time = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
                                    @endphp
                                    <option value="{{ $time }}">{{ date('h:i A', strtotime($time)) }}</option>
                                @endfor
                            </select>

                            <label class="form-label">End Time *</label>
                            <select class="form-select mb-3" id="EndTime" name="end_time" required>
                                <option value="">Select End Time</option>
                                @for ($hour = 5; $hour <= 22; $hour++)
                                    @php
                                        $time = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
                                    @endphp
                                    <option value="{{ $time }}">{{ date('h:i A', strtotime($time)) }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </form>

                <div class="modal-footer">
                    <button type="submit" form="fitnessHubsForm" id="saveFitnessHubBtn"
                        class="btn btn-primary d-flex align-items-center justify-content-center"
                        style="min-width: 100px; height: 38px;">
                        <span class="btn-text">Create</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>




<!-- EDIT FITNESS HUB -->
<div class="modal fade" id="editFitnessHubModal" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-3" id="staticBackdropLabel">EDIT FITNESS HUB</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('update.fitness.hub') }}" id="updateFitnessHubsForm" method="POST"
                    enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf
                    <input type="hidden" id="fitnessHubId" name="id">
                    <div class="row">
                        <!-- LEFT COLUMN -->
                        <div class="col-md-6">
                            <label for="fitnessHubName" class="form-label">Fitness Hub *</label>
                            <input type="text" class="form-control mb-3" id="editFitnessHubName" name="fitness_hub_name"
                                required>
                            <div class="invalid-feedback">Required</div>

                            <label for="fitnessHubDescription" class="form-label">Description *</label>
                            <textarea class="form-control mb-3" id="editFitnessHubDescription"
                                name="fitness_hub_description" required></textarea>
                            <div class="invalid-feedback">Required</div>
                        </div>

                        <!-- RIGHT COLUMN -->
                        <div class="col-md-6">

                            <label for="fitnessHubImage" class="form-label">Image *</label>
                            <input type="file" class="form-control w-100" id="fitnessHubImage" name="fitness_hub_image"
                                accept="image/*">

                            <div id="editImagePreviewContainer"
                                style="width: 130px; height: 130px; margin-top: 10px; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center; color: #888;">
                                <span>Image Preview Here</span>
                                <img id="editImagePreviewFitnessHub" src="#" alt="Image Preview"
                                    style="display: none; width: 100%; height: 100%; object-fit: cover;">
                            </div>

                            <div class="invalid-feedback">Required</div>

                            <label class="form-label mt-3">Max Booking Per HR Per Unit Per Week *</label>
                            <div class="d-flex align-items-center ms-3 mb-3 gap-3 flex-wrap">
                                @foreach([1, 2, 3, 4] as $num)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="edit_fitness_hub_max_booking"
                                            id="booking{{ $num }}" value="{{ $num }}" required>
                                        <label class="form-check-label" for="booking{{ $num }}">{{ $num }}</label>
                                    </div>
                                @endforeach
                            </div>

                            <label class="form-label">Start Time *</label>
                            <select class="form-select mb-3" id="editStartTime" name="start_time" required>
                                <option value="">Select Start Time</option>
                                @for ($hour = 5; $hour <= 22; $hour++)
                                    @php
                                        $time = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
                                    @endphp
                                    <option value="{{ $time }}">{{ date('h:i A', strtotime($time)) }}</option>
                                @endfor
                            </select>

                            <label class="form-label">End Time *</label>
                            <select class="form-select mb-3" id="editEndTime" name="end_time" required>
                                <option value="">Select End Time</option>
                                @for ($hour = 5; $hour <= 22; $hour++)
                                    @php
                                        $time = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
                                    @endphp
                                    <option value="{{ $time }}">{{ date('h:i A', strtotime($time)) }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </form>

                <div class="modal-footer">
                    <button type="submit" form="updateFitnessHubsForm" id="updateFitnessHubBtn"
                        class="btn btn-primary d-flex align-items-center justify-content-center"
                        style="min-width: 100px; height: 38px;">
                        <span class="btn-text">Update</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="fitnessHubRemarks" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-3" id="staticBackdropLabel">ADD REMARKS</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="deactivateFitnessHubForm" method="POST" action="{{ route('deactivate.fitness.hub') }}"
                    enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf
                    <input type="hidden" id="fitnessHub_id" name="fitnessHub_id" required>
                    <div class="mb-3">
                        <label for="fitnessHubRemarks" class="form-label">Remarks *</label>
                        <textarea class="form-control" id="fitnessHubRemarksInput" name="fitnessHub_remarks"
                            required></textarea>
                        <div class="invalid-feedback">
                            Required
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="submit" form="deactivateFitnessHubForm" id="deactivateFitnessHubBtn"
                    class="btn btn-primary d-flex align-items-center justify-content-center"
                    style="min-width: 100px; height: 38px;">
                    <span class="btn-text">Save</span>
                </button>
            </div>
        </div>
    </div>
</div>



<!-- DATE BLOCKING -->
<div class="modal fade" id="NewDateBlockingFitnessHubModal" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-3" id="staticBackdropLabel">BLOCK DATE</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.new.date.blocking.fitness.hub') }}" id="NewDateBlockingFitnessHub" method="POST"
                    enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf
                    <div class="row">
                        <div class="col-12 mb-3">
                            <select class="form-select" id="fitnessHubSelectBlocking" name="fitnessHub_id_blocking" required>
                                <option value="" disabled selected>Select Fitness Hub</option>
                                @foreach($fitnessHubs as $fitnessHub)
                                    <option value="{{ $fitnessHub->id }}">
                                        {{ strtoupper($fitnessHub->fitness_hub_name) }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Please select an Fitness Hub</div>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="blockingRemarks" class="form-label">Remarks *</label>
                            <textarea class="form-control" id="blockingRemarks" name="blocking_remarks"
                                required></textarea>
                            <div class="invalid-feedback">
                                Required
                            </div>
                        </div>


                        <div class="col-6 ">
                            <div class="mb-3 position-relative">
                                <label for="BlockingDateFieldStartFitnessHub" class="form-label">Start Date *</label>
                                <input type="text" id="BlockingDateFieldStartFitnessHub" class="form-control"
                                    name="date_blocking_start" required>
                                <i class="fa-regular fa-calendar position-absolute"
                                    style="top: 73%; right: 8px; transform: translateY(-50%);"></i>
                            </div>
                        </div>

                        <div class="col-6 ">
                            <div class="mb-3 position-relative">
                                <label for="BlockingDateFieldEndFitnessHub" class="form-label">End Date *</label>
                                <input type="text" id="BlockingDateFieldEndFitnessHub" class="form-control"
                                    name="date_blocking_end" required>
                                <i class="fa-regular fa-calendar position-absolute"
                                    style="top: 73%; right: 8px; transform: translateY(-50%);"></i>
                            </div>

                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="submit" form="NewDateBlockingFitnessHub" id="saveDateBlockingFitnessHubBtn"
                    class="btn btn-primary d-flex align-items-center justify-content-center"
                    style="min-width: 100px; height: 38px;">
                    <span class="btn-text">Save</span>
                </button>
            </div>
        </div>
    </div>
</div>