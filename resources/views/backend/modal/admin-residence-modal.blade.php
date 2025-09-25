<div class="modal fade" id="adminAddResidence" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="addResidenceLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content residence-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="addResidenceLabel">Add Residence</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.add.new.residence') }}" method="POST" id="admin-add-residence-form"
                    enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:30%;">Email</th>
                                    <th style="width:20%;">Resident Type</th>
                                    <th style="width:20%;">Section</th>
                                    <th style="width:20%;">Unit No.</th>
                                    <th style="width:10%;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="adminResidenceTableBody">
                                <tr class="residence-row">
                                    <td style="width: 30%;">
                                        <select name="user_id[]" class="user-email-select" required></select>
                                        <div class="invalid-feedback">
                                            Required
                                        </div>
                                    </td>
                                    <td style="width: 20%;">
                                        <select name="resident_type[]" class="form-select" required>
                                            <option value="" disabled selected>Select</option>
                                            <option value="Owner">Owner</option>
                                            <option value="Tenant">Tenant</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            Required
                                        </div>
                                    </td>
                                    <td style="width: 20%;">
                                        <select name="section[]" class="form-select" required>
                                            <option value="" disabled selected>Select</option>
                                            <option value="Almond">Almond</option>
                                            <option value="Belize">Belize</option>
                                            <option value="Callery">Callery</option>
                                            <option value="Dolce">Dolce</option>
                                            <option value="Aston">Aston</option>
                                            <option value="Red Oak">Red Oak</option>
                                            <option value="Meranti">Meranti</option>
                                            <option value="Sequoia">Sequoia</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            Required
                                        </div>
                                    </td>
                                    <td style="width: 20%;">
                                        <input type="text" name="unit_no[]" class="form-control" required>
                                        <div class="invalid-feedback">
                                            Required
                                        </div>
                                    </td>
                                    <td style="width: 10%;">
                                        <!-- First row has no delete button -->
                                    </td>
                                </tr>
                            </tbody>

                        </table>
                    </div>

                    <div class="d-flex align-items-center justify-content-end"><button type="button"
                            class="btn btn-primary mt-3 btn-sm" id="adminAddResidenceRowBtn">
                            <i class="bx bx-plus"></i> Add Row
                        </button></div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"
                    style="color: #fff !important;">Close</button>
                <button type="submit" form="admin-add-residence-form" id="addAddResidenceBtn"
                    class="btn btn-primary btn-sm ">
                    <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                    <span class="btn-text">Create</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="adminUpdateResidence" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="addResidenceLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content residence-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="addResidenceLabel">Update Residence</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.update.residence') }}" method="POST" id="admin-update-residence-form"
                    enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <input type="hidden" name="info_id" id="info_id" />
                            <thead class="table-light">
                                <tr>
                                    <th style="width:30%;">Email</th>
                                    <th style="width:20%;">Resident Type</th>
                                    <th style="width:20%;">Section</th>
                                    <th style="width:20%;">Unit No.</th>

                                </tr>
                            </thead>
                            <tbody id="adminResidenceTableBody">
                                <tr class="residence-row">
                                    <td style="width: 30%;">
                                        <!-- Actual user_id to submit -->
                                        <input type="hidden" name="user_id" id="update_residence_user_id">

                                        <!-- Display email only, not submitted -->
                                        <input type="text" class="form-control" id="update_residence_email" readonly>
                                    </td>
                                    <td style="width: 20%;">
                                        <select name="resident_type" id="update_residence_type" class="form-select"
                                            required>
                                            <option value="Owner">Owner</option>
                                            <option value="Tenant">Tenant</option>
                                        </select>
                                        <div class="invalid-feedback">Required</div>
                                    </td>
                                    <td style="width: 20%;">
                                        <select name="section" id="update_residence_section" class="form-select"
                                            required>
                                            <option value="" disabled selected>Select</option>
                                            <option value="Almond">Almond</option>
                                            <option value="Belize">Belize</option>
                                            <option value="Callery">Callery</option>
                                            <option value="Dolce">Dolce</option>
                                            <option value="Aston">Aston</option>
                                            <option value="Red Oak">Red Oak</option>
                                            <option value="Meranti">Meranti</option>
                                            <option value="Sequoia">Sequoia</option>
                                        </select>
                                        <div class="invalid-feedback">Required</div>
                                    </td>
                                    <td style="width: 20%;">
                                        <input type="number" name="unit_no" id="update_residence_unit_no"
                                            class="form-control" required>
                                        <div class="invalid-feedback">Required</div>
                                    </td>

                                </tr>
                            </tbody>

                        </table>
                    </div>

                    <!-- <div class="d-flex align-items-center justify-content-end"><button type="button"
                            class="btn btn-primary mt-3 btn-sm" id="adminUpdateResidenceRowBtn">
                            <i class="bx bx-plus"></i> Update Row
                        </button></div> -->

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"
                    style="color: #fff !important;">Close</button>
                <button type="submit" form="admin-update-residence-form" id="updateResidenceBtn"
                    class="btn btn-primary btn-sm ">
                    <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                    <span class="btn-text">Update</span>
                </button>
            </div>
        </div>
    </div>
</div>