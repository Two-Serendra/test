<!-- ADD FUNCTION ROOM -->
<div class="modal fade" id="adminCreateFunctionRooms" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
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
                                <label class="form-label">Discount (%)</label>
                                <input type="number" name="discount" id="discount" class="form-control"
                                    min="0" max="100" step="0.01">
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
                                <label for="functionRoomImage" class="form-label">Images (Max 4) *</label>
                                <input type="file" class="form-control" id="functionRoomImage"
                                    name="function_room_image[]" accept="image/*" multiple required>

                                <!-- Fixed 4-slot Preview Container -->
                                <div id="imagePreviewContainer" style="width: 100%; margin-top: 10px; 
                                display: grid; grid-template-columns: repeat(4, 100px); 
                                gap: 10px; padding: 10px; justify-content: center;">
                                    <!-- 4 fixed slots -->
                                    <div class="image-slot" data-slot="0">Image 1</div>
                                    <div class="image-slot" data-slot="1">Image 2</div>
                                    <div class="image-slot" data-slot="2">Image 3</div>
                                    <div class="image-slot" data-slot="3">Image 4</div>
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
                <button type="submit" form="admin-new-function-rooms" id="saveFunctionRoomBtn"
                    class="btn btn-primary d-flex align-items-center justify-content-center"
                    style="min-width: 100px; height: 38px;">
                    <span class="btn-text">Create</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- UPDATE FUNCTION ROOM -->
<div class="modal fade" id="adminUpdateFunctionRooms" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-dark">EDIT FUNCTION ROOM</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.update.function.rooms') }}" id="admin-update-function-rooms" method="POST"
                    enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="editFunctionRoomId">

                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editFunctionRoomSection" class="form-label">Section *</label>
                                <select name="function_room_section" id="editFunctionRoomSection" class="form-select"
                                    required>
                                    <option value="" disabled>SELECT</option>
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
                                <label for="editFunctionRoomRate" class="form-label">Rate /hr *</label>
                                <input type="number" class="form-control" id="editFunctionRoomRate"
                                    name="function_room_rate" required>
                                <div class="invalid-feedback">Required</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Discount (%)</label>
                                <input type="number" name="discount" id="editFunctionRoomDiscount" class="form-control"
                                    min="0" max="100" step="0.01">
                            </div>

                            <div class="mb-3">
                                <label for="editFunctionRoomName" class="form-label">Function Room *</label>
                                <input type="text" class="form-control" id="editFunctionRoomName"
                                    name="function_room_name" required>
                                <div class="invalid-feedback">Required</div>
                            </div>

                            <div class="mb-3">
                                <label for="editFunctionRoomCapacity" class="form-label">Capacity</label>
                                <input type="number" class="form-control" id="editFunctionRoomCapacity"
                                    name="function_room_capacity" required>
                                <div class="invalid-feedback">Required</div>
                            </div>

                            <div class="mb-3">
                                <label for="editFunctionRoomDescription" class="form-label">Description *</label>
                                <textarea class="form-control" id="editFunctionRoomDescription"
                                    name="function_room_description" required></textarea>
                                <div class="invalid-feedback">Required</div>
                            </div>

                            <div class="mb-3">
                                <label for="editFunctionRoomPolicy" class="form-label">Policy *</label>
                                <textarea class="form-control" id="editFunctionRoomPolicy" name="function_room_policy"
                                    required></textarea>
                                <div class="invalid-feedback">Required</div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editFunctionRoomImage" class="form-label">Replace Images (Max 4)
                                    (Optional)</label>
                                <input type="file" class="form-control" id="editFunctionRoomImage"
                                    name="function_room_image[]" accept="image/*" multiple>
                            </div>
                            <!-- Current Images Preview -->
                            <div class="mb-3">
                                <label class="form-label">Current Images</label>
                                <div id="editImagePreviewContainer" style="
                                    width: 100%;
                                    display: grid;
                                    grid-template-columns: repeat(4, 100px);
                                    gap: 10px;
                                    padding: 10px;
                                    justify-content: center;
                                ">
                                    <!-- Images will be inserted dynamically via jQuery -->
                                </div>
                            </div>

                            <!-- Replace Images -->

                            <div class="mb-3">
                                <label for="editFunctionRoom360" class="form-label">Replace 360 (Optional)</label>
                                <input type="file" class="form-control" id="editFunctionRoom360"
                                    name="function_room_360" accept="image/*">
                            </div>

                            <!-- Current 360 Image -->
                            <div class="mb-3">
                                <label class="form-label">Current 360 Image</label>
                                <div id="editImagePreviewContainer360" style="
                                    width: 100%;
                                    height: 150px;
                                    border: 1px solid #6c757d;
                                    margin-top: 10px;
                                    position: relative;
                                    overflow: hidden;
                                    border-radius: 5px;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    background-color: #f8f9fa;
                                ">
                                    <span id="edit360Placeholder" style="color: #6c757d;">No 360 Image</span>
                                    <img id="edit360Preview" src="#" alt="360 Preview" style="
                                        display: none;
                                        width: 100%;
                                        height: 100%;
                                        object-fit: cover;
                                        position: absolute;
                                        top: 0;
                                        left: 0;
                                    " />
                                </div>
                            </div>

                            <!-- Replace 360 -->


                            <!-- Featured Checkbox -->
                            <div class="mb-3">
                                <div class="form-check align-items-center d-flex gap-2">
                                    <input class="form-check-input mt-0" type="checkbox" id="editFunctionRoomFeatured"
                                        name="featured" value="1">
                                    <label class="form-check-label mb-0" for="editFunctionRoomFeatured">
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
                    class="btn btn-primary d-flex align-items-center justify-content-center"
                    style="min-width: 100px; height: 38px;">
                    <span class="btn-text">Update</span>
                </button>
            </div>
        </div>
    </div>
</div>