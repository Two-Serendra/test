$(document).ready(function () {


    $(document).on('click', '.AddPesControlBookingAdmin', function () {
        $('#pestcontrolAdd').modal('show');
        disableCreateBtn();
        $('.booking-slot-admin-pest-control').prop('disabled', true).prop('checked', false);
    });

    $(document).on('click', '.AddEmergencyPesControlBooking', function () {
        $('#emergencyPestControlBooking').modal('show');

    });

    $('#unit, #PestControlBookingDateAdmin').on('change keyup', function () {
        disableCreateBtn('Input changed');
        $bookingSlots.prop('disabled', true).prop('checked', false);
    });

    let slotsLoaded = false;

    function disableCreateBtn(reason = '') {
        slotsLoaded = false;
        $('#saveAdminPestControlBtn')
            .prop('disabled', true)
            .removeClass('btn-primary')
            .addClass('btn-secondary');

        $('#slotStatusBadge')
            .removeClass('bg-primary bg-warning')
            .addClass('bg-secondary')
            .text(reason || 'Not checked');
    }

    function enableCreateBtn() {
        slotsLoaded = true;
        $('#saveAdminPestControlBtn')
            .prop('disabled', false)
            .removeClass('btn-secondary')
            .addClass('btn-primary');

        $('#slotStatusBadge')
            .removeClass('bg-secondary bg-warning')
            .addClass('bg-primary')
            .text('Slots loaded');
    }

    disableCreateBtn();


    flatpickr("#PestControlBookingDateAdmin, #PestControlBookingDateAdminEmergency", {
        dateFormat: "Y-m-d",
        minDate: "today"
    });
    const $bookingSlots = $('.booking-slot-admin-pest-control');
    $bookingSlots.prop('disabled', true);

    const $bookingDate = $('#PestControlBookingDateAdmin');
    const $submitBtn = $('#saveAdminPestControlBtn');

    $('#checkPestControlSlots').on('click', function () {

        let date = $('#PestControlBookingDateAdmin').val();
        let unit = $('#unit').val();

        if (!date || !unit) {
            Swal.fire('Missing Info', 'Enter Unit and Date first', 'warning');
            return;
        }

        $('#slotStatusBadge').removeClass().addClass('badge bg-warning').text('Checking...');
        $(this).prop('disabled', true).text('Checking...');

        updateSlots(date, unit);
    });



    function updateSlots(date, unit) {
        $.ajax({
            url: '/admin/admin-pest-control/booked-slots',
            type: 'GET',
            data: { date, unit },
            success: function (res) {
                resetSlots();
                disableBookedSlots(res.booked_slots);
                enableCreateBtn();
            },
            complete: function () {
                $('#checkPestControlSlots').prop('disabled', false).html('<i class="bx bx-search"></i> Refresh Slots');
            }
        });
    }

    function resetSlots() {
        const $bookingSlots = $('.booking-slot-admin-pest-control');
        $bookingSlots.prop('disabled', false).prop('checked', false);
        $bookingSlots.each(function () {
            $('label[for="' + this.id + '"]').removeClass('btn-secondary disabled').addClass('btn-outline-primary');
        });
    }

    function disableBookedSlots(bookedSlots) {
        bookedSlots.forEach(slot => {
            const $radio = $('.booking-slot-admin-pest-control[data-slot="' + slot + '"]');
            if ($radio.length) {
                $radio.prop('disabled', true);
                $('label[for="' + $radio.attr('id') + '"]').removeClass('btn-outline-primary').addClass('btn-secondary disabled');
            }
        });
    }

    $('#pestControlBookingFormAdmin').submit(function (event) {
        event.preventDefault();

        const form = this;
        const $submitBtn = $('#saveAdminPestControlBtn');
        const $bookingDate = $('#PestControlBookingDateAdmin');
        const modal = $bookingDate.val();

        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }
        form.classList.remove('was-validated');

        const originalWidth = $submitBtn.outerWidth();

        const lockSubmitBtn = () => {
            $submitBtn
                .attr('disabled', true)
                .html(`<div class="spinner-border spinner-border-sm text-light"></div>`)
                .css('width', originalWidth + 'px');
        };

        const unlockSubmitBtn = () => {
            $submitBtn
                .attr('disabled', false)
                .html(`<span class="btn-text">Create</span>`)
                .css('width', '');
        };

        const sendBooking = (forcePayment = false) => {
            const formData = new FormData(form);
            if (forcePayment) {
                formData.append('force_payment', true);
            }

            lockSubmitBtn();

            $.ajax({
                url: $(form).attr('action'),
                type: $(form).attr('method'),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: formData,
                processData: false,
                contentType: false,

                success(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Booking Submitted!',
                        text: response.message || 'Your booking has been successfully submitted.',
                        timer: 2000,
                        showConfirmButton: false
                    });

                    const selectedDate = $bookingDate.val();

                    form.reset();
                    $(form).removeClass('was-validated');

                    flatpickr('#PestControlBookingDateAdmin', {
                        dateFormat: 'Y-m-d',
                        minDate: new Date().fp_incr(1)
                    });
                    updateSlots(selectedDate);
                },

                error(xhr) {

                    if (xhr.status === 409 && xhr.responseJSON?.requires_payment) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Free Booking Limit Reached',
                            text: xhr.responseJSON.message,
                            showCancelButton: true,
                            confirmButtonText: 'Yes, continue with payment',
                            cancelButtonText: 'Cancel',
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33'
                        }).then(result => {
                            if (result.isConfirmed) {
                                sendBooking(true);
                            } else {
                                unlockSubmitBtn();
                            }
                        });
                        return;
                    }


                    if (xhr.status === 422) {
                        let msg = 'Please check the form fields.';

                        if (xhr.responseJSON?.errors) {
                            msg = Object.values(xhr.responseJSON.errors)
                                .map(e => e[0])
                                .join('\n');
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text: msg,
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#d33'
                        });
                        return;
                    }

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: 'Something went wrong. Please try again later.',
                        timer: 3000,
                        showConfirmButton: false
                    });
                },

                complete() {
                    unlockSubmitBtn();
                    refreshPestControlTableBookings()
                    $('#pestcontrolAdd').modal('hide');
                }
            });
        };
        sendBooking();
    });




    function refreshPestControlTableBookings() {
        $.ajax({
            url: '/admin/admin-get-updated-pest-control-table',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                var bookings = response.data;
                var tableBody = $('#pestControlBookingTable tbody');

                $('[data-bs-toggle="tooltip"]').tooltip('dispose');
                tableBody.empty();

                bookings.forEach(function (booking) {

                    var isCancelled = booking.booking_status == 2; // 2 = cancelled

                    var actionButtons = `
                        <div class="d-flex gap-1 justify-content-center">
                            <button type="button" class="btn btn-primary edit_pest_control_booking btn-sm btn-equal"
                                data-bs-toggle="tooltip" data-bs-placement="left" title="View"
                                data-id="${booking.id}">
                                <i class="fa-solid fa-eye"></i>
                            </button>

                            <button type="button"
                                class="btn btn-sm btn-equal ${isCancelled ? 'btn-secondary cancel-booking' : 'btn-danger admin-pest-control-booking-cancel'}"
                                data-bs-toggle="tooltip" data-bs-placement="right"
                                title="${isCancelled ? 'Cancelled' : 'Cancel'}"
                                data-id="${booking.id}" ${isCancelled ? 'disabled' : ''}>
                                <i class="fa-solid fa-ban"></i>
                            </button>
                        </div>
                    `;


                    // Resident type badge
                    var residentType = booking.resident_type ? booking.resident_type.toUpperCase() : 'N/A';
                    var residentTypeHtml = residentType === 'OWNER'
                        ? `<span class="badge bg-primary">${residentType}</span>`
                        : residentType === 'TENANT'
                            ? `<span class="badge bg-danger">${residentType}</span>`
                            : `<span class="badge bg-secondary">N/A</span>`;

                    // Charged type
                    var chargedType = booking.charged_type == 1
                        ? `<span class="badge bg-primary">Free</span>`
                        : `<span class="badge bg-danger">Billable</span>`;

                    // Booking status
                    var bookingStatus = booking.booking_status == 1
                        ? `<span class="badge bg-primary">Booked</span>`
                        : `<span class="badge bg-danger">Cancelled</span>`;

                    // Emergency
                    var emergency = booking.emergency == 1
                        ? `<span class="badge bg-danger">Yes</span>`
                        : `<span class="badge bg-secondary">No</span>`;

                    var row = `
                    <tr>
                        <td>${booking.transaction_no ?? 'N/A'}</td>
                        <td>${booking.srf_no ?? 'N/A'}</td>
                        <td>${booking.name ?? 'N/A'}</td>
                        <td>${residentTypeHtml}</td>
                        <td>${booking.unit_no ?? 'N/A'}</td>
                        <td>${booking.booking_date ?? 'N/A'}</td>
                        <td>${booking.booking_time_slot ?? 'N/A'}</td>
                        <td>${chargedType}</td>
                        <td>${emergency}</td>
                        <td>${booking.remarks ?? 'N/A'}</td>
                        <td>${bookingStatus}</td>
                        <td>${booking.createdBy?.name ? booking.createdBy.name.toUpperCase() : 'N/A'}</td>
                        <td>${booking.created_at ?? 'N/A'}</td>
                        <td>${booking.cancelledBy?.name ? booking.cancelledBy.name.toUpperCase() : 'N/A'}</td>
                        <td>${booking.cancelled_at ?? 'N/A'}</td>

                        <td class="sticky-col sticky-col-color">${actionButtons}</td>
                    </tr>
                `;

                    tableBody.append(row);
                });

                $('[data-bs-toggle="tooltip"]').tooltip();
            },
            error: function (xhr, status, error) {
                console.error('Error refreshing grease trap table:', error);
            }
        });
    }

    $(document).on('click', '.admin-pest-control-booking-cancel', function () {
        const bookingId = $(this).data('id');
        const swalText = "Are you sure you want to cancel this booking?";

        Swal.fire({
            title: 'Cancel Booking',
            text: swalText,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, cancel it',
            cancelButtonText: 'No, keep it'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/admin/admin-pest-control-booking/cancel/' + bookingId,
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (res) {
                        if (res.success) {
                            Swal.fire('Cancelled!', 'The booking has been cancelled.', 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Error', res.message || 'Failed to cancel booking.', 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'Something went wrong while cancelling.', 'error');
                    }
                });
            }
        });
    });

    $('#pestControlBookingEmergencyForm').on('submit', function (e) {
        e.preventDefault();
        const form = this;
        const $submitBtn = $('#saveEmergencyPestControlBtn');

        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }
        form.classList.remove('was-validated');

        const originalWidth = $submitBtn.outerWidth();

        const lockSubmitBtn = () => {
            $submitBtn
                .attr('disabled', true)
                .html(`<div class="spinner-border spinner-border-sm text-light"></div>`)
                .css('width', originalWidth + 'px');
        };

        const unlockSubmitBtn = () => {
            $submitBtn
                .attr('disabled', false)
                .html(`<span class="btn-text">Submit</span>`)
                .css('width', '');
        };

        const sendBooking = (forcePayment = false) => {
            const formData = new FormData(form);
            if (forcePayment) {
                formData.append('force_payment', true);
            }

            lockSubmitBtn();

            $.ajax({
                url: $(form).attr('action'),
                type: $(form).attr('method'),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: formData,
                processData: false,
                contentType: false,

                success(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Booking Submitted!',
                        text: response.message || 'Your booking has been successfully submitted.',
                        timer: 2000,
                        showConfirmButton: false
                    });

                    const selectedDate = $bookingDate.val();

                    form.reset();
                    $(form).removeClass('was-validated');

                    flatpickr('#PestControlBookingDateAdmin', {
                        dateFormat: 'Y-m-d',
                        minDate: new Date().fp_incr(1)
                    });

                    updateSlots(selectedDate);
                },

                error(xhr) {

                    if (xhr.status === 409 && xhr.responseJSON?.requires_payment) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Free Booking Limit Reached',
                            text: xhr.responseJSON.message,
                            showCancelButton: true,
                            confirmButtonText: 'Yes, continue with payment',
                            cancelButtonText: 'Cancel',
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33'
                        }).then(result => {
                            if (result.isConfirmed) {
                                sendBooking(true);
                            } else {
                                unlockSubmitBtn();
                            }
                        });
                        return;
                    }


                    if (xhr.status === 422) {
                        let msg = 'Please check the form fields.';

                        if (xhr.responseJSON?.errors) {
                            msg = Object.values(xhr.responseJSON.errors)
                                .map(e => e[0])
                                .join('\n');
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text: msg,
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#d33'
                        });
                        return;
                    }

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: 'Something went wrong. Please try again later.',
                        timer: 3000,
                        showConfirmButton: false
                    });
                },

                complete() {
                    unlockSubmitBtn();
                    refreshPestControlTableBookings()
                    $('#emergencyPestControlBooking').modal('hide');
                }
            });
        };

        // Initial submit
        sendBooking();
    });


    $('#pestControlBookingTable').on('click', '.edit_pest_control_booking', function () {

        let info_id = $(this).data("id");
        showLoading();

        $.get('/admin/admin-fetch-pest-control-booking/' + info_id, function (data) {



            $('#display_name').text(data.name);
            $('#display_unit').text(data.unit_no);

            // Resident badge
            let residentType = data.resident_type?.toUpperCase() ?? 'N/A';
            let residentBadge = `<span class="badge bg-secondary">${residentType}</span>`;

            if (residentType === 'TENANT') residentBadge = `<span class="badge bg-danger">TENANT</span>`;
            if (residentType === 'OWNER') residentBadge = `<span class="badge bg-primary">OWNER</span>`;

            $('#display_resident_type').html(residentBadge);

            $('#display_booking_date').text(data.booking_date);
            let chargedType = data.charged_type;
            let chargedBadge = `<span class="badge bg-secondary">N/A</span>`;

            // Map types
            if (chargedType == 1) chargedBadge = `<span class="badge bg-primary">FREE</span>`;
            if (chargedType == 2) chargedBadge = `<span class="badge bg-danger">BILLABLE</span>`;

            $('#display_charged_type').html(chargedBadge);

            // Time slot badge
            $('#display_time_slot').text(data.booking_time_slot);
            $('#display_transaction_no').text(data.transaction_no);

            // Editable fields
            $('#srf_no').val(data.srf_no);
            $('#remarks_grease_trap').val(data.remarks);
            $('#info_id').val(info_id);
            $('#pestcontrolEdit').modal('show');

            hideLoading();
        })
            .fail(function () {
                alert("Data not found");
            });
    });



    $('#pestControlBookingTable').on('click', '.view_pest_control_booking', function () {

        let info_id = $(this).data("id");
        showLoading();

        $.get('/admin/admin-fetch-pest-control-booking/' + info_id, function (data) {



            $('#display_name_reports').text(data.name);
            $('#display_unit_reports').text(data.unit_no);

            // Resident badge
            let residentType = data.resident_type?.toUpperCase() ?? 'N/A';
            let residentBadge = `<span class="badge bg-secondary">${residentType}</span>`;

            if (residentType === 'TENANT') residentBadge = `<span class="badge bg-danger">TENANT</span>`;
            if (residentType === 'OWNER') residentBadge = `<span class="badge bg-primary">OWNER</span>`;

            $('#display_resident_type_reports').html(residentBadge);

            $('#display_booking_date_reports').text(data.booking_date);
            let chargedType = data.charged_type;
            let chargedBadge = `<span class="badge bg-secondary">N/A</span>`;

            // Map types
            if (chargedType == 1) chargedBadge = `<span class="badge bg-primary">FREE</span>`;
            if (chargedType == 2) chargedBadge = `<span class="badge bg-danger">BILLABLE</span>`;

            $('#display_charged_type_reports').html(chargedBadge);

            // Time slot badge
            $('#display_time_slot_reports').text(data.booking_time_slot);
            $('#display_transaction_no_reports').text(data.transaction_no);

            // Editable fields
            $('#srf_no_reports').text(data.srf_no);
            $('#remarks_pest_control_reports').text(data.remarks);
            $('#info_id').val(info_id);
            $('#viewPestControlBooking').modal('show');

            hideLoading();
        })
            .fail(function () {
                alert("Data not found");
            });
    });


    $('#updatePestControlBookingFormAdmin').submit(function (event) {
        event.preventDefault();

        if (!this.checkValidity()) {
            this.classList.add('was-validated');
            return;
        }
        this.classList.remove('was-validated');

        const $btn = $('#UpdatePestControlBookingBtn');
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
                $('#pestcontrolEdit').modal('hide');
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
                    title: 'Pest Control Booking Updated Successfully'
                });

                form.reset();
                $(form).removeClass('was-validated');
                refreshPestControlTableBookings()
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
                    title: 'Failed to update event'
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


    flatpickr("#DownloadStartDatePC,#DownloadEndDatePC", {
        enableTime: false,
        dateFormat: "Y-m-d",
        time_24hr: false,
        allowInput: true,
        defaultHour: 8,
        defaultMinute: 0
    });



    $('.DownloadPestControlBookingReports').on('click', function () {
        $('#DownloadPestControlBookingReports').modal('show');
    });

    $('#download-pest-control-booking-reports').submit(function (e) {
        e.preventDefault();

        const $btn = $('#DownloadPestControlBookingReportsBtn');
        const originalWidth = $btn.outerWidth();

        $btn.attr('disabled', true)
            .html('<div class="spinner-border spinner-border-sm text-light"></div>')
            .css('width', originalWidth + 'px');

        const formData = $(this).serialize();

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            xhrFields: {
                responseType: 'blob' // Important to handle file download
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response, status, xhr) {
                const filename = xhr.getResponseHeader('Content-Disposition')
                    .split('filename=')[1]
                    .replace(/"/g, '');

                const blob = new Blob([response], { type: 'text/csv' });
                const link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                $('#DownloadPestControlBookingReports').modal('hide');
                $('#download-pest-control-booking-reports')[0].reset();

            },
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Download Failed',
                    text: xhr.responseJSON?.message || 'Something went wrong. Please try again.',
                    confirmButtonColor: '#d33'
                });
            },
            complete: function () {
                $btn.attr('disabled', false).html('Download').css('width', '');
            }
        });
    });


    $('#uploadBookingBtnPC').on('click', function () {
        $('#PCbookingFileInput').click();
    });

    $('#PCbookingFileInput').on('change', function () {

        if (this.files.length === 0) return;

        let fileName = this.files[0].name;

        if (confirm("Upload file: " + fileName + " ?")) {
            $('#bookingImportFormPC').submit();
        } else {
            $(this).val('');
        }

    });


});