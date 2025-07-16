<!-- ADD FUNCTION ROOM -->
<div class="modal fade" id="adminCreateFunctionRooms" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-dark">NEW FUNCTION ROOM</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.store.function.rooms') }}" id="admin-new-function-rooms" method="POST"
                    enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf
                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="section" class="form-label">Section *</label>
                                <select name="function_room_section" class="form-select" required>
                                    <option value="" disabled selected>SELECT</option>
                                    <option value="ALMOND">ALMOND</option>
                                    <option value="BELIZE">BELIZE</option>
                                    <option value="CALLERY">CALLERY</option>
                                    <option value="DOLCE">DOLCE</option>
                                    <option value="ENCINO">ENCINO</option>
                                    <option value="ASTON">ASTON</option>
                                    <option value="RED OAK">RED OAK</option>
                                    <option value="MERANTI">MERANTI</option>
                                    <option value="SEQUOIA">SEQUOIA</option>
                                </select>
                                <div class="invalid-feedback">Required</div>
                            </div>

                            <div class="mb-3">
                                <label for="functionRoomRate" class="form-label">Rate /hr *</label>
                                <input type="number" class="form-control" id="functionRoomRate"
                                    name="function_room_rate" required>
                                <div class="invalid-feedback">Required</div>
                            </div>

                            <div class="mb-3">
                                <label for="functionRoomName" class="form-label">Function Room *</label>
                                <input type="text" class="form-control" id="functionRoomName" name="function_room_name"
                                    required>
                                <div class="invalid-feedback">Required</div>
                            </div>

                            <div class="mb-3">
                                <label for="functionRoomCapacity" class="form-label">Capacity</label>
                                <input type="number" class="form-control" id="functionRoomCapacity"
                                    name="function_room_capacity" required>
                                <div class="invalid-feedback">Required</div>
                            </div>

                            <div class="mb-3">
                                <label for="functionRoomDescription" class="form-label">Description *</label>
                                <textarea class="form-control" id="functionRoomDescription"
                                    name="function_room_description" required></textarea>
                                <div class="invalid-feedback">Required</div>
                            </div>

                            <div class="mb-3">
                                <label for="functionRoomPolicy" class="form-label">Policy *</label>
                                <textarea class="form-control" id="functionRoomPolicy" name="function_room_policy"
                                    required></textarea>
                                <div class="invalid-feedback">Required</div>
                            </div>


                        </div>
                        <!-- Right Column -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="functionRoomImage" class="form-label">Image *</label>
                                <input type="file" class="form-control" id="functionRoomImage"
                                    name="function_room_image" accept="image/*" required>

                                <div id="imagePreviewContainer"
                                    style="width: 100%; height: 150px; border: 1px solid #6c757d; margin-top: 10px; position: relative; overflow: hidden; border-radius: 5px; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa;">
                                    <span id="imagePlaceholderText" style="color: #6c757d;">Image Preview</span>
                                    <img id="imagePreview" src="#" alt="Image Preview"
                                        style="display: none; width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0;" />
                                </div>
                                <div class="invalid-feedback">Required</div>
                            </div>

                            <div class="mb-3">
                                <label for="functionRoom360" class="form-label">360 *</label>
                                <input type="file" class="form-control" id="functionRoom360" name="function_room_360"
                                    accept="image/*" required>

                                <div id="imagePreviewContainer360"
                                    style="width: 100%; height: 150px; border: 1px solid #6c757d; margin-top: 10px; position: relative; overflow: hidden; border-radius: 5px; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa;">
                                    <span style="color: #6c757d;">360 Preview</span>
                                    <img id="360Preview" src="#" alt="360 Preview"
                                        style="display: none; width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0;" />
                                </div>
                                <div class="invalid-feedback">Required</div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check align-items-center d-flex gap-2">
                                    <input class="form-check-input mt-0" type="checkbox" id="functionRoomFeatured"
                                        name="featured" value="1">
                                    <label class="form-check-label mb-0" for="functionRoomFeatured">
                                        Display on Home Page (Featured)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="admin-new-function-rooms" id="saveFunctionRoomBtn" class="btn btn-primary">
                    <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                    <span class="btn-text">Save</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- UPDATE FUNCTION ROOM -->
