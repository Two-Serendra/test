<!-- ADD ACTIVITIES -->
<div class="modal fade" id="activityAdd" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-3" id="staticBackdropLabel">ADD NEW ACTIVITY</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('activitiesAdd') }}" id="activitiesForm" method="POST"
                    enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf
                    <div class="row">
                        <div class="col-12 mb-3">
                            <select class="form-select" id="amenitySelect" name="amenity_id" required>
                                <option value="" disabled selected>Select Amenity</option>
                                @foreach($amenities as $amenity)
                                    <option value="{{ $amenity->id }}">
                                        {{ strtoupper($amenity->amenity_name) }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Please select an amenity</div>

                        </div>

                        <div class="col-6">
                            <label for="activityName" class="form-label">Activity *</label>
                            <input type="text" class="form-control mb-3" id="activityName" name="activity_name"
                                required>
                            <div class="invalid-feedback">
                                Required
                            </div>

                            <label for="activityDescription" class="form-label">Description *</label>
                            <textarea class="form-control mb-3" id="activityDescription" name="activity_description"
                                required></textarea>
                            <div class="invalid-feedback">
                                Required
                            </div>

                            <label for="activityImage" class="form-label">Image *</label>
                            <input type="file" class="form-control w-100" id="activityImage" name="activity_image"
                                accept="image/*" required>
                            <div id="imagePreviewContainer"
                                style="width: 130px; height: 130px; margin-top: 10px; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center; color: #888;">
                                <span>Image Preview Here</span>
                                <img id="imagePreviewActivity" src="#" alt="Image Preview"
                                    style="display: none; width: 100%; height: 100%; object-fit: cover;">
                            </div>
                            <div class="invalid-feedback">Required</div>


                            <label class="form-label mt-3">Max Booking Per Unit Per Month *</label>
                            <div class="d-flex align-items-center ms-3 mb-3 gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="activity_max_booking"
                                        id="oneBooking" value="1" required>
                                    <label class="form-check-label" for="oneBooking">1</label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="activity_max_booking"
                                        id="twoBooking" value="2" required>
                                    <label class="form-check-label" for="twoBooking">2</label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="activity_max_booking"
                                        id="threeBooking" value="3" required>
                                    <label class="form-check-label" for="threeBooking">3</label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="activity_max_booking"
                                        id="fourBooking" value="4" required>
                                    <label class="form-check-label" for="fourBooking">4</label>
                                </div>
                            </div>


                            <label class="form-label mt-3">Space *</label>
                            <div class="d-flex align-items-center ms-2 mb-3">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="space" id="capacity1" value="1"
                                        required>
                                    <label class="form-check-label" for="capacity1">1</label>
                                </div>

                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="space" id="capacity2" value="2"
                                        required>
                                    <label class="form-check-label" for="capacity2">2</label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="space" id="capacity3" value="3"
                                        required>
                                    <label class="form-check-label" for="capacity3">3</label>
                                </div>
                            </div>
                            <div class="invalid-feedback">Required</div>


                        </div>

                        <div class="col-6 mb-3">
                            <label class="form-label">Set Time *</label>
                            <div class="d-flex mb-3">
                                <div class="form-check me-3">

                                    <input class="form-check-input" type="radio" name="timeOption" id="sameTime"
                                        value="same" checked>
                                    <label class="form-check-label" for="sameTime">For All Days</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="timeOption" id="manualTime"
                                        value="manual">
                                    <label class="form-check-label" for="manualTime">Set Manually</label>
                                </div>
                            </div>
                            <div class="" id="manualTimeInputs" style="display: none;">
                                @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                    <div class="row align-items-center mb-3">
                                        <div class="col-md-2 col-sm-1 text-center">
                                            <label class="form-label m-0">{{ $day }}</label>
                                        </div>
                                        <div class="col-md-5 col-sm-6">
                                            <input type="time" class="form-control"
                                                name="times[{{ strtolower($day) }}][start]">
                                        </div>
                                        <div class="col-md-5 col-sm-6">
                                            <input type="time" class="form-control"
                                                name="times[{{ strtolower($day) }}][end]">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="" id="sameTimeInputs">
                                <div class="row">
                                    <div class="col-md-5 col-sm-6">
                                        <input type="time" class="form-control time" id="sameStartTime"
                                            name="start_time" required>
                                    </div>
                                    <div class="col-md-5 col-sm-6">
                                        <input type="time" class="form-control time" id="sameEndTime" name="end_time"
                                            required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="submit" form="activitiesForm" id="saveActivityBtn"
                    class="btn btn-primary d-flex align-items-center justify-content-center"
                    style="min-width: 100px; height: 38px;">
                    <span class="btn-text">Create</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- EDIT ACTIVITY -->
<div class="modal fade" id="activityEdit" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-3" id="staticBackdropLabel">EDIT ACTIVITY INFORMATION</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateFormActivity" method="POST" enctype="multipart/form-data" class="needs-validation"
                    novalidate>
                    @csrf
                    <input type="hidden" id="act_id" name="act_id" required>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <input type="hidden" id="hidden_amenity_id_activity" name="hidden_amenity_id_activity">

                            <select class="form-select" id="edit_amenity_select" name="amenity_id_activity" disabled>
                                <option value="" disabled selected>Select Amenity</option>
                                @foreach($amenities as $amenity)
                                    <option value="{{ $amenity->id }}">{{ strtoupper($amenity->amenity_name) }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Please select an amenity</div>
                        </div>

                        <div class="col-6">
                            <label for="activityName" class="form-label">Activity *</label>
                            <input type="text" class="form-control" id="edit_activity_name" name="activity_name"
                                required>
                            <div class="invalid-feedback">
                                Required
                            </div>

                            <label for="edit_activity_description" class="form-label">Description *</label>
                            <textarea class="form-control" id="edit_activity_description" name="activity_description"
                                required></textarea>
                            <div class="invalid-feedback">
                                Required
                            </div>

                            <label for="currentImageFileNameActivity" class="form-label">Current Image File Name</label>
                            <input type="text" class="form-control w-100 mb-3" id="currentImageFileNameActivity"
                                name="activity_image_file_name" readonly>

                            <div class="mb-3">
                                <label for="edit_activity_image" class="form-label">Upload New Image (Optional)</label>
                                <input type="file" class="form-control" id="edit_amenity_image" name="activity_image"
                                    accept="image/*">
                                <img id="edit_imagePreviewActivity" src="#" alt="Image Preview"
                                    style="display: none; width: 100px; height: auto; margin-top: 10px;" />
                            </div>
                            <div class="invalid-feedback">Required</div>

                            <label class="form-label mt-3">Max Booking per unit per month *</label>
                            <div class="d-flex align-items-center ms-2 mb-3 gap-3">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="edit_activity_max_booking"
                                        id="oneBooking" value="1" required>
                                    <label class="form-check-label" for="oneBooking">1</label>
                                </div>

                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="edit_activity_max_booking"
                                        id="twoBooking" value="2" required>
                                    <label class="form-check-label" for="twoBooking">2</label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="edit_activity_max_booking"
                                        id="threeBooking" value="3" required>
                                    <label class="form-check-label" for="threeBooking">3</label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="edit_activity_max_booking"
                                        id="fourBooking" value="4" required>
                                    <label class="form-check-label" for="fourBooking">4</label>
                                </div>

                            </div>


                            <label class="form-label mt-3">Space *</label>
                            <div class="d-flex align-items-center ms-2 mb-3">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="edit_activity_space"
                                        id="capacity1" value="1" required>
                                    <label class="form-check-label" for="capacity1">1</label>
                                </div>

                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="edit_activity_space"
                                        id="capacity2" value="2" required>
                                    <label class="form-check-label" for="capacity2">2</label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="edit_activity_space"
                                        id="capacity3" value="3" required>
                                    <label class="form-check-label" for="capacity3">3</label>
                                </div>
                            </div>



                        </div>

                        <div class="col-6">
                            <label class="form-label">Set Time *</label>
                            <div class="d-flex mb-3">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="edit_timeOption"
                                        id="edit_sameTime" value="same" checked>
                                    <label class="form-check-label" for="edit_sameTime">For All Days</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="edit_timeOption"
                                        id="edit_manualTime" value="manual">
                                    <label class="form-check-label" for="edit_manualTime">Set Manually</label>
                                </div>
                            </div>

                            <div id="edit_manualTimeInputs" style="display: none;">
                                @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                    <div class="row align-items-center mb-3">
                                        <div class="col-md-2 col-sm-1 text-center">
                                            <label class="form-label m-0">{{ ucfirst($day) }}</label>
                                        </div>
                                        <div class="col-md-5 col-sm-6">
                                            <input type="time" class="form-control" name="times[{{ $day }}][start]">
                                        </div>
                                        <div class="col-md-5 col-sm-6">
                                            <input type="time" class="form-control" name="times[{{ $day }}][end]">
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div id="edit_sameTimeInputs">
                                <div class="row">
                                    <div class="col-md-5 col-sm-6">
                                        <input type="time" class="form-control time" id="edit_sameStartTime"
                                            name="start_time">
                                    </div>
                                    <div class="col-md-5 col-sm-6">
                                        <input type="time" class="form-control time" id="edit_sameEndTime"
                                            name="end_time">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="submit" form="updateFormActivity" class="btn btn-primary">Update</button>
            </div>
        </div>
    </div>
</div>

<!-- REMARKS ACTIVITY -->
<div class="modal fade" id="activityRemarks" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-3" id="staticBackdropLabel">ADD REMARKS</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addActivityRemarks" method="POST" enctype="multipart/form-data" class="needs-validation"
                    novalidate>
                    @csrf
                    <input type="hidden" id="activity_id" name="activity_id" required>
                    <div class="mb-3">
                        <label for="amenityRemarks" class="form-label">Remarks *</label>
                        <textarea class="form-control" id="activityRemarks" name="activity_remarks" required></textarea>
                        <div class="invalid-feedback">
                            Required
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="submit" form="addActivityRemarks" class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>
</div>


{{-- Calendar Modal --}}
<div class="modal fade" id="calendarModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
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

                        <div class="col-12 mb-2">
                            <label for="" class="form-label"><b>Activity</b></label>
                            <p id="calendar_activity_name" class="form-control-static calendar-value"></p> <!-- Display as text -->
                        </div>

                        <div class="col-6">
                            <div class="mb-2">
                                <label for="" class="form-label"><b>Unit</b></label>
                                <p id="calendar_unit" class="form-control-static calendar-value"></p> <!-- Display as text -->
                            </div>

                            <div class="mb-2">
                                <label for="" class="form-label"><b>Name</b></label>
                                <p id="calendar_name" class="form-control-static calendar-value"></p> <!-- Display as text -->
                            </div>

                            <div class="mb-2">
                                <label for="" class="form-label"><b>Contact</b></label>
                                <p id="calendar_contact_number" class="form-control-static calendar-value"></p>
                                <!-- Display as text -->
                            </div>

                        </div>

                        <div class="col-6">
                            <div class="mb-2">
                                <label for="" class="form-label"><b>Date</b></label>
                                <p id="calendar_booking_date" class="form-control-static calendar-value"></p> <!-- Display as text -->
                            </div>

                            <div class="mb-2">
                                <label for="" class="form-label"><b>Start</b></label>
                                <p id="calendar_booking_start_time" class="form-control-static calendar-value"></p>
                                <!-- Display as text -->
                            </div>

                            <div class="mb-2">
                                <label for="" class="form-label"><b>End</b></label>
                                <p id="calendar_booking_end_time" class="form-control-static calendar-value"></p>
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