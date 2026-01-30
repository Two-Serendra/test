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