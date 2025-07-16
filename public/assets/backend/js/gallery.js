$(document).ready(function () {
    $('#uploadGalleryForm').on('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const progressBar = $('.progress');
        const progress = $('.progress-bar');

        progressBar.show();
        progress.css('width', '0%').text('0%');

        $.ajax({
            xhr: function () {
                const xhr = new XMLHttpRequest();
                xhr.upload.addEventListener('progress', function (e) {
                    if (e.lengthComputable) {
                        const percent = Math.round((e.loaded / e.total) * 100);
                        progress.css('width', percent + '%').text(percent + '%');
                    }
                });
                return xhr;
            },
            url: $('#uploadGalleryForm').attr('action'),
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (res) {
                progressBar.hide();
                $('#galleryFileInput').val('');

                const Toast = Swal.mixin({
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                    customClass: {
                        popup: 'colored-toast'
                    },
                    didOpen: (toast) => {
                        toast.onmouseenter = Swal.stopTimer;
                        toast.onmouseleave = Swal.resumeTimer;
                    }
                });

                Toast.fire({
                    icon: 'success',
                    title: 'Images uploaded successfully'
                });

                if (typeof refreshTableDownloads === "function") {
                    refreshTableDownloads(currentPage);
                }
            },
            error: function (err) {
                progressBar.hide();

                const Toast = Swal.mixin({
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                    customClass: {
                        popup: 'colored-toast-error'
                    },
                    didOpen: (toast) => {
                        toast.onmouseenter = Swal.stopTimer;
                        toast.onmouseleave = Swal.resumeTimer;
                    }
                });

                Toast.fire({
                    icon: 'error',
                    title: 'Failed to upload images'
                });
            }
        });
    });


    function refreshTableDownloads(page = currentPage) {
        currentPage = page;

        $.ajax({
            url: '/admin/get-updated-images-table?page=' + page,
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                const images = response.data;
                const tableBody = $('#imageTable tbody');

                $('[data-bs-toggle="tooltip"]').tooltip('dispose');
                tableBody.empty();

                images.forEach(function (image) {
                    const actionButtons = `
                        <button type="button" class="btn btn-sm btn-icon btn-danger delete-file-btn"
                            data-bs-toggle="tooltip" data-bs-placement="right" title="Delete" data-id="${image.id}">
                            <i class='bx bx-trash'></i>
                        </button>`;

                    const file_name = image.file_name || 'N/A';
                    const created_at = image.created_at || 'N/A';

                    const row = $(`
                        <tr>
                            <td>${file_name}</td>
                            <td>${created_at}</td>
                            <td>${actionButtons}</td>
                        </tr>
                    `);

                    tableBody.append(row);
                });

                $('[data-bs-toggle="tooltip"]').tooltip();
                $('#paginationLinks').html(response.links);
                $('#paginationLinks a').on('click', function (e) {
                    e.preventDefault();
                    const url = new URL($(this).attr('href'), window.location.origin);
                    const page = url.searchParams.get('page');
                    refreshTableDownloads(page);
                });
            },
            error: function (xhr, status, error) {
                console.error('Error refreshing the table:', error);
            }
        });
    }
});
