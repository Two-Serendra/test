<!-- ADD FUNCTION ROOM DISCOUNT -->
<div class="modal fade" id="adminCreateFunctionRoomDiscount" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-dark">New Function Room Discount</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('create.function.room.discounts') }}" id="admin-new-function-room-discount"
                enctype="multipart/form-data" method="POST" class="needs-validation" novalidate>
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <!-- Function Rooms -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Select Function Rooms *</label>
                            <select name="function_room_id[]" class="selectpicker form-control" multiple
                                data-live-search="true" data-actions-box="true" required>
                                @foreach($functionRooms as $room)
                                    <option value="{{ $room->id }}">{{ $room->function_room_name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">At least one function room is required</div>
                        </div>


                        <!-- Discount -->
                        <div class="col-md-6 mb-3">
                            <label for="discount" class="form-label">Discount (%) *</label>
                            <input type="number" step="0.01" min="1" max="100" name="discount" id="discount"
                                class="form-control" required>
                            <div class="invalid-feedback">Enter a valid discount between 1-100</div>
                        </div>

                        <!-- Remarks -->
                        <div class="col-md-6 mb-3">
                            <label for="remarks" class="form-label">Remarks</label>
                            <input type="text" name="remarks" id="remarks" class="form-control">
                        </div>

                        <!-- Start Date -->
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Start Date *</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" required>
                            <div class="invalid-feedback">Start date is required</div>
                        </div>

                        <!-- End Date -->
                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label">End Date *</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" required>
                            <div class="invalid-feedback">End date is required</div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" form="admin-new-function-room-discount" id="saveFunctionRoomDiscountBtn"
                        class="btn btn-primary d-flex align-items-center justify-content-center"
                        style="min-width: 100px; height: 38px;">
                        <span class="btn-text">Create</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- ADD FUNCTION ROOM DISCOUNT -->
<div class="modal fade" id="adminCreateFunctionRoomDiscount" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-dark">New Function Room Discount</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('create.function.room.discounts') }}" id="admin-new-function-room-discount"
                enctype="multipart/form-data" method="POST" class="needs-validation" novalidate>
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <!-- Function Rooms -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Select Function Rooms *</label>
                            <select name="function_room_id[]" class="selectpicker form-control" multiple
                                data-live-search="true" data-actions-box="true" required>
                                @foreach($functionRooms as $room)
                                    <option value="{{ $room->id }}">{{ $room->function_room_name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">At least one function room is required</div>
                        </div>


                        <!-- Discount -->
                        <div class="col-md-6 mb-3">
                            <label for="discount" class="form-label">Discount (%) *</label>
                            <input type="number" step="0.01" min="1" max="100" name="discount" id="discount"
                                class="form-control" required>
                            <div class="invalid-feedback">Enter a valid discount between 1-100</div>
                        </div>

                        <!-- Remarks -->
                        <div class="col-md-6 mb-3">
                            <label for="remarks" class="form-label">Remarks</label>
                            <input type="text" name="remarks" id="remarks" class="form-control">
                        </div>

                        <!-- Start Date -->
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Start Date *</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" required>
                            <div class="invalid-feedback">Start date is required</div>
                        </div>

                        <!-- End Date -->
                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label">End Date *</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" required>
                            <div class="invalid-feedback">End date is required</div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" form="admin-new-function-room-discount" id="saveFunctionRoomDiscountBtn"
                        class="btn btn-primary d-flex align-items-center justify-content-center"
                        style="min-width: 100px; height: 38px;">
                        <span class="btn-text">Create</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>



<div class="modal fade" id="adminUpdateFuntionRoomDiscount" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-3">Create Add Ons</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.update.function.room.discount') }}" method="POST"
                    id="adminUpdateFunctionRoomDiscount" enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf
                    <input type="hidden" id="functionRoomDiscountId" name="functionRoomDiscountId">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Function Room</label>
                        <input type="text" id="edit_function_room_name" class="form-control" readonly>
                        <input type="hidden" id="edit_function_room_id" name="function_room_id">
                    </div>


                    <!-- Discount -->
                    <div class="col-md-12 mb-3">
                        <label for="discount" class="form-label">Discount (%) *</label>
                        <input type="number" step="0.01" min="1" max="100" name="discount" id="edit_discount"
                            class="form-control" required>
                        <div class="invalid-feedback">Enter a valid discount between 1-100</div>
                    </div>

                    <!-- Remarks -->
                    <div class="col-md-12 mb-3">
                        <label for="remarks" class="form-label">Remarks</label>
                        <input type="text" name="remarks" id="edit_remarks" class="form-control">
                    </div>

                    <!-- Start Date -->
                    <div class="col-md-12 mb-3">
                        <label for="start_date" class="form-label">Start Date *</label>
                        <input type="date" name="start_date" id="edit_start_date" class="form-control" required>
                        <div class="invalid-feedback">Start date is required</div>
                    </div>

                    <!-- End Date -->
                    <div class="col-md-12 mb-3">
                        <label for="end_date" class="form-label">End Date *</label>
                        <input type="date" name="end_date" id="edit_end_date" class="form-control" required>
                        <div class="invalid-feedback">End date is required</div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="adminUpdateFunctionRoomDiscount" id="updateFunctionRoomDiscountBtn"
                    class="btn btn-primary d-flex align-items-center justify-content-center"
                    style="min-width: 100px; height: 38px;">
                    <span class="btn-text">Update</span>
                </button>
            </div>
        </div>
    </div>
</div>