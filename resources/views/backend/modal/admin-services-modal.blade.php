<!-- Add Modal -->
<div class="modal fade" id="serviceAddModal" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg"> <!-- Larger modal for better spacing -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-dark">New Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form action="{{ route('new.services') }}" id="newServiceForm" method="POST"
                    enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf

                    <div class="row">
                        <!-- Left Side: Form Fields -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-dark" for="service-name">Service Name</label>
                                <input type="text" class="form-control" id="service-name" name="service_name"
                                    placeholder="Enter service name" required>
                                <div class="invalid-feedback">Required</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-dark" for="short-description">Short Description</label>
                                <textarea id="service-short-description" name="service_short_description"
                                    class="form-control" rows="2" placeholder="Brief details..." required></textarea>
                                <div class="invalid-feedback">Required</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-dark" for="long-description">Long Description</label>
                                <textarea id="service-long-description" name="service_long_description"
                                    class="form-control" rows="4" placeholder="Detailed information..."
                                    required></textarea>
                                <div class="invalid-feedback">Required</div>
                            </div>


                        </div>

                        <!-- Right Side: Image Preview -->
                        <div class="col-md-6 d-flex flex-column align-items-center">
                            <div class="mb-3 w-100">
                                <label class="form-label text-dark">Service Image</label>

                                <!-- Image preview frame -->
                                <div id="image-frame"
                                    class="border border-secondary rounded mb-3 d-flex justify-content-center align-items-center"
                                    style="height: 200px; width: 100%; background-color: #f8f9fa;">
                                    <img id="preview-image" src="#" alt="Image Preview" class="img-fluid d-none"
                                        style="max-height: 100%; max-width: 100%;">
                                    <span id="placeholder-text" class="text-muted">Image preview will appear here</span>
                                </div>

                                <!-- Upload button -->
                                <div class="button-wrapper text-center">
                                    <label for="upload" class="btn btn-primary me-2 mb-2" tabindex="0">
                                        <span class="d-none d-sm-block">Upload</span>
                                        <i class="bx bx-upload d-block d-sm-none"></i>
                                    </label>
                                    <input type="file" id="upload" name="service_image" class="form-control d-none"
                                        required accept="image/png, image/jpeg">
                                    <div class="invalid-feedback text-start">Required</div>
                                </div>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="newServiceForm" id="saveServiceBtn" class="btn btn-primary">
                    <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                    <span class="btn-text">Save</span>
                </button>
            </div>
        </div>
    </div>
</div>


<!-- Update Modal -->
<div class="modal fade" id="serviceUpdateModal" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg"> <!-- Larger modal for better spacing -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-dark">Update Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form action="{{ route('update.services') }}" id="updateServiceForm" method="POST"
                    enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="info_id" id="info_id" value="" />

                    <div class="row">
                        <!-- Left Side: Form Fields -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-dark" for="update_service_name">Service Name</label>
                                <input type="text" class="form-control" id="update_service_name" name="service_name"
                                    placeholder="Enter service name" required>
                                <div class="invalid-feedback">Required</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-dark" for="update_service_short_description">Short
                                    Description</label>
                                <textarea id="update_service_short_description" name="service_short_description"
                                    class="form-control" rows="2" placeholder="Brief details..." required></textarea>
                                <div class="invalid-feedback">Required</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-dark" for="update_service_long_description">Long
                                    Description</label>
                                <textarea id="update_service_long_description" name="service_long_description"
                                    class="form-control" rows="4" placeholder="Detailed information..."
                                    required></textarea>
                                <div class="invalid-feedback">Required</div>
                            </div>
                        </div>

                        <!-- Right side: Image -->
                        <div class="col-md-6 d-flex flex-column align-items-center">
                            <div class="mb-3 w-100">
                                <label class="form-label text-dark">Service Image</label>

                                <div id="update_image_frame"
                                    class="border border-secondary rounded mb-3 d-flex justify-content-center align-items-center"
                                    style="height: 200px; width: 100%; background-color: #f8f9fa;">
                                    <img id="update_preview_image" src="#" alt="Image Preview" class="img-fluid d-none"
                                        style="max-height: 100%; max-width: 100%;">
                                    <span id="update_placeholder_text" class="text-muted">Image preview will appear
                                        here</span>
                                </div>

                                <div class="text-center">
                                    <label for="update_upload" class="btn btn-primary mb-2">
                                        <span class="d-none d-sm-inline">Upload</span>
                                        <i class="bx bx-upload d-inline d-sm-none"></i>
                                    </label>
                                    <input type="file" id="update_upload" name="service_image"
                                        class="form-control d-none" accept="image/png, image/jpeg">
                                    <div class="invalid-feedback text-start">Required</div>
                                </div>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>

                <button type="submit" form="updateServiceForm" id="updateServiceBtn" class="btn btn-primary">
                    <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                    <span class="btn-text">Update</span>
                </button>
            </div>
        </div>
    </div>
</div>