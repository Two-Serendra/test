$(document).ready(function () {

    flatpickr("#dateFieldFitnessHubBooking", {
        dateFormat: "Y-m-d",

        onChange: function (selectedDates, dateStr, instance) {
            let modal = $(instance.input).closest("#fitnessHubBookingModalUser");

            console.log("Flatpickr triggered:", dateStr);

            checkUnitAvailabilityFitnessHub(modal);
        }
    });

    $('.AddNewBookingFitnessHub').on('click', function () {
        showLoading();

        const fitnessHubId = $(this).data('fitness-hub-id');
        const modal = $('#fitnessHubBookingModalUser');
        modal.find('#fitnessHubId').val(fitnessHubId);

        console.log("Clicked hub ID:", fitnessHubId);
        const bookingTabs = modal.find('#bookingTabs');
        const bookingTypeInput = modal.find('#bookingType');
        const dateField = modal.find('#dateFieldFitnessHubBooking');
        const startTimeDropdown = modal.find('#booking_start_time_FH');
        const endTimeDropdown = modal.find('#booking_end_time_FH');
        const submitButton = modal.find('button[type="submit"]');
        const residentSelect = modal.find('#residentSelect');
        const selectResidentType = modal.find('#selectResidentType');
        const nameField = modal.find('#name');
        const contactField = modal.find('#contact_number');
        const checkUnit = modal.find('.checkUnit');
        const unitStatus = modal.find('#unitStatusFitnessHub');

        unitStatusInfo = modal.find('.unitStatusInfo');
        function resetFields() {
            modal.find('input[type="text"], input[type="number"]').val('');
            modal.find('select').prop('selectedIndex', 0);

            dateField.val('');
            startTimeDropdown.prop('disabled', true).empty().append('<option>Select a Date First</option>');
            resetEndTimeDropdown(modal);
            unitStatus.text('0/0').attr('class', 'mt-1 text-muted').hide();
            submitButton.prop('disabled', true);
            selectResidentType.prop('disabled', true);
            nameField.prop('disabled', true);
            checkUnit.prop('disabled', false).show();
            residentSelect.prop('disabled', false);
            modal.find('form').removeClass('was-validated');
            modal.find('input, select').removeClass('is-valid is-invalid');
        }

        resetFields();

        contactField.off('input').on('input', function () {
            let v = $(this).val().replace(/\D/g, '');
            if (v.length > 11) v = v.substring(0, 11);
            $(this).val(v);
        });

        bookingTabs.find('a').removeClass('active');
        bookingTabs.find('a[data-value="Advanced Booking"]').addClass('active');
        bookingTabs.find('a[data-value="Advanced Booking"]').tab('show');
        const initialType = 'Advanced Booking';
        bookingTypeInput.val(initialType);

        $.ajax({
            url: '/fetch-date-blocking-fitness-hub-user',
            method: 'GET',
            data: { fitness_hub_id: fitnessHubId },
            success: function (blockedDates) {
                modal.data('blockedDates', blockedDates);
                applyFieldToggles(initialType);
                setDatePicker(initialType, blockedDates);
            },
            error: function (xhr) {
                console.error('Failed to fetch blocked dates:', xhr.responseText);
            },
            complete: function () {
                hideLoading();
                modal.modal('show');
            }
        });


        bookingTabs.find('a[data-bs-toggle="tab"]').off('shown.bs.tab').on('shown.bs.tab', function (e) {
            const type = $(e.target).data('value');
            bookingTypeInput.val(type);
            applyFieldToggles(type);

            const blocked = modal.data('blockedDates');
            if (blocked) setDatePicker(type, blocked);
        });

        function applyFieldToggles(type) {
            if (type === 'Walk-in') {
                checkUnit.hide();
                unitStatus.hide();
                dateField.prop('disabled', false);
                startTimeDropdown.prop('disabled', true);
                endTimeDropdown.prop('disabled', true);
                submitButton.prop('disabled', false);
                selectResidentType.prop('disabled', false);
                nameField.prop('disabled', false);
                contactField.prop('disabled', false);
            }
            else if (type === 'Advanced Booking') {
                checkUnit.show().prop('disabled', false);
                unitStatus.show().text('0/0').attr('class', 'mt-1 text-muted');
                residentSelect.prop('disabled', false);
                dateField.prop('disabled', false);
                submitButton.prop('disabled', true);
            }
            else if (type === '20hrs') {
                bookingTypeInput.val('20hrs');
                checkUnit.hide();
                unitStatus.hide();
                unitStatusInfo.hide();
                residentSelect.prop('disabled', false);
                selectResidentType.prop('disabled', false);
                nameField.prop('disabled', false);
                contactField.prop('disabled', false);
                dateField.prop('disabled', false);
                submitButton.prop('disabled', false);
            }

            startTimeDropdown.prop('disabled', true).empty().append('<option>Select a Date First</option>');
            endTimeDropdown.prop('disabled', true).empty().append('<option>Select Start Time First</option>');
            dateField.val('');
        }

        function setDatePicker(type, blockedDates) {
            const df = dateField[0];
            if (df._flatpickr) df._flatpickr.destroy();

            const today = new Date();
            today.setHours(0, 0, 0, 0);

            let minDate = today;
            let maxDate = new Date(today);

            if (type === '20hrs') {
                maxDate.setDate(today.getDate() + 1);
            } else if (type === 'Advanced Booking') {
                maxDate.setDate(today.getDate() + 9);
            }

            flatpickr(df, {
                enableTime: false,
                dateFormat: 'Y-m-d',
                minDate,
                maxDate,
                altInput: true,
                altFormat: 'F j, Y',
                disable: blockedDates,
                disableMobile: true,
                onChange: function (selectedDates, dateStr) {
                    if (!dateStr) return;

                    startTimeDropdown
                        .prop('disabled', true)
                        .empty()
                        .append('<option>Loading...</option>');

                    resetEndTimeDropdown(modal);

                    unitStatus
                        .text('0/0')
                        .removeClass('text-success text-danger text-primary')
                        .addClass('text-muted');

                    fetchAvailableStartTimes(modal, dateStr, fitnessHubId);
                }
            });
        }
    });


    $(document).on("change", "#residentSelectFitnessHub", function () {
        let modal = $(this).closest("#fitnessHubBookingModalUser");
        const unit = $(this).find(':selected').data('unit');

        modal.find('#unitInput').val(unit); // ✅ FIXED
        checkUnitAvailabilityFitnessHub(modal);
    });

    $(document).on("change", "#dateFieldFitnessHubBooking", function () {
        let modal = $(this).closest("#fitnessHubBookingModalUser");
        checkUnitAvailabilityFitnessHub(modal);
    });



    function checkUnitAvailabilityFitnessHub(modal) {
        let fitnessHubId = modal.find("#fitnessHubId").val();
        let selectResidentType = modal.find("#residentSelectFitnessHub");
        let unitNumber = selectResidentType.find("option:selected").data('unit')?.toString().trim();
        let submitButton = modal.find("#submitButton");
        let checkUnit = modal.find(".checkUnit");
        let selectedDate = modal.find("#dateFieldFitnessHubBooking").val()?.trim();
        let contact_number = modal.find("#contact_number");
        let radio = modal.find(":radio");
        let bookingType = modal.find("#bookingType").val();
        console.log({ unitNumber, selectedDate, bookingType });
        if (!unitNumber || !selectedDate) return;

        console.log({
            unit: unitNumber,
            fitness_hub_id: fitnessHubId,
            date: selectedDate
        });

        $.ajax({
            url: '/check-unit-booking-fitness-hub',
            type: "GET",
            data: {
                unit: unitNumber,
                fitness_hub_id: fitnessHubId,
                dateField: selectedDate
            },
            success: function (response) {

                let unitStatus = modal.find("#unitStatusFitnessHub");

                if (response.success) {

                    let count = response.count;
                    // console.log("API Response:", response);
                    let maxBookings = response.maxBookings;
                    let statusText = `${count}/${maxBookings}`;

                    unitStatus.removeClass("text-muted text-danger text-success text-primary");

                    if (bookingType === "Advanced Booking") {
                        if (count < maxBookings) {
                            unitStatus.addClass("text-success").text(statusText);
                            enableFields();
                        } else {
                            unitStatus.addClass("text-danger").text(statusText + " (Max)");
                            disableFields();
                            Swal.fire({
                                icon: 'warning',
                                title: 'Limit Reached',
                                text: 'This unit has already reached the maximum hours of booking for this week.'
                            });
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

        function enableFields() {
            submitButton.prop('disabled', false);
            checkUnit.prop('disabled', false);
            selectResidentType.prop('disabled', false);
            contact_number.prop('disabled', false);
            radio.prop('disabled', false);
        }

        function disableFields() {
            submitButton.prop('disabled', true);
            radio.prop('disabled', true);
        }
    }

    function resetEndTimeDropdown(modal) {
        const endTimeDropdown = modal.find('#booking_end_time_FH');

        endTimeDropdown
            .prop('disabled', true)
            .html('<option value="">Select start time</option>');
    }

    function fetchAvailableStartTimes(modal, bookingDate, selectedFitnessHubId) {
        const fitnessHubId = selectedFitnessHubId;
        const startTimeDropdown = modal.find('#booking_start_time_FH');
        const endTimeDropdown = modal.find('#booking_end_time_FH');
        startTimeDropdown.prop('disabled', true).empty().append('<option>Loading...</option>');
        endTimeDropdown.prop('disabled', true).empty().append('<option>Select a start time first</option>');

        if (!bookingDate || !fitnessHubId) return;

        $.ajax({
            url: '/fetch-available-times-fitness-hub',
            method: 'GET',
            data: { fitness_hub_id: fitnessHubId, booking_date: bookingDate, booking_type: modal.find('#bookingType').val() },
            success: function (availableTimePairs) {
                console.log("Available Times Response:", availableTimePairs);
                startTimeDropdown.empty();
                resetEndTimeDropdown(modal);

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
        const modal = $('#fitnessHubBookingModalUser');
        const selectedStartTime = $(this).val();
        const fitnessHubId = modal.find('#fitnessHubId').val();
        const bookingDate = modal.find('#dateFieldFitnessHubBooking').val();
        const endTimeDropdown = modal.find('#booking_end_time_FH');

        if (!selectedStartTime || !fitnessHubId || !bookingDate) return;

        endTimeDropdown.prop('disabled', true).empty().append('<option>Loading...</option>');

        $.ajax({
            url: '/fetch-available-end-times-fitness-hub',
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


    $('.userFitnessHubBookingForm').submit(function (event) {
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
                text: 'Please enter valid start and end times.'
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
            confirmText = 'This booking is made within 12 hours and can no longer be cancelled for free. Proceed?';
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
                submitBooking(form, $form, modal);
            }
        });

        return;
    });

    function submitBooking(form, $form, modal) {
        const $btn = modal.find('.fitness-hub-submit-btn');
        const originalWidth = $btn.outerWidth();

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


    $(document).on('click', '.fitness-hub-booking-details', function () {
        let b = $(this).data('id');
        showLoading();

        $.ajax({
            url: '/resident/fitness-hub-booking/details/' + b,
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

                const startTime = formatTime(booking.booking_start_time);
                const endTime = formatTime(booking.booking_end_time);

                $('#detail-transaction-no').text(booking.transaction_no)
                    .data('booking-id', booking.id)
                    .data('within-penalty', withinPenalty);
                $('#detail-booking-type').text(booking.booking_type ?? 'N/A');
                $('#detail-name').text(booking.name ?? booking.created_by_name ?? 'N/A');
                $('#detail-unit').text(booking.unit ?? 'N/A');
                $('#detail-fitness-hub-name').text(booking.fitness_hub?.fitness_hub_name ?? 'N/A');
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

                        const now = new Date();

                        // Build full datetime
                        const startDateTime = new Date(`${booking.booking_date}T${booking.booking_start_time}`);
                        const endDateTime = new Date(`${booking.booking_date}T${booking.booking_end_time}`);

                    case 1:
                        if (now >= endDateTime) {
                            statusText = 'Completed';
                            statusClass = 'text-success';
                            $('#cancelFitnessHubBookingBtn').hide();
                        }
                        else if (now >= startDateTime) {
                            statusText = 'Ongoing';
                            statusClass = 'text-warning';
                            $('#cancelFitnessHubBookingBtn').hide(); // 🔥 KEY FIX
                        }
                        else {
                            statusText = 'Confirmed';
                            statusClass = 'text-success';
                            $('#cancelFitnessHubBookingBtn').show();
                        }
                        break;

                    case 2:
                        statusText = 'Cancelled';
                        statusClass = 'text-danger';
                        if (booking.cancelled_at) {
                            cancelledAtText = `<small class="text-muted"> at ${formatDateTime(booking.cancelled_at)}</small>`;
                        }
                        $('#cancelFitnessHubBookingBtn').hide();
                        break;

                    case 3:
                        statusText = 'Late cancel';
                        statusClass = 'text-warning';

                        if (booking.cancelled_at) {
                            cancelledAtText = `<small class="text-muted"> at ${formatDateTime(booking.cancelled_at)}</small>`;
                        }
                        $('#cancelFitnessHubBookingBtn').hide();
                        break;

                    case 4:
                        statusText = 'No Show';
                        statusClass = 'text-dark';
                        $('#cancelFitnessHubBookingBtn').hide();
                        break;

                    default:
                        // ONLY mark as completed if no specific status
                        if (bookingDate < today) {
                            statusText = 'Completed';
                            statusClass = 'text-success';
                        } else {
                            statusText = 'N/A';
                            statusClass = 'text-secondary';
                        }
                        $('#cancelFitnessHubBookingBtn').hide();
                }

                // Reset
                $('#detail-penalty-display').removeClass('text-danger text-success fw-semibold');

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
                            .html(`₱${amount} <span class="text-success">(Waived)</span>`)
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

                $('#residentFitnessHubBookingDetailsModal').modal('show');
            },
            error: function () {
                alert('Booking not found.');
            },
            complete: function () {
                hideLoading();
            }
        });
    });



    $(document).on('click', '#cancelFitnessHubBookingBtn', function () {
        const bookingEl = $('#detail-transaction-no');
        const bookingId = bookingEl.data('booking-id');
        Swal.fire({
            title: 'Cancel Booking',
            html: 'Are you sure you want to cancel this booking?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, cancel it',
            cancelButtonText: 'No, keep it'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/resident/fitness-hub-booking/cancel/${bookingId}`,
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
                                title: 'Cancel Booking with Penalty',
                                html: 'Cancelling this booking will incur a ₱' + res.penaltyAmount + ' penalty. Proceed?',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#d33',
                                cancelButtonColor: '#3085d6',
                                confirmButtonText: 'Yes, cancel it',
                                cancelButtonText: 'No, keep it'
                            }).then((result2) => {
                                if (result2.isConfirmed) {
                                    $.ajax({
                                        url: `/resident/fitness-hub-booking/cancel/${bookingId}`,
                                        method: 'POST',
                                        data: {
                                            _token: $('meta[name="csrf-token"]').attr('content'),
                                            confirm: 1
                                        },
                                        success: function (res2) {
                                            Swal.fire('Cancelled!', res2.message, 'success').then(() => {
                                                $('#residentFitnessHubBookingDetailsModal').modal('hide');

                                                let page = $('.pagination .active span').text() || 1;
                                                loadBookings(page);
                                            });
                                        },
                                        error: function () {
                                            Swal.fire('Error', 'Something went wrong while cancelling.', 'error');
                                        }
                                    });
                                }
                            });
                        } else {
                            Swal.fire('Cancelled!', res.message, 'success').then(() => {
                                $('#residentFitnessHubBookingDetailsModal').modal('hide');

                                let page = $('.pagination .active span').text() || 1;
                                loadBookings(page);
                            });
                        }
                    },
                    error: function (xhr) {

                        if (xhr.status === 403) {
                            Swal.fire('Not Allowed', xhr.responseJSON?.message || 'You are not allowed to cancel this booking.', 'warning');
                            return;
                        }

                        if (xhr.status === 404) {
                            Swal.fire('Not Found', xhr.responseJSON?.message || 'Booking not found.', 'error');
                            return;
                        }

                        if (xhr.status === 429) {
                            Swal.fire('Too Many Attempts', 'Please wait before trying again.', 'warning');
                            return;
                        }

                        Swal.fire('Error', 'Something went wrong.', 'error');
                    }
                });
            }
        });
    });

    $('.SlotCheckingModalUserbBtnFitnessHub').on('click', function () {
        $('#FitnessHubSlotCheckingModalUser').modal('show');
        $('#SearchSlotUserFitnessHub')[0].reset();
        $('#fitnessHubDateFieldSearchUser').prop('disabled', true);
        $('.slot-checking-submit-btn-fitness-hub').prop('disabled', true);
        $('.all-slot-available-user-fitness-hub').empty();
        $('#spinner').addClass('d-none');
    });

    $('#FitnessHubSlotCheckingModalUser').on('hidden.bs.modal', function () {
        $('.modal-backdrop').remove();
        $('#fitnessHubDateFieldSearchUser').prop('disabled', true);
        $('.slot-checking-submit-btn-fitness-hub').prop('disabled', true);
        $('#SearchSlotUserFitnessHub')[0].reset();
        $('.all-slot-available-user-fitness-hub').empty();
        $('#spinner').addClass('d-none');
    });

    $('#fitnessHubSelectBookingSearchUser').on('change', function () {

        const fitnessHubSelect = $(this);
        const selectedFitnessHubId = fitnessHubSelect.val();
        const dateField = $('#fitnessHubDateFieldSearchUser');

        $('.all-slot-available-user-fitness-hub').empty();
        $('#unitStatus').text('0/0').attr('class', 'mt-1 text-muted');
        dateField.val('').prop('disabled', false);
        $('.slot-checking-submit-btn-fitness-hub').prop('disabled', true);

        if (!selectedFitnessHubId) return;

        $.ajax({
            url: '/fetch-date-blocking-fitness-hub-user',
            method: 'GET',
            data: {
                fitness_hub_id: selectedFitnessHubId
            },
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

                if (dateField[0]._flatpickr) {
                    dateField[0]._flatpickr.destroy();
                }

                flatpickr(dateField[0], {
                    enableTime: false,
                    dateFormat: "Y-m-d",
                    minDate: today,
                    maxDate: maxBookingDate,
                    allowInput: true,
                    altInput: true,
                    altFormat: "F j, Y",
                    disable: blockedDates,
                    disableMobile: true,

                    onChange: function (selectedDates, dateStr) {

                        $('.slot-checking-submit-btn-fitness-hub')
                            .prop('disabled', !dateStr);

                        $('.all-slot-available-user-fitness-hub').empty();
                    }
                });
            },
            error: function (xhr) {
                console.error('Failed to fetch blocked dates:', xhr.responseText);
            }
        });
    });


    $('.SearchSlotUserFitnessHub').submit(function (event) {
        event.preventDefault();

        let fitnessHubId = $('#fitnessHubSelectBookingSearchUser').val();
        let dateField = $('#fitnessHubDateFieldSearchUser').val();
        const $btn = $('.slot-checking-submit-btn-fitness-hub');
        const originalWidth = $btn.outerWidth();
        $btn
            .attr('disabled', true)
            .html(`<div class="spinner-border spinner-border-sm text-light"></div>`)
            .css('width', originalWidth + 'px');


        $.ajax({
            url: '/fetch-all-slots-user-fitness-hub',
            method: 'get',
            data: {
                fitness_hub_id: fitnessHubId,
                booking_date: dateField
            },
            success: function (response) {
                $('#spinner').addClass('d-none');

                if (response.error) {
                    $('.all-slot-available-user-fitness-hub').html(`
                    <div class="alert alert-warning text-center mb-0">
                        ${response.error}
                    </div>
                `);
                    return;
                }

                if (!response.slots || response.slots.length === 0) {
                    $('.all-slot-available-user-fitness-hub').html(`
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
                    let badgeClass = 'custom-badge bg-primary';

                    if (status === 'Booked') {
                        badgeClass = 'custom-badge bg-danger';
                    } else if (status === 'Blocked') {
                        badgeClass = 'custom-badge bg-secondary';
                    } else {
                        badgeClass = 'custom-badge bg-primary';
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
                $('.all-slot-available-user-fitness-hub').html(html);
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
});