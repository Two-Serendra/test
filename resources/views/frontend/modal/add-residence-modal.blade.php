<div class="modal fade" id="addResidence" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="addResidenceLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="addResidenceLabel">Add Residence</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" id="add-residence-form"
                    enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 30%;">Resident Type</th>
                                    <th style="width: 30%;">Section</th>
                                    <th style="width: 30%;">Unit No.</th>
                                    <th style="width: 00%;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="residenceTableBody">
                                <tr class="residence-row">
                                    <td style="width: 30%;">
                                        <select name="resident_type[]" class="form-select" required>
                                            <option value="" disabled selected>Select</option>
                                            <option value="Owner">Owner</option>
                                            <option value="Tenant">Tenant</option>
                                        </select>
                                        <div class="invalid-feedback">Required</div>
                                    </td>
                                    <td style="width: 30%;">
                                        <select name="section[]" required class="form-select">
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
                                    <td style="width: 30%;">
                                        <input type="number" name="unit_no[]" class="form-control" required>
                                        <div class="invalid-feedback">Required</div>
                                    </td>
                                    <td style="width: 10%;">
                                        <!-- First row has no delete button -->
                                    </td>
                                </tr>
                            </tbody>

                        </table>
                    </div>

                    <div class="d-flex align-items-center justify-content-end"><button type="button"
                            class="btn btn-success mt-3 btn-sm btn-forge" id="addRowBtn">
                            <i class="bx bx-plus"></i> Add Row
                        </button></div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary text-light btn-sm btn-forge"
                    data-bs-dismiss="modal">Close</button>
                <button type="submit" form="add-residence-form" id="addResidenceBtn"
                    class="btn btn-primary btn-sm btn-forge d-flex align-items-center justify-content-center"
                    style="min-width: 90px; height: 32px;">
                    <span class="spinner-border spinner-border-sm text-light me-1 d-none" role="status"
                        aria-hidden="true"></span>
                    <span class="btn-text">Submit</span>
                </button>
            </div>
        </div>
    </div>
</div>