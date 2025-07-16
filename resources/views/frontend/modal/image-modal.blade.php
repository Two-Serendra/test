<!-- Gallery Modal -->
<div class="modal fade" id="galleryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark text-white position-relative"
            style="border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.5);">

            <!-- Close Button -->
            <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-2"
                data-bs-dismiss="modal" aria-label="Close"></button>

            <!-- Modal Body -->
            <div class="modal-body p-0 d-flex justify-content-center align-items-center"
                style="width: 100%; padding: 10px; background-color: #e6e6e6; box-sizing: border-box;">

                <div class="w-100 position-relative" style="max-width: 100%; max-height: 80vh;">
                    <!-- Main Image -->
                    <img id="modalImage" src="" class="w-100"
                        style="object-fit: contain; max-height: 80vh; border: 2px solid #444; border-radius: 6px;">

                    <!-- Left Arrow -->
                    <button class="btn btn-light position-absolute top-50 start-0 translate-middle-y prev-btn"
                        style="z-index: 10; background-color: rgba(255,255,255,0.5); border: none; font-size: 1.5rem;">
                        &lt;
                    </button>

                    <!-- Right Arrow -->
                    <button class="btn btn-light position-absolute top-50 end-0 translate-middle-y next-btn"
                        style="z-index: 10; background-color: rgba(255,255,255,0.5); border: none; font-size: 1.5rem;">
                        &gt;
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>