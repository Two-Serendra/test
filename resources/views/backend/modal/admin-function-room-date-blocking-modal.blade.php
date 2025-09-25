<div class="modal fade" id="AddDateBlockingFunctionRoom" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-3" id="staticBackdropLabel">BLOCK DATE</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('new.function.room.date.blocking')}}" id="function-room-date-blocking"
                    method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf
                    <div class="row">
                        <div class="col-12 mb-3">
                            <select class="form-select" id="functionRoomSelectBlocking" name="function_room_id_blocking"
                                required>
                                <option value="" disabled selected>Select Function Room</option>
                                @foreach($functionRooms as $functionRoom)
                                    <option value="{{ $functionRoom->id }}">
                                        {{ strtoupper($functionRoom->function_room_name) }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Please select a Function Room</div>
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
                                <label for="FunctionRoomBlockingDateFieldStart" class="form-label">Start Date *</label>
                                <input type="text" id="FunctionRoomBlockingDateFieldStart" class="form-control"
                                    name="function_room_date_blocking_start">
                                <i class="fa-regular fa-calendar position-absolute"
                                    style="top: 73%; right: 8px; transform: translateY(-50%);"></i>
                            </div>
                        </div>

                        <div class="col-6 ">
                            <div class="mb-3 position-relative">
                                <label for="FunctionRoomBlockingDateFieldEnd" class="form-label">End Date *</label>
                                <input type="text" id="FunctionRoomBlockingDateFieldEnd" class="form-control"
                                    name="function_room_date_blocking_end">
                                <i class="fa-regular fa-calendar position-absolute"
                                    style="top: 73%; right: 8px; transform: translateY(-50%);"></i>
                            </div>

                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="function-room-date-blocking" id="saveDateBlockingBtn"
                    class="btn btn-primary d-flex align-items-center justify-content-center"
                    style="min-width: 100px; height: 38px;">
                    <span class="btn-text">Save</span>
                </button>
            </div>
        </div>
    </div>
</div>