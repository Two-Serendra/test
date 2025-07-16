

$(document).ready(function () {
    $('.AdminAddFunctionRoom').on('click', function () {
        $('#adminCreateFunctionRooms').modal('show');
    });



    $('#functionRoomImage').change(function () {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#imagePreview').attr('src', e.target.result).show();
                $('#imagePlaceholderText').hide(); // Hide placeholder text
            };
            reader.readAsDataURL(file);
        } else {
            $('#imagePreview').hide();
            $('#imagePlaceholderText').show(); // Show placeholder if image removed
        }
    });

    $('#functionRoom360').change(function () {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#360Preview').attr('src', e.target.result).show();
                $('#360PlaceholderText').hide(); // Hide placeholder text
            };
            reader.readAsDataURL(file);
        } else {
            $('#360Preview').hide();
            $('#360PlaceholderText').show(); // Show placeholder if image removed
        }
    });


    $('#admin-new-function-rooms').submit(function (event) {
        console.log('Event bound');
        event.preventDefault();


        if (!this.checkValidity()) {
            this.classList.add('was-validated');
            return;
        }
        this.classList.remove('was-validated');

        $('#saveFunctionRoomBtn').attr('disabled', true);
        $('#saveFunctionRoomBtn .spinner-border').removeClass('d-none');
        $('#saveFunctionRoomBtn .btn-text').text('Saving...');

        var formData = new FormData(this);
        var form = this;

        $.ajax({
            url: $(this).attr('action'),
            type: $(this).attr('method'),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                $('#adminCreateFunctionRooms').modal('hide');
                $('#imagePreview').attr('src', '#').hide();

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
                    title: 'Added Successfully'
                });

                form.reset();
                $(form).removeClass('was-validated');


                $('#imagePreview').hide();
                $('#360Preview').hide();
                $('#imagePlaceholderText').show();
                $('#saveFunctionRoomBtn').attr('disabled', false);
                $('#saveFunctionRoomBtn .spinner-border').addClass('d-none');
                $('#saveFunctionRoomBtn .btn-text').text('Save');

                refreshTableFunctionRooms();
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
                    title: 'Failed to add'
                });
            }
        });

    });

    $('#functionRoomTable').on('click', '.admin_edit_function_room', function () {
        var info_id = $(this).data("id");
        // $('#updateImagePreview').attr('src', '').hide();
        // $('#updateImagePlaceholderText').show();
        // $('#update_function_room_image').val('');
        // $('#update_function_room_featured').prop('checked', false);

        $.get('/admin/admin-fetch-function-rooms/' + info_id, function (data) {
            $('#adminUpdateFunctionRooms').modal('show');


            $('#update_function_room_section').val(data.function_room_section);
            $('#update_function_room_name').val(data.function_room_name);
            $('#update_function_room_rate').val(data.function_room_rate);
            $('#update_function_room_capacity').val(data.function_room_capacity);
            $('#update_function_room_description').val(data.function_room_description);
            $('#update_function_room_policy').val(data.function_room_policy);
            $('#current_image_function_room').val(data.function_room_image);
            $('#current_360_function_room').val(data.function_room_360);


            $('#info_id').val(info_id);

            if (data.function_room_image) {
                const imagePath = '/assets/images/function-rooms/' + data.function_room_image;
                $('#updateImagePreview').attr('src', imagePath).show();
                $('#updateImagePlaceholderText').hide();
            }

            if (data.function_room_360) {
                const imagePath = '/assets/images/function-rooms-360/' + data.function_room_360;
                $('#update360Preview').attr('src', imagePath).show();
                $('#update360PlaceholderText').hide();
            }

            if (data.featured == 1) {
                $('#update_function_room_featured').prop('checked', true);
            } else {
                $('#update_function_room_featured').prop('checked', false);
            }
        }).fail(function () {
            alert("Data not found");
        });
    });

    $('#update_function_room_image').on('change', function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                $('#updateImagePreview').attr('src', e.target.result).show();
                $('#updateImagePlaceholderText').hide();
            };
            reader.readAsDataURL(file);
        } else {
            $('#updateImagePreview').hide();
            $('#updateImagePlaceholderText').show();
        }
    });

    $('#update_function_room_360').on('change', function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                $('#update360Preview').attr('src', e.target.result).show();
                $('#update360PlaceholderText').hide();
            };
            reader.readAsDataURL(file);
        } else {
            $('#update360Preview').hide();
            $('#update360PlaceholderText').show();
        }
    });


    $('#admin-update-function-rooms').submit(function (event) {
        event.preventDefault();

        if (!this.checkValidity()) {
            this.classList.add('was-validated');
            return;
        }
        this.classList.remove('was-validated');

        $('#updateFunctionRoomBtn').attr('disabled', true);
        $('#updateFunctionRoomBtn .spinner-border').removeClass('d-none');
        $('#updateFunctionRoomBtn .btn-text').text('Updating...');

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
                $('#adminUpdateFunctionRooms').modal('hide');
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
                refreshTableFunctionRooms();
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
                    title: 'Update Failed'
                });
            },
            complete: function () {
                $('#updateFunctionRoomBtn').attr('disabled', false);
                $('#updateFunctionRoomBtn .spinner-border').addClass('d-none');
                $('#updateFunctionRoomBtn .btn-text').text('Update');
            }
        });
    });

    $('#functionRoomTable').on('click', '.delete_function_room', function () {
        var functionRoomId = $(this).data('id');
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
                    url: '/admin/admin-delete-function-rooms',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        functionRoom_id: functionRoomId,
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
                        refreshTableFunctionRooms();
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

    function refreshTableFunctionRooms() {
        $.ajax({
            url: '/admin/get-updated-function-rooms-table',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                var functionRooms = response.data;
                var tableBody = $('#functionRoomTable tbody');
                $('[data-bs-toggle="tooltip"]').tooltip('dispose');
                tableBody.empty();
                functionRooms.forEach(function (functionRoom) {
                    var actionButtons = `
                    <button type="button" class="btn btn-sm btn-icon btn-primary admin_edit_function_room"
                        data-bs-toggle="tooltip" data-bs-placement="left" title="Edit" data-id="${functionRoom.id}">
                        <i class='bx bx-edit'></i>
                    </button>

                    <button type="button" class="btn btn-sm btn-icon btn-danger delete_function_room"
                        data-bs-toggle="tooltip" data-bs-placement="right" title="Delete" data-id="${functionRoom.id}">
                        <i class='bx bx-trash'></i>
                    </button>`;
                    var function_room_section = functionRoom.function_room_section ? functionRoom.function_room_section.toUpperCase() : 'N/A';
                    var function_room_name = functionRoom.function_room_name ? functionRoom.function_room_name.toUpperCase() : 'N/A';
                    var function_room_rate = functionRoom.function_room_rate ? functionRoom.function_room_rate : 'N/A';
                    var function_room_capacity = functionRoom.function_room_capacity ? functionRoom.function_room_capacity : 'N/A';
                    var function_room_description = functionRoom.function_room_description ? functionRoom.function_room_description.toUpperCase() : 'N/A';
                    var function_room_policy = functionRoom.function_room_policy ? functionRoom.function_room_policy : 'N/A';
                    var function_room_image = functionRoom.function_room_image
                        ? `<img src="/assets/images/function-rooms/${functionRoom.function_room_image}" alt="Amenity Image"
         style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px;">`
                        : 'N/A';

                    var function_room_360 = functionRoom.function_room_360
                        ? `<img src="/assets/images/function-rooms-360/${functionRoom.function_room_360}" alt="Amenity Image 360"
         style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px;">`
                        : 'N/A';

                    var is_featured = functionRoom.featured == 1
                        ? `<span class="badge bg-success">Yes</span>`
                        : `<span class="badge bg-secondary">No</span>`;
                    var row = $(`
                                <tr>
                                    <td>${function_room_section}</td>
                                    <td>${function_room_name}</td>
                                    <td>${function_room_rate}</td>
                                    <td>${function_room_capacity}</td>
                                    <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${function_room_description}</td>
                                    <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${function_room_policy}</td>
                                    <td style="vertical-align: middle;">${function_room_image}</td>
                                    <td style="vertical-align: middle;">${function_room_360}</td>
                                    <td>${is_featured}</td>
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
