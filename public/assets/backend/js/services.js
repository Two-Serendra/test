$(document).ready(function () {
    $('.addService').on('click', function () {
        $('#serviceAddModal').modal('show');
    });

    $('#newServiceForm').submit(function (event) {
        console.log('Event bound');
        event.preventDefault();
        if (!this.checkValidity()) {
            this.classList.add('was-validated');
            return;
        }

        this.classList.remove('was-validated');

        $('#saveServiceBtn').attr('disabled', true);
        $('#saveServiceBtn .spinner-border').removeClass('d-none');
        $('#saveServiceBtn .btn-text').text('Saving...');

        var formData = new FormData(this);
        var form = this;

        $.ajax({
            url: $(form).attr('action'),
            type: $(form).attr('method'), // ✅ fixed here
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                $('#serviceAddModal').modal('hide');
                $('#newServiceForm')[0].reset();

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
                    title: 'Service Added Successfully'
                });

                form.reset();
                $(form).removeClass('was-validated');
                refreshTableServices();
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
                    title: 'Failed to add service'
                });


            },
            complete: function () {
                // Re-enable button + hide spinner
                $('#saveServiceBtn').attr('disabled', false);
                $('#saveServiceBtn .spinner-border').addClass('d-none');
                $('#saveServiceBtn .btn-text').text('Save');
            }
        });
    });

    $('#updateServiceForm').submit(function (event) {
        event.preventDefault();

        if (!this.checkValidity()) {
            this.classList.add('was-validated');
            return;
        }
        this.classList.remove('was-validated');

        $('#updateServiceBtn').attr('disabled', true);
        $('#updateServiceBtn .spinner-border').removeClass('d-none');
        $('#updateServiceBtn .btn-text').text('Updating...');

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
                $('#serviceUpdateModal').modal('hide');
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
                    title: 'Service Updated Successfully'
                });

                $(form).removeClass('was-validated');
                refreshTableServices();
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
                    title: 'Failed to update service'
                });
            },
            complete: function () {
                $('#updateServiceBtn').attr('disabled', false);
                $('#updateServiceBtn .spinner-border').addClass('d-none');
                $('#updateServiceBtn .btn-text').text('Update');
            }
        });
    });

    $('#upload').on('change', function () {
        var input = this;
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $('#preview-image')
                    .attr('src', e.target.result)
                    .removeClass('d-none')
                    .show();
                $('#placeholder-text').hide();
            };

            reader.readAsDataURL(input.files[0]);
        } else {
            $('#preview-image').attr('src', '#').addClass('d-none').hide();
            $('#placeholder-text').show();
        }
    });


    $('#serviceTable').on('click', '.edit_service', function () {
        var info_id = $(this).data("id");
        $('#update_preview_image').attr('src', '').addClass('d-none').hide();
        $('#update_placeholder_text').show();
        $('#update_upload').val('');

        $.get('/admin/admin-fetch-service/' + info_id, function (data) {
            $('#serviceUpdateModal').modal('show');

            $('#update_service_name').val(data.service_name);
            $('#update_service_short_description').val(data.service_short_description);
            $('#update_service_long_description').val(data.service_long_description);
            $('#info_id').val(info_id);
            if (data.service_image) {
                const imagePath = '/assets/images/services/' + data.service_image;
                $('#update_preview_image').attr('src', imagePath).removeClass('d-none').show();
                $('#update_placeholder_text').hide();
            } else {
                $('#update_preview_image').attr('src', '').addClass('d-none').hide();
                $('#update_placeholder_text').show();
            }
        }).fail(function () {
            alert("Data not found");
        });
    });



    $('#update_upload').on('change', function (event) {
        const input = event.target;
        const reader = new FileReader();

        reader.onload = function (e) {
            $('#update_preview_image').attr('src', e.target.result).removeClass('d-none').show();
            $('#update_placeholder_text').hide();
        };

        if (input.files && input.files[0]) {
            reader.readAsDataURL(input.files[0]);
        } else {
            $('#update_preview_image').attr('src', '').addClass('d-none').hide();
            $('#update_placeholder_text').show();
        }
    });

    $('#serviceTable').on('click', '.delete_service', function () {
        var serviceId = $(this).data('id');
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
                    url: '/admin/admin-delete-services',  // your route URL
                    type: 'POST',                        // must be POST
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')  // get token from meta tag
                    },
                    data: {
                        service_id: serviceId,
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
                        refreshTableServices();
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
                            title: 'Failed to delete service'
                        });
                    },
                });
            }
        });
    });

    function refreshTableServices() {
        $.ajax({
            url: '/admin/get-updated-services-table',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                var services = response.data;
                var tableBody = $('#serviceTable tbody');
                $('[data-bs-toggle="tooltip"]').tooltip('dispose');
                tableBody.empty();
                services.forEach(function (service) {
                    var actionButtons = `
                    <button type="button" class="btn btn-sm btn-icon btn-primary edit_service"
                        data-bs-toggle="tooltip" data-bs-placement="left" title="Edit" data-id="${service.id}">
                        <i class='bx bx-edit'></i>
                    </button>

                    <button type="button" class="btn btn-sm btn-icon btn-danger delete_service"
                        data-bs-toggle="tooltip" data-bs-placement="right" title="Delete" data-id="${service.id}">
                        <i class='bx bx-trash'></i>
                    </button>`;
                    var service_name = service.service_name ? service.service_name : 'N/A';
                    var service_short_description = service.service_short_description ? service.service_short_description : 'N/A';
                    var service_long_description = service.service_long_description ? service.service_long_description : 'N/A';
                    var service_image = service.service_image
                        ? `<img src="/assets/images/services/${service.service_image}" alt="Service Image" style="width: 100px; height: auto;">`
                        : 'N/A';
                    var row = $(`
                                <tr>
                                    <td>${service_name}</td>        
                                    <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${service_short_description}</td>
                                    <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${service_long_description}</td>
                                    <td>${service_image}</td>
                                    <td>${actionButtons}</td>
                                </tr>
                            `);
                    tableBody.append(row);
                });
                $('[data-bs-toggle="tooltip"]').tooltip();
            },
            error: function (xhr, status, error) {
                console.error('Error refreshing the table:', error);
            }
        });
    }
});
