<!-- Image Modal -->
<div class="modal" id="imageGalleryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark text-white position-relative"
            style="max-width: 100%; width: 100%; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.5);">

            <!-- Close Button -->
            <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-2"
                data-bs-dismiss="modal" aria-label="Close"></button>

            <!-- Modal Body -->
            <div class="modal-body p-0 d-flex justify-content-center align-items-center"
                style="width: 100%; height: 100%; padding: 10px; box-sizing: border-box; background-color: #e6e6e6;">

                <div class="w-100 position-relative" style="max-width: 100%; height: auto;">
                    <!-- Main Image -->
                    <img id="galleryModalImage" src="" class="w-100"
                        style="object-fit: contain; max-height: 80vh; border: 2px solid #444; border-radius: 6px;">

                    <!-- Previous Button -->
                    <button type="button" id="prevImageBtn"
                        class="btn btn-light position-absolute top-50 start-0 translate-middle-y"
                        style="z-index: 10; background-color: rgba(255,255,255,0.5); border: none; font-size: 1.5rem;">
                        &lt;
                    </button>

                    <!-- Next Button -->
                    <button type="button" id="nextImageBtn"
                        class="btn btn-light position-absolute top-50 end-0 translate-middle-y"
                        style="z-index: 10; background-color: rgba(255,255,255,0.5); border: none; font-size: 1.5rem;">
                        &gt;
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>