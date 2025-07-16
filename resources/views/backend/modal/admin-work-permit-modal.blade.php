<!-- Create Walkin Work Permit Modal -->
<div class="modal fade" id="AdminCreateWorkPermit" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-3">Create Work Permit</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.store.walkin.work.permit') }}" method="POST" id="adminNewWorkPermit"
                    enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf
                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label for="permit" class="form-label">Type of Permit</label>
                            <select name="permit_type" id="permit_type" class="form-select" required>
                                <option value="" disabled selected>Select</option>
                                <option value="work permit">Work Permit</option>
                                <option value="pull-out">Pull-out</option>
                                <option value="delivery">Delivery</option>
                            </select>
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="unit_number" class="form-label">Unit Number</label>
                            <input type="text" name="unit_no" id="unit_no" class="form-control" required>
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="section" class="form-label">Section</label>
                            <select name="section" id="section" class="form-select" required>
                                <option value="" disabled selected>Select</option>
                                <option value="Almond">Almond</option>
                                <option value="Belize">Belize</option>
                                <option value="Callery">Callery</option>
                                <option value="Dolce">Dolce</option>
                                <option value="Encino">Encino</option>
                                <option value="Aston">Aston</option>
                                <option value="Red Oak">Red Oak</option>
                                <option value="Meranti">Meranti</option>
                                <option value="Sequoia">Sequoia</option>
                            </select>
                        </div>



                        <div class="mb-3 col-md-6">
                            <label for="no_days" class="form-label">Number of Days</label>
                            <input type="text" name="no_days" id="no_days" class="form-control" required>
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="work_permit_booking_date" class="form-label">Date *</label>
                            <input type="date" id="work_permit_booking_date" name="work_permit_booking_date"
                                class="form-control" required>
                        </div>

                        <div class="mb-3 col-md-6">
                            <label class="form-label">Under Renovation</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="under_renovation" id="radio1"
                                    value="Yes" required>
                                <label class="form-check-label" for="radio1">Yes</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="under_renovation" id="radio2"
                                    value="No">
                                <label class="form-check-label" for="radio2">No</label>
                            </div>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="description" class="form-label">Work to be done / Items to be pulled out / Items
                                to be
                                delivered</label>
                            <textarea class="form-control" id="description" name="description" rows="3"
                                required></textarea>
                        </div>


                        <div class="mb-3 col-md-6">
                            <label for="contractor" class="form-label">Name of Contractor/Worker</label>
                            <textarea class="form-control" id="contractor" name="contractor" rows="3"
                                required></textarea>
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="update_aprroved_by" class="form-label">Approved by</label>
                            <input type="text" name="aprroved_by" id="update_aprroved_by" class="form-control" required>
                        </div>

                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="adminNewWorkPermit" id="saveWorkPermitBtn" class="btn btn-primary">
                    <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                    <span class="btn-text">Create</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Update Walkin Work Permit Modal -->
<div class="modal fade" id="walkinWorkPermitUpdateModal" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-3">Create Work Permit</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.update.walkin.work.permit') }}" method="POST" id="adminUpdateWorkPermit">
                    @csrf
                    <input type="hidden" name="info_id" id="info_id" value="" />
                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label for="update_permit_type" class="form-label">Type of Permit</label>
                            <select name="permit_type" id="update_permit_type" class="form-select" required>
                                <option value="work permit">Work Permit</option>
                                <option value="pull-out">Pull-out</option>
                                <option value="delivery">Delivery</option>
                            </select>
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="update_unit_no" class="form-label">Unit Number</label>
                            <input type="text" name="unit_no" id="update_unit_no" class="form-control" required>
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="update_section" class="form-label">Section</label>
                            <select name="section" id="update_section" class="form-select" required>
                                <option value="Almond">Almond</option>
                                <option value="Belize">Belize</option>
                                <option value="Callery">Callery</option>
                                <option value="Dolce">Dolce</option>
                                <option value="Encino">Encino</option>
                                <option value="Aston">Aston</option>
                                <option value="Red Oak">Red Oak</option>
                                <option value="Meranti">Meranti</option>
                                <option value="Sequoia">Sequoia</option>
                            </select>
                        </div>



                        <div class="mb-3 col-md-6">
                            <label for="update_no_days" class="form-label">Number of Days</label>
                            <input type="text" name="no_days" id="update_no_days" class="form-control" required>
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="update_work_permit_booking_date" class="form-label">Date *</label>
                            <input type="date" id="update_work_permit_booking_date" name="work_permit_booking_date"
                                class="form-control" required>
                        </div>

                        <div class="mb-3 col-md-6">
                            <label class="form-label">Under Renovation</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="under_renovation" id="Yes"
                                    value="Yes" required>
                                <label class="form-check-label" for="radio1">Yes</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="under_renovation" id="No" value="No">
                                <label class="form-check-label" for="radio2">No</label>
                            </div>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="update_description" class="form-label">Work to be done / Items to be pulled out
                                / Items
                                to be
                                delivered</label>
                            <textarea class="form-control" id="update_description" name="description" rows="3"
                                required></textarea>
                        </div>


                        <div class="mb-3 col-md-6">
                            <label for="update_contractor" class="form-label">Contractor/Worker</label>
                            <textarea class="form-control" id="update_contractor" name="contractor" rows="3"
                                required></textarea>
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="update_approved_by" class="form-label">Approved by</label>
                            <input type="text" name="approved_by" id="update_approved_by" class="form-control" required>
                        </div>

                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="adminUpdateWorkPermit" id="updateWorkPermitBtn" class="btn btn-primary">
                    <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                    <span class="btn-text">Update</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Download Walkin Permit -->
<div class="modal fade" id="DownloadWalkinWorkPemit" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-3" id="staticBackdropLabel">DOWNLOAD</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-6 ">
                        <div class="mb-3 position-relative">
                            <label for="DownloadStartDate" class="form-label">Start Date *</label>
                            <input type="text" id="DownloadStartDate" class="form-control" name="download_start_date" required>
                
                        </div>
                    </div>

                    <div class="col-6 ">
                        <div class="mb-3 position-relative">
                            <label for="DownloadEndDate" class="form-label">End Date *</label>
                            <input type="text" id="DownloadEndDate" class="form-control" name="download_end_date" required>
                         
                        </div>

                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" id="downloadWalkinPermitBtn" class="btn btn-primary">Download</button>
            </div>
        </div>
    </div>
</div>