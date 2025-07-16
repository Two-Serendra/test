<!-- ADD AMENITY -->
<div class="modal fade" id="adminCreateAmenities" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-dark">New Amenity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.store.amenities') }}" id="admin-new-amenities" method="POST"
                    enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf
                    <div class="row">

                        <div class="col-12">
                            <div class="mb-3">
                                <label for="amenityName" class="form-label">Amenity *</label>
                                <input type="text" class="form-control" id="amenityName" name="amenity_name" required>
                                <div class="invalid-feedback">
                                    Required
                                </div>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="amenityDescription" class="form-label">Description *</label>
                                <textarea class="form-control" id="amenityDescription" name="amenity_description"
                                    required></textarea>
                                <div class="invalid-feedback">
                                    Required
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="amenityImage" class="form-label">Image *</label>
                            <input type="file" class="form-control w-100" id="amenityImage" name="amenity_image"
                                accept="image/*" required>
                            <img id="imagePreview" src="#" alt="Image Preview"
                                style="display: none; width: 100px; height: auto; margin-top: 10px;" />
                            <div class="invalid-feedback">Required</div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="submit" form="admin-new-amenities" class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- EDIT AMENITY -->
<div class="modal fade" id="amenityEdit" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-3" id="staticBackdropLabel">EDIT AMENITY INFORMATION</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateFormAmenity" method="POST" enctype="multipart/form-data" class="needs-validation"
                    novalidate>
                    @csrf
                    <input type="hidden" id="info_id" name="info_id" required>
                    <div class="col-12 mb-3">
                        <label for="edit_amenity_name" class="form-label">Amenity</label>
                        <input type="text" class="form-control" id="edit_amenity_name" name="amenity_name" required>
                        <div class="invalid-feedback">
                            Required
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="amenityDescription" class="form-label">Description *</label>
                        <textarea class="form-control" id="edit_amenity_description" name="amenity_description"
                            required></textarea>
                        <div class="invalid-feedback">
                            Required
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="mb-3">
                            <label for="currentImageFileName" class="form-label">Current Image File Name</label>
                            <input type="text" class="form-control" id="currentImageFileName"
                                name="current_image_file_name" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_amenity_image" class="form-label">Upload New Image (Optional)</label>
                        <input type="file" class="form-control" id="edit_amenity_image" name="amenity_image"
                            accept="image/*">
                        <img id="edit_imagePreview" src="#" alt="Image Preview"
                            style="display: none; width: 100px; height: auto; margin-top: 10px;" />
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="submit" form="updateFormAmenity" class="btn btn-primary">Update</button>
            </div>
        </div>
    </div>
</div>

<!-- REMARKS AMENITY -->
<div class="modal fade" id="amenityRemarks" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-3" id="staticBackdropLabel">ADD REMARKS</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addAmenityRemarks" method="POST" enctype="multipart/form-data" class="needs-validation"
                    novalidate>
                    @csrf
                    <input type="hidden" id="info_id" name="info_id" required>
                    <div class="mb-3">
                        <label for="amenityRemarks" class="form-label">Remarks *</label>
                        <textarea class="form-control" id="amenityRemarks" name="amenity_remarks" required></textarea>
                        <div class="invalid-feedback">
                            Required
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>