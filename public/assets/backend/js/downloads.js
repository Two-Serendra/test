$(document).ready(function () {
    let currentPage = 1;

    $('#fileUploadForm').on('submit', function (e) {
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
            url: '/admin/admin-upload-file',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (res) {
                progressBar.hide();
                $('#fileInput').val('');

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
                    },
                    target: 'body'
                });

                Toast.fire({
                    icon: 'success',
                    title: 'File Uploaded Successfully'
                });
                refreshTableDownloads(currentPage);
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
                    title: 'Failed to upload file'
                });
            }
        });
    });

    $('#categorySelect').on('change', function () {
        const selectedCategory = $(this).val();

        if (selectedCategory) {
            $('#fileInput').prop('disabled', false);
            $('#uploadFile').prop('disabled', false);
        } else {
            $('#fileInput').prop('disabled', true);
            $('#uploadFile').prop('disabled', true);
        }
    });

    $('#downloadTable').on('click', '.delete-file-btn', function () {
        const fileId = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: 'This will permanently delete the file.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/admin/admin-delete-file/' + fileId,
                    type: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
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
                            },
                            target: 'body'
                        });

                        Toast.fire({
                            icon: 'success',
                            title: 'File Deleted Successfully'
                        });
                        refreshTableDownloads(currentPage);
                    },
                    error: function () {
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
                            title: 'Failed to delete file'
                        });
                    }
                });
            }
        });
    });



    function refreshTableDownloads(page = currentPage) {
        currentPage = page;

        $.ajax({
            url: '/admin/get-updated-downloads-table?page=' + page,
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                const downloads = response.data;
                const tableBody = $('#downloadTable tbody');

                $('[data-bs-toggle="tooltip"]').tooltip('dispose');
                tableBody.empty();

                downloads.forEach(function (download) {
                    const actionButtons = `
                        <button type="button" class="btn btn-sm btn-icon btn-danger delete-file-btn"
                            data-bs-toggle="tooltip" data-bs-placement="right" title="Delete" data-id="${download.id}">
                            <i class='bx bx-trash'></i>
                        </button>`;

                    const category_name = (download.category_name || 'N/A').toUpperCase();
                    const file_name = download.file_name || 'N/A';
                    const created_at = download.created_at || 'N/A';

                    const row = $(`
                        <tr>
                            <td>${category_name}</td>
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