<div class="modal fade" id="emailUpdateModal" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog"> <!-- Larger modal for better spacing -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-dark">Update Email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('update.emails') }}" id="updateEmailForm" method="POST"
                    enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="info_id" id="info_id" value="" />
                    <div class="mb-3">
                        <label class="form-label text-dark" for="update_unit_no">Unit No</label>
                        <input type="text" class="form-control" id="update_unit_no" name="unit_no"
                            placeholder="Enter email name" required>
                        <div class="invalid-feedback">Required</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-dark" for="update_email">Email Name</label>
                        <input type="text" class="form-control" id="update_email" name="email"
                            placeholder="Enter email name" required>
                        <div class="invalid-feedback">Required</div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="updateEmailForm" id="updateEmailBtn" class="btn btn-primary">
                    <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                    <span class="btn-text">Update</span>
                </button>
            </div>
        </div>
    </div>
</div>