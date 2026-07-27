$(document).ready(function () {

    $('.addFitnessHubBookingBtn').on('click', function () {
        resetBookingForm();
        $('#NewFitnessHubBookingModal').modal('show');
    });

    window.showLoading = function () {
        $('#loadingOverlay').css('display', 'flex').hide().fadeIn(150);
    }

    window.hideLoading = function () {
        $('#loadingOverlay').fadeOut(150);
    }

    const adminModal = $('#NewFitnessHubBookingModal');
    const bookingTabs = adminModal.find('#bookingTabs');
    const bookingTypeInput = adminModal.find('#bookingType');
    const unitNumber = adminModal.find('#unitNumber');
    const checkUnitBtn = adminModal.find('.checkUnit');
    const unitStatus = adminModal.find('#unitStatus');
    const residentType = adminModal.find('#selectResidentType');
    const nameField = adminModal.find('#name');
    const contactField = adminModal.find('#contact_number');
    const dateField = adminModal.find('#dateFieldBookingFH');
    const startTimeDropdown = adminModal.find('#booking_start_time_FH');
    const endTimeDropdown = adminModal.find('#booking_end_time_FH');
    const submitButton = adminModal.find('#saveFitnessHubBookingBtn');

    let bookingType = bookingTabs.find('a.active').data('value') || "Advanced Booking";
    bookingTypeInput.val(bookingType);
    updateFormState(bookingType);

    bookingTabs.find('a').on('click', function () {
        bookingType = $(this).data('value');
        bookingTypeInput.val(bookingType);
        updateFormState(bookingType);
    });


    function updateFormState(type) {
        if (type === "Walk-in") {
            checkUnitBtn.hide();
            unitStatus.hide();
            toggleFields(true);
            enableAllFields();
        }

        else if (type === "Advanced Booking") {
            checkUnitBtn.show().prop('disabled', false);
            unitStatus.show();
            toggleFields(true);
            dateField.prop('disabled', true);
            submitButton.prop('disabled', true);
        }

        else if (type === "24hrs") {
            checkUnitBtn.hide();
            unitStatus.hide();
            toggleFields(true);
            enableAllFields();
        }
    }


    function toggleFields(disable) {
        residentType.prop('disabled', true);
        nameField.prop('disabled', true);
        contactField.prop('disabled', true);
        startTimeDropdown.prop('disabled', disable)
            .empty()
            .append('<option>' + (disable ? 'Select a Date First' : 'Select Start Time') + '</option>');
        endTimeDropdown.prop('disabled', disable)
            .empty()
            .append('<option>' + (disable ? 'Select start time first' : 'Select End Time') + '</option>');
    }

    function enableAllFields() {
        submitButton.prop('disabled', false);
        residentType.prop('disabled', false);
        nameField.prop('disabled', false);
        contactField.prop('disabled', false);
    }

    function resetBookingForm() {
        const form = $('#AdminNewBookingFitnessHub')[0];
        form.reset();
        bookingType = "Advanced Booking";
        bookingTypeInput.val(bookingType);

        startTimeDropdown
            .empty()
            .append('<option>Select a Date First</option>')
            .prop('disabled', true);

        endTimeDropdown
            .empty()
            .append('<option>Select Start Time First</option>')
            .prop('disabled', true);

        // Reset Tabs
        $('#bookingTabs .nav-link').removeClass('active');
        $('#advanced-tab').addClass('active');
        $('#selectResidentType').prop('disabled', true).val('Owner');
        $('#unitNumber, #name, #contact_number, #dateFieldBooking').prop('disabled', true).val('');

        $('.bookingType').prop('checked', false);
        $('#selectedSlotsInput').val('');
        $('.checkUnit, #unitStatus').show();
        $('.checkUnit').prop('disabled', true);
        $('#unitStatus').text('0/0');
        $('#saveFitnessHubBookingBtn').prop('disabled', true);
        $('#amenityIdBooking').val('');
        $('#fitnessHubSelectBooking').val('').change();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').hide();
        updateFormState(bookingType);
    }

    $("#contact_number").on("input", function () {
        let value = $(this).val().replace(/\D/g, "");
        if (value.length > 11) value = value.substring(0, 11);
        $(this).val(value);
    });


    $('#NewFitnessHubBookingModal').on('change', '#fitnessHubSelectBooking', function () {
        let selectedFitnessHubId = $(this).val();

        $('#unitStatus').text('0/0').attr('class', 'mt-1 text-muted');

        const modal = $('#NewFitnessHubBookingModal');
        const unitNumber = modal.find('#unitNumberFH');
        const checkUnit = modal.find('.checkUnitFH');
        const dateField = modal.find('#dateFieldBookingFH');

        const startTimeDropdown = modal.find('#booking_start_time_FH');
        const endTimeDropdown = modal.find('#booking_end_time_FH');

        if (dateField[0]._flatpickr) {
            dateField[0]._flatpickr.clear();
        }
        dateField.val('');

        dateField.prop('disabled', false);
        startTimeDropdown.prop('disabled', true);
        endTimeDropdown.prop('disabled', true);
        unitNumber.prop('disabled', false);
        checkUnit.prop('disabled', false);

        $.ajax({
            url: '/admin/fetch-date-blocking-fitness-hub',
            method: 'GET',
            data: { fitness_hub_id: selectedFitnessHubId },

            success: function (blockedDates) {
                console.log("blockedDates:", blockedDates);
                if (dateField[0]._flatpickr) {
                    dateField[0]._flatpickr.destroy();
                }

                const today = new Date();
                today.setHours(0, 0, 0, 0);

                let currentMonday = new Date(today);
                currentMonday.setDate(today.getDate() - today.getDay() + 1);

                let maxBookingDate = new Date(currentMonday);
                maxBookingDate.setDate(currentMonday.getDate() + 13);

                if (today.getDay() >= 5) {
                    maxBookingDate.setDate(maxBookingDate.getDate() + 7);
                }

                flatpickr(dateField[0], {
                    enableTime: false,
                    dateFormat: "Y-m-d",
                    minDate: today,
                    maxDate: maxBookingDate,
                    allowInput: false,
                    altInput: true,
                    altFormat: "F j, Y",
                    disable: blockedDates,

                    onChange: function (selectedDates, dateStr) {
                        if (dateStr) {
                            fetchAvailableStartTimes(modal, dateStr, selectedFitnessHubId);
                        }
                    }
                });
            },

            error: function (xhr) {
                console.error('Failed to fetch blocked dates:', xhr.responseText);
            }
        });

        if ($(this).val()) {
            dateField.prop('disabled', false);
        } else {
            dateField.prop('disabled', true).val('');
            startTimeDropdown.prop('disabled', true).empty().append('<option>Select Date First</option>');
            endTimeDropdown.prop('disabled', true).empty().append('<option>Select a start time</option>');
        }
    });

    function fetchAvailableStartTimes(modal, bookingDate, selectedFitnessHubId) {
        const fitnessHubId = modal.find('#fitnessHubSelectBooking').val();
        const startTimeDropdown = modal.find('#booking_start_time_FH');
        const endTimeDropdown = modal.find('#booking_end_time_FH');
        startTimeDropdown.prop('disabled', true).empty().append('<option>Loading...</option>');
        endTimeDropdown.prop('disabled', true).empty().append('<option>Select a start time first</option>');

        if (!bookingDate || !fitnessHubId) return;

        $.ajax({
            url: '/admin/fetch-available-times-fitness-hub',
            method: 'GET',
            data: { fitness_hub_id: fitnessHubId, booking_date: bookingDate },
            success: function (availableTimePairs) {
                console.log("Available Times Response:", availableTimePairs);
                startTimeDropdown.empty();
                endTimeDropdown.empty();


                if (availableTimePairs.error) {
                    startTimeDropdown.append('<option>No Schedule</option>').prop('disabled', true);
                    endTimeDropdown.append('<option>No Schedule</option>').prop('disabled', true);
                    return;
                }

                if (availableTimePairs.length > 0) {
                    startTimeDropdown.append('<option>Select Start Time</option>');
                    availableTimePairs.forEach(pair => {
                        startTimeDropdown.append(`<option value="${pair.start}">${pair.start}</option>`);
                    });
                    startTimeDropdown.prop('disabled', false);
                } else {
                    console.warn("No available times - Fully Booked");
                    startTimeDropdown.append('<option>Fully Booked</option>').prop('disabled', true);
                    endTimeDropdown.append('<option>Fully Booked</option>').prop('disabled', true);

                }
            },
            error: function (xhr) {
                console.error('Failed to fetch available times:', xhr.responseText);
            }
        });
    }

    $(document).on('change', '#booking_start_time_FH', function () {
        const modal = $('#NewFitnessHubBookingModal');
        const selectedStartTime = $(this).val();
        const fitnessHubId = modal.find('#fitnessHubSelectBooking').val();
        const bookingDate = modal.find('#dateFieldBookingFH').val();
        const endTimeDropdown = modal.find('#booking_end_time_FH');

        if (!selectedStartTime || !fitnessHubId || !bookingDate) return;

        endTimeDropdown.prop('disabled', true).empty().append('<option>Loading...</option>');

        $.ajax({
            url: '/admin/fetch-available-end-times-fitness-hub',
            method: 'GET',
            data: {
                fitness_hub_id: fitnessHubId,
                booking_date: bookingDate,
                start_time: selectedStartTime
            },
            success: function (response) {
                endTimeDropdown.empty();

                if (response.availableEndTimes.length > 0) {
                    endTimeDropdown.append('<option>Select End Time</option>');
                    response.availableEndTimes.forEach(time => {
                        endTimeDropdown.append(`<option value="${time}">${time}</option>`);
                    });
                    endTimeDropdown.prop('disabled', false);
                } else {
                    endTimeDropdown.append('<option>No available times</option>').prop('disabled', true);
                }
            },
            error: function (xhr) {
                console.error('Failed to fetch end times:', xhr.responseText);
            }
        });
    });


    $(".checkUnitFH").on("click", function () {

        const modal = $('#NewFitnessHubBookingModal');

        const unitNumber = modal.find('#unitNumberFH'); // ✅ FIXED
        const dateField = modal.find('#dateFieldBookingFH'); // ✅ FIXED
        const unitStatus = modal.find('#unitStatus');

        let unit = unitNumber.val()?.trim(); // ✅ FIXED
        let selectedDate = dateField.val()?.trim(); // ✅ FIXED

        if (!unit || !selectedDate) { // ✅ FIXED validation
            Swal.fire({
                icon: "warning",
                title: "Missing Information",
                text: "Please enter a unit number and select a date.",
            });
            return;
        }

        $.ajax({
            url: '/admin/check-unit-booking-fitness-hub',
            type: "GET",
            data: {
                unit: unit,
                fitness_hub_id: modal.find('#fitnessHubSelectBooking').val(),
                dateField: selectedDate
            },

            success: function (response) {
                if (response.success) {

                    let count = response.count;
                    let maxBookings = response.maxBookings;
                    let statusText = `${count}/${maxBookings}`;

                    unitStatus.removeClass("text-muted text-danger text-success text-primary");

                    if (bookingType === "Advanced Booking") {

                        if (count < maxBookings) {
                            unitStatus.addClass("text-success").text(statusText);

                            bookingAllowed = true;
                            enableAllFields();

                        } else {
                            unitStatus.addClass("text-danger").text(statusText + " (Max)");

                            bookingAllowed = false;
                            disableSubmitOnly();
                        }
                    }

                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: response.message,
                    });
                }
            },

            error: function () {
                Swal.fire({
                    icon: "error",
                    title: "Server Error",
                    text: "Something went wrong. Please try again later.",
                });
            }
        });
    });


    $('.AdminNewBookingFitnessHub').submit(function (event) {
        event.preventDefault();

        const form = this;
        const $form = $(form);
        const modal = $form.closest('.modal');

        const startTime = $form.find('[name="booking_start_time"]').val();
        const endTime = $form.find('[name="booking_end_time"]').val();

        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        const startMoment = moment(startTime, 'h:mm A', true);
        let endMoment = moment(endTime, 'h:mm A', true);

        if (!startMoment.isValid() || !endMoment.isValid()) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Time Format',
                text: 'Please select valid start and end times.'
            });
            return;
        }

        if (endMoment.isSameOrBefore(startMoment)) {
            endMoment.add(1, 'day');
        }

        if (endMoment.diff(startMoment) <= 0) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Time Selection',
                text: 'End time must be greater than start time.'
            });
            return;
        }

        const bookingDate = $form.find('[name="booking_date"]').val();

        const bookingDateTime = moment(
            bookingDate + ' ' + startTime,
            'YYYY-MM-DD h:mm A'
        );

        const now = moment();
        const diffHours = bookingDateTime.diff(now, 'hours', true);

        let confirmText = '';
        let confirmIcon = 'question';

        if (diffHours <= 12) {
            confirmText = 'This booking is within 12 hours and cannot be cancelled for free. Proceed?';
            confirmIcon = 'warning';
        } else {
            confirmText = 'Are you sure you want to submit this booking?';
            confirmIcon = 'question';
        }

        Swal.fire({
            icon: confirmIcon,
            title: 'Confirm Booking',
            text: confirmText,
            showCancelButton: true,
            confirmButtonText: 'Yes, proceed',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                submitBookingFitnessHub(form, $form, modal);
            }
        });
    });

    function submitBookingFitnessHub(form, $form, modal) {

        const $btn = modal.find('#saveFitnessHubBookingBtn'); // ✅ FIXED
        const originalWidth = $btn.outerWidth();
        const fitnessHubId = $form.find('[name="fitness_hub_id"]').val();

        if (!fitnessHubId) {
            Swal.fire({
                icon: 'error',
                title: 'Missing Fitness Hub',
                text: 'Please select a fitness hub.'
            });
            return;
        }

        $btn
            .attr('disabled', true)
            .html(`<div class="spinner-border spinner-border-sm text-light"></div>`)
            .css('width', originalWidth + 'px');

        const formData = new FormData(form);


        $.ajax({
            url: $form.attr('action'),
            type: $form.attr('method'),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
            processData: false,
            contentType: false,

            success: function (response) {

                modal.modal('hide');

                Swal.fire({
                    icon: 'success',
                    title: 'Booking Submitted!',
                    text: response.message || 'Your booking has been successfully submitted.',
                    timer: 2000,
                    showConfirmButton: false
                });

                form.reset();
                form.classList.remove('was-validated');
                refreshFitnessHubBookingsTable();
            },

            error: function (xhr) {
                let message = 'Something went wrong. Please try again.';

                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    message = Object.values(xhr.responseJSON.errors)[0][0];
                } else if (xhr.responseJSON?.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.status === 409) {
                    message = xhr.responseText || message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Booking Failed',
                    text: message
                });
            },

            complete: function () {
                $btn
                    .attr('disabled', false)
                    .html(`<span class="btn-text">Submit</span>`)
                    .css('width', '');
            }
        });
    }


    $('#fitnessHubBookingTable').on('click', '.viewFitnessHubRecordDetailsBtn', function () {
        var booking_id = $(this).data("id");
        showLoading();
        $.ajax({
            url: '/admin/fetch/fitness-hub-booking/' + booking_id,
            method: 'GET',
            success: function (response) {
                const booking = response.booking;
                const withinPenalty = $('#detail-transaction-no').data('within-penalty');

                const bookingDate = new Date(booking.booking_date);
                const today = new Date();
                today.setHours(0, 0, 0, 0); // Reset time for accurate comparison
                const optionsDate = { year: 'numeric', month: 'long', day: '2-digit' };
                const formattedDate = bookingDate.toLocaleDateString(undefined, optionsDate);

                // Format start/end times
                const formatTime = (timeStr) => {
                    if (!timeStr) return 'N/A';
                    const d = new Date(`1970-01-01T${timeStr}`);
                    let hours = d.getHours();
                    const minutes = d.getMinutes();
                    const ampm = hours >= 12 ? 'PM' : 'AM';
                    hours = hours % 12 || 12;
                    return `${hours}:${minutes.toString().padStart(2, '0')} ${ampm}`;
                };

                const startTime = booking.booking_start_time;
                const endTime = booking.booking_end_time;

                $('#detail-transaction-no').text(booking.transaction_no)
                    .data('booking-id', booking.id)
                    .data('within-penalty', withinPenalty);
                $('#detail-booking-type').text(booking.booking_type ?? 'N/A');
                $('#detail-name').text(booking.name ?? booking.created_by_name ?? 'N/A');
                $('#detail-unit').text(booking.unit ?? 'N/A');
                $('#detail-activity-name').text(booking.fitness_hub_name ?? 'N/A');
                $('#detail-start-time').text(`${startTime} - ${endTime}` ?? 'N/A');
                $('#detail-contact').text(booking.contact ?? 'N/A');
                $('#detail-booking-date').text(formattedDate ?? 'N/A');
                $('#detail-transaction-no').data('booking-id', booking.id);


                let residentBadgeClass = '';
                if (booking.resident_type === 'TENANT') residentBadgeClass = 'fw-semibold text-danger';
                else if (booking.resident_type === 'OWNER') residentBadgeClass = 'fw-semibold text-primary';
                else residentBadgeClass = 'text-secondary text-white';

                $('#detail-resident-type').html(
                    `<span class="fw-semibold ${residentBadgeClass}">${booking.resident_type ?? 'N/A'}</span>`
                );

                let statusText = '';
                let statusClass = '';
                let cancelledAtText = ''; // 👈 NEW

                const formatDateTime = (dateStr) => {
                    if (!dateStr) return '';
                    const d = new Date(dateStr);

                    const options = {
                        year: 'numeric',
                        month: 'short',
                        day: '2-digit',
                        hour: 'numeric',
                        minute: '2-digit'
                    };

                    return d.toLocaleString(undefined, options);
                };

                // PRIORITY: real booking status first
                switch (booking.booking_status) {
                    case 1:
                        if (bookingDate < today) {
                            statusText = 'Completed';
                            statusClass = 'text-primary';
                            $('#cancelAmenityBookingBtn').hide();
                        } else {
                            statusText = 'Confirmed';
                            statusClass = 'text-primary';
                            $('#cancelAmenityBookingBtn').show();
                        }
                        break;

                    case 2:
                        statusText = 'Cancelled';
                        statusClass = 'text-danger';
                        if (booking.cancelled_at) {
                            cancelledAtText = `<small class="text-muted"> at ${formatDateTime(booking.cancelled_at)}</small>`;
                        }
                        $('#cancelAmenityBookingBtn').hide();
                        break;

                    case 3:
                        statusText = 'Late cancel';
                        statusClass = 'text-warning';

                        if (booking.cancelled_at) {
                            cancelledAtText = `<small class="text-muted"> at ${formatDateTime(booking.cancelled_at)}</small>`;
                        }
                        $('#cancelAmenityBookingBtn').hide();
                        break;

                    case 4:
                        statusText = 'No Show';
                        statusClass = 'text-dark';
                        $('#cancelAmenityBookingBtn').hide();
                        break;

                    default:
                        // ONLY mark as completed if no specific status
                        if (bookingDate < today) {
                            statusText = 'Completed';
                            statusClass = 'text-primary';
                        } else {
                            statusText = 'N/A';
                            statusClass = 'text-secondary';
                        }
                        $('#cancelAmenityBookingBtn').hide();
                }

                // Reset
                $('#detail-penalty-display').removeClass('text-danger text-primary fw-semibold');

                // No penalty
                if (!booking.has_penalty || booking.penalty_amount == 0) {
                    $('#detail-penalty-display').text('-');

                } else {
                    const amount = parseFloat(booking.penalty_amount).toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });

                    if (booking.penalty_waived) {
                        // Waived
                        $('#detail-penalty-display')
                            .html(`₱${amount} <span class="text-primary">(Waived)</span>`)
                            .addClass('fw-semibold');

                    } else {
                        // Not waived
                        $('#detail-penalty-display')
                            .text(`₱${amount}`)
                            .addClass('text-danger fw-semibold');
                    }
                }
                $('#detail-booking-status').html(`
                    <span class="${statusClass}">${statusText}</span>
                    ${cancelledAtText}
                `);

                $('#viewFitnessHubRecordModal').modal('show');
            },
            error: function () {
                alert('Booking not found.');
            },
            complete: function () {
                hideLoading();
            }
        });
    });


    $('#fitnessHubBookingTable').on('click', '.cancel-fitnessHubBooking', function () {

        const bookingId = $(this).data('id');

        Swal.fire({
            title: "Cancel Booking?",
            text: "Are you sure you want to cancel this booking?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, cancel it",
            cancelButtonText: "No, keep it"
        }).then((result) => {

            if (!result.isConfirmed) return;

            $.ajax({
                url: `/admin/cancel-fitness-hub-booking/${bookingId}`,
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (res) {

                    if (!res.success) {
                        Swal.fire('Error', res.message || 'Failed to cancel booking.', 'error');
                        return;
                    }

                    if (res.requires_confirmation) {

                        Swal.fire({
                            title: 'Penalty Warning',
                            html: res.message,
                            icon: 'warning',
                            showDenyButton: true, // 👈 NEW
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            denyButtonColor: '#28a745',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Apply Penalty',
                            denyButtonText: 'Waive Penalty', // 👈 NEW
                            cancelButtonText: 'Cancel'
                        }).then((result2) => {

                            if (result2.isConfirmed) {
                                sendCancelRequestFitnessHub(bookingId, false);
                            }

                            if (result2.isDenied) {
                                sendCancelRequestFitnessHub(bookingId, true);
                            }

                        });
                    } else {
                        // ✅ FIX HERE (no penalty case)
                        Swal.fire('Cancelled!', res.message || 'Booking cancelled successfully.', 'success')
                            .then(() => refreshFitnessHubBookingsTable());
                    }
                }
            });

        });

    });

    function sendCancelRequestFitnessHub(bookingId, waivePenalty = false) {
        $.ajax({
            url: `/admin/cancel-fitness-hub-booking/${bookingId}`,
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                confirm: 1,
                waive_penalty: waivePenalty ? 1 : 0
            },
            success: function (res2) {
                Swal.fire('Cancelled!', res2.message, 'success')
                    .then(() => refreshFitnessHubBookingsTable());
            }
        });
    }


    function refreshFitnessHubBookingsTable() {
        $.ajax({
            url: '/admin/get-updated-fitness-hub-bookings-table',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                var bookings = response.data;
                var tableBody = $('#fitnessHubBookingTable tbody');
                $('[data-bs-toggle="tooltip"]').tooltip('dispose');
                tableBody.empty();

                if (bookings.length === 0) {
                    tableBody.append(`
                    <tr>
                       <td colspan="19" class="text-center">No Records Found</td>
                    </tr>
                `);
                    return;
                }

                bookings.forEach(function (booking) {
                    // Resident Type Badge
                    var residentTypeHtml = 'N/A';
                    if (booking.resident_type) {
                        if (booking.resident_type.toUpperCase() === 'TENANT') {
                            residentTypeHtml = `<span class="badge bg-danger border-danger custom-badge">TENANT</span>`;
                        } else if (booking.resident_type.toUpperCase() === 'OWNER') {
                            residentTypeHtml = `<span class="badge bg-primary border-primary custom-badge">OWNER</span>`;
                        }
                    }

                    // Booking Status Badge
                    var bookingStatusHtml = '';
                    switch (booking.booking_status) {
                        case 1:
                            bookingStatusHtml = `<span class="badge bg-primary">BOOKED</span>`;
                            break;
                        case 2:
                            bookingStatusHtml = `<span class="badge bg-danger">CANCELLED</span>`;
                            break;
                        case 3:
                            bookingStatusHtml = `<span class="badge bg-warning">LATE CANCEL</span>`;
                            break;
                        case 4:
                            bookingStatusHtml = `<span class="badge bg-dark">NO SHOW</span>`;
                            break;
                        default:
                            bookingStatusHtml = 'N/A';
                    }

                    var actionButtons = `
                    <div class="d-flex gap-1 justify-content-center">
                        <!-- View button -->
                        <button type="button" class="btn btn-primary viewFitnessHubBookingDetailsBtn btn-sm btn-equal"
                            data-bs-toggle="tooltip" data-bs-placement="left" title="View"
                            data-id="${booking.id}">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                `;


                    if (booking.booking_status == 2 || booking.booking_status == 3 || booking.booking_status == 4) {
                        actionButtons += `
                        <button type="button" class="btn btn-secondary cancelled-booking btn-sm btn-equal"
                            data-bs-toggle="tooltip" data-bs-placement="right" title="Cancelled"
                            data-id="${booking.id}" disabled>
                            <i class="fa-solid fa-ban"></i>
                        </button>
                    `;
                    } else {
                        actionButtons += `
                        <button type="button" class="btn btn-danger cancel-booking btn-sm btn-equal"
                            data-bs-toggle="tooltip" data-bs-placement="right" title="Cancel"
                            data-id="${booking.id}">
                            <i class="fa-solid fa-ban"></i>
                        </button>
                    `;
                    }
                    var bookingDateTime = new Date(booking.booking_date + " " + booking.booking_start_time);
                    var now = new Date();

                    var disableNoShow =
                        booking.booking_status == 2 ||
                        booking.booking_status == 3 ||
                        booking.booking_status == 4 ||
                        bookingDateTime > now;

                    if (disableNoShow) {
                        actionButtons += `
        <button type="button" class="btn btn-secondary marked-as-no-show btn-sm btn-equal"
            data-bs-toggle="tooltip" title="No show" disabled>
            <i class="fa-solid fa-user-slash"></i>
        </button>
    `;
                    } else {
                        actionButtons += `
        <button type="button" class="btn btn-warning mark-as-no-show-fitness-hub btn-sm btn-equal"
            data-bs-toggle="tooltip" data-bs-placement="right" title="Mark as no show"
            data-id="${booking.id}">
            <i class="fa-solid fa-user-slash"></i>
        </button>
    `;
                    }

                    if (booking.penalty_amount > 0 && !booking.penalty_waived) {
                        actionButtons += `
        <button type="button"
            class="btn btn-success manage-penalty-fitness-hub btn-sm btn-equal"
            data-action="waive"
            data-id="${booking.id}"
            data-bs-toggle="tooltip"
            title="Waive Penalty">
            <i class="fa-solid fa-hand-holding-dollar"></i>
        </button>
    `;

                    } else {

                        actionButtons += `
        <button type="button"
            class="btn btn-dark manage-penalty-fitness-hub btn-sm btn-equal"
            data-action="apply"
            data-id="${booking.id}"
            data-bs-toggle="tooltip"
            title="Apply Penalty">
            <i class="fa-solid fa-coins"></i>
        </button>
    `;
                    }
                    actionButtons += `</div>`;

                    var penaltyAmountHtml = booking.penalty_amount && booking.penalty_amount > 0
                        ? `<span class="text-danger fw-semibold">₱${parseFloat(booking.penalty_amount).toFixed(2)}</span>`
                        : `₱0.00`;

                    var penaltyWaivedHtml = booking.penalty_waived
                        ? `<span class="badge bg-primary">YES</span>`
                        : `<span class="badge bg-danger">NO</span>`;


                    var waivedByHtml = booking.waived_by
                        ? booking.waived_by.toUpperCase()
                        : 'N/A';


                    var cancelledByHtml = booking.cancelled_by
                        ? booking.cancelled_by.toUpperCase()
                        : 'N/A';

                    var cancelledAtHtml = booking.cancelled_at || 'N/A';

                    var createdByHtml = booking.created_by ? booking.created_by.toUpperCase() : 'N/A';

                    var penaltyAppliedByHtml = booking.penalty_applied_by
                        ? booking.penalty_applied_by.toUpperCase()
                        : 'N/A';
                    var row = $(`
                    <tr>
                        <td>${booking.transaction_no ? booking.transaction_no.toUpperCase() : 'N/A'}</td>
                        <td>${booking.fitness_hub?.fitness_hub_name ? booking.fitness_hub.fitness_hub_name.toUpperCase() : 'N/A'}</td>
                        <td>${booking.unit ? booking.unit.toUpperCase() : 'N/A'}</td>
                        <td>${residentTypeHtml}</td>
                        <td>${booking.name ? booking.name.toUpperCase() : 'N/A'}</td>
                        <td>${booking.contact_number || 'N/A'}</td>
                        <td>${booking.booking_type || 'N/A'}</td>
                        <td>${bookingStatusHtml}</td>
                        <td>${booking.booking_date || 'N/A'}</td>
                        <td>${booking.booking_start_time || 'N/A'}</td>
                        <td>${booking.booking_end_time || 'N/A'}</td>
                        <td>${cancelledByHtml}</td>
                        <td>${cancelledAtHtml}</td>
                         <td>${penaltyAmountHtml}</td>   
                        <td>${penaltyWaivedHtml}</td>
                        <td>${waivedByHtml}</td>
                        <td>${booking.penalty_waived_at || 'N/A'}</td>
                        <td>${penaltyAppliedByHtml}</td>
                        <td>${booking.penalty_applied_at || 'N/A'}</td>
                        <td>${createdByHtml}</td>
                        <td>${booking.created_at || 'N/A'}</td>
                        <td>${booking.updated_at || 'N/A'}</td>
                        <td class="sticky-col sticky-col-color">${actionButtons}</td>
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


    $('#fitnessHubBookingTable').on('click', '.mark-as-no-show-fitness-hub', function () {

        const bookingId = $(this).data('id');

        Swal.fire({
            title: "Mark as No Show?",
            html: "The resident did not attend. A <b>₱1000 penalty</b> will be applied.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, mark as no show",
            cancelButtonText: "Cancel"
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: `/admin/admin-mark-no-show-fitness-hub/${bookingId}`,
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (res) {
                    if (!res.success) {
                        Swal.fire('Error', res.message || 'Failed to mark no show.', 'error');
                        return;
                    }

                    Swal.fire('Updated!', res.message, 'success')
                        .then(() => refreshFitnessHubBookingsTable());
                },
                error: function () {
                    Swal.fire('Error', 'Something went wrong.', 'error');
                }
            });
        });
    });

    $('#fitnessHubBookingTable').on('click', '.manage-penalty-fitness-hub', function () {

        const bookingId = $(this).data('id');
        const action = $(this).data('action'); // apply | waive

        let config = {};

        if (action === 'apply') {
            config = {
                title: "Apply Penalty?",
                text: "This will manually apply a ₱1000 penalty to this booking.",
                icon: "warning",
                confirmButtonText: "Yes, apply",
                confirmButtonColor: "#d33"
            };
        } else {
            config = {
                title: "Waive Penalty?",
                text: "This will remove/waive the penalty for this booking.",
                icon: "question",
                confirmButtonText: "Yes, waive",
                confirmButtonColor: "#28a745"
            };
        }

        Swal.fire({
            ...config,
            showCancelButton: true,
            cancelButtonText: "Cancel"
        }).then((result) => {

            if (!result.isConfirmed) return;

            $.ajax({
                url: `/admin/admin-manage-penalty-fitness-hub/${bookingId}`,
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    action: action
                },
                success: function (res) {

                    if (!res.success) {
                        Swal.fire('Error', res.message || 'Failed.', 'error');
                        return;
                    }

                    Swal.fire('Success', res.message, 'success')
                        .then(() => refreshFitnessHubBookingsTable());
                },
                error: function () {
                    Swal.fire('Error', 'Something went wrong.', 'error');
                }
            });

        });
    });

    $('.SlotCheckingFitnessHubBtn').on('click', function () {
        $('#fitnessHubSlotCheckingModal').modal('show');
        $('#SearchSlotAdminFitnessHub')[0].reset();

        $('#fitnessHubDateFieldSearchAdmin').prop('disabled', true);
        $('.searchBtn').prop('disabled', true);

        $('.all-slot-available-admin').empty();
        $('#spinner').addClass('d-none');
    });

    $('#fitnessHubSlotCheckingModal').on('hidden.bs.modal', function () {
        $('.modal-backdrop').remove();

        $('#fitnessHubDateFieldSearchAdmin').prop('disabled', true);
        $('.searchBtn').prop('disabled', true);

        $('#SearchSlotAdminFitnessHub')[0].reset();
        $('.all-slot-available-admin').empty();
        $('#spinner').addClass('d-none');
    });


    $('#SearchSlotAdminFitnessHub').submit(function (event) {
        event.preventDefault();

        let fitnessHubId = $('#fitnessHubSelectBookingSearchAdmin').val();
        let dateField = $('#fitnessHubDateFieldSearchAdmin').val();

        if (!fitnessHubId || !dateField) {
            alert('Please select fitness hub and date.');
            return;
        }

        const $btn = $('.slot-checking-submit-btn-admin');
        const originalWidth = $btn.outerWidth();

        $btn
            .attr('disabled', true)
            .html(`<div class="spinner-border spinner-border-sm text-light"></div>`)
            .css('width', originalWidth + 'px');

        $.ajax({
            url: '/admin/fetch-all-slots-admin-fitness-hub',
            method: 'get',
            data: {
                fitness_hub_id: fitnessHubId,
                booking_date: dateField
            },

            success: function (response) {
                $('#spinner').addClass('d-none');

                if (response.error) {
                    $('.all-slot-available-admin').html(`
                    <div class="alert alert-warning text-center mb-0">
                        ${response.error}
                    </div>
                `);
                    return;
                }

                if (!response.slots || response.slots.length === 0) {
                    $('.all-slot-available-admin').html(`
                    <div class="alert alert-info text-center mb-0">
                        No available time slots for this day.
                    </div>
                `);
                    return;
                }

                let html = `
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

                response.slots.forEach(slot => {
                    let status = slot.status;
                    let label = slot.label;
                    let badgeClass = 'bg-primary';

                    if (status === 'Booked') {
                        badgeClass = 'bg-danger';
                    } else if (status === 'Blocked') {
                        badgeClass = 'bg-secondary';
                    } else {
                        badgeClass = 'bg-primary';
                    }

                    let displayText = label;

                    html += `
<tr>
    <td>${slot.time_range}</td>
    <td><span class="badge ${badgeClass} text-uppercase">${displayText}</span></td>
</tr>
`;
                });

                html += '</tbody></table>';
                $('.all-slot-available-admin').html(html);
            },

            error: function () {
                $('#spinner').addClass('d-none');
                alert('An error occurred while fetching the data.');
            },

            complete: function () {
                $btn
                    .attr('disabled', false)
                    .html(`<i class="fa-solid fa-search me-1"></i><span> Search</span>`)
                    .css('width', '');
            }
        });
    });

    $('#fitnessHubSelectBookingSearchAdmin').on('change', function () {
        $('#fitnessHubDateFieldSearchAdmin').prop('disabled', false);
        $('.searchBtn').prop('disabled', false);

        const fitnessHubDateFieldSearchAdmin = $('#fitnessHubDateFieldSearchAdmin');

        // ✅ FIXED: correct way to get data attribute
        const selectedFitnessHubId = $(this).find(':selected').data('fitnessHubId');

        // Optional: store in hidden input
        $('#fitnessHubIdBooking').val(selectedFitnessHubId);

        $.ajax({
            url: '/admin/fetch-blocked-dates-fitness-hub',
            method: 'GET',
            data: { fitness_hub_id: selectedFitnessHubId },

            success: function (blockedDates) {
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                let currentMonday = new Date(today);
                currentMonday.setDate(today.getDate() - today.getDay() + 1);

                let maxBookingDate = new Date(currentMonday);
                maxBookingDate.setDate(currentMonday.getDate() + 13);

                if (today.getDay() >= 5) {
                    maxBookingDate.setDate(maxBookingDate.getDate() + 7);
                }

                // ✅ FIX: destroy previous flatpickr
                if (fitnessHubDateFieldSearchAdmin[0]._flatpickr) {
                    fitnessHubDateFieldSearchAdmin[0]._flatpickr.destroy();
                }

                flatpickr(fitnessHubDateFieldSearchAdmin[0], {
                    enableTime: false,
                    dateFormat: "Y-m-d",
                    minDate: today,
                    maxDate: maxBookingDate,
                    allowInput: true,
                    altInput: true,
                    altFormat: "F j, Y",
                    disable: blockedDates,

                    onChange: function (selectedDates, dateStr) {
                        $('.all-slot-available-admin').empty();

                        if (dateStr) {
                            $('.searchBtn').prop('disabled', false);
                        } else {
                            $('.searchBtn').prop('disabled', true);
                        }
                    }
                });
            },

            error: function (xhr) {
                console.error('Failed to fetch blocked dates:', xhr.responseText);
            }
        });
    });


    $('#fitnessHubBookingTable').on('click', '.viewFitnessHubBookingDetailsBtn', function () {
        var booking_id = $(this).data("id");
        showLoading();
        $.ajax({
            url: '/admin/fetch/fitness-hub-booking/' + booking_id,
            method: 'GET',
            success: function (response) {
                const booking = response.booking;
                const withinPenalty = $('#detail-transaction-no').data('within-penalty');

                const bookingDate = new Date(booking.booking_date);
                const today = new Date();
                today.setHours(0, 0, 0, 0); // Reset time for accurate comparison
                const optionsDate = { year: 'numeric', month: 'long', day: '2-digit' };
                const formattedDate = bookingDate.toLocaleDateString(undefined, optionsDate);

                // Format start/end times
                const formatTime = (timeStr) => {
                    if (!timeStr) return 'N/A';
                    const d = new Date(`1970-01-01T${timeStr}`);
                    let hours = d.getHours();
                    const minutes = d.getMinutes();
                    const ampm = hours >= 12 ? 'PM' : 'AM';
                    hours = hours % 12 || 12;
                    return `${hours}:${minutes.toString().padStart(2, '0')} ${ampm}`;
                };

                const startTime = booking.booking_start_time;
                const endTime = booking.booking_end_time;

                $('#detail-transaction-no').text(booking.transaction_no)
                    .data('booking-id', booking.id)
                    .data('within-penalty', withinPenalty);
                $('#detail-booking-type').text(booking.booking_type ?? 'N/A');
                $('#detail-name').text(booking.name ?? booking.created_by_name ?? 'N/A');
                $('#detail-unit').text(booking.unit ?? 'N/A');
                $('#detail-fitness-hub-name').text(booking.fitness_hub?.name ?? 'N/A');
                $('#detail-start-time').text(`${startTime} - ${endTime}` ?? 'N/A');
                $('#detail-contact').text(booking.contact ?? 'N/A');
                $('#detail-booking-date').text(formattedDate ?? 'N/A');
                $('#detail-transaction-no').data('booking-id', booking.id);


                let residentBadgeClass = '';
                if (booking.resident_type === 'TENANT') residentBadgeClass = 'fw-semibold text-danger';
                else if (booking.resident_type === 'OWNER') residentBadgeClass = 'fw-semibold text-primary';
                else residentBadgeClass = 'text-secondary text-white';

                $('#detail-resident-type').html(
                    `<span class="fw-semibold ${residentBadgeClass}">${booking.resident_type ?? 'N/A'}</span>`
                );

                let statusText = '';
                let statusClass = '';
                let cancelledAtText = ''; // 👈 NEW

                const formatDateTime = (dateStr) => {
                    if (!dateStr) return '';
                    const d = new Date(dateStr);

                    const options = {
                        year: 'numeric',
                        month: 'short',
                        day: '2-digit',
                        hour: 'numeric',
                        minute: '2-digit'
                    };

                    return d.toLocaleString(undefined, options);
                };

                // PRIORITY: real booking status first
                switch (booking.booking_status) {
                    case 1:
                        if (bookingDate < today) {
                            statusText = 'Completed';
                            statusClass = 'text-primary';
                            $('#cancelAmenityBookingBtn').hide();
                        } else {
                            statusText = 'Confirmed';
                            statusClass = 'text-primary';
                            $('#cancelAmenityBookingBtn').show();
                        }
                        break;

                    case 2:
                        statusText = 'Cancelled';
                        statusClass = 'text-danger';
                        if (booking.cancelled_at) {
                            cancelledAtText = `<small class="text-muted"> at ${formatDateTime(booking.cancelled_at)}</small>`;
                        }
                        $('#cancelAmenityBookingBtn').hide();
                        break;

                    case 3:
                        statusText = 'Late cancel';
                        statusClass = 'text-warning';

                        if (booking.cancelled_at) {
                            cancelledAtText = `<small class="text-muted"> at ${formatDateTime(booking.cancelled_at)}</small>`;
                        }
                        $('#cancelAmenityBookingBtn').hide();
                        break;

                    case 4:
                        statusText = 'No Show';
                        statusClass = 'text-dark';
                        $('#cancelAmenityBookingBtn').hide();
                        break;

                    default:
                        // ONLY mark as completed if no specific status
                        if (bookingDate < today) {
                            statusText = 'Completed';
                            statusClass = 'text-primary';
                        } else {
                            statusText = 'N/A';
                            statusClass = 'text-secondary';
                        }
                        $('#cancelAmenityBookingBtn').hide();
                }

                // Reset
                $('#detail-penalty-display').removeClass('text-danger text-primary fw-semibold');

                // No penalty
                if (!booking.has_penalty || booking.penalty_amount == 0) {
                    $('#detail-penalty-display').text('-');

                } else {
                    const amount = parseFloat(booking.penalty_amount).toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });

                    if (booking.penalty_waived) {
                        // Waived
                        $('#detail-penalty-display')
                            .html(`₱${amount} <span class="text-primary">(Waived)</span>`)
                            .addClass('fw-semibold');

                    } else {
                        // Not waived
                        $('#detail-penalty-display')
                            .text(`₱${amount}`)
                            .addClass('text-danger fw-semibold');
                    }
                }
                $('#detail-booking-status').html(`
                    <span class="${statusClass}">${statusText}</span>
                    ${cancelledAtText}
                `);

                $('#viewFitnessHubBookingModal').modal('show');
            },
            error: function () {
                alert('Booking not found.');
            },
            complete: function () {
                hideLoading();
            }
        });
    });
});