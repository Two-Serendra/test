$(document).ready(function () {
    $(document).on('click', '.AddGreaseTrapBookingAdmin', function () {
        $('#greastrapAdd').modal('show');
    });

    const emergencyBookingPicker = flatpickr('    #GreaseTrapBookingDateAdminEmergency', {
        dateFormat: 'Y-m-d',
        minDate: new Date().fp_incr(1)
    });

    const bookingPicker = flatpickr('#GreaseTrapBookingDateAdmin', {
        dateFormat: 'Y-m-d',
        minDate: new Date().fp_incr(1)
    });

    const $bookingDate = $('#GreaseTrapBookingDateAdmin');
    const $bookingSlots = $('.booking-slot-admin');
    const $submitBtn = $('#saveAdminGreaseTrapBtn');

    function updateSlots(date) {
        if (!date) return;

        $.ajax({
            url: '/admin/grease-trap/booked-slots',
            type: 'GET',
            data: { date },
            success: function (res) {
                resetSlots();
                disableBookedSlots(res.booked_slots);
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Unable to load available slots.'
                });
            }
        });
    }

    function resetSlots() {
        $bookingSlots.each(function () {
            $(this).prop('disabled', false).prop('checked', false);
            $('label[for="' + this.id + '"]')
                .removeClass('disabled btn-secondary')
                .addClass('btn-outline-primary')
                .css('cursor', 'pointer');
        });
    }

    function disableBookedSlots(bookedSlots) {
        bookedSlots.forEach(slot => {
            const $radio = $('.booking-slot-admin[data-slot="' + slot + '"]');

            if ($radio.length) {
                $radio.prop('disabled', true);
                $('label[for="' + $radio.attr('id') + '"]')
                    .removeClass('btn-outline-primary')
                    .addClass('btn-secondary disabled')
                    .css('cursor', 'not-allowed');
            }
        });
    }

    $bookingSlots.prop('disabled', true);


    $bookingDate.on('change', function () {
        const selectedDate = $(this).val();
        updateSlots(selectedDate);
    });

    $('.AddEmergencyGreaseTrapBooking').on('click', function () {
        $('#emergencyGreaseTrapBooking').modal('show');
    });


    $('#greaseTrapBookingFormAdmin').submit(function (event) {
        event.preventDefault();

        const form = this;
        const $submitBtn = $('#saveAdminGreaseTrapBtn');
        const $bookingDate = $('#GreaseTrapBookingDateAdmin');
        const selectedDate = $bookingDate.val();

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
                        text: response.message || 'Booking successfully submitted.',
                        timer: 2000,
                        showConfirmButton: false
                    });

                    form.reset();
                    $(form).removeClass('was-validated');
                    bookingPicker.clear();
                    resetSlots();
                    $bookingSlots.prop('disabled', true);
                    refreshGreaseTrapTableBookings();
                    $('#greastrapAdd').modal('hide');

                },

                error(xhr) {
                    const res = xhr.responseJSON || {};
                    if (res && res.requires_payment) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Free Booking Limit Reached',
                            text: res.message,
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
                        if (res.errors) {
                            msg = Object.values(res.errors).map(e => e[0]).join('\n');
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

                    if (xhr.status === 409) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Time Slot Taken',
                            text: res.message || 'This time slot is already booked just now.',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#d33'
                        });
                        updateSlots(selectedDate);
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

                }
            });
        };

        sendBooking();
    });


    function refreshGreaseTrapTableBookings() {
        $.ajax({
            url: '/admin/admin-get-updated-grease-trap-table',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                var bookings = response.data;
                var tableBody = $('#greaseTrapBookingTable tbody');

                $('[data-bs-toggle="tooltip"]').tooltip('dispose');
                tableBody.empty();

                bookings.forEach(function (booking) {

                    var isCancelled = booking.booking_status == 2; // 2 = cancelled

                    var actionButtons = `
                        <div class="d-flex gap-1 justify-content-center">
                            <button type="button" class="btn btn-primary edit_grease_trap_booking btn-sm btn-equal"
                                data-bs-toggle="tooltip" data-bs-placement="left" title="View"
                                data-id="${booking.id}">
                                <i class="fa-solid fa-eye"></i>
                            </button>

                            <button type="button"
                                class="btn btn-sm btn-equal ${isCancelled ? 'btn-secondary cancel-booking' : 'btn-danger admin-grease-trap-booking-cancel'}"
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

                    var cancelledAt = booking.cancelled_at ? booking.cancelled_at : 'N/A';

                    // Penalty column
                    var penaltyHtml = booking.has_penalty
                        ? `<span class="text-warning fw-bold">₱${parseFloat(booking.penalty_amount).toFixed(2)}</span>`
                        : `-`;

                    // Cancelled By column
                    var cancelledBy = booking.cancelled_by?.name ?? 'N/A';

                    var row = `
                    <tr>
                        <td>${booking.transaction_no ?? 'N/A'}</td>
                        <td>${booking.srf_no ?? 'N/A'}</td>
                        <td>${booking.user ? booking.user.name : 'CONCIERGE'}</td>
                        <td>${residentTypeHtml}</td>
                        <td>${booking.unit_no ?? 'N/A'}</td>
                        <td>${booking.booking_date ?? 'N/A'}</td>
                        <td>${booking.booking_time_slot ?? 'N/A'}</td>
                        <td>${chargedType}</td>
                        <td>${emergency}</td>
                        <td>${booking.remarks ?? 'N/A'}</td>
                        <td>${bookingStatus}</td>
                        <td>${cancelledAt}</td>
                        <td>${penaltyHtml}</td>
                        <td>${cancelledBy}</td>
                        <td class="sticky-col sticky-col-color">${actionButtons}</td>
                    </tr>
                `;

                    tableBody.append(row);
                });

                $('[data-bs-toggle="tooltip"]').tooltip();
                console.log(response.data);
            },
            error: function (xhr, status, error) {
                console.error('Error refreshing grease trap table:', error);
            }
        });
    }

    $(document).on('click', '.admin-grease-trap-booking-cancel', function () {
        const bookingId = $(this).data('id');

        // STEP 1: Check if penalty applies
        $.ajax({
            url: '/admin/admin-grease-trap-booking/cancel/' + bookingId,
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (res) {

                if (!res.success) {
                    Swal.fire('Error', res.message || 'Failed to cancel booking.', 'error');
                    return;
                }

                // If cancellation requires confirmation (penalty applies)
                if (res.requires_confirmation) {

                    Swal.fire({
                        title: 'Cancel Booking',
                        html: 'Are you sure you want to cancel this booking?' +
                            res.message,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, cancel it',
                        cancelButtonText: 'No, keep it'
                    }).then((result) => {

                        if (result.isConfirmed) {
                            $.ajax({
                                url: '/admin/admin-grease-trap-booking/cancel/' + bookingId,
                                type: 'POST',
                                data: {
                                    _token: $('meta[name="csrf-token"]').attr('content'),
                                    confirm: 1
                                },
                                success: function (res2) {

                                    if (res2.success) {
                                        Swal.fire('Cancelled!', res2.message, 'success')
                                            .then(() => {
                                                refreshGreaseTrapTableBookings();
                                            });
                                    } else {
                                        Swal.fire('Error', res2.message || 'Failed to cancel booking.', 'error');
                                    }

                                },
                                error: function () {
                                    Swal.fire('Error', 'Something went wrong while cancelling.', 'error');
                                }
                            });
                        }
                    });

                } else {

                    Swal.fire('Cancelled!', res.message, 'success')
                        .then(() => {
                            refreshGreaseTrapTableBookings();
                        });
                }
            },
            error: function () {
                Swal.fire('Error', 'Something went wrong while cancelling.', 'error');
            }
        });
    });

    $('#greaseTrapBookingEmergencyForm').on('submit', function (e) {
        e.preventDefault();
        const form = this;
        const $submitBtn = $('#saveEmergencyGreaseTrapBtn');

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

                    flatpickr('#GreaseTrapBookingDateAdmin', {
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

                    /** -------------------------------
                     * Fallback error
                     * ------------------------------- */
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
                    refreshGreaseTrapTableBookings()
                    $('#emergencyGreaseTrapBooking').modal('hide');
                }
            });
        };

        // Initial submit
        sendBooking();
    });


    $('#greaseTrapBookingTable').on('click', '.edit_grease_trap_booking', function () {

        let info_id = $(this).data("id");

        $.get('/admin/admin-fetch-grease-trap-booking/' + info_id, function (data) {

            $('#greastrapEdit').modal('show');

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

            $('#display_time_slot').text(data.booking_time_slot);
            $('#display_transaction_no').text(data.transaction_no);


            $('#srf_no').val(data.srf_no);
            $('#remarks_grease_trap').val(data.remarks);
            $('#info_id').val(info_id);
        })
            .fail(function () {
                alert("Data not found");
            });
    });


    $('#updateGreaseTrapBookingFormAdmin').submit(function (event) {
        event.preventDefault();

        if (!this.checkValidity()) {
            this.classList.add('was-validated');
            return;
        }
        this.classList.remove('was-validated');

        const $btn = $('#UpdateGreaseTrapBookingBtn');
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
                $('#greastrapEdit').modal('hide');
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
                    title: 'Grease Trap Booking Updated Successfully'
                });

                form.reset();
                $(form).removeClass('was-validated');
                refreshGreaseTrapTableBookings();
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

    flatpickr("#DownloadStartDateGT,#DownloadEndDateGT", {
        enableTime: false,
        dateFormat: "Y-m-d",
        time_24hr: false,
        allowInput: true,
        defaultHour: 8,
        defaultMinute: 0
    });



    $('.DownloadGreaseTrapBookingReports').on('click', function () {
        $('#DownloadGreaseTrapBookingRecords').modal('show');
    });

    $('#download-grease-trap-booking-reports').submit(function (e) {
        e.preventDefault();

        const $btn = $('#DownloadGreaseTrapBookingReportsBtn');
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

                $('#DownloadGreaseTrapBookingRecords').modal('hide');
                $('#download-grease-trap-booking-reports')[0].reset();

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



    $('#uploadBookingBtnGT').on('click', function () {
        $('#GTbookingFileInput').click();
    });

    $('#GTbookingFileInput').on('change', function () {

        if (this.files.length === 0) return;

        let fileName = this.files[0].name;

        if (confirm("Upload file: " + fileName + " ?")) {
            $('#bookingImportFormGT').submit();
        } else {
            $(this).val('');
        }

    });



});