<div class="modal fade" id="adminUpdateFunctionRooms" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-dark">UPDATE FUNCTION ROOM</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.update.function.rooms') }}" id="admin-update-function-rooms" method="POST"
                    enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="info_id" id="info_id" value="" />
                    <div class="row">

                        <div class="col-6 ">
                            <div class="mb-3">
                                <label for="update_function_room_section" class="form-label">Section *</label>
                                <select name="function_room_section" class="form-select"
                                    id="update_function_room_section" required>
                                    <option value="" disabled selected>SELECT</option>
                                    <option value="ALMOND">ALMOND</option>
                                    <option value="BELIZE">BELIZE</option>
                                    <option value="CALLERY">CALLERY</option>
                                    <option value="DOLCE">DOLCE</option>
                                    <option value="ENCINO">ENCINO</option>
                                    <option value="ASTON">ASTON</option>
                                    <option value="RED OAK">RED OAK</option>
                                    <option value="MERANTI">MERANTI</option>
                                    <option value="SEQUOIA">SEQUOIA</option>
                                </select>
                                <div class="invalid-feedback">Required</div>
                            </div>

                            <div class="mb-3">
                                <label for="update_function_room_rate" class="form-label">Rate /hr *</label>
                                <input type="number" class="form-control" id="update_function_room_rate"
                                    name="function_room_rate" required>
                                <div class="invalid-feedback">Required</div>
                            </div>

                            <div class="mb-3">
                                <label for="update_function_room_name" class="form-label">Function Room *</label>
                                <input type="text" class="form-control" id="update_function_room_name"
                                    name="function_room_name" required>
                                <div class="invalid-feedback">
                                    Required
                                </div>
                            </div>


                            <div class="mb-3">
                                <label for="update_function_room_capacity" class="form-label">Capacity</label>
                                <input type="number" class="form-control" id="update_function_room_capacity"
                                    name="function_room_capacity" required>
                                <div class="invalid-feedback">Required</div>
                            </div>

                            <div class="mb-3">
                                <label for="update_function_room_description" class="form-label">Description *</label>
                                <textarea class="form-control" id="update_function_room_description"
                                    name="function_room_description" required></textarea>
                                <div class="invalid-feedback">Required</div>
                            </div>

                            <div class="mb-3">
                                <label for="update_function_room_policy" class="form-label">Policy *</label>
                                <textarea class="form-control" id="update_function_room_policy"
                                    name="function_room_policy" required></textarea>
                                <div class="invalid-feedback">Required</div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="current_image_function_room" class="form-label">Current Image File Name</label>
                            <input type="text" class="form-control w-100 mb-3" id="current_image_function_room"
                                name="current_image_function_room" readonly>

                            <label for="update_function_room_image" class="form-label">Image *</label>
                            <input type="file" class="form-control w-100" id="update_function_room_image"
                                name="function_room_image" accept="image/*">

                            <!-- Always-visible Image Preview Container -->
                            <div id="imagePreviewContainer" class="mb-3"
                                style="width: 200px; height: 150px; border: 1px solid  #6c757d; margin-top: 10px; position: relative; overflow: hidden; border-radius: 5px; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa;">
                                <!-- Placeholder text -->
                                <span id="updateImagePlaceholderText" style="color: #6c757d;">Image Preview</span>

                                <!-- Actual image -->
                                <img id="updateImagePreview" src="#" alt="Image Preview"
                                    style="display: none; width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0;" />
                            </div>

                            

                            <label for="current_360_function_room" class="form-label">Current Image File Name</label>
                            <input type="text" class="form-control w-100 mb-3" id="current_360_function_room"
                                name="current_360_function_room" readonly>

                            <label for="update_function_room_360" class="form-label">Image *</label>
                            <input type="file" class="form-control w-100" id="update_function_room_360"
                                name="function_room_360" accept="image/*">

                            <!-- Always-visible Image Preview Container -->
                            <div id="360PreviewContainer" class="mb-3"
                                style="width: 200px; height: 150px; border: 1px solid  #6c757d; margin-top: 10px; position: relative; overflow: hidden; border-radius: 5px; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa;">

                                <!-- Placeholder text -->
                                <span id="updateImagePlaceholderText" style="color: #6c757d;">Image Preview</span>

                                <!-- Actual 360 -->
                                <img id="update360Preview" src="#" alt="360 Preview"
                                    style="display: none; width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0;" />
                            </div>

                            <div class="mb-3">
                                <div class="form-check align-items-center d-flex gap-2">
                                    <input class="form-check-input mt-0" type="checkbox"
                                        id="update_function_room_featured" name="featured" value="1">
                                    <label class="form-check-label mb-0" for="functionRoomFeatured">
                                        Display on Home Page (Featured)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="admin-update-function-rooms" id="updateFunctionRoomBtn"
                    class="btn btn-primary">
                    <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                    <span class="btn-text">Update</span>
                </button>
            </div>
        </div>
    </div>
</div>