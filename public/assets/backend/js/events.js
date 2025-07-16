$(document).ready(function () {

    $('#eventDate').flatpickr({
        altInput: true,
        altFormat: "F j, Y",
        dateFormat: "Y-m-d",
        minDate: "today",
        defaultDate: "today"
    });
    $('#update_event_date').flatpickr({
        altInput: true,
        altFormat: "F j, Y",
        dateFormat: "Y-m-d",
        minDate: "today"
    });

    let currentEventsPageUrl = '/admin/get-updated-events-table';
    let currentSearchTerm = '';

    function stripAndLimit(html, maxLength) {
        const div = document.createElement("div");
        div.innerHTML = html;
        const text = div.textContent || div.innerText || "";
        const trimmed = text.trim();
        return trimmed.length > maxLength ? trimmed.substring(0, maxLength) + "..." : trimmed;
    }
    function refreshTableEvents(url = currentEventsPageUrl) {
        $.ajax({
            url: url,
            type: 'GET',
            data: {
                searchEvent: currentSearchTerm
            },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function (response) {
                const events = response.data;
                const tableBody = $('#eventTable tbody');
                const paginationContainerEvents = $('.pagination-container-events');

                $('[data-bs-toggle="tooltip"]').tooltip('dispose');
                tableBody.empty();

                events.forEach(function (event) {
                    const event_title = event.event_title || 'N/A';
                    const event_details = event.event_details || 'N/A'

                    const event_image = event.event_image
                        ? `<img src="/assets/images/events/${event.event_image}" alt="Event Image"
                    style="width: 100px; height: 100px; object-fit: contain;">`
                        : 'N/A';
                    const event_date = event.event_date ?? 'N/A';
                    const created_at = event.created_at ?? 'N/A';
                    const updated_at = event.updated_at ?? 'N/A';

                    const actionButtons = `
                    <button type="button" class="btn btn-sm btn-icon btn-primary admin_edit_event"
                        data-bs-toggle="tooltip" data-bs-placement="left" title="Edit" data-id="${event.id}">
                        <i class='bx bx-edit'></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-icon btn-danger delete_event"
                        data-bs-toggle="tooltip" data-bs-placement="right" title="Delete" data-id="${event.id}">
                        <i class='bx bx-trash'></i>
                    </button>`;

                    const row = `
                    <tr>
                        <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${event_title}</td>
                        <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            ${stripAndLimit(event_details, 100)}
                        </td>
                        <td>${event_image}</td>
                        <td>${event_date}</td>F
                        <td>${created_at}</td>
                        <td>${updated_at}</td>
                        <td>${actionButtons}</td>
                    </tr>`;

                    tableBody.append(row);
                });

                paginationContainerEvents.html(response.links);

                $('.pagination-container-events').find('a').off('click').on('click', function (e) {
                    e.preventDefault();
                    let pageUrl = $(this).attr('href');
                    if (currentSearchTerm) {
                        pageUrl = updateQueryStringParameter(pageUrl, 'searchEvent', currentSearchTerm);
                    }

                    currentEventsPageUrl = pageUrl;
                    refreshTableEvents(pageUrl);
                });

                $('[data-bs-toggle="tooltip"]').tooltip();
            },
            error: function (xhr, status, error) {
                console.error('Status:', status);
                console.error('Error:', error);
                console.error('Response:', xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error loading events',
                    html: `<pre>${xhr.status}</pre>`
                });
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

    $('#searchFormEvents').on('submit', function (e) {
        e.preventDefault();
        currentSearchTerm = $('#searchInputEvents').val(); // grab input
        refreshTableEvents(); // AJAX search
    });


    $('.AdminAddEvent').on('click', function () {
        $('#adminCreateEvents').modal('show');
    });

    $('#eventImage').on('change', function () {
        const file = this.files[0];
        const reader = new FileReader();

        if (file) {
            reader.onload = function (e) {
                $('#imagePreview').attr('src', e.target.result).show();
                $('#previewPlaceholder').hide();
            };
            reader.readAsDataURL(file);
        } else {
            $('#imagePreview').hide().attr('src', '#');
            $('#previewPlaceholder').show();
        }
    });

    $('#adminNewEvent').submit(function (event) {
        event.preventDefault();

        if (!this.checkValidity()) {
            this.classList.add('was-validated');
            return;
        }
        this.classList.remove('was-validated');

        const $btn = $('#saveEventBtn');
        const originalWidth = $btn.outerWidth(); // Preserve width for consistency

        // Replace button text with spinner, center it
        $btn
            .attr('disabled', true)
            .html(`<div class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></div>`)
            .css('width', originalWidth + 'px');

        const formData = new FormData(this);
        const form = this;

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
                $('#adminCreateEvents').modal('hide');
                $('#imagePreview').attr('src', '#').hide();

                // Success toast
                Swal.fire({
                    toast: true,
                    position: "top-end",
                    icon: 'success',
                    title: 'Event Added Successfully',
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

                form.reset();
                $(form).removeClass('was-validated');
                refreshTableEvents();
            },
            error: function () {
                // Error toast
                Swal.fire({
                    toast: true,
                    position: "top-end",
                    icon: 'error',
                    title: 'Failed to add event',
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
            },
            complete: function () {
                // Restore button to original state
                $btn
                    .attr('disabled', false)
                    .html(`<span class="btn-text">Create</span>`)
                    .css('width', '');
            }
        });
    });

    $('#eventTable').on('click', '.admin_edit_event', function () {
        var info_id = $(this).data("id");
        $.get('/admin/admin-fetch-events/' + info_id, function (data) {
            $('#adminUpdateEvents').modal('show');
            $('#update_event_title').val(data.event_title);
            $('#update_event_date')[0]._flatpickr.setDate(data.event_date, true);
            $('#update_event_details').summernote('code', data.event_details);
            $('#current_event_image').val(data.event_image);
            $('#info_id').val(info_id);

            if (data.event_image) {
                const imagePath = '/assets/images/events/' + data.event_image;
                $('#updateEventImagePreview').attr('src', imagePath).show();
                $('#updateEventPreviewPlaceholder').hide();
            } else {
                $('#updateEventImagePreview').hide();
                $('#updateEventPreviewPlaceholder').show();
            }
        }).fail(function () {
            alert("Data not found");
        });
    });

    $('#update_event_image').on('change', function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                $('#updateEventImagePreview').attr('src', e.target.result).show();
                $('#updateEventPreviewPlaceholder').hide();
            };
            reader.readAsDataURL(file);
        } else {
            $('#updateEventImagePreview').hide();
            $('#updateEventPreviewPlaceholder').show();
        }
    });

    $('#admin-update-event').submit(function (event) {
        event.preventDefault();

        if (!this.checkValidity()) {
            this.classList.add('was-validated');
            return;
        }
        this.classList.remove('was-validated');

        const $btn = $('#updateEventBtn');
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
                $('#adminUpdateEvents').modal('hide');
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
                    }
                });

                Toast.fire({
                    icon: 'success',
                    title: 'Event Updated Successfully'
                });

                form.reset();
                $(form).removeClass('was-validated');
                refreshTableEvents();
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
                    title: 'Failed to add event'
                });
            },
            complete: function () {
                $btn
                    .attr('disabled', false)
                    .html(`<span class="btn-text">Update</span>`)
                    .css('width', '');
            }
        });
    });


    $('#eventTable').on('click', '.delete_event', function () {
        var eventId = $(this).data('id');
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
                    url: '/admin/admin-delete-events',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        event_id: eventId,
                        _method: 'DELETE'
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
                            title: 'Deleted Successfully'
                        });
                        refreshTableEvents();
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
                            title: 'Failed to delete'
                        });
                    },
                });
            }
        });
    });
});
