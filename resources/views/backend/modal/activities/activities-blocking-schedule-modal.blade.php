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
                            <label class="form-label">Activity</label>
                            <select name="activity_id" class="form-select" required>
                                <option value="">Select Activity</option>
                                @foreach($activities as $activity)
                                    <option value="{{ $activity->id }}">
                                        {{ strtoupper($activity->activity_name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                        <div class="col-md-6 mb-3">
                            <label class="form-label">Day</label>
                            <select name="days[]" class="form-select" multiple required id="blocking_days">
                                <option value="Monday">Monday</option>
                                <option value="Tuesday">Tuesday</option>
                                <option value="Wednesday">Wednesday</option>
                                <option value="Thursday">Thursday</option>
                                <option value="Friday">Friday</option>
                                <option value="Saturday">Saturday</option>
                                <option value="Sunday">Sunday</option>
                            </select>
                            <small class="text-muted">Hold CTRL to select multiple days</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Time</label>
                            <select name="blocking_start_time" id="blocking_start_time" class="form-select" required>
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

                        <div class="col-md-6 mb-3">
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

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Remarks</label>
                            <input type="text" name="remarks" class="form-control"
                                placeholder="Example: Basketball Clinic">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Repeat Weekly</label>
                            <select name="repeat_weekly" class="form-select">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
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