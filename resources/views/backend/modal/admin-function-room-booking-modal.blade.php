<!-- Admin Booking Modal -->
<div class="modal fade" id="adminFunctionRoomBookingModal" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="adminBookingForm">
        <div class="modal-header">
          <h5 class="modal-title">Function Room Booking (Admin)</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body row g-3">
          <div class="col-md-6">
            <label class="form-label">Function Room <span class="text-danger"> *</span></label>
            <select name="function_room_id" class="form-select" required>
              <option value="">-- Function Room --</option>
              @foreach($functionRooms as $room)
                <option value="{{ $room->id }}">{{ $room->name }}</option>
              @endforeach
            </select>
          </div>


          <div class="col-md-6">
            <label class="form-label">Date <span class="text-danger"> *</span></label>
            <div class="input-group">
              <span class="input-group-text bg-white"><i class='bx bx-calendar'></i></span>
              <input type="text" class="form-control bg-white text-dark" id="functionRoomBookingDate"
                name="function_room_booking_date" required>
            </div>
          </div>



          <div class="col-md-6">
            <label class="form-label">Name <span class="text-danger"> *</span></label>
            <input type="text" name="resident_name" class="form-control" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Unit<span class="text-danger"> *</span></label>
            <input type="text" name="unit_no" class="form-control" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Resident Type<span class="text-danger"> *</span></label>
            <select name="function_room_id" class="form-select" required>
              <option value="Owner">Owner</option>
              <option value="Tenant">Tenant</option>
            </select>
          </div>




          <div class="col-md-6">
            <label class="form-label">Start Time <span class="text-danger"> *</span></label>
            <input type="time" name="event_start_time" id="startTime" class="form-control" step="3600" min="00:00"
              max="23:00" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Purpose <span class="text-danger"> *</span></label>
            <input type="text" name="purpose" class="form-control" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">End Time <span class="text-danger"> *</span></label>
            <input type="time" name="event_end_time" id="endTime" class="form-control" step="3600" min="00:00"
              max="23:00" required>
            <small class="text-danger d-none" id="timeError">End time must be later than start
              time.</small>
          </div>



          <div class="col-md-6">
            <label class="form-label">Pax<span class="text-danger"> *</span></label>
            <input type="number" name="pax" class="form-control" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Contact<span class="text-danger"> *</span></label>
            <input type="number" name="pax" class="form-control" required>
          </div>

        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save Booking</button>
        </div>
      </form>
    </div>
  </div>
</div>