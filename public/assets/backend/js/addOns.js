
$(document).ready(function () {


    let currentAddOnsPageUrl = '/admin/get-updated-add-ons-table';
    let currentSearchTerm = '';

    function refreshTableAddOns(url = currentAddOnsPageUrl) {
        $.ajax({
            url: url,
            type: 'GET',
            data: {
                searchAddOns: currentSearchTerm
            },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function (response) {
                const addOns = response.data;
                const tableBody = $('#addOnsTable tbody');
                const paginationContainerAddOns = $('.pagination-container-add-ons');

                $('[data-bs-toggle="tooltip"]').tooltip('dispose');
                tableBody.empty();
                if (addOns.length === 0) {
                    tableBody.append(`
        <tr>
            <td colspan="4" class="text-center">No Add-Ons Found</td>
        </tr>
    `);
                }
                addOns.forEach(function (addOn) {
                    var actionButtons = `
            <button type="button" class="btn btn-sm btn-icon btn-secondary admin_edit_add_ons"
                data-bs-toggle="tooltip" data-bs-placement="left" title="Edit"
                data-id="${addOn.id}">
                <i class='bx bx-edit'></i>
            </button>
        `;

                    if (addOn.status == 1) {
                        actionButtons += `
                <button type="button" class="btn btn-sm btn-warning btn-icon disable_add_ons"
                    data-id="${addOn.id}" data-bs-toggle="tooltip" data-bs-placement="top"
                    title="Disable">
                    <i class='bx bx-block'></i> 
                </button>
            `;
                    } else {
                        actionButtons += `
                <button type="button" class="btn btn-sm btn-primary btn-icon enable_add_ons"
                    data-id="${addOn.id}" data-bs-toggle="tooltip" data-bs-placement="top"
                    title="Enable">
                    <i class='bx bx-check'></i> 
                </button>
            `;
                    }

                    actionButtons += `
            <button type="button" class="btn btn-sm btn-icon btn-danger delete_function_room"
                data-bs-toggle="tooltip" data-bs-placement="right" title="Delete"
                data-id="${addOn.id}">
                <i class='bx bx-trash'></i>
            </button>
        `;

                    var item = addOn.item ? addOn.item.toUpperCase() : 'N/A';
                    var statusDisplay = addOn.status == 1
                        ? `<span class="badge bg-success">Active</span>`
                        : `<span class="badge bg-danger">Disabled</span>`;
                    var qty = addOn.qty;
                    var price = addOn.price;

                    tableBody.append(`
            <tr>
                <td>${item}</td>
                <td>${qty}</td>
                <td>${price}</td>
                <td>${statusDisplay}</td>
                <td>${actionButtons}</td>
            </tr>
        `);
                });
                paginationContainerAddOns.html(response.links);

                $('.pagination-container-add-ons').find('a').off('click').on('click', function (e) {
                    e.preventDefault();
                    let pageUrl = $(this).attr('href');
                    if (currentSearchTerm) {
                        pageUrl = updateQueryStringParameter(pageUrl, 'searchAddOns', currentSearchTerm);
                    }
                    currentAddOnsPageUrl = pageUrl;
                    refreshTableAddOns(pageUrl);
                });

                $('[data-bs-toggle="tooltip"]').tooltip();
            },

            error: function (xhr, status, error) {
                console.error('Error refreshing the table:', error);
            }
        });
    }

    $('#addOnsTable').on('click', '.admin_edit_add_ons', function () {
        var info_id = $(this).data("id");
        $.get('/admin/admin-fetch-add-ons/' + info_id, function (data) {
            $('#adminUpdateAddOns').modal('show');
            $('#edit_item').val(data.item);
            $('#edit_price').val(data.price);
            $('#edit_qty').val(data.qty);
            $('#info_id').val(info_id);
        }).fail(function () {
            alert("Data not found");
        });
    });

    $('#adminEditBookingForm').submit(function (event) {
        event.preventDefault();

        const form = this;
        let isValid = true;

        // Custom validation
        const startTime = $('#editStartTime').val();
        const endTime = $('#editEndTime').val();
        const pax = parseInt($('#editPaxInput').val() || 0);
        const roomCapacity = parseInt($('#editRoomCapacity').text() || 0);

        function convertToMinutes(time) {
            if (!time) return null;
            const [hours, minutes] = time.split(':').map(Number);
            return hours * 60 + minutes;
        }

        const startMinutes = convertToMinutes(startTime);
        const endMinutes = convertToMinutes(endTime);

        if (startMinutes !== null && endMinutes !== null && endMinutes <= startMinutes) {
            $('#timeError').removeClass('d-none');
            $('#editEndTime').addClass('is-invalid');
            isValid = false;
        } else {
            $('#timeError').addClass('d-none');
            $('#editEndTime').removeClass('is-invalid');
        }

        if (pax > roomCapacity) {
            $('#capacityError').removeClass('d-none');
            $('#editPaxInput').addClass('is-invalid');
            isValid = false;
        } else {
            $('#capacityError').addClass('d-none');
            $('#editPaxInput').removeClass('is-invalid');
        }

        if (!isValid) return;

        // Bootstrap form validation
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }
        form.classList.remove('was-validated');

        // Button spinner
        const $btn = $('#updateFunctionRoomBookingBtn');
        const originalWidth = $btn.outerWidth();
        $btn
            .attr('disabled', true)
            .html(`<div class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></div>`)
            .css('width', originalWidth + 'px');

        // FormData
        const formData = new FormData(form);

        $.ajax({
            url: $(form).attr('action'),
            type: $(form).attr('method') || 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                $('#adminEditBookingModal').modal('hide');
                form.reset();
                $(form).removeClass('was-validated');

                const Toast = Swal.mixin({
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                    customClass: { popup: 'colored-toast' },
                    didOpen: (toast) => {
                        toast.onmouseenter = Swal.stopTimer;
                        toast.onmouseleave = Swal.resumeTimer;
                    }
                });

                Toast.fire({
                    icon: 'success',
                    title: 'Updated Successfully'
                });

                refreshTableAddOns();
            },
            error: function (xhr) {
                if (xhr.status === 409) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Booking Conflict',
                        text: xhr.responseJSON.message,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#d33'
                    });
                } else if (xhr.status === 422) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: xhr.responseJSON.message,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#d33'
                    });
                } else {
                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        icon: 'error',
                        title: 'Something went wrong. Please try again later.',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }
            },
            complete: function () {
                $btn
                    .attr('disabled', false)
                    .html(`<span class="btn-text">Update</span>`)
                    .css('width', '');
            }
        });
    });


    $('#searchFormAddOns').on('submit', function (e) {
        e.preventDefault();
        currentSearchTerm = $('#searchInputAddOns').val(); // grab input
        refreshTableAddOns(); // AJAX search
    });


    function updateQueryStringParameter(uri, key, value) {
        let re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
        let separator = uri.indexOf('?') !== -1 ? "&" : "?";
        if (uri.match(re)) {
            return uri.replace(re, '$1' + key + "=" + value + '$2');
        } else {
            return uri + separator + key + "=" + value;
        }
    }




    $('.AdminAddaddOns').on('click', function () {
        $('#adminCreateAddOns').modal('show');
    });


    $('#admin-new-add-ons').submit(function (event) {
        event.preventDefault();

        if (!this.checkValidity()) {
            this.classList.add('was-validated');
            return;
        }
        this.classList.remove('was-validated');

        const $btn = $('#saveAddOnsBtn');
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
                $('#adminCreateAddOns').modal('hide');
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
                refreshTableAddOns();
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
                $btn
                    .attr('disabled', false)
                    .html(`<span class="btn-text">Save</span>`)
                    .css('width', '');
            }
        });

    });

    $('#addOnsTable').on('click', '.enable_add_ons', function () {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Enable Item',
            text: 'Are you sure you want to enable this item?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Enable',
            confirmButtonColor: '#28a745',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/admin/admin-add-ons/enable/' + id,
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (res) {
                        Swal.fire('Enabled!', res.message, 'success');
                        refreshTableAddOns();
                    },
                    error: function () {
                        Swal.fire('Error!', 'Unable to enable function room.', 'error');
                    }
                });
            }
        });
    });


    $('#addOnsTable').on('click', '.delete_add_ons', function () {
        var addOnsId = $(this).data('id');
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
                    url: '/admin/admin-delete-add-ons',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        addOns_id: addOnsId,
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
                        refreshTableAddOns();
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

    $('#addOnsTable').on('click', '.disable_add_ons', function () {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Disable Item',
            text: 'Are you sure you want to disable this item? You can enable it anytime.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Disable',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#d33',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/admin/admin-add-ons/disable/' + id,
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        remarks: result.value
                    },
                    success: function (res) {
                        Swal.fire('Disabled!', res.message, 'success');
                        refreshTableAddOns();
                    },
                    error: function () {
                        Swal.fire('Error!', 'Unable to disable function room.', 'error');
                    }
                });
            }
        });
    });
});
