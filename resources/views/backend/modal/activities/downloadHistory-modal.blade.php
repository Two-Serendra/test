<!-- Download History -->
<div class="modal fade" id="DownloadHistory" data-bs-backdrop="static" data-bs-keyboard="false">
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
                            <input type="text" id="DownloadStartDate" class="form-control" name="download_start_date">
                            <i class="fa-regular fa-calendar position-absolute"
                                style="top: 73%; right: 8px; transform: translateY(-50%);"></i>
                        </div>
                    </div>

                    <div class="col-6 ">
                        <div class="mb-3 position-relative">
                            <label for="DownloadEndDate" class="form-label">End Date *</label>
                            <input type="text" id="DownloadEndDate" class="form-control" name="download_end_date">
                            <i class="fa-regular fa-calendar position-absolute"
                                style="top: 73%; right: 8px; transform: translateY(-50%);"></i>
                        </div>

                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" id="downloadHistoryBtn"
                    class="btn btn-primary d-flex align-items-center justify-content-center"
                    style="min-width: 100px; height: 38px;">
                    <span class="btn-text">Download</span>
                </button>
            </div>

        </div>
    </div>
</div>