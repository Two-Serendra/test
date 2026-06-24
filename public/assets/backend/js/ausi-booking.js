$(document).ready(function () {

    const philippineHolidays = [
        "2026-01-01",
        "2026-02-25",
        "2026-04-09",
        "2026-04-17",
        "2026-04-18",
        "2026-05-01",
        "2026-06-12",
        "2026-08-21",
        "2026-08-31",
        "2026-11-01",
        "2026-11-30",
        "2026-12-25",
        "2026-12-30"
    ];


    let ausiPickerAdmin = null;
    let ausiPickerEmergency = null;

    initAusiPicker();

    function initAusiPicker() {

        if (ausiPickerAdmin) {
            ausiPickerAdmin.destroy();
        }

        if (ausiPickerEmergency) {
            ausiPickerEmergency.destroy();
        }

        const commonOptions = {
            dateFormat: "Y-m-d",
            minDate: "today",

            disable: [
                function (date) {

                    const unit = $('unitAusi').val();
                    const category = getUnitCategory(unit);

                    const formattedDate = flatpickr.formatDate(date, "Y-m-d");

                    const isSunday = date.getDay() === 0;
                    const isFriday = date.getDay() === 5;
                    const isHoliday = philippineHolidays.includes(formattedDate);

                    if (category === 'group1') {
                        return isFriday || isSunday || isHoliday;
                    }

                    if (category === 'group2') {
                        return isSunday || isHoliday;
                    }

                    return false;
                }
            ]
        };

        ausiPickerAdmin = flatpickr("#ausiBookingDateAdmin", commonOptions);
        ausiPickerEmergency = flatpickr("#ausiBookingDateAdminEmergency", commonOptions);
    }

    function getUnitCategory(unit) {

        if (!unit) return null;
        unit = unit.replace(/[\s-]/g, '');

        const lastLetter = unit.slice(-1).toUpperCase();

        if (['A', 'B', 'C', 'D', 'E'].includes(lastLetter)) {
            return 'group1';
        }

        if (['F', 'R', 'H', 'I'].includes(lastLetter)) {
            return 'group2';
        }

        return null;
    }


    $('.AddAusiBookingAdmin').on('click', function () {
        $('#ausiAddBookingAdminModal').modal('show');
        disableCreateBtn();
        $('.booking-slot-admin-ausi').prop('disabled', true).prop('checked', false);
    });

    $(document).on('click', '.AddEmergencyAusiBooking', function () {
        $('#emergencyausiBooking').modal('show');

    });

    $('#unitAusi, #ausiBookingDateAdmin').on('change keyup', function () {
        disableCreateBtn('Input changed');
        $bookingSlots.prop('disabled', true).prop('checked', false);
    });

    let slotsLoaded = false;

    function disableCreateBtn(reason = '') {
        slotsLoaded = false;
        $('#saveAdminAusiBtn')
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
        $('#saveAdminAusiBtn')
            .prop('disabled', false)
            .removeClass('btn-secondary')
            .addClass('btn-primary');

        $('#slotStatusBadge')
            .removeClass('bg-secondary bg-warning')
            .addClass('bg-primary')
            .text('Slots loaded');
    }

    disableCreateBtn();


    flatpickr("#ausiBookingDateAdmin", {
        dateFormat: "Y-m-d",
        minDate: "today"
    });

    flatpickr("#ausiBookingDateAdminEmergency", {
        dateFormat: "Y-m-d",
    });

    const $bookingSlots = $('.booking-slot-admin-ausi');
    $bookingSlots.prop('disabled', true);

    const $bookingDate = $('#ausiBookingDateAdmin');
    const $submitBtn = $('#saveAdminAusiBtn');

    $('#checkAusiSlots').on('click', function () {

        let date = $('#ausiBookingDateAdmin').val();
        let unit = $('#unitAusi').val();

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
            url: '/admin/admin-ausi/booked-slots',
            type: 'GET',
            data: { date, unit },
            success: function (res) {
                resetSlots();
                disableBookedSlots(res.booked_slots);
                enableCreateBtn();
            },
            complete: function () {
                $('#checkAusiSlots').prop('disabled', false).html('<i class="bx bx-search"></i> Refresh Slots');
            }
        });
    }

    function resetSlots() {
        const $bookingSlots = $('.booking-slot-admin-ausi');
        $bookingSlots.prop('disabled', false).prop('checked', false);
        $bookingSlots.each(function () {
            $('label[for="' + this.id + '"]').removeClass('btn-secondary disabled').addClass('btn-outline-primary');
        });
    }

    function disableBookedSlots(bookedSlots) {
        bookedSlots.forEach(slot => {
            const $radio = $('.booking-slot-admin-ausi[data-slot="' + slot + '"]');
            if ($radio.length) {
                $radio.prop('disabled', true);
                $('label[for="' + $radio.attr('id') + '"]').removeClass('btn-outline-primary').addClass('btn-secondary disabled');
            }
        });
    }


    $('#ausiBookingFormAdmin').submit(function (event) {
        event.preventDefault();

        const form = this;
        const $submitBtn = $('#saveAdminAusiBtn');
        const $bookingDate = $('#ausiBookingDateAdmin');
        const modal = $bookingDate.val();

        const $enabledSlots = $('input[name="booking_time_slot"]:not(:disabled)');
        const $selectedEnabledSlot = $('input[name="booking_time_slot"]:checked:enabled');

        if ($enabledSlots.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Available Time Slots',
                text: 'All time slots are already booked for this date.'
            });
            return;
        }

        if ($selectedEnabledSlot.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Select Time Slot',
                text: 'Please select an available time slot.'
            });
            return;
        }

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

        const sendBooking = (forceBooking = false) => {
            const formData = new FormData(form);

            if (forceBooking) {
                formData.append('force_booking', true);
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

                    flatpickr('#AusiBookingDateAdmin', {
                        dateFormat: 'Y-m-d',
                        minDate: "today"
                    });
                    updateSlots(selectedDate);
                },

                error(xhr) {

                    if (xhr.status === 409) {

                        const res = xhr.responseJSON || {};
                        if (res.type === 'slot_taken') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Time Slot Taken',
                                text: res.message,
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#d33'
                            });

                            updateSlots($bookingDate.val());
                            return;
                        }

                        if (res.type === 'yearly_booking_exists') {

                            Swal.fire({
                                icon: 'warning',
                                title: 'Existing Yearly Booking',
                                text: res.message,
                                showCancelButton: true,
                                confirmButtonText: 'Yes, continue',
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
                    refreshAusiTableBookings();
                    $('#ausiAddBookingAdminModal').modal('hide');
                }
            });
        };
        sendBooking(false);
    });


    $(document).on('click', '.admin-ausi-booking-cancel', function () {
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
                    url: '/admin/admin-ausi-booking/cancel/' + bookingId,
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (res) {
                        if (res.success) {
                            Swal.fire('Cancelled!', 'The booking has been cancelled.', 'success')
                            refreshAusiTableBookings();
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


    function refreshAusiTableBookings() {
        $.ajax({
            url: '/admin/admin-get-updated-ausi-table',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                var bookings = response.data;
                var tableBody = $('#ausiBookingTable tbody');

                $('[data-bs-toggle="tooltip"]').tooltip('dispose');
                tableBody.empty();

                bookings.forEach(function (booking) {

                    var disableActions = [0, 2].includes(Number(booking.booking_status));

                    var actionButtons = `
    <div class="d-flex gap-1 justify-content-center">

        <button type="button"
            class="btn btn-primary edit_ausi_booking btn-sm btn-equal"
            data-bs-toggle="tooltip"
            data-bs-placement="left"
            title="View"
            data-id="${booking.id}">
            <i class="fa-solid fa-eye"></i>
        </button>

        <button type="button"
            class="btn ${disableActions ? 'btn-secondary' : 'btn-success'} inspection_ausi_booking btn-sm"
            data-bs-toggle="tooltip"
            data-bs-placement="top"
            title="${disableActions ? booking.display_status : 'Inspect'}"
            data-id="${booking.id}"
            ${disableActions ? 'disabled' : ''}>
            <i class="fa-solid fa-clipboard-check"></i>
        </button>

        <button type="button"
            class="btn btn-sm btn-equal ${disableActions ? 'btn-secondary cancel-booking' : 'btn-danger admin-ausi-booking-cancel'}"
            data-bs-toggle="tooltip"
            data-bs-placement="right"
            title="${disableActions ? booking.display_status : 'Cancel'}"
            data-id="${booking.id}"
            ${disableActions ? 'disabled' : ''}>
            <i class="fa-solid fa-ban"></i>
        </button>

    </div>
`;

                    // Resident type
                    var resType = booking.resident_type
                        ? booking.resident_type.toLowerCase()
                        : '';

                    var residentTypeHtml = '';

                    if (resType === 'tenant') {
                        residentTypeHtml = `
            <span class="badge bg-danger text-uppercase">
                ${booking.resident_type}
            </span>
        `;
                    } else if (resType === 'owner') {
                        residentTypeHtml = `
            <span class="badge bg-primary text-uppercase">
                ${booking.resident_type}
            </span>
        `;
                    } else {
                        residentTypeHtml = `
            <span class="badge bg-secondary">N/A</span>
        `;
                    }

                    // Charged type
                    var chargedType = booking.charged_type == 1
                        ? `<span class="badge bg-primary">FREE</span>`
                        : `<span class="badge bg-danger">BILLABLE</span>`;

                    // Booking status
                    var bookingStatus = `
        <span class="badge bg-${booking.status_badge || 'secondary'} custom-badge">
            ${(booking.display_status || 'Unknown').toUpperCase()}
        </span>
    `;

                    // var emergency = booking.emergency == 1
                    //     ? `<span class="badge bg-danger">Yes</span>`
                    //     : `<span class="badge bg-secondary">No</span>`;

                    var createdBy = booking.createdBy?.name
                        ? booking.createdBy.name.toUpperCase()
                        : 'N/A';

                    var cancelledBy = booking.cancelledBy?.name
                        ? booking.cancelledBy.name.toUpperCase()
                        : 'N/A';

                    var cancelledAt = booking.cancelled_at ?? 'N/A';

                    var completedBy = booking.completedBy?.name
                        ? booking.completedBy.name.toUpperCase()
                        : 'N/A';

                    var completedAt = booking.cancelled_at ?? 'N/A';

                    var row = `
        <tr>
            <td>${booking.transaction_no ?? 'N/A'}</td>
            <td>${booking.name ?? 'N/A'}</td>
            <td>${residentTypeHtml}</td>
            <td>${booking.unit_no ?? 'N/A'}</td>
            <td>${booking.booking_date ?? 'N/A'}</td>
            <td>${booking.booking_time_slot ?? 'N/A'}</td>
            <td
                style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                data-bs-toggle="tooltip"
                title="${booking.remarks}">
                ${booking.remarks ?? 'N/A'}
            </td>

            <td>${bookingStatus}</td>

            <td>${createdBy}</td>
            <td>${booking.created_at ?? 'N/A'}</td>

            <td>${cancelledBy}</td>
            <td>${cancelledAt}</td>
                
             <td>${completedBy}</td>
            <td>${completedAt}</td>
            <td class="sticky-col sticky-col-color">
                ${actionButtons}
            </td>
        </tr>
    `;

                    tableBody.append(row);
                });

                $('[data-bs-toggle="tooltip"]').tooltip();
            },
            error: function (xhr, status, error) {
                console.error('Error refreshing pest control table:', error);
            }
        });
    }

    $('#ausiBookingTable').on('click', '.edit_ausi_booking', function () {

        let info_id = $(this).data("id");
        showLoading();

        $.get('/admin/admin-fetch-ausi-booking/' + info_id, function (data) {

            let bookingStatusBadge = `
    <span class="badge bg-${data.status_badge} text-white">
        ${data.display_status.toUpperCase()}
    </span>
`;


            $('#display_name').text(data.name);
            $('#display_unit').text(data.unit_no);

            // Resident badge
            let residentType = data.resident_type?.toUpperCase() ?? 'N/A';
            let residentBadge = `<span class="badge bg-secondary">${residentType}</span>`;

            if (residentType === 'TENANT') residentBadge = `<span class="badge bg-danger text-white">TENANT</span>`;
            if (residentType === 'OWNER') residentBadge = `<span class="badge bg-primary text-white">OWNER</span>`;

            $('#display_resident_type').html(residentBadge);

            $('#display_booking_date').text(data.booking_date);
            let chargedType = data.charged_type;
            let chargedBadge = `<span class="badge bg-secondary">N/A</span>`;

            $('#display_time_slot').text(data.booking_time_slot);
            $('#display_transaction_no').text(data.transaction_no);

            // Editable fields
            $('#srf_no').val(data.srf_no);
            $('#remarks_ausi').text(data.remarks ?? 'No remarks provided.');
            $('#info_id').val(info_id);
            let inspectionHtml = '';

            if (
                !data.inspection_results ||
                data.inspection_results.length === 0
            ) {

                inspectionHtml = `
            <div class="alert alert-warning mb-0">
                <i class="fa-solid fa-circle-exclamation me-2"></i>
                Unit has not been inspected yet.
            </div>
        `;

            } else {

                inspectionHtml += `
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Inspection Item</th>
                        <th>Result</th>
                    </tr>
                </thead>
                <tbody>
             `;

                data.inspection_results.forEach(function (result) {
                    let badgeClass =
                        result.status == 1
                            ? 'bg-primary'
                            : 'bg-danger';

                    let label =
                        result.status == 1
                            ? result.inspection_item.option_1
                            : result.inspection_item.option_2;

                    inspectionHtml += `
            <tr>
                <td>
                    ${result.inspection_item.item_name}
                </td>
                <td>
                 
                        ${label}
                  
                </td>
            </tr>
        `;
                });

                inspectionHtml += `
                </tbody>
            </table>
        </div>
    `;
            }

            $('#inspectionResultsContainer').html(inspectionHtml);
            $('#display_booking_status').html(bookingStatusBadge);
            hideLoading();
            $('#ausiEdit').modal('show');

        })
            .fail(function () {
                alert("Data not found");
            });
    });

    // $('#updateAusiBookingFormAdmin').submit(function (event) {
    //     event.preventDefault();

    //     if (!this.checkValidity()) {
    //         this.classList.add('was-validated');
    //         return;
    //     }
    //     this.classList.remove('was-validated');

    //     const $btn = $('#UpdateAusiBookingBtn');
    //     const originalWidth = $btn.outerWidth();
    //     $btn
    //         .attr('disabled', true)
    //         .html(`<div class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></div>`)
    //         .css('width', originalWidth + 'px');

    //     var formData = new FormData(this);
    //     var form = this;

    //     $.ajax({
    //         url: $(form).attr('action'),
    //         type: $(form).attr('method'),
    //         headers: {
    //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //         },
    //         data: formData,
    //         processData: false,
    //         contentType: false,
    //         success: function (response) {
    //             $('#ausiEdit').modal('hide');
    //             form.reset();
    //             const Toast = Swal.mixin({
    //                 toast: true,
    //                 position: "top-end",
    //                 showConfirmButton: false,
    //                 timer: 2000,
    //                 timerProgressBar: true,
    //                 customClass: {
    //                     popup: 'colored-toast'
    //                 },
    //                 didOpen: (toast) => {
    //                     toast.onmouseenter = Swal.stopTimer;
    //                     toast.onmouseleave = Swal.resumeTimer;
    //                 }
    //             });

    //             Toast.fire({
    //                 icon: 'success',
    //                 title: 'AUSI Booking Updated Successfully'
    //             });

    //             form.reset();
    //             $(form).removeClass('was-validated');
    //             refreshAusiTableBookings()
    //         },
    //         error: function (xhr, status, error) {
    //             const Toast = Swal.mixin({
    //                 toast: true,
    //                 position: "top-end",
    //                 showConfirmButton: false,
    //                 timer: 2000,
    //                 timerProgressBar: true,
    //                 customClass: {
    //                     popup: 'colored-toast-error'
    //                 },
    //                 didOpen: (toast) => {
    //                     toast.onmouseenter = Swal.stopTimer;
    //                     toast.onmouseleave = Swal.resumeTimer;
    //                 }
    //             });
    //             Toast.fire({
    //                 icon: 'error',
    //                 title: 'Failed to update ausi booking. Please try again.'
    //             });
    //         },
    //         complete: function () {
    //             $btn
    //                 .attr('disabled', false)
    //                 .html(`<span class="btn-text">Update</span>`)
    //                 .css('width', '');
    //         }
    //     });
    // });


    $('.DownloadAusiBookingReports').on('click', function () {
        $('#DownloadAusiBookingReports').modal('show');
    });

    $('.AddAusiInspectionItem').on('click', function () {
        $('#NewInspectionItemModal').modal('show');
    });

    $('#storeInspectionItem').submit(function (event) {
        event.preventDefault();



        if (!this.checkValidity()) {
            this.classList.add('was-validated');
            return;
        }

        this.classList.remove('was-validated');
        const $btn = $('#storeInspectionItemBtn');
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
                $('#NewInspectionItemModal').modal('hide');

                $('#storeInspectionItem')[0].reset(); // Reset the form

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
                    title: 'Item Added Successfully'
                });

                form.reset();
                $(form).removeClass('was-validated');
                refreshTableInspectionItem();
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
                    title: 'Failed to add item'
                });
            },
            complete: function () {
                $btn
                    .attr('disabled', false)
                    .html(`<span class="btn-text">Create</span>`)
                    .css('width', '');
            }
        });
    });

    function refreshTableInspectionItem() {

        $.ajax({
            url: '/admin/get-updated-inspection-item',
            type: 'GET',
            dataType: 'json',

            success: function (response) {

                var inspectionItems = response.data;
                var tableBody = $('#AusiInspectionItemTable tbody');

                $('[data-bs-toggle="tooltip"]').tooltip('dispose');

                tableBody.empty();

                if (inspectionItems.length === 0) {
                    tableBody.append(`
            <tr>
                <td colspan="4" class="text-center">
                    No Record Found
                </td>
            </tr>
        `);

                    return;
                }

                inspectionItems.forEach(function (inspectionItem) {

                    let item_name = inspectionItem.item_name
                        ? inspectionItem.item_name.toUpperCase()
                        : 'N/A';

                    let option_1 = inspectionItem.option_1
                        ? inspectionItem.option_1.toUpperCase()
                        : 'N/A';

                    let option_2 = inspectionItem.option_2
                        ? inspectionItem.option_2.toUpperCase()
                        : 'N/A';


                    let actionButtons = `
            <button type="button"
                class="btn btn-primary edit_inspection_item btn-sm"
                data-bs-toggle="tooltip"
                data-bs-placement="left"
                title="Edit"
                data-id="${inspectionItem.id}">
                <i class="fa-solid fa-pen"></i>
            </button>

            <button type="button"
                class="btn btn-danger delete_inspection_item btn-equal btn-responsive btn-sm"
                data-bs-toggle="tooltip"
                data-bs-placement="right"
                title="Delete"
                data-id="${inspectionItem.id}">
                <i class="fa-solid fa-trash"></i>
            </button>
        `;


                    let row = `
            <tr>
                <td>${item_name}</td>
                <td>${option_1}</td>
                <td>${option_2}</td>
                <td>
                    ${actionButtons}
                </td>
            </tr>
        `;

                    tableBody.append(row);

                });

                $('[data-bs-toggle="tooltip"]').tooltip();

            },

            error: function (xhr, status, error) {
                console.error('Error refreshing the table:', error);
            }
        });

    }

    $('#AusiInspectionItemTable').on('click', '.delete_inspection_item', function () {
        var inspectionItemId = $(this).data('id');
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
                    url: '/admin/delete-inspection-item',
                    type: 'get',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        inspectionItemId: inspectionItemId,
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
                            }
                        });

                        Toast.fire({
                            icon: 'success',
                            title: 'Deleted Successfully'
                        });

                        refreshTableInspectionItem();
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
                            title: 'Activity Deletion Failed'
                        });
                    }
                });
            }
        });
    });

    $(document).on('click', '.edit_inspection_item', function () {

        let id = $(this).data('id');

        $.ajax({
            url: '/admin/admin-fetch-inspection-item/' + id,
            type: 'GET',

            success: function (response) {

                $('#edit_item_id').val(response.id);
                $('#edit_item_name').val(response.item_name);
                $('#edit_option_1').val(response.option_1);
                $('#edit_option_2').val(response.option_2);

                $('#EditInspectionItemModal').modal('show');
            }
        });

    });


    $('#updateInspectionItemForm').submit(function (event) {
        event.preventDefault();
        if (!this.checkValidity()) {
            this.classList.add('was-validated');
            return;
        }
        this.classList.remove('was-validated');

        const $btn = $('#UpdateInspectionItemBtn');
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
                $('#EditInspectionItemModal').modal('hide');
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
                    title: 'Item Updated Successfully'
                });

                form.reset();
                $(form).removeClass('was-validated');
                refreshTableInspectionItem();
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
                    title: 'Failed to update item. Please try again.'
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

    $(document).on('click', '.inspection_ausi_booking', function () {
        let bookingId = $(this).data('id');
        showLoading();
        $('#inspection_booking_id').val(bookingId);
        $('#inspectionChecklist').empty();
        $('#inspection_name').text('');
        $('#inspection_unit').text('');
        $.ajax({
            url: '/admin/admin/fetch-ausi-inspection/' + bookingId,
            type: 'GET',
            success: function (data) {
                $('#inspection_unit').text(data.unit_no);

                let html = '';

                data.inspection_items.forEach(function (item) {

                    html += `
    <div class="card mb-3 inspection-row">
        <div class="card-body">
            <h6 class="inspection-title">
               
                ${item.item_name}
            </h6>
            <div class="inspection-options">

                <label class="me-4">
                    <input 
                        type="radio"
                        class="inspection-radio"
                        name="inspection_${item.id}"
                        value="1"
                        required>

                    ${item.option_1}

                </label>

                <label>

                    <input 
                        type="radio"
                        class="inspection-radio"
                        name="inspection_${item.id}"
                        value="0"
                        required>

                    ${item.option_2}

                </label>
            </div>

        </div>

    </div>
    `;

                });

                $('#inspectionChecklist').html(html);

                $('#UnitAusiInspectionModal').modal('show');
                hideLoading();

            },
            error: function () {

                Swal.fire(
                    'Error',
                    'Unable to load inspection',
                    'error'
                );
            }

        });

    });

    $('#saveInspectionBtn').click(function () {

        let bookingId = $('#inspection_booking_id').val();
        let valid = true;
        let inspections = [];
        $('.inspection-row').removeClass('invalid');
        $('.inspection-row').each(function () {

            let row = $(this);

            let checked = row.find('input[type="radio"]:checked');


            if (checked.length === 0) {

                row.addClass('invalid');

                valid = false;

            } else {

                let name = checked.attr('name');

                let itemId = name.replace('inspection_', '');


                inspections.push({

                    inspection_item_id: itemId,

                    status: checked.val()

                });
            }

        });

        if (!valid) {

            Swal.fire(
                'Incomplete Inspection',
                'Please answer all checklist items.',
                'warning'
            );

            return;

        }
        const $btn = $('#saveInspectionBtn');
        const originalWidth = $btn.outerWidth();
        $btn
            .attr('disabled', true)
            .html(`<div class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></div>`)
            .css('width', originalWidth + 'px');
        $.ajax({

            url: '/admin/admin-save-ausi-inspection',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },

            data: {
                ausi_booking_id: bookingId,
                inspections: inspections
            },

            success: function (response) {
                $('#UnitAusiInspectionModal').modal('hide');
                Swal.fire(
                    'Completed',
                    'Inspection completed successfully.',
                    'success'
                );
                refreshAusiTableBookings();
            },

            error: function (xhr) {

                console.log(xhr.responseText);


                Swal.fire(
                    'Error',
                    'Failed to save inspection.',
                    'error'
                );

            },

            complete: function () {

                $btn
                    .attr('disabled', false)
                    .html(`<span class="btn-text">Complete Inspection</span>`)
                    .css('width', '');
            }
        });
    });


});