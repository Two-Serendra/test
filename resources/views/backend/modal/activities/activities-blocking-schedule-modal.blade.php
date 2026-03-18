<div class="modal fade" id="addActivityBlockingModal" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('admin.new.schedule.blocking') }}" id="ActivityScheduleBlocking"
            method="POST" enctype="multipart/form-data" class="ActivityScheduleBlocking needs-validation" novalidate>
            @csrf

            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Create Activity Blocking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="row">

                        <!-- Activity -->
                        <div class="col-md-6 mb-3">

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Activity</label>
                                <select name="activity_id" class="form-select" required>
                                    <option value="">Select Activity</option>
                                    @foreach($activities as $activity)
                                        <option value="{{ $activity->id }}">
                                            {{ strtoupper($activity->activity_name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>


                            <div class="mb-3">
                                <label class="form-label fw-semibold">Day</label>
                                <div class="border rounded p-3" id="blocking_days">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="selectAllDays">
                                                <label class="form-check-label fw-semibold">
                                                    Select All Days
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-6">

                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="days[]"
                                                    value="Monday" id="day_mon">
                                                <label class="form-check-label" for="day_mon">Monday</label>
                                            </div>


                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="days[]"
                                                    value="Tuesday" id="day_tue">
                                                <label class="form-check-label" for="day_tue">Tuesday</label>
                                            </div>



                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="days[]"
                                                    value="Wednesday" id="day_wed">
                                                <label class="form-check-label" for="day_wed">Wednesday</label>
                                            </div>



                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="days[]"
                                                    value="Thursday" id="day_thu">
                                                <label class="form-check-label" for="day_thu">Thursday</label>
                                            </div>
                                        </div>



                                        <div class="col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="days[]"
                                                    value="Friday" id="day_fri">
                                                <label class="form-check-label" for="day_fri">Friday</label>
                                            </div>

                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="days[]"
                                                    value="Saturday" id="day_sat">
                                                <label class="form-check-label" for="day_sat">Saturday</label>
                                            </div>



                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="days[]"
                                                    value="Sunday" id="day_sun">
                                                <label class="form-check-label" for="day_sun">Sunday</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="mb-3">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" class="form-control" placeholder="Example: Basketball Clinic"
                                    rows="5"></textarea>
                            </div>
                        </div>




                        <div class="col-md-6 mb-3">
                            <div class="mb-3">
                                <label class="form-label">Start Time</label>
                                <select name="blocking_start_time" id="blocking_start_time" class="form-select"
                                    required>
                                    <option value="">Select Start Time</option>
                                    @for ($i = 0; $i < 24; $i++)
                                        @php
                                            $time = \Carbon\Carbon::createFromTime($i, 0)->format('H:i:s');
                                            $label = \Carbon\Carbon::createFromTime($i, 0)->format('h:i A');
                                        @endphp
                                        <option value="{{ $time }}">{{ $label }}</option>
                                    @endfor
                                </select>
                            </div>


                            <div class="mb-3">
                                <label class="form-label">End Time</label>
                                <select name="blocking_end_time" id="blocking_end_time" class="form-select" required>
                                    <option value="">Select End Time</option>
                                    @for ($i = 1; $i <= 24; $i++)
                                        @php
                                            $time = \Carbon\Carbon::createFromTime($i % 24, 0)->format('H:i:s');
                                            $label = \Carbon\Carbon::createFromTime($i % 24, 0)->format('h:i A');
                                        @endphp
                                        <option value="{{ $time }}">{{ $label }}</option>
                                    @endfor
                                </select>
                            </div>


                            <div class="mb-3">
                                <label class="form-label">Repeat Weekly</label>
                                <select name="repeat_weekly" class="form-select">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="ActivityScheduleBlocking" id="saveActivityScheduleBlockingBtn"
                        class="btn btn-primary d-flex align-items-center justify-content-center"
                        style="min-width: 100px; height: 38px;">
                        <span class="btn-text">Create</span>
                    </button>
                </div>
            </div>

    </div>

    </form>
</div>
</div>