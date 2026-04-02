$(document).ready(function () {


    window.showLoading = function () {
        $('#loadingOverlay').css('display', 'flex').hide().fadeIn(150);
    }

    window.hideLoading = function () {
        $('#loadingOverlay').fadeOut(150);
    }
    $(document).ready(function () {

        $('#uploadBookingBtn').on('click', function () {
            console.log("Upload button clicked");
            $('#bookingFileInput').click();
        });

        $('#bookingFileInput').on('change', function () {

            console.log("File input changed");

            if (this.files.length === 0) {
                console.log("No file selected");
                return;
            }

            let fileName = this.files[0].name;
            console.log("Selected file:", fileName);

            if (confirm("Upload file: " + fileName + " ?")) {
                console.log("Submitting form...");
                $('#bookingImportForm').submit();
            } else {
                console.log("Upload cancelled");
                $(this).val('');
            }

        });

    });




    $(document).on('click', '.AddBookingAdmin', function () {
        resetBookingForm();
        $('#AddBookingAdmin').modal('show');
    });


    const adminModal = $('#AddBookingAdmin');
    const bookingTabs = adminModal.find('#bookingTabs');
    const bookingTypeInput = adminModal.find('#bookingType');
    const unitNumber = adminModal.find('#unitNumber');
    const checkUnitBtn = adminModal.find('.checkUnit');
    const unitStatus = adminModal.find('#unitStatus');
    const residentType = adminModal.find('#selectResidentType');
    const nameField = adminModal.find('#name');
    const contactField = adminModal.find('#contact_number');
    const dateField = adminModal.find('#dateFieldBooking');
    const startTimeDropdown = adminModal.find('#booking_start_time');
    const endTimeDropdown = adminModal.find('#booking_end_time');
    const submitButton = adminModal.find('#saveActivityBookingBtn');

    let bookingType = bookingTabs.find('a.active').data('value') || "Advanced Booking";
    bookingTypeInput.val(bookingType);
    updateFormState(bookingType);

    bookingTabs.find('a').on('click', function () {
        bookingType = $(this).data('value');
        bookingTypeInput.val(bookingType);
        updateFormState(bookingType);
    });

    // dateField.on('change', function () {
    //     if (bookingType === "Walk-in") {
    //         toggleFields(false);
    //         enableAllFields();
    //     }
    // });

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

    $('.AdminNewBooking').submit(function (event) {
        event.preventDefault();
        const form = this;
        const startTime = $(form).find('[name="booking_start_time"]').val();
        const endTime = $(form).find('[name="booking_end_time"]').val();
        const submitButton = $('#saveActivityBookingBtn');
        const spinner = $('#spinner');

        let selectedCount = $('.selected-slot').length;
        $('#selectedSlotsInput').val(selectedCount);

        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        const startMoment = moment(startTime, 'h:mm A');
        let endMoment = moment(endTime, 'h:mm A');

        if (endMoment.isSameOrBefore(startMoment)) {
            endMoment.add(1, 'day');
        }

        if (endMoment.diff(startMoment) <= 0) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Time Selection',
                text: 'End time must be greater than start time.',
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }

        const $btn = $('#saveActivityBookingBtn');
        const originalWidth = $btn.outerWidth();
        $btn
            .attr('disabled', true)
            .html(`<div class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></div>`)
            .css('width', originalWidth + 'px');
        form.classList.remove('was-validated');
        const formData = new FormData(form);

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
                $(form).closest('.modal').modal('hide');
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
                    title: 'Booked Successfully'
                });
                // form.reset();
                $(form).removeClass('was-validated');
                resetBookingForm();
                refreshTableBookings();
                submitButton.prop('disabled', false);
                spinner.addClass('d-none');
            },

            error: function (xhr) {
                spinner.addClass('d-none');
                submitButton.prop('disabled', false);

                let message = 'Something went wrong. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message,
                    confirmButtonColor: '#d33',
                });
            },

            complete: function () {
                $btn
                    .attr('disabled', false)
                    .html(`<span class="btn-text">Submit</span>`)
                    .css('width', '');
            }
        });
    });



    function resetBookingForm() {
        const form = $('#AdminNewBooking')[0];
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
        $('#availableSlotsContainer').empty();
        $('#unitStatus').text('0/0');
        $('#saveActivityBookingBtn').prop('disabled', true);
        $('#amenityIdBooking').val('');
        $('#activitySelectBooking').val('').change();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').hide();
        updateFormState(bookingType);
    }

    function hideSpinner(button) {
        var $btn = $(button);
        var $spinner = $btn.find('.spinner');
        $spinner.addClass("d-none");
    }

    $("#contact_number").on("input", function () {
        let value = $(this).val().replace(/\D/g, "");
        if (value.length > 11) value = value.substring(0, 11);
        $(this).val(value);
    });




    $(document).ready(function () {
        const $btn = $("#saveActivityBookingBtn");

        let bookingAllowed = false;
        let timeAllowed = false;

        function checkTimeAvailability() {
            if (userRole !== 6) {
                timeAllowed = true;
                updateButtonState();
                return;
            }

            const now = new Date();
            const day = now.getDay();
            const hours = now.getHours();
            const minutes = now.getMinutes();

            const isFriday = day === 5;
            const isAfter10 = hours > 10 || (hours === 10 && minutes >= 0);

            // ❌ Only block Friday before 10 AM
            const isRestrictedTime = isFriday && !isAfter10;

            timeAllowed = !isRestrictedTime;

            if (!timeAllowed) {
                $btn.attr("title", "Available every Friday at 10:00 AM");
            } else {
                $btn.attr("title", "");
            }

            updateButtonState();
        }

        function updateButtonState() {
            if (bookingAllowed && timeAllowed) {
                $btn.prop("disabled", false);
            } else {
                $btn.prop("disabled", true);
            }
        }

        checkTimeAvailability();
        setInterval(checkTimeAvailability, 60000);

        $('#submitWrapper').tooltip();

        $(".checkUnit").on("click", function () {
            let unit = unitNumber.val().trim();
            let selectedDate = dateField.val()?.trim();

            if (!unit || !selectedDate) {
                Swal.fire({
                    icon: "warning",
                    title: "Missing Information",
                    text: "Please enter a unit number and select a date.",
                });
                return;
            }

            $.ajax({
                url: '/admin/check-unit-booking',
                type: "GET",
                data: {
                    unit: unit,
                    activity_id: adminModal.find('#activitySelectBooking').val(),
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

                                bookingAllowed = true;   // ✅ allow booking
                                enableAllFields();
                            } else {
                                unitStatus.addClass("text-danger").text(statusText + " (Max)");

                                bookingAllowed = false;  // ❌ block booking
                                disableSubmitOnly();
                            }
                        }

                        // 🔥 Apply BOTH conditions
                        updateButtonState();
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
    });


});




$('#AddBookingAdmin').on('change', '#activitySelectBooking', function () {
    let selectedActivityId = $(this).val();
    let selectedAmenityId = $(this).find(':selected').data('amenity-id');
    $('#amenityIdBooking').val(selectedAmenityId);
    $('#unitStatus').text('0/0').attr('class', 'mt-1 text-muted');
    const modal = $('#AddBookingAdmin');
    const unitNumber = modal.find('#unitNumber');
    const checkUnit = modal.find('.checkUnit');
    const dateField = modal.find('#dateFieldBooking');
    if (dateField[0]._flatpickr) {
        dateField[0]._flatpickr.clear();
    }
    dateField.val('');

    const startTimeDropdown = modal.find('#booking_start_time');
    const endTimeDropdown = modal.find('#booking_end_time');

    $('#availableSlotsContainer').empty();


    dateField.prop('disabled', false);
    startTimeDropdown.prop('disabled', true);
    endTimeDropdown.prop('disabled', true);
    unitNumber.prop('disabled', false);
    checkUnit.prop('disabled', false);

    $.ajax({
        url: '/admin/fetch-blocked-dates',
        method: 'GET',
        data: { amenity_id: selectedAmenityId },
        success: function (blockedDates) {
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
                        fetchAvailableStartTimes(modal, dateStr, selectedActivityId);
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
        startTimeDropdown.prop('disabled', true).empty().append('<option>Select Date First </option>');
        endTimeDropdown.prop('disabled', true).empty().append('<option>Select a start time</option>');
    } else {
        console.log("Disabling date field");
        dateField.prop('disabled', true).val('');
        startTimeDropdown.prop('disabled', true).empty().append('<option>Select Date First </option>');
        endTimeDropdown.prop('disabled', true).empty().append('<option>Select a start time </option>');
    }
});

function fetchAvailableStartTimes(modal, bookingDate, selectedActivityId) {
    $('#availableSlotsContainer').empty();
    const activityId = modal.find('#activitySelectBooking').val();
    const startTimeDropdown = modal.find('#booking_start_time');
    const endTimeDropdown = modal.find('#booking_end_time');
    startTimeDropdown.prop('disabled', true).empty().append('<option>Loading...</option>');
    endTimeDropdown.prop('disabled', true).empty().append('<option>Select a start time first</option>');

    if (!bookingDate || !activityId) return;

    $.ajax({
        url: '/admin/fetch-available-times',
        method: 'GET',
        data: { activity_id: activityId, booking_date: bookingDate },
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

$(document).on('change', '#booking_start_time', function () {
    $('#availableSlotsContainer').empty();
    const modal = $('#AddBookingAdmin');
    const selectedStartTime = $(this).val();
    const activityId = modal.find('#activitySelectBooking').val();
    const bookingDate = modal.find('#dateFieldBooking').val();
    const endTimeDropdown = modal.find('#booking_end_time');

    if (!selectedStartTime || !activityId || !bookingDate) return;

    endTimeDropdown.prop('disabled', true).empty().append('<option>Loading...</option>');

    $.ajax({
        url: '/admin/fetch-end-times',
        method: 'GET',
        data: {
            activity_id: activityId,
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


$(document).on('change', '#booking_end_time', function () {
    const modal = $('#AddBookingAdmin');
    const selectedStartTime = modal.find('#booking_start_time').val();
    const selectedEndTime = $(this).val();
    const activityId = modal.find('#activitySelectBooking').val();
    const bookingDate = modal.find('#dateFieldBooking').val();

    if (!selectedStartTime || !selectedEndTime || !activityId || !bookingDate) return;

    $.ajax({
        url: '/admin/fetch-available-slots',
        method: 'GET',
        data: {
            activity_id: activityId,
            booking_date: bookingDate,
            start_time: selectedStartTime,
            end_time: selectedEndTime
        },
        success: function (response) {
            updateAvailableSlots(response.activity_space, response.booked_slots);
        },
        error: function (xhr) {
            console.error('Failed to fetch available slots:', xhr.responseText);
        }
    });
});


function updateAvailableSlots(activitySpace, bookedSlots) {
    $('#availableSlotsContainer').empty();
    let row = $('<div class="d-flex justify-content-start flex-wrap w-100"></div>');
    let firstAvailableSelected = false;

    for (let i = 0; i < activitySpace; i++) {
        let slotNumber = i + 1;
        let isDisabled = bookedSlots.includes(slotNumber);
        let isSelected = !firstAvailableSelected && !isDisabled;

        if (isSelected) firstAvailableSelected = true;

        let cardHtml = `
                <div class="card p-2 m-1 shadow-sm border slot-card ${isSelected ? 'selected-slot border-primary' : ''} ${isDisabled ? 'bg-secondary text-white' : 'text-primary'}" 
                     data-slot="${slotNumber}"
                     style="width: 120px; height: 100px; cursor: ${isDisabled ? 'not-allowed' : 'pointer'}; flex: 1 1 calc(33.33% - 10px); max-width: 120px;">
                    <div class="card-body p-1 text-center d-flex flex-column justify-content-center">
                        <h6 class="card-title ${isDisabled ? 'text-white' : 'text-primary'}" style="font-size: 14px; font-weight: bold;">Court ${slotNumber}</h6>
                        <p class="m-0 ${isDisabled ? 'text-white' : 'text-primary'}" style="font-size: 12px; font-weight: bold;">
                            ${isDisabled ? 'Booked' : 'Available'}
                        </p>
                    </div>
                </div>
            `;
        row.append(cardHtml);

        if (slotNumber % 3 === 0 || slotNumber === activitySpace) {
            $('#availableSlotsContainer').append(row);
            row = $('<div class="d-flex justify-content-start flex-wrap w-100"></div>');
        }
    }

    updateSelectedSlotCount();
}

$(document).on('click', '.slot-card', function () {
    if ($(this).hasClass('bg-secondary')) return;

    let selectedSlots = $('.selected-slot');

    if ($(this).hasClass('selected-slot') && selectedSlots.length === 1) {
        return;
    }

    $(this).toggleClass('selected-slot border-success');


    updateSelectedSlotCount();
});

function updateSelectedSlotCount() {
    let selectedSlots = $('.selected-slot').map(function () {
        return $(this).data('slot');
    }).get();

    console.log("Selected Slots:", selectedSlots);

    $('#selectedSlotsInput').val(selectedSlots.join(','));
}

$('#AddBookingAdmin').on('hidden.bs.modal', function () {
    resetAdminBookingForm();
});

function resetAdminBookingForm() {
    $('#AdminNewBooking')[0].reset();
    $('#booking_start_time, #booking_end_time').prop('disabled', true).empty();
}


$('#AddBookingAdmin').on('hidden.bs.modal', function () {
    $('.modal-backdrop').remove();
});

$('#AddDateBlocking').on('hidden.bs.modal', function () {
    $('.modal-backdrop').remove();
});


$('#bookingTable').on('click', '.mark-as-no-show', function () {

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
            url: `/admin/admin-mark-no-show/${bookingId}`,
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
                    .then(() => refreshTableBookings());
            },
            error: function () {
                Swal.fire('Error', 'Something went wrong.', 'error');
            }
        });
    });
});

$('#bookingTable').on('click', '.editInfo_id_booking', function () {
    var booking_id = $(this).data("id");
    showLoading();
    $.ajax({
        url: '/admin/fetch/activity-booking/' + booking_id,
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
            $('#detail-activity-name').text(booking.activity_name ?? 'N/A');
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

            $('#bookingEdit').modal('show');
        },
        error: function () {
            alert('Booking not found.');
        },
        complete: function () {
            hideLoading();
        }
    });
});


$('#bookingEdit').on('change', '#edit_booking_select', function () {
    let selectedActivityId = $(this).val();
    console.log("Selected Activity ID:", selectedActivityId);

    const modal = $('#bookingEdit');
    const dateField = modal.find('#edit_booking_date');
    const startTimeDropdown = modal.find('#edit_booking_start_time');
    const endTimeDropdown = modal.find('#edit_booking_end_time');

    if (selectedActivityId) {
        dateField.prop('disabled', false);
        startTimeDropdown.prop('disabled', true).empty().append('<option>Select a date</option>');
        endTimeDropdown.prop('disabled', true).empty().append('<option>Select time start</option>');
    } else {
        dateField.prop('disabled', true).val('');
        startTimeDropdown.prop('disabled', true).empty();
        endTimeDropdown.prop('disabled', true).empty();
    }
});

function editfetchAvailableStartTimes(modal, bookingDate, selectedActivityId) {
    const activityId = modal.find('#edit_booking_select').val();
    const startTimeDropdown = modal.find('#edit_booking_start_time');
    const endTimeDropdown = modal.find('#edit_booking_end_time');

    startTimeDropdown.prop('disabled', true).empty().append('<option>Loading...</option>');
    endTimeDropdown.prop('disabled', true).empty().append('<option>Select a time start</option>');

    if (!bookingDate || !activityId) return;

    $.ajax({
        url: '/admin/fetch-available-times',
        method: 'GET',
        data: { activity_id: activityId, booking_date: bookingDate },
        success: function (availableTimePairs) {
            startTimeDropdown.empty();
            endTimeDropdown.empty();

            if (availableTimePairs.length > 0) {
                startTimeDropdown.append('<option>Select Time Start</option>');
                availableTimePairs.forEach(pair => {
                    startTimeDropdown.append(`<option value="${pair.start}">${pair.start}</option>`);
                });
                startTimeDropdown.prop('disabled', false);
            } else {
                startTimeDropdown.append('<option>Fully Booked</option>').prop('disabled', true);
            }
        },
        error: function (xhr) {
            console.error('Failed to fetch available times:', xhr.responseText);
        }
    });
}


$('#edit_booking_start_time').on('change', function () {
    const modal = $('#bookingEdit');
    const selectedStartTime = $(this).val();
    const activityId = modal.find('#edit_booking_select').val();
    const bookingDate = modal.find('#edit_booking_date').val();
    const endTimeDropdown = modal.find('#edit_booking_end_time');

    if (!selectedStartTime || !activityId || !bookingDate) return;
    endTimeDropdown.prop('disabled', true).empty().append('<option>Loading...</option>');

    $.ajax({
        url: '/admin/fetch-end-times',
        method: 'GET',
        data: {
            activity_id: activityId,
            booking_date: bookingDate,
            start_time: selectedStartTime
        },
        success: function (availableEndTimes) {
            endTimeDropdown.empty();

            if (availableEndTimes.length > 0) {
                endTimeDropdown.append('<option>Select End Time</option>');
                availableEndTimes.forEach(time => {
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

$('#updateBooking').on('submit', function (e) {
    e.preventDefault();
    var formData = $(this).serialize();
    $.ajax({
        url: '/admin/updateBooking',
        type: "POST",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        success: function (response) {
            $('#bookingEdit').modal('hide');
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
                title: 'Updated Successfully'
            });

            refreshTableBookings();
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
                title: 'Update failed'
            });
        }
    });

});

function refreshTableBookings() {
    $.ajax({
        url: '/admin/get-updated-bookings-table',
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            var bookings = response.data;
            var tableBody = $('#bookingTable tbody');
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
                        bookingStatusHtml = `<span class="badge bg-primary">Booked</span>`;
                        break;
                    case 2:
                        bookingStatusHtml = `<span class="badge bg-danger">Cancelled</span>`;
                        break;
                    case 3:
                        bookingStatusHtml = `<span class="badge bg-warning">Penalty</span>`;
                        break;
                    case 4:
                        bookingStatusHtml = `<span class="badge bg-dark">No Show</span>`;
                        break;
                    default:
                        bookingStatusHtml = 'N/A';
                }

                var actionButtons = `
                    <div class="d-flex gap-1 justify-content-center">
                        <!-- View button -->
                        <button type="button" class="btn btn-primary editInfo_id_booking btn-sm btn-equal"
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
        <button type="button" class="btn btn-warning mark-as-no-show btn-sm btn-equal"
            data-bs-toggle="tooltip" data-bs-placement="right" title="Mark as no show"
            data-id="${booking.id}">
            <i class="fa-solid fa-user-slash"></i>
        </button>
    `;
                }

                if (booking.penalty_amount > 0 && !booking.penalty_waived) {
                    actionButtons += `
        <button type="button"
            class="btn btn-success manage-penalty btn-sm btn-equal"
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
            class="btn btn-dark manage-penalty btn-sm btn-equal"
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
                        <td>${booking.activity?.activity_name ? booking.activity.activity_name.toUpperCase() : 'N/A'}</td>
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




$('#booking_start_time, #booking_end_time').prop('disabled', true);
$('#activitySelect').on('change', function () {
    const selectedOption = $(this).find(':selected');
    const startTime = selectedOption.data('start-time');
    const endTime = selectedOption.data('end-time');

    if (!startTime || !endTime) {
        $('#booking_start_time, #booking_end_time').empty().prop('disabled', true);
        return;
    }
    $('#booking_start_time, #booking_end_time').prop('disabled', false);
    populateTimeSlots(startTime, endTime, '#booking_start_time');
    populateTimeSlots(startTime, endTime, '#booking_end_time');
});

function populateTimeSlots(startTime, endTime, target) {
    const start = moment(startTime, 'HH:mm');
    const end = moment(endTime, 'HH:mm');

    $(target).empty();
    $(target).append(new Option('Select Time', '', true, false));

    while (start <= end) {
        const timeSlot = start.format('hh:mm A');
        $(target).append(new Option(timeSlot, timeSlot));
        start.add(1, 'hour');
    }
}

$('#bookingTable').on('click', '.confirm-booking', function () {
    var bookingId = $(this).data('id');

    $.ajax({
        url: '/admin/confirm-booking',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            booking_id: bookingId,
            booking_status: 1
        },
        success: function (response) {
            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                customClass: {
                    popup: response.status ? 'colored-toast' : 'colored-toast-error'
                },
                didOpen: (toast) => {
                    toast.onmouseenter = Swal.stopTimer;
                    toast.onmouseleave = Swal.resumeTimer;
                }
            });

            if (response.status) {
                Toast.fire({
                    icon: 'success',
                    title: 'Booking Confirmed'
                });
                refreshTableBookings();
            } else {
                Toast.fire({
                    icon: 'warning',
                    title: response.message // Show message from backend
                });
            }
        },
        error: function () {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to confirm booking. Please try again.'
            });
        }
    });
});


$('#bookingTable').on('click', '.cancel-booking', function () {

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
            url: `/admin/cancel-booking/${bookingId}`,
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
                            sendCancelRequest(bookingId, false);
                        }

                        if (result2.isDenied) {
                            sendCancelRequest(bookingId, true);
                        }

                    });
                } else {
                    // ✅ FIX HERE (no penalty case)
                    Swal.fire('Cancelled!', res.message || 'Booking cancelled successfully.', 'success')
                        .then(() => refreshTableBookings());
                }
            }
        });

    });

});

function sendCancelRequest(bookingId, waivePenalty = false) {
    $.ajax({
        url: `/admin/cancel-booking/${bookingId}`,
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            confirm: 1,
            waive_penalty: waivePenalty ? 1 : 0
        },
        success: function (res2) {
            Swal.fire('Cancelled!', res2.message, 'success')
                .then(() => refreshTableBookings());
        }
    });
}

$('#bookingTable').on('click', '.manage-penalty', function () {

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
            url: `/admin/admin-manage-penalty/${bookingId}`,
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
                    .then(() => refreshTableBookings());
            },
            error: function () {
                Swal.fire('Error', 'Something went wrong.', 'error');
            }
        });

    });
});

$('.SlotChecking').on('click', function () {
    $('#SlotCheckingModal').modal('show');
    $('#SearchSlotAdmin')[0].reset();
    $('#activityDateFieldSearchAdmin').prop('disabled', true);
    $('.searchBtn').prop('disabled', true);
    $('.all-slot-available-admin').empty();
    $('#spinner').addClass('d-none');
});

$('#SlotCheckingModal').on('hidden.bs.modal', function () {
    $('.modal-backdrop').remove();
    $('#activityDateFieldSearchAdmin').prop('disabled', true);
    $('.searchBtn').prop('disabled', true);
    $('#SearchSlotAdmin')[0].reset();
    $('.all-slot-available-admin').empty();
    $('#spinner').addClass('d-none');
});

$('#activitySelectBookingSearchAdmin').on('change', function () {
    const activitySelect = $(this);
    const selectedActivityId = activitySelect.val();
    const selectedAmenityId = activitySelect.find(':selected').data('amenity-id');
    const activityDateFieldSearchAdmin = $('#activityDateFieldSearchAdmin');

    $('.all-slot-available-admin').empty();
    $('#amenityIdBooking').val(selectedAmenityId);
    $('#unitStatus').text('0/0').attr('class', 'mt-1 text-muted');
    activityDateFieldSearchAdmin.val('').prop('disabled', false);
    $('.searchBtn').prop('disabled', true);

    if (activityDateFieldSearchAdmin[0]._flatpickr) {
        activityDateFieldSearchAdmin[0]._flatpickr.destroy();
    }

    $.ajax({
        url: '/admin/fetch-blocked-dates',
        method: 'GET',
        data: { amenity_id: selectedAmenityId },
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

            flatpickr(activityDateFieldSearchAdmin[0], {
                enableTime: false,
                dateFormat: "Y-m-d",
                minDate: today,
                maxDate: maxBookingDate,
                allowInput: true,
                altInput: true,
                altFormat: "F j, Y",
                disable: blockedDates,

                onChange: function (selectedDates, dateStr, instance) {
                    if (dateStr) {
                        $('.searchBtn').prop('disabled', false);
                        $('.all-slot-available-admin').empty();
                    } else {
                        $('.searchBtn').prop('disabled', true);
                        $('.all-slot-available-admin').empty();
                    }
                }
            });
        },
        error: function (xhr) {
            console.error('Failed to fetch blocked dates:', xhr.responseText);
        }
    });
});


$('.SearchSlotAdmin').submit(function (event) {
    event.preventDefault();

    let activityId = $('#activitySelectBookingSearchAdmin').val();
    let amenityId = $('#activitySelectBookingSearchAdmin option:selected').data('amenity-id');
    let dateField = $('#activityDateFieldSearchAdmin').val();
    const $btn = $('.slot-checking-submit-btn-admin');
    const originalWidth = $btn.outerWidth();
    $btn
        .attr('disabled', true)
        .html(`<div class="spinner-border spinner-border-sm text-light"></div>`)
        .css('width', originalWidth + 'px');

    $.ajax({
        url: '/admin/fetch-all-slots-admin',
        method: 'get',
        data: {
            activity_id: activityId,
            amenity_id: amenityId,
            booking_date: dateField
        },
        success: function (response) {
            $('#spinner').addClass('d-none');

            if (response.error) {
                $('.all-slot-available-admin').html(
                    `<div class="alert alert-warning text-center mb-0">
                No Schedule for the selected date. Please choose another date.
            </div>`
                );
                return;
            }

            let html = '<table class="table table-bordered"><thead><tr><th>Time</th>';

            for (let i = 0; i < response.activity_space; i++) {
                html += `<th>Slot ${i + 1}</th>`;
            }

            html += '</tr></thead><tbody>';

            response.slots.forEach(slot => {
                html += `<tr><td>${slot.time_range}</td>`;
                slot.slots.forEach(status => {
                    let badgeClass;

                    if (status === 'Available') {
                        badgeClass = 'bg-primary';
                    } else {

                        badgeClass = 'bg-danger';
                    }

                    html += `<td><span class="badge ${badgeClass} text-uppercase">${status}</span></td>`;
                });
                html += '</tr>';
            });

            html += '</tbody></table>';
            $('.all-slot-available-admin').html(html);
        },
        error: function (xhr) {
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



