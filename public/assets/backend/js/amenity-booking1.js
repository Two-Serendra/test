$(document).ready(function () {
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
    const submitButton = adminModal.find('.submitBtn');

    // Set initial booking type
    let bookingType = bookingTabs.find('a.active').data('value') || "Advanced Booking";
    bookingTypeInput.val(bookingType);
    updateFormState(bookingType);

    // Booking type tab change
    bookingTabs.find('a').on('click', function () {
        bookingType = $(this).data('value');
        bookingTypeInput.val(bookingType);
        updateFormState(bookingType);
    });


    function updateFormState(type) {
        resetFields();

        if (type === "Walk-in") {
            checkUnitBtn.hide();
            unitStatus.hide();
            toggleFields(true);
            dateField.prop('disabled', false);
            submitButton.prop('disabled', false);

            dateField.off('change.enableFields').on('change.enableFields', function () {
                toggleFields(false);
                // Walk-in: Enable all fields immediately
                enableAllFields();
            });
        } else if (type === "Advanced Booking") {
            checkUnitBtn.show().prop('disabled', false);
            unitStatus.show();
            toggleFields(true);
            dateField.prop('disabled', false);
            submitButton.prop('disabled', true);
        }

        else if (type === "Advanced Booking") {
            checkUnitBtn.show().prop('disabled', false);
            unitStatus.show();
            toggleFields(true);
            dateField.prop('disabled', false);
            submitButton.prop('disabled', true);
        }

        else if (type === "24hrs") {
            checkUnitBtn.show().prop('disabled', false);
            checkUnitBtn.hide();
            unitStatus.hide();
            toggleFields(false);
            enableAllFields();
        }

        //  } else if (type === "Advanced Booking" || type === "24hrs") {
        //     checkUnitBtn.show().prop('disabled', false);
        //     unitStatus.show();
        //     toggleFields(true);
        //     dateField.prop('disabled', false);
        //     submitButton.prop('disabled', true);
        // }
    }

    function resetFields() {
        adminModal.find('input[type="text"], input[type="number"]').val('');
        adminModal.find('select').prop('selectedIndex', 0);
        dateField.val('');

        startTimeDropdown.prop('disabled', true).empty().append('<option>Select a Date First</option>');
        endTimeDropdown.prop('disabled', true).empty().append('<option>Select Start Time First</option>');

        unitStatus.text('').hide();
        adminModal.find('form').removeClass('was-validated');
        adminModal.find('input, select').removeClass('is-valid is-invalid');
    }

    function toggleFields(disable) {
        residentType.prop('disabled', true); // 🔒 Always locked for now
        nameField.prop('disabled', true);    // 🔒 Always locked for now
        contactField.prop('disabled', true); // 🔒 Always locked for now
        startTimeDropdown.prop('disabled', disable)
            .empty()
            .append('<option>' + (disable ? 'Select a Date First' : 'Select Start Time') + '</option>');
        endTimeDropdown.prop('disabled', disable)
            .empty()
            .append('<option>' + (disable ? 'Select start time first' : 'Select End Time') + '</option>');
    }

    $('.AdminNewBooking').submit(function (event) {
        event.preventDefault();
        const form = this;
        const startTime = $(form).find('[name="booking_start_time"]').val();
        const endTime = $(form).find('[name="booking_end_time"]').val();
        const submitButton = $('.submitBtn');
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

        submitButton.prop('disabled', true);
        spinner.removeClass('d-none');
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
                form.reset();
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
            }
        });
    });






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

    function resetBookingForm() {
        const form = $('#AdminNewBooking')[0];
        form.reset();

        $('#selectResidentType').prop('disabled', true).val('Owner');
        $('#unitNumber, #name, #contact_number, #dateFieldBooking').prop('disabled', true).val('');
        $('#booking_start_time, #booking_end_time').empty();
        $('.bookingType').prop('checked', false);
        $('#selectedSlotsInput').val('');
        $('.checkUnit, #unitStatus').show();
        $('.checkUnit').prop('disabled', true);
        $('#availableSlotsContainer').empty();
        $('#unitStatus').text('0/0');
        $('.submitBtn').prop('disabled', true);

        // Reset Tabs
        $('#bookingTabs .nav-link').removeClass('active');
        $('#advanced-tab').addClass('active');

        // Reset Booking Type
        $('#bookingType').val('Advanced Booking');

        // Reset Dropdowns
        $('#amenityIdBooking').val('');
        $('#activitySelectBooking').val('').change();
        $('#selectResidentType, #booking_start_time, #booking_end_time').prop('selectedIndex', 0);

        // Remove validation states
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').hide();
    }


    $('.AddBookingAdmin').on('click', function () {
        resetBookingForm();
        let submitButton = $(".submitBtn");
        $('#AddBookingAdmin').modal('show');
        hideSpinner(submitButton);

    });




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
            url: '/check-unit-booking',
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
                            enableAllFields();
                        } else {
                            unitStatus.addClass("text-danger").text(statusText + " (Max)");
                            disableSubmitOnly();
                        }
                        // } else if (bookingType === "24hrs") {
                        //     if (count >= maxBookings) {
                        //         unitStatus.addClass("text-primary").text(statusText + " (Eligible)");
                        //         enableAllFields();
                        //     } else {
                        //         unitStatus.addClass("text-danger").text(statusText + " (Advance Booking Required)");
                        //         disableSubmitOnly();
                        //     }
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

    function enableAllFields() {
        submitButton.prop('disabled', false);
        residentType.prop('disabled', false);
        nameField.prop('disabled', false);
        contactField.prop('disabled', false);
    }

    // function disableSubmitOnly() {
    //     submitButton.prop('disabled', true);
    // }
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
    const startTimeDropdown = modal.find('#booking_start_time');
    const endTimeDropdown = modal.find('#booking_end_time');

    $('#availableSlotsContainer').empty();


    dateField.prop('disabled', false);
    startTimeDropdown.prop('disabled', true);
    endTimeDropdown.prop('disabled', true);
    unitNumber.prop('disabled', false);
    checkUnit.prop('disabled', false);

    $.ajax({
        url: '/fetch-blocked-dates',
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
        url: '/fetch-available-times',
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
        url: '/fetch-end-times',
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
        url: '/fetch-available-slots',
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
                <div class="card p-2 m-1 shadow-sm border slot-card ${isSelected ? 'selected-slot border-success' : ''} ${isDisabled ? 'bg-secondary text-white' : 'text-success'}" 
                     data-slot="${slotNumber}"
                     style="width: 120px; height: 100px; cursor: ${isDisabled ? 'not-allowed' : 'pointer'}; flex: 1 1 calc(33.33% - 10px); max-width: 120px;">
                    <div class="card-body p-1 text-center d-flex flex-column justify-content-center">
                        <h6 class="card-title ${isDisabled ? 'text-white' : 'text-success'}" style="font-size: 14px; font-weight: bold;">Court ${slotNumber}</h6>
                        <p class="m-0 ${isDisabled ? 'text-white' : 'text-success'}" style="font-size: 12px; font-weight: bold;">
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

$('#bookingTable').on('click', '.editInfo_id_booking', function () {
    var booking_id = $(this).data("id");

    $.get('/fetch/booking/' + booking_id, function (data) {
        $('#bookingEdit').modal('show');

        $('#edit_transaction_no').text(data.transaction_no);
        $('#edit_booking_select_text').text(data.activity_name);
        $('#edit_booking_unit_text').text(data.unit);
        $('#edit_booking_name_text').text(data.name);
        $('#edit_contact_number_text').text(data.contact_number);
        $('#edit_booking_date_text').text(data.booking_date);
        $('#edit_booking_start_time_text').text(data.booking_start_time);
        $('#edit_booking_end_time_text').text(data.booking_end_time);
        $('#edit_selectResidentType_text').text(
            data.resident_type.charAt(0).toUpperCase() + data.resident_type.slice(1).toLowerCase()
        );

        $('#booking_id').val(booking_id);

        $('.booking_type_text').text(data.booking_type);
        $('#booking_status_text').text(data.booking_status);

        $.ajax({
            url: '/fetch-blocked-dates',
            method: 'GET',
            data: { amenity_id: data.amenity_id },
            success: function (blockedDates) {
                console.log("Blocked dates:", blockedDates);
            },
            error: function (xhr) {
                console.error('Failed to fetch blocked dates:', xhr.responseText);
            }
        });

    }).fail(function () {
        alert("Data not found");
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
        url: '/fetch-available-times',
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
        url: '/fetch-end-times',
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
        url: '/updateBooking',
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
        url: '/get-updated-bookings-table',
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            var bookings = response.data;
            var tableBody = $('#bookingTable tbody');
            $('[data-bs-toggle="tooltip"]').tooltip('dispose');
            tableBody.empty();

            bookings.forEach(function (booking) {
                var actionButtons = `
                        <button type="button" class="btn btn-primary editInfo_id_booking btn-sm btn-equal"
                            data-bs-toggle="tooltip" data-bs-placement="left" title="View"
                            data-id="${booking.id}">
                            <i class="fa-solid fa-eye"></i>
                        </button>`;

                if (booking.booking_status == 0) {
                    actionButtons += `
                            <button type="button" class="btn btn-secondary cancel-booking btn-sm btn-equal"
                                data-bs-toggle="tooltip" data-bs-placement="right" title="Cancel"
                                data-id="${booking.id}" disabled>
                                <i class="fa-solid fa-ban"></i>
                            </button>`;

                } else {
                    actionButtons += `
                            <button type="button" class="btn btn-danger cancel-booking btn-sm btn-equal"
                                data-bs-toggle="tooltip" data-bs-placement="right" title="Cancel"
                                data-id="${booking.id}">
                                <i class="fa-solid fa-ban"></i>
                            </button>`;
                }

                var lobby = booking.lobby ? booking.lobby.toUpperCase() : 'N/A';
                var transaction_no = booking.transaction_no ? booking.transaction_no : 'N/A';
                var booking_name = booking.activity.activity_name ? booking.activity.activity_name.toUpperCase() : 'N/A';
                var booking_unit = booking.unit ? booking.unit.toUpperCase() : 'N/A';

                var residentTypeHtml = booking.resident_type
                    ? (booking.resident_type.toUpperCase() === 'OWNER'
                        ? `<span class="text-success">${booking.resident_type.toUpperCase()}</span>`
                        : booking.resident_type.toUpperCase() === 'TENANT'
                            ? `<span class="text-danger">${booking.resident_type.toUpperCase()}</span>`
                            : booking.resident_type.toUpperCase())
                    : 'N/A';

                var name = booking.name ? booking.name.toUpperCase() : 'N/A';
                var booking_contact = booking.contact_number ? booking.contact_number.toUpperCase() : 'N/A';
                var booking_type = booking.booking_type ? booking.booking_type.toUpperCase() : 'N/A';

                var booking_status = booking.booking_status == 1
                    ? `<span class="badge bg-success border-success custom-badge">Booked</span>`
                    : `<span class="badge bg-danger border-danger custom-badge">Cancelled</span>`;

                var booking_booking_date = booking.booking_date || 'N/A';
                var booking_booking_start_time = booking.booking_start_time || 'N/A';
                var booking_booking_end_time = booking.booking_end_time || 'N/A';

                // Use raw values from the database
                var booking_created_at = booking.created_at || 'N/A';
                var booking_updated_at = booking.updated_at || 'N/A';

                var row = $(`
                        <tr>
                            <td class="sticky-th">${lobby}</td>
                            <td class="sticky-th">${transaction_no}</td>
                            <td class="sticky-th">${booking_name}</td>
                            <td class="sticky-th">${booking_unit}</td>
                            <td class="sticky-th">${residentTypeHtml}</td>
                            <td class="sticky-th">${name}</td>
                            <td class="sticky-th">${booking_contact}</td>
                            <td class="sticky-th">${booking_type}</td>
                            <td class="sticky-th">${booking_status}</td>
                            <td class="sticky-th">${booking_booking_date}</td>
                            <td class="sticky-th">${booking_booking_start_time}</td>
                            <td class="sticky-th">${booking_booking_end_time}</td>
                            <td class="sticky-th">${booking_created_at}</td>
                            <td class="sticky-th">${booking_updated_at}</td>
                            <td class="sticky-th sticky-col sticky-col-color">${actionButtons}</td>
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
        url: '/confirm-booking',
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
    var bookingId = $(this).data('id');
    Swal.fire({
        title: "Are you sure?",
        text: "This will cancel the booking.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, cancel it!"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/cancel-booking',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    booking_id: bookingId,
                    booking_status: 0
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
                        title: 'Booking Cancelled'
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
                        title: 'Failed to cancel'
                    });
                }
            });
        }
    });
});

$('.SlotChecking').on('click', function () {
    $('#SlotCheckingModal').modal('show');
    $('#SearchSlotAdmin')[0].reset();
    $('#dateFieldSearchAdmin').prop('disabled', true);
    $('.searchBtn').prop('disabled', true);
    $('.all-slot-available-admin').empty();
    $('#spinner').addClass('d-none');
});

$('#SlotCheckingModal').on('hidden.bs.modal', function () {
    $('.modal-backdrop').remove();
    $('#dateFieldSearchAdmin').prop('disabled', true);
    $('.searchBtn').prop('disabled', true);
    $('#SearchSlotAdmin')[0].reset();
    $('.all-slot-available-admin').empty();
    $('#spinner').addClass('d-none');
});

$('#activitySelectBookingSearchAdmin').on('change', function () {
    const activitySelect = $(this);
    const selectedActivityId = activitySelect.val();
    const selectedAmenityId = activitySelect.find(':selected').data('amenity-id');
    const dateFieldSearchAdmin = $('#dateFieldSearchAdmin');

    $('.all-slot-available-admin').empty();
    $('#amenityIdBooking').val(selectedAmenityId);
    $('#unitStatus').text('0/0').attr('class', 'mt-1 text-muted');
    dateFieldSearchAdmin.val('').prop('disabled', false);
    $('.searchBtn').prop('disabled', true);

    if (dateFieldSearchAdmin[0]._flatpickr) {
        dateFieldSearchAdmin[0]._flatpickr.destroy();
    }

    $.ajax({
        url: '/fetch-blocked-dates',
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

            flatpickr(dateFieldSearchAdmin[0], {
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
    let dateField = $('#dateFieldSearchAdmin').val();

    $('#spinner').removeClass('d-none');

    $.ajax({
        url: '/fetch-all-slots-admin',
        method: 'get',
        data: {
            activity_id: activityId,
            amenity_id: amenityId,
            booking_date: dateField
        },
        success: function (response) {
            $('#spinner').addClass('d-none');
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
        }
    });
});

