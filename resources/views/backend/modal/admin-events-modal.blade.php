<!-- ADD EVENTS -->
<div class="modal fade" id="adminCreateEvents" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl"> <!-- WIDER MODAL -->
        <div class="modal-content ">
            <div class="modal-header">
                <h5 class="modal-title text-dark">New Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.store.events') }}" id="adminNewEvent" method="POST"
                    enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf
                    <div class="row">
                        <div class="col-6">
                            <!-- Event Name -->
                            <div class="mb-3">
                                <label for="eventTitle" class="form-label">Event Title *</label>
                                <textarea class="form-control" id="eventTitle" name="event_title" rows="2"
                                    required></textarea>
                                <div class="invalid-feedback">Required</div>
                            </div>

                            <div class="mb-3">
                                <label for="eventDate" class="form-label">Event Date *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">
                                        <i class='bx bx-calendar'></i>
                                    </span>
                                    <input type="text" class="form-control bg-white text-dark" id="eventDate"
                                        name="event_date" required>
                                </div>
                                <div class="invalid-feedback">Required</div>
                            </div>

                            <!-- File Input (100% width) -->
                            <div class="mb-3">
                                <label for="eventImage" class="form-label">Image *</label>
                                <input type="file" class="form-control w-100" id="eventImage" name="event_image"
                                    accept="image/*" required>
                                <div class="invalid-feedback">Required</div>
                            </div>

                            <!-- Preview Box (100% width) -->
                            <div id="imagePreviewContainer" style="margin-top: 10px; border: 2px dashed #ced4da; padding: 10px; text-align: center; border-radius: 5px;
                           width: 100%; height: 400px; display: flex; align-items: center; justify-content: center;">
                                <img id="imagePreview" src="#" alt="Image Preview"
                                    style="display: none; max-height: 100%; max-width: 100%; object-fit: contain;" />
                                <p id="previewPlaceholder" class="text-muted m-0">Image preview</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label for="eventDetails" class="form-label">Event Details *</label>
                                <textarea class="form-control" id="event_details" name="event_details" rows="20"
                                    required></textarea>
                                <div class="invalid-feedback">Required</div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="adminNewEvent" id="saveEventBtn"
                    class="btn btn-primary d-flex align-items-center justify-content-center"
                    style="min-width: 100px; height: 38px;">
                    <span class="btn-text">Create</span>
                </button>
            </div>
        </div>
    </div>
</div>


<!-- UPDATE EVENTS -->
<div class="modal fade" id="adminUpdateEvents" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-dark">Update Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form action="{{ route('admin.update.events') }}" id="admin-update-event" method="POST"
                    enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf
                    <div class="row">
                        <div class="col-6">
                            <input type="hidden" name="info_id" id="info_id" />

                            <div class="mb-3">
                                <label for="update_event_title" class="form-label">Event Title *</label>
                                <textarea class="form-control" id="update_event_title" name="event_title" rows="2"
                                    required></textarea>
                                <div class="invalid-feedback">Required</div>
                            </div>

                            <div class="mb-3">
                                <label for="eventDate" class="form-label">Event Date *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">
                                        <i class='bx bx-calendar'></i>
                                    </span>
                                    <input type="text" class="form-control bg-white text-dark" id="update_event_date"
                                        name="event_date" required>
                                </div>
                                <div class="invalid-feedback">Required</div>
                            </div>



                            <div class="mb-3">
                                <label for="current_event_image" class="form-label">Current Image File Name</label>
                                <input type="text" class="form-control" id="current_event_image" readonly>
                            </div>

                            <div class="mb-3">
                                <label for="update_event_image" class="form-label">Replace Image (optional)</label>
                                <input type="file" class="form-control w-100" id="update_event_image" name="event_image"
                                    accept="image/*">
                            </div>

                            <!-- Image Preview -->
                            <div id="eventImagePreviewContainer"
                                style="margin-top: 10px; border: 2px dashed #ced4da; padding: 10px; text-align: center; border-radius: 5px;
                               width: 100%; height: 400px; display: flex; align-items: center; justify-content: center;">
                                <img id="updateEventImagePreview" src="#" alt="Image Preview"
                                    style="display: none; max-height: 100%; max-width: 100%; object-fit: contain;" />
                                <p id="updateEventPreviewPlaceholder" class="text-muted m-0">Image preview</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label for="update_event_details" class="form-label">Event Details *</label>
                                <textarea class="form-control" id="update_event_details" name="event_details" rows="20"
                                    required></textarea>
                                <div class="invalid-feedback">Required</div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="admin-update-event" id="updateEventBtn"
                    class="btn btn-primary d-flex align-items-center justify-content-center"
                    style="min-width: 100px; height: 38px;">
                    <span class="btn-text">Update</span>
                </button>
            </div>
        </div>
    </div>
</div>