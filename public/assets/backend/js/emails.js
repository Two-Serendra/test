$(document).ready(function () {
    let currentEmailPageUrl = '/admin/get-updated-resident-details-table';
    let currentEmailSearchTerm = '';

    function refreshTableEmails(url = currentEmailPageUrl) {
        $.ajax({
            url: url,
            type: 'GET',
            data: {
                searchEmails: currentEmailSearchTerm
            },
            dataType: 'json',
            success: function (response) {
                const emails = response.data;
                const tableBody = $('#emailTable tbody');
                const paginationContainer = $('.pagination-container-email');

                $('[data-bs-toggle="tooltip"]').tooltip('dispose');
                tableBody.empty();

                emails.forEach(function (email) {
                    // 🟦 Badge logic for resident_type
                    let residentBadge = '';
                    if (email.resident_type === 'OWNER') {
                        residentBadge = `<span class="badge bg-primary text-uppercase">Owner</span>`;
                    } else if (email.resident_type === 'TENANT') {
                        residentBadge = `<span class="badge bg-danger text-uppercase">Tenant</span>`;
                    } else {
                        residentBadge = `<span class="badge bg-secondary text-uppercase">${email.resident_type || 'N/A'}</span>`;
                    }

                    const row = `
                <tr>
                    <td class="text-uppercase">${email.unit_no || 'N/A'}</td>
                    <td>${email.email || 'N/A'}</td>
                    <td>${residentBadge}</td>
                    <td>${email.created_at || 'N/A'}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-icon btn-primary edit_email"
                            data-bs-toggle="tooltip" title="Edit" data-id="${email.id}">
                            <i class='bx bx-edit'></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-icon btn-danger delete_email"
                            data-bs-toggle="tooltip" title="Delete" data-id="${email.id}">
                            <i class='bx bx-trash'></i>
                        </button>
                    </td>
                </tr>`;
                    tableBody.append(row);
                });

                paginationContainer.html(response.links);

                // 🧭 Bind pagination links with search term retained
                $('.pagination-container-email').find('a').off('click').on('click', function (e) {
                    e.preventDefault();
                    let pageUrl = $(this).attr('href');
                    if (currentEmailSearchTerm) {
                        pageUrl = updateQueryStringParameter(pageUrl, 'searchEmails', currentEmailSearchTerm);
                    }
                    currentEmailPageUrl = pageUrl;
                    refreshTableEmails(pageUrl);
                });

                $('[data-bs-toggle="tooltip"]').tooltip();
            },
            error: function (xhr) {
                console.error('Email table error:', xhr.responseText);
            }
        });
    }

    function updateQueryStringParameter(uri, key, value) {
        let re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
        let separator = uri.indexOf('?') !== -1 ? "&" : "?";
        if (uri.match(re)) {
            return uri.replace(re, '$1' + key + "=" + value + '$2');
        } else {
            return uri + separator + key + "=" + value;
        }
    }

    $('#searchFormEmails').on('submit', function (e) {
        e.preventDefault();
        currentEmailSearchTerm = $('#searchInputEmails').val();
        refreshTableEmails(); // it will include search term
    });

    $('#emailUploadForm').on('submit', function (e) {
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
            url: '/admin/admin-upload-resident-details',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (res) {
                progressBar.hide();
                $('#emailInput').val('');
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
                refreshTableEmails();
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

    $('#emailTable').on('click', '.edit_email', function () {
        var info_id = $(this).data("id");
        $.get('/admin/admin-fetch-email/' + info_id, function (data) {
            $('#emailUpdateModal').modal('show');
            $('#update_unit_no').val(data.unit_no);
            $('#update_resident_type').val(data.resident_type);
            $('#update_email').val(data.email);
            $('#info_id').val(info_id);
        }).fail(function () {
            alert("Data not found");
        });
    });

    $('#updateEmailForm').submit(function (event) {
        event.preventDefault();

        if (!this.checkValidity()) {
            this.classList.add('was-validated');
            return;
        }
        this.classList.remove('was-validated');

        const $btn = $('#updateEmailBtn');
        const originalWidth = $btn.outerWidth();
        $btn
            .attr('disabled', true)
            .html(`<div class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></div>`)
            .css('width', originalWidth + 'px');
        var formData = new FormData(this);
        var form = this;

        $.ajax({
            url: $(form).attr('action'),
            type: $(form).attr('method'),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                $('#emailUpdateModal').modal('hide');
                form.reset();
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
                    title: 'Updated Successfully'
                });

                $(form).removeClass('was-validated');
                refreshTableEmails()
            },
            error: function (xhr, status, error) {
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
                    title: 'Failed to update email'
                });
            },
            complete: function () {
                // Restore button to original state
                $btn
                    .attr('disabled', false)
                    .html(`<span class="btn-text">Update</span>`)
                    .css('width', '');
            }
        });
    });

    $('#emailTable').on('click', '.delete_email', function () {
        var emailId = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/admin/admin-delete-emails',  // your route URL
                    type: 'POST',                        // must be POST
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')  // get token from meta tag
                    },
                    data: {
                        email_id: emailId,
                        _method: 'DELETE'                 // spoof the DELETE method
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
                            title: 'Service Deleted Successfully'
                        });
                        refreshTableEmails();
                    },
                    error: function (xhr, status, error) {
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
                            title: 'Failed to delete email'
                        });
                    },
                });
            }
        });
    });
});
