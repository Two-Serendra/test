<!-- Create Add Ons Modal -->
<div class="modal fade" id="adminCreateAddOns" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-3">Create Add Ons</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.store.add.ons') }}" method="POST" id="admin-new-add-ons"
                    enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf

                    <div class="mb-3">
                        <label for="item" class="form-label">Item</label>
                        <input type="text" name="item" class="form-control" required>
                        <div class="invalid-feedback">Required</div>
                    </div>

                    <div class="mb-3">
                        <label for="qty" class="form-label">Qty</label>
                        <input type="number" name="qty" class="form-control" required>
                        <div class="invalid-feedback">Required</div>
                    </div>

                    <div class="mb-3">
                        <label for="price" class="form-label">Price</label>
                        <input type="number" name="price" class="form-control" required>
                        <div class="invalid-feedback">Required</div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="admin-new-add-ons" id="saveAddOnsBtn"
                    class="btn btn-primary d-flex align-items-center justify-content-center"
                    style="min-width: 100px; height: 38px;">
                    <span class="btn-text">Create</span>
                </button>
            </div>
        </div>
    </div>
</div>


<!-- Update Add Ons Modal -->
<div class="modal fade" id="adminUpdateAddOns" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-3">Create Add Ons</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.store.add.ons') }}" method="POST" id="adminEditBookingForm"
                    enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf
                    <input type="hidden" id="info_id" name="info_id">
                    <div class="mb-3">
                        <label for="item" class="form-label">Item</label>
                        <input type="text" name="item" class="form-control" id="edit_item" required>
                        <div class="invalid-feedback">Required</div>
                    </div>

                    <div class="mb-3">
                        <label for="qty" class="form-label">Qty</label>
                        <input type="number" name="qty" class="form-control" id="edit_qty" required>
                        <div class="invalid-feedback">Required</div>
                    </div>

                    <div class="mb-3">
                        <label for="price" class="form-label">Price</label>
                        <input type="number" name="price" class="form-control" id="edit_price" required>
                        <div class="invalid-feedback">Required</div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="adminEditBookingForm" id="saveUserBtn"
                    class="btn btn-primary d-flex align-items-center justify-content-center"
                    style="min-width: 100px; height: 38px;">
                    <span class="btn-text">Create</span>
                </button>
            </div>
        </div>
    </div>
</div>