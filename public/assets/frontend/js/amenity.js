$(document).ready(function () {
    $('#bookingTabs a.booking-tab').on('shown.bs.tab', function (e) {
        let bookingType = $(e.target).data('value');
        let modal = $(this).closest(".modal");
        modal.find("#bookingType").val(bookingType);
    });

    $('.modal').each(function () {
        var modal = $(this);
        var bookingTabs = modal.find('#bookingTabs');
        var bookingTypeInput = modal.find('#bookingType');
        var unitNumber = modal.find('#unitNumber');
        var checkUnit = modal.find('.checkUnit');
        var unitStatus = modal.find('#unitStatus');
        var residentType = modal.find('#selectResidentType');
        var nameField = modal.find('[id^=name]');
        var contactField = modal.find('[id^=contact_number]');
        var dateField = modal.find('#dateField');
        var startTimeDropdown = modal.find('[id^=booking_start_time]');
        var endTimeDropdown = modal.find('[id^=booking_end_time]');
        var submitButton = modal.find('button[type="submit"]');

        var bookingType = bookingTabs.find('a.active').data('value') || "Advanced Booking";
        bookingTypeInput.val(bookingType);
        updateFormState(bookingType);

        bookingTabs.find('a').on('click', function () {
            var bookingType = $(this).data('value');
            bookingTypeInput.val(bookingType);
            updateFormState(bookingType);
        });

        function updateFormState(bookingType) {
            resetFields(); //

            if (bookingType === "Walk-in") {
                checkUnit.hide();
                unitStatus.hide();
                toggleFields(true);
                dateField.prop('disabled', false);
                submitButton.prop('disabled', false);
                dateField.off('change.enableFields').on('change.enableFields', function () {
                    toggleFields(false);
                });
            }
            else if (bookingType === "Advanced Booking") {
                checkUnit.show().prop('disabled', false);
                unitStatus.show();
                toggleFields(true);
                dateField.prop('disabled', false);
                submitButton.prop('disabled', true);
            }
            else if (bookingType === "24hrs") {
                checkUnit.hide();
                unitStatus.hide();
                toggleFields(false);
                dateField.prop('disabled', false);
                submitButton.prop('disabled', false);
            }
        }

        function resetFields() {
            modal.find('input[type="text"], input[type="number"]').val('');
            modal.find('select').prop('selectedIndex', 0);
            dateField.val('');

            startTimeDropdown.prop('disabled', true).empty().append('<option>Select a Date First</option>');
            endTimeDropdown.prop('disabled', true).empty().append('<option>Select Start Time First</option>');

            unitStatus.text('').hide();

            modal.find('form').removeClass('was-validated');
            modal.find('input, select').removeClass('is-valid is-invalid');
        }

        function toggleFields(disable) {
            residentType.prop('disabled', disable);
            nameField.prop('disabled', disable);
            contactField.prop('disabled', disable);
            startTimeDropdown.prop('disabled', disable)
                .empty()
                .append('<option>' + (disable ? 'Select a Date First' : 'Select Start Time') + '</option>');
            endTimeDropdown.prop('disabled', disable)
                .empty()
                .append('<option>' + (disable ? 'Select start time first' : 'Select End Time') + '</option>');
        }
    });




    $("form[id^='bookAmenityForm']").on("submit", function (event) {
        var form = this;
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();

            $(form).addClass("was-validated");
            return;
        }

        var activityId = $(this).attr("id").replace("bookAmenityForm", "");
        var $btn = $("#submitButton" + activityId);
        var $spinner = $("#spinner" + activityId);
        $btn.prop("disabled", true);
        $spinner.removeClass("d-none");
    });


    $('.AddNewBooking').on('click', function () {
        var activityId = $(this).data('activity-id');
        var modal = $('#modalActivity' + activityId);
        var amenityId = modal.find('#amenityId' + activityId).val();

        modal.data('activity-id', activityId);
        modal.modal('show');
        modal.find('#userAvailableSlotsContainer' + activityId).empty();

        const dateField = modal.find('#dateField');
        const startTimeDropdown = modal.find('.booking_start_time');
        const endTimeDropdown = modal.find('.booking_end_time');
        const submitButton = modal.find('button[type="submit"]');
        const selectResidentType = modal.find('#selectResidentType');
        const name = modal.find('#name' + activityId);
        const contact_number = modal.find('#contact_number' + activityId);

        selectResidentType.prop('disabled', true);
        name.prop('disabled', true).val('');
        contact_number.prop('disabled', true).val('');
        submitButton.prop('disabled', true);
        startTimeDropdown.prop('disabled', true).empty().append('<option>Select a Date First</option>');
        endTimeDropdown.prop('disabled', true).empty().append('<option>Select a start time</option>');
        modal.find('#unitStatus').text('0/0').attr('class', 'mt-1 text-muted');
        contact_number.off('input').on('input', function () {
            var v = $(this).val().replace(/\D/g, '');
            if (v.length > 11) v = v.substring(0, 11);
            $(this).val(v);
        });

        var initialType = modal.find('#bookingTabs a.active').data('value') || 'Advanced Booking';
        modal.find('#bookingType').val(initialType);
        $.ajax({
            url: '/fetch-blocked-dates',
            method: 'GET',
            data: { amenity_id: amenityId },
            success: function (blockedDates) {
                modal.data('blockedDates', blockedDates);
                applyFieldToggles(modal, initialType);
                setDatePicker(modal, initialType, blockedDates);
            },
            error: function (xhr) {
                console.error('Failed to fetch blocked dates:', xhr.responseText);
            }
        });
        modal.find('#bookingTabs a[data-bs-toggle="tab"]')
            .off('shown.bs.tab')
            .on('shown.bs.tab', function (e) {
                const newType = $(e.target).data('value');
                modal.find('#bookingType').val(newType).trigger('change');
            });
        modal.find('input[name="booking_type"]')
            .off('change')
            .on('change', function () {
                const type = $(this).val();
                applyFieldToggles(modal, type);
                const blocked = modal.data('blockedDates');
                if (blocked) {
                    setDatePicker(modal, type, blocked);
                } else {

                    $.get('/fetch-blocked-dates', { amenity_id: amenityId }, function (bd) {
                        modal.data('blockedDates', bd);
                        setDatePicker(modal, type, bd);
                    });
                }
            });

        function applyFieldToggles(modal, type) {
            const checkUnit = modal.find('.checkUnit');
            const unitStatus = modal.find('#unitStatus');

            if (type === 'Advanced Booking') {
                modal.find('#selectResidentType, input[name="name"], input[name="contact_number"]').prop('disabled', true);
                modal.find('#unitNumber').prop('disabled', false);
                checkUnit.prop('disabled', false);
                submitButton.prop('disabled', true);
            } else if (type === 'Walk-in') {
                unitStatus.text('0/0').attr('class', 'mt-1 text-muted');
                checkUnit.prop('disabled', true);
                modal.find('#unitNumber, #selectResidentType, input[name="name"], input[name="contact_number"], #dateField')
                    .prop('disabled', false);
                submitButton.prop('disabled', false);
            }

            startTimeDropdown.prop('disabled', true).empty().append('<option>Select a Date First</option>');
            endTimeDropdown.prop('disabled', true).empty().append('<option>Select a start time</option>');
            dateField.val('');
        }

        function setDatePicker(modal, type, blockedDates) {
            const df = modal.find('#dateField')[0];

            if (df._flatpickr) df._flatpickr.destroy();

            const today = new Date(); today.setHours(0, 0, 0, 0);
            const tomorrow = new Date(today); tomorrow.setDate(today.getDate() + 1);

            let minDate, maxDate;

            if (type === '24hrs') {
                minDate = today;
                maxDate = tomorrow;
            } else if (type === 'Walk-in') {
                minDate = today; maxDate = today;
            } else {
                minDate = today;
                let currentMonday = new Date(today);
                currentMonday.setDate(today.getDate() - today.getDay() + 1);
                let maxBookingDate = new Date(currentMonday);
                maxBookingDate.setDate(currentMonday.getDate() + 13);
                if (today.getDay() >= 5) {
                    maxBookingDate.setDate(maxBookingDate.getDate() + 7);
                }
                maxDate = maxBookingDate;
            }

            flatpickr(df, {
                enableTime: false,
                dateFormat: 'Y-m-d',
                minDate: minDate,
                maxDate: maxDate,
                allowInput: false,
                altInput: true,
                altFormat: 'F j, Y',
                disable: blockedDates,
                onChange: function (selectedDates, dateStr) {
                    if (dateStr) {
                        startTimeDropdown.prop('disabled', false);
                        fetchAvailableStartTimes(modal, dateStr, modal.data('activity-id'));
                    }
                }
            });
        }

    });



    function fetchAvailableStartTimes(modal, bookingDate, activityId) {
        const startTimeDropdown = modal.find('.booking_start_time');
        const endTimeDropdown = modal.find('.booking_end_time');
        console.log("fetchAvailableStartTimes - Activity ID:", activityId);
        console.log("fetchAvailableStartTimes - Booking Date:", bookingDate);
        startTimeDropdown.prop('disabled', true).empty().append('<option>Loading...</option>');
        endTimeDropdown.prop('disabled', true).empty().append('<option>Select a start time first</option>');

        if (!bookingDate || !activityId) return;

        $.ajax({
            url: '/fetch-available-times-user',
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




    $(document).on('change', '.booking_start_time', function () {
        const modal = $(this).closest('.modal');
        const selectedStartTime = $(this).val();
        const activityId = modal.find('[name="activity_id"]').val();
        console.log("fetchAvailableEndTimes - Activity ID:", activityId);
        modal.find(`#userAvailableSlotsContainer${activityId}`).empty();
        const bookingDate = modal.find('[name="booking_date"]').val();
        const endTimeDropdown = modal.find('.booking_end_time');

        if (!selectedStartTime || !activityId || !bookingDate) return;

        endTimeDropdown.prop('disabled', true).empty().append('<option>Loading...</option>');

        $.ajax({
            url: '/fetch-end-times-user',
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

    $(document).on('change', '.booking_end_time', function () {
        const modal = $(this).closest('.modal');
        const selectedStartTime = modal.find('.booking_start_time').val();
        const selectedEndTime = $(this).val();
        const activityId = modal.find('[name="activity_id"]').val();
        const bookingDate = modal.find('[name="booking_date"]').val();

        if (!selectedStartTime || !selectedEndTime || !activityId || !bookingDate) return;

        $.ajax({
            url: '/fetch-available-slots-user',
            method: 'GET',
            data: {
                activity_id: activityId,
                booking_date: bookingDate,
                start_time: selectedStartTime,
                end_time: selectedEndTime
            },
            success: function (response) {
                updateAvailableSlotsUser(response.activity_space, response.booked_slots, modal);
            },
            error: function (xhr) {
                console.error('Failed to fetch available slots:', xhr.responseText);
            }
        });
    });

    function updateAvailableSlotsUser(activitySpace, bookedSlots, modal) {
        const activityId = modal.find('[name="activity_id"]').val();
        const slotsContainer = modal.find(`#userAvailableSlotsContainer${activityId}`);

        slotsContainer.empty();
        let row = $('<div class="d-flex justify-content-start flex-wrap w-100"></div>');
        let firstAvailableSelected = false;

        for (let i = 0; i < activitySpace; i++) {
            let slotNumber = i + 1;
            let isDisabled = bookedSlots.includes(slotNumber);
            let isSelected = !firstAvailableSelected && !isDisabled;

            if (isSelected) firstAvailableSelected = true;

            let boxHtml = `
                <div class="slot-box p-2 m-1 shadow-sm border ${isSelected ? 'selected-slots-user border-success' : ''} ${isDisabled ? 'bg-secondary text-white' : 'text-success'}" 
                     data-slot="${slotNumber}"
                     style="width: 120px; height: 100px; cursor: ${isDisabled ? 'not-allowed' : 'pointer'}; flex: 1 1 calc(33.33% - 10px); max-width: 120px;">
                    <div class="slot-box-body p-1 text-center d-flex flex-column justify-content-center">
                        <h6 class="slot-title ${isDisabled ? 'text-white' : 'text-success'}" style="font-size: 14px; font-weight: bold;">Court ${slotNumber}</h6>
                        <p class="m-0 ${isDisabled ? 'text-white' : 'text-success'}" style="font-size: 12px; font-weight: bold;">
                            ${isDisabled ? 'Booked' : 'Available'}
                        </p>
                    </div>
                </div>
            `;
            row.append(boxHtml);

            if (slotNumber % 3 === 0 || slotNumber === activitySpace) {
                slotsContainer.append(row);
                row = $('<div class="d-flex justify-content-start flex-wrap w-100"></div>');
            }
        }
        updateSelectedSlotCountUser(modal);
    }



    $(document).on('click', '.slot-box', function () {
        let modal = $(this).closest('.modal');
        let slotsContainer = modal.find('.slot-box');

        if ($(this).hasClass('bg-secondary')) return;

        let selectedSlots = modal.find('.selected-slots-user');

        if ($(this).hasClass('selected-slots-user') && selectedSlots.length === 1) {
            return;
        }

        $(this).toggleClass('selected-slots-user border-success');

        // Pass the modal to the function
        updateSelectedSlotCountUser(modal);
    });

    function updateSelectedSlotCountUser(modal) {
        if (!modal || modal.length === 0) {
            console.error("Modal not found or undefined in updateSelectedSlotCountUser");
            return;
        }

        let selectedSlots = modal.find('.selected-slots-user').map(function () {
            return $(this).data('slot');
        }).get();

        console.log("Selected Slots:", selectedSlots);

        modal.find('#selectedSlotsInputUser').val(selectedSlots.join(',')); // Update input in modal
    }

    // Clear fields when modal closes
    $('.modal').on('hidden.bs.modal', function () {
        const modal = $(this);
        modal.find('input, select').val('');
        modal.find('.booking_start_time, .booking_end_time').prop('disabled', true).empty();
    });



    $('.bookAmenityForm').submit(function (event) {
        event.preventDefault();
        const form = this;
        const formId = $(form).attr('id');
        const submitBtn = $(`#submitButton${formId.replace('bookAmenityForm', '')}`);
        const spinner = $(`#spinner${formId.replace('bookAmenityForm', '')}`);

        // disable button + show spinner
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');

        const startTime = $(form).find('[name="booking_start_time"]').val();
        const endTime = $(form).find('[name="booking_end_time"]').val();

        let selectedCount = $('.selected-slots-user').length;
        $('#selectedSlotsInputUser').val(selectedCount);

        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            submitBtn.prop('disabled', false);
            spinner.addClass('d-none');
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
            submitBtn.prop('disabled', false);
            spinner.addClass('d-none');
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
            submitBtn.prop('disabled', false);
            spinner.addClass('d-none');
            return;
        }

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

                Swal.fire({
                    icon: response.success ? 'success' : 'error',
                    title: response.message,
                    confirmButtonText: 'OK'
                }).then(() => {
                    if (response.success) {
                        window.location.reload();
                    }
                });
            },
            error: function (xhr) {
                let message = 'Something went wrong. Please try again.';
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    const firstError = Object.values(xhr.responseJSON.errors)[0][0];
                    message = firstError;
                } else if (xhr.responseJSON?.message) {
                    message = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Booking Failed',
                    text: message,
                    confirmButtonText: 'OK'
                });
            },
            complete: function () {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });

    });



    $("#customUserDropdownBtn").on("click", function (e) {
        e.stopPropagation();
        $("#customUserDropdownMenu").toggle();
    });

    // Close Dropdown when clicking outside
    $(document).on("click", function () {
        $("#customUserDropdownMenu").hide();
    });

    // Toggle Mobile Navigation
    $("#customMenuToggle").on("click", function () {
        $("#customMobileNav").slideToggle();
    });

    // Close Mobile Menu when clicking outside
    $(document).on("click", function (e) {
        if (!$(e.target).closest(".custom-navbar, .custom-mobile-nav").length) {
            $("#customMobileNav").slideUp();
        }
    });


    $(".checkUnit").on("click", function () {
        let button = $(this);
        let modal = button.closest(".modal");
        let activityId = modal.attr("id").replace("modalActivity", "");
        let unitNumber = modal.find("#unitNumber").val().trim();
        let submitButton = modal.find("#submitButton" + activityId);
        let checkUnit = modal.find(".checkUnit");
        let selectedDate = modal.find("#dateField").val()?.trim();
        let selectResidentType = modal.find("#selectResidentType");
        let name = modal.find("#name" + activityId);
        let contact_number = modal.find("#contact_number" + activityId);
        let radio = modal.find(":radio");
        let bookingType = modal.find("#bookingType").val(); // 🔥 Get current booking type

        if (unitNumber === "") {
            Swal.fire({
                icon: "warning",
                title: "Missing Information",
                text: "Please enter a unit number.",
            });
            return;
        }

        $.ajax({
            url: '/check-unit-frontend',
            type: "GET",
            data: {
                unit: unitNumber,
                activity_id: activityId,
                dateField: selectedDate
            },
            success: function (response) {
                let unitStatus = modal.find("#unitStatus");

                if (response.success) {
                    let count = response.count;
                    let maxBookings = response.maxBookings;
                    let statusText = `${count}/${maxBookings}`;
                    unitStatus.removeClass("text-muted text-danger text-success text-primary");

                    // ✅ Advanced Booking logic
                    if (bookingType === "Advanced Booking") {
                        if (count < maxBookings) {
                            // Slots available → allow booking
                            unitStatus.addClass("text-success").text(statusText);
                            enableFields();
                        } else {
                            // Full → block booking
                            unitStatus.addClass("text-danger").text(statusText + " (Max)");
                            disableFields();
                        }
                    }

                    // ✅ 24 Hrs logic (inverse)
                    // else if (bookingType === "24hrs") {
                    //     if (count >= maxBookings) {

                    //         unitStatus.addClass("text-primary").text(statusText + " (Eligible)");
                    //         enableFields();
                    //     } else {
                    //         unitStatus.addClass("text-danger").text(statusText + " (Advance Booking Required)");
                    //         disableFields();
                    //     }
                    // }
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

        // 🔥 Helper functions
        function enableFields() {
            submitButton.prop('disabled', false);
            checkUnit.prop('disabled', false);
            selectResidentType.prop('disabled', false);
            name.prop('disabled', false);
            contact_number.prop('disabled', false);
            radio.prop('disabled', false);
        }

        // function disableFields() {
        //     submitButton.prop('disabled', true);
        //     selectResidentType.prop('disabled', true);
        //     name.prop('disabled', true);
        //     contact_number.prop('disabled', true);
        //     radio.prop('disabled', true);
        // }
    });


    $('#activitySelectBooking').on('change', function () {
        const activitySelect = $(this); // ✅ Define it here
        const selectedActivityId = activitySelect.val();
        const selectedAmenityId = activitySelect.find(':selected').data('amenity-id');
        const dateFieldSearchFront = $('#dateFieldSearchFront'); // ✅ Corrected this line

        $('.all-slot-available-front').empty();
        $('#amenityIdBooking').val(selectedAmenityId);
        $('#unitStatus').text('0/0').attr('class', 'mt-1 text-muted');

        // ✅ Clear the date field and reset submit button state
        dateFieldSearchFront.val('').prop('disabled', false); // Clear date field and enable it
        $('.searchBtn').prop('disabled', true);  // Disable submit button initially

        // ✅ Reset the flatpickr instance if it's already initialized
        if (dateFieldSearchFront[0]._flatpickr) {
            dateFieldSearchFront[0]._flatpickr.destroy(); // Destroy the existing instance
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

                flatpickr(dateFieldSearchFront[0], {
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
                            $('.all-slot-available-front').empty();
                        } else {
                            $('.searchBtn').prop('disabled', true);
                            $('.all-slot-available-front').empty();
                        }
                    }
                });
            },
            error: function (xhr) {
                console.error('Failed to fetch blocked dates:', xhr.responseText);
            }
        });
    });


    $('.SearchSlotFront').submit(function (event) {
        event.preventDefault();

        let activityId = $('#activitySelectBooking').val();
        let amenityId = $('#activitySelectBooking option:selected').data('amenity-id');
        let dateField = $('#dateFieldSearchFront').val();

        $('#spinner').removeClass('d-none');

        $.ajax({
            url: '/fetch-all-slots-user',
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
                        let badgeClass = status === 'Available' ? 'bg-success' :
                            status === 'Booked' ? 'bg-danger' : 'bg-warning text-dark';
                        html += `<td><span class="badge ${badgeClass} text-uppercase">${status}</span></td>`;
                    });
                    html += '</tr>';
                });

                html += '</tbody></table>';
                $('.all-slot-available-front').html(html);
            },
            error: function (xhr) {
                $('#spinner').addClass('d-none');
                alert('An error occurred while fetching the data.');
            }
        });
    });

});
