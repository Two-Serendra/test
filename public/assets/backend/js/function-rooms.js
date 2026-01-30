

$(document).ready(function () {
    $('.AdminAddFunctionRoom').on('click', function () {
        $('#adminCreateFunctionRooms').modal('show');
    });

    $('#adminCreateFunctionRooms').on('hidden.bs.modal', function () {
        const form = $('#admin-new-function-rooms')[0];


        form.reset();
        $(form).removeClass('was-validated');


        $('#functionRoomImage').val('');
        $('#functionRoom360').val('');

        // Reset preview slots
        $('.image-slot').each(function (index) {
            $(this).empty().text(`Image ${index + 1}`);
        });
        $('#360Preview').hide();
        $('#imagePreviewContainer360 span').show();
    });

    $('#functionRoomImage').on('change', function (e) {
        const files = e.target.files;

        if (files.length > 4) {
            alert('You can only upload up to 4 images.');
            $(this).val('');
            return;
        }


        const dt = new DataTransfer();

        $('.image-slot').each(function () {
            $(this).empty().text('Empty');
        });

        $.each(files, function (index, file) {
            if (!file.type.startsWith('image/')) return;

            const reader = new FileReader();
            reader.onload = function (event) {
                const $slot = $('.image-slot').eq(index).empty();
                const $img = $('<img>').attr('src', event.target.result);
                const $removeBtn = $('<button>').addClass('remove-btn').html('&times;');

                $removeBtn.on('click', function () {
                    $slot.empty().text('Empty');
                    dt.items.clear(); // reset
                    $('.image-slot img').each(function () {
                        const slotIndex = $(this).parent().data('slot');
                        if (slotIndex !== index) {
                            dt.items.add(files[slotIndex]);
                        }
                    });
                    $('#functionRoomImage')[0].files = dt.files;
                });

                $slot.append($img).append($removeBtn);
            };
            reader.readAsDataURL(file);
            dt.items.add(file);
        });

        $('#functionRoomImage')[0].files = dt.files;
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

        const $btn = $('#saveFunctionRoomBtn');
        const originalWidth = $btn.outerWidth();
        $btn
            .attr('disabled', true)
            .html(`<div class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></div>`)
            .css('width', originalWidth + 'px');

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

            },
            complete: function () {
                // Restore button to original state
                $btn
                    .attr('disabled', false)
                    .html(`<span class="btn-text">Save</span>`)
                    .css('width', '');
            }
        });

    });

    $(document).on('click', '.admin_edit_function_room', function () {
        let info_id = $(this).data('id'); // get the ID from the button

        $.get('/admin/admin-fetch-function-rooms/' + info_id, function (data) {
            $('#adminUpdateFunctionRooms').modal('show');

            $('#editFunctionRoomSection').val(data.function_room_section);
            $('#editFunctionRoomName').val(data.function_room_name);
            $('#editFunctionRoomRate').val(data.function_room_rate);
            $('#editFunctionRoomDiscount').val(data.discount);
            $('#editFunctionRoomCapacity').val(data.function_room_capacity);
            $('#editFunctionRoomDescription').val(data.function_room_description);
            $('#editFunctionRoomShortDescription').val(data.function_room_short_description);
            $('#editFunctionRoomPolicy').val(data.function_room_policy);
            $('#editFunctionRoomId').val(info_id);

            let container = $('#editImagePreviewContainer');
            container.empty();
            if (data.function_room_images && data.function_room_images.length > 0) {
                data.function_room_images.forEach(function (img) {
                    container.append(`
                    <img src="/assets/images/uploads/function-rooms/images/${img}" 
                         style="width:80px;height:80px;object-fit:cover;border-radius:5px;">
                `);
                });
            } else {
                container.append('<span>No images</span>');
            }

            if (data.function_room_360) {
                $('#edit360Preview').attr('src', '/assets/images/uploads/function-rooms/360/' + data.function_room_360).show();
                $('#edit360Placeholder').hide();
            } else {
                $('#edit360Preview').hide();
                $('#edit360Placeholder').show();
            }

            $('#editFunctionRoomFeatured').prop('checked', data.featured == 1);
        });
    });



    $('#editFunctionRoomImage').on('change', function () {
        const files = this.files;
        const previewContainer = $('#editImagePreviewContainer');

        // Restrict to max 4 images
        if (files.length > 4) {
            alert('You can only select up to 4 images.');
            $(this).val(''); // Clear selection
            previewContainer.empty().html('<span style="color: #6c757d;">No Images Selected</span>');
            return;
        }

        // Clear old previews
        previewContainer.empty();

        if (files.length > 0) {
            Array.from(files).forEach(file => {
                if (!file.type.startsWith('image/')) return; // Skip non-images

                const reader = new FileReader();
                reader.onload = function (e) {
                    const img = $('<img>').attr('src', e.target.result).css({
                        width: '100px',
                        height: '100px',
                        objectFit: 'cover',
                        borderRadius: '5px',
                        border: '1px solid #ccc'
                    });
                    previewContainer.append(img);
                };
                reader.readAsDataURL(file);
            });
        } else {
            previewContainer.html('<span style="color: #6c757d;">No Images Selected</span>');
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
        const $btn = $('#updateFunctionRoomBtn');
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
                $btn
                    .attr('disabled', false)
                    .html(`<span class="btn-text">Update</span>`)
                    .css('width', '');
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

    $('#functionRoomTable').on('click', '.disable_function_room', function () {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Disable Function Room',
            input: 'textarea',
            inputPlaceholder: 'Enter remarks...',
            inputValidator: (value) => {
                if (!value) {
                    return 'Remarks are required!';
                }
            },
            showCancelButton: true,
            confirmButtonText: 'Disable',
            confirmButtonColor: '#d33',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/admin/admin-function-rooms/disable/' + id,
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        remarks: result.value
                    },
                    success: function (res) {
                        Swal.fire('Disabled!', res.message, 'success');
                        refreshTableFunctionRooms();
                    },
                    error: function () {
                        Swal.fire('Error!', 'Unable to disable function room.', 'error');
                    }
                });
            }
        });
    });

    // Enable
    $('#functionRoomTable').on('click', '.enable_function_room', function () {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Enable Function Room',
            text: 'Are you sure you want to enable this function room?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Enable',
            confirmButtonColor: '#28a745',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/admin/admin-function-rooms/enable/' + id,
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (res) {
                        Swal.fire('Enabled!', res.message, 'success');
                        refreshTableFunctionRooms();
                    },
                    error: function () {
                        Swal.fire('Error!', 'Unable to enable function room.', 'error');
                    }
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
                    <button type="button" class="btn btn-sm btn-icon btn-secondary admin_edit_function_room"
                        data-bs-toggle="tooltip" data-bs-placement="left" title="Edit"
                        data-id="${functionRoom.id}">
                        <i class='bx bx-edit'></i>
                    </button>
                `;
                    if (functionRoom.function_room_status == 1) {
                        actionButtons += `
                        <button type="button" class="btn btn-sm btn-warning btn-icon disable_function_room"
                            data-id="${functionRoom.id}" data-bs-toggle="tooltip" data-bs-placement="top"
                            title="Disable">
                            <i class='bx bx-block'></i> 
                        </button>
                    `;
                    } else {
                        actionButtons += `
                        <button type="button" class="btn btn-sm btn-primary btn-icon enable_function_room"
                            data-id="${functionRoom.id}" data-bs-toggle="tooltip" data-bs-placement="top"
                            title="Enable">
                            <i class='bx bx-check'></i> 
                        </button>
                    `;
                    }

                    actionButtons += `
                    <button type="button" class="btn btn-sm btn-icon btn-danger delete_function_room"
                        data-bs-toggle="tooltip" data-bs-placement="right" title="Delete"
                        data-id="${functionRoom.id}">
                        <i class='bx bx-trash'></i>
                    </button>
                `;
                    var function_room_section = functionRoom.function_room_section ? functionRoom.function_room_section.toUpperCase() : 'N/A';
                    var function_room_name = functionRoom.function_room_name ? functionRoom.function_room_name.toUpperCase() : 'N/A';
                    var function_room_rate = functionRoom.function_room_rate ? functionRoom.function_room_rate : 'N/A';
                    var function_room_capacity = functionRoom.function_room_capacity ? functionRoom.function_room_capacity : 'N/A';
                    var function_room_short_description = functionRoom.function_room_short_description ? functionRoom.function_room_short_description.toUpperCase() : 'N/A';
                    var function_room_description = functionRoom.function_room_description ? functionRoom.function_room_description.toUpperCase() : 'N/A';
                    var function_room_policy = functionRoom.function_room_policy ? functionRoom.function_room_policy : 'N/A';
                    var function_room_360 = functionRoom.function_room_360
                    var function_room_remarks = functionRoom.function_room_remarks ? functionRoom.function_room_remarks : 'N/A';
                    var discountDisplay = 'N/A';
                    if (functionRoom.discount > 0) {
                        let formattedDiscount = parseFloat(functionRoom.discount)
                            .toFixed(2)           // always 2 decimals
                            .replace(/\.?0+$/, ""); // remove trailing .00 or .0
                        discountDisplay = formattedDiscount + '%';
                    }
                    var function_room_360 = functionRoom.function_room_360
                        ? `<img src="/assets/images/uploads/function-rooms/360/${functionRoom.function_room_360}" alt="Amenity Image 360"
         style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px;">`
                        : 'N/A';

                    var statusDisplay = functionRoom.function_room_status == 1
                        ? `<span class="badge bg-success">Active</span>`
                        : `<span class="badge bg-danger">Disabled</span>`;

                    var is_featured = functionRoom.featured == 1
                        ? `<span class="badge bg-success">Yes</span>`
                        : `<span class="badge bg-secondary">No</span>`;
                    var row = $(`
                                <tr>
                                    <td>${function_room_section}</td>
                                    <td>${function_room_name}</td>
                                    <td>${function_room_rate}</td>
                                    <td>${discountDisplay}</td>
                                    <td>${function_room_capacity}</td>
                                    <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${function_room_short_description}</td>
                                    <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${function_room_description}</td>
                                    <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${function_room_policy}</td>
                                    <td style="vertical-align: middle;">${function_room_360}</td>
                                    <td>${is_featured}</td>
                                    <td>${statusDisplay}</td>
                                    <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${function_room_remarks}</td>
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
