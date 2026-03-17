$(document).ready(function () {
    // $('#bookingTabs a.booking-tab').on('shown.bs.tab', function (e) {
    //     let bookingType = $(e.target).data('value');
    //     let modal = $(this).closest(".modal");
    //     modal.find("#bookingType").val(bookingType);
    // });


    // $('.modal').each(function () {
    //     var modal = $(this);
    //     var bookingTabs = modal.find('#bookingTabs');
    //     var bookingTypeInput = modal.find('#bookingType');
    //     var unitNumber = modal.find('#residentSelect');
    //     var checkUnit = modal.find('.checkUnit');
    //     var unitStatus = modal.find('#unitStatus');
    //     var residentType = modal.find('#selectResidentType');
    //     var nameField = modal.find('[id^=name]');
    //     var contactField = modal.find('[id^=contact_number]');
    //     var dateField = modal.find('#dateField');
    //     var startTimeDropdown = modal.find('[id^=booking_start_time]');
    //     var endTimeDropdown = modal.find('[id^=booking_end_time]');
    //     var submitButton = modal.find('.activity-submit-btn');

    //     var bookingType = bookingTabs.find('a.active').data('value') || "Advanced Booking";
    //     bookingTypeInput.val(bookingType);
    //     updateFormState(bookingType);

    //     bookingTabs.find('a').on('click', function () {
    //         var bookingType = $(this).data('value');
    //         bookingTypeInput.val(bookingType);
    //         updateFormState(bookingType);
    //     });

    //     function updateFormState(bookingType) {
    //         resetFields(); //

    //         if (bookingType === "Walk-in") {
    //             checkUnit.hide();
    //             unitStatus.hide();
    //             toggleFields(true);
    //             dateField.prop('disabled', false);
    //             submitButton.prop('disabled', false);
    //             dateField.off('change.enableFields').on('change.enableFields', function () {
    //                 toggleFields(false);
    //             });
    //         }
    //         else if (bookingType === "Advanced Booking") {
    //             checkUnit.show().prop('disabled', false);
    //             unitStatus.show();
    //             toggleFields(true);
    //             dateField.prop('disabled', false);
    //             submitButton.prop('disabled', true);
    //         }
    //         else if (bookingType === "20hrs") {
    //             checkUnit.hide();
    //             unitStatus.hide();
    //             toggleFields(false);
    //             dateField.prop('disabled', false);
    //             submitButton.prop('disabled', false);
    //         }
    //     }

    //     function resetFields() {
    //         modal.find('input[type="text"], input[type="number"]').val('');
    //         modal.find('select').prop('selectedIndex', 0);
    //         dateField.val('');

    //         startTimeDropdown.prop('disabled', true).empty().append('<option>Select a Date First</option>');
    //         endTimeDropdown.prop('disabled', true).empty().append('<option>Select Start Time First</option>');

    //         unitStatus.text('').hide();

    //         modal.find('form').removeClass('was-validated');
    //         modal.find('input, select').removeClass('is-valid is-invalid');
    //     }

    //     function toggleFields(disable) {
    //         residentType.prop('disabled', disable);
    //         nameField.prop('disabled', disable);
    //         contactField.prop('disabled', disable);
    //         startTimeDropdown.prop('disabled', disable)
    //             .empty()
    //             .append('<option>' + (disable ? 'Select a Date First' : 'Select Start Time') + '</option>');
    //         endTimeDropdown.prop('disabled', disable)
    //             .empty()
    //             .append('<option>' + (disable ? 'Select start time first' : 'Select End Time') + '</option>');
    //     }
    // });

    $('.AddNewBooking').on('click', function () {
        showLoading();

        const activityId = $(this).data('activity-id');
        const modal = $('#modalActivity' + activityId);
        const amenityId = modal.find('#amenityId' + activityId).val();

        modal.data('activity-id', activityId);

        // Standard field selectors
        const bookingTabs = modal.find('#bookingTabs');
        const bookingTypeInput = modal.find('#bookingType');
        const dateField = modal.find('#dateField');
        const startTimeDropdown = modal.find('#booking_start_time' + activityId);
        const endTimeDropdown = modal.find('#booking_end_time' + activityId);
        const submitButton = modal.find('button[type="submit"]');
        const residentSelect = modal.find('#residentSelect');
        const selectResidentType = modal.find('#selectResidentType');
        const nameField = modal.find('#name' + activityId);
        const contactField = modal.find('#contact_number' + activityId);
        const checkUnit = modal.find('.checkUnit');
        const unitStatus = modal.find('#unitStatus');

        // Reset fields
        function resetFields() {
            modal.find('input[type="text"], input[type="number"]').val('');
            modal.find('select').prop('selectedIndex', 0);
            dateField.val('');
            startTimeDropdown.prop('disabled', true).empty().append('<option>Select a Date First</option>');
            endTimeDropdown.prop('disabled', true).empty().append('<option>Select Start Time First</option>');
            unitStatus.text('0/0').attr('class', 'mt-1 text-muted').hide();
            submitButton.prop('disabled', true);
            selectResidentType.prop('disabled', true);
            nameField.prop('disabled', true);
            // contactField.prop('disabled', true);
            checkUnit.prop('disabled', false).show();
            residentSelect.prop('disabled', false);
            modal.find('form').removeClass('was-validated');
            modal.find('input, select').removeClass('is-valid is-invalid');
        }

        resetFields();

        // Input mask for contact number
        contactField.off('input').on('input', function () {
            let v = $(this).val().replace(/\D/g, '');
            if (v.length > 11) v = v.substring(0, 11);
            $(this).val(v);
        });

        // Determine initial booking type
        const initialType = bookingTabs.find('a.active').data('value') || 'Advanced Booking';
        bookingTypeInput.val(initialType);

        // Fetch blocked dates
        $.ajax({
            url: '/fetch-blocked-dates',
            method: 'GET',
            data: { amenity_id: amenityId },
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

        // Booking tab change
        bookingTabs.find('a[data-bs-toggle="tab"]').off('shown.bs.tab').on('shown.bs.tab', function (e) {
            const type = $(e.target).data('value');
            bookingTypeInput.val(type);
            applyFieldToggles(type);

            const blocked = modal.data('blockedDates');
            if (blocked) setDatePicker(type, blocked);
        });

        // Apply booking type field toggles
        function applyFieldToggles(type) {
            resetFields();

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
                checkUnit.hide();
                unitStatus.show().text('0/0').attr('class', 'mt-1 text-muted');
                residentSelect.prop('disabled', false);
                selectResidentType.prop('disabled', false);
                nameField.prop('disabled', false);
                contactField.prop('disabled', false);
                dateField.prop('disabled', false);
                submitButton.prop('disabled', false);
            }

            // Always reset start/end time dropdowns
            startTimeDropdown.prop('disabled', true).empty().append('<option>Select a Date First</option>');
            endTimeDropdown.prop('disabled', true).empty().append('<option>Select Start Time First</option>');
            dateField.val('');
        }

        // Initialize flatpickr
        function setDatePicker(type, blockedDates) {
            const df = dateField[0];
            if (df._flatpickr) df._flatpickr.destroy();

            const today = new Date();
            today.setHours(0, 0, 0, 0);

            let minDate = today;
            let maxDate = new Date(today);

            if (type === '20hrs') maxDate.setDate(today.getDate() + 1);
            else maxDate.setDate(today.getDate() + 9);

            flatpickr(df, {
                enableTime: false,
                dateFormat: 'Y-m-d',
                minDate,
                maxDate,
                altInput: true,
                altFormat: 'F j, Y',
                disable: blockedDates,
                onChange: function (selectedDates, dateStr) {
                    if (!dateStr) return;
                    startTimeDropdown.prop('disabled', false);
                    endTimeDropdown.prop('disabled', true).empty().append('<option>Select Start Time First</option>');
                    modal.find(`#userAvailableSlotsContainer${activityId}`).empty();
                    unitStatus.text('0/0').removeClass('text-success text-danger text-primary').addClass('text-muted');
                    fetchAvailableStartTimes(modal, dateStr, activityId);
                }
            });
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



    function fetchAvailableStartTimes(modal, bookingDate, activityId) {
        const startTimeDropdown = modal.find('.booking_start_time');
        const endTimeDropdown = modal.find('.booking_end_time');
        startTimeDropdown.prop('disabled', true).empty().append('<option>Loading...</option>');
        endTimeDropdown.prop('disabled', true).empty().append('<option>Select a start time first</option>');

        if (!bookingDate || !activityId) return;

        $.ajax({
            url: '/fetch-available-times-user',
            method: 'GET',
            data: {
                activity_id: activityId, booking_date: bookingDate,
                booking_type: modal.find('#bookingType').val()
            },
            success: function (availableTimePairs) {
                console.log("Available Times Response:", availableTimePairs);
                startTimeDropdown.empty();
                endTimeDropdown.empty();

                // if (availableTimePairs.error) {
                //     startTimeDropdown.append('<option>No Schedule</option>').prop('disabled', true);
                //     endTimeDropdown.append('<option>No Schedule</option>').prop('disabled', true);
                //     return;
                // }

                // if (availableTimePairs.length > 0) {
                //     startTimeDropdown.append('<option>Select Start Time</option>');
                //     availableTimePairs.forEach(pair => {
                //         startTimeDropdown.append(`<option value="${pair.start}">${pair.start}</option>`);
                //     });
                //     startTimeDropdown.prop('disabled', false);
                // } else {
                //     console.warn("No available times - Fully Booked");
                //     startTimeDropdown.append('<option>Fully Booked</option>').prop('disabled', true);
                //     endTimeDropdown.append('<option>Fully Booked</option>').prop('disabled', true);
                // }

                if (availableTimePairs.length > 0) {
                    startTimeDropdown.append('<option>Select Start Time</option>');
                    availableTimePairs.forEach(pair => {
                        startTimeDropdown.append(`<option value="${pair.start}">${pair.start}</option>`);
                    });
                    startTimeDropdown.prop('disabled', false);
                } else {
                    let msg = "Fully Booked";
                    const bookingType = modal.find('#bookingType').val();
                    const selectedDate = bookingDate;
                    const today = new Date();
                    const tomorrow = new Date(today);
                    tomorrow.setDate(today.getDate() + 1);
                    const tomorrowStr = tomorrow.toISOString().split('T')[0];

                    if (bookingType === '20hrs' && selectedDate === tomorrowStr) {
                        msg = "Beyond 20 hours. Contact concierge.";
                    }

                    startTimeDropdown.append(`<option>${msg}</option>`).prop('disabled', true);
                    endTimeDropdown.append(`<option>${msg}</option>`).prop('disabled', true);
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
        // console.log("fetchAvailableEndTimes - Activity ID:", activityId);
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


        const $btn = $('.activity-submit-btn');
        const originalWidth = $btn.outerWidth();
        $btn
            .attr('disabled', true)
            .html(`<div class="spinner-border spinner-border-sm text-light"></div>`)
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

                // let swalTitle = response.success ? 'Booking Successful!' : 'Booking Failed';
                // let swalText = response.message;

                // Swal.fire({
                //     icon: response.success ? 'success' : 'error',
                //     title: swalTitle,
                //     text: swalText,
                //     timer: 3000,
                //     showConfirmButton: true
                // }).then(() => {
                //     if (response.success) {
                //         // Reset form
                //         form.reset();
                //         form.classList.remove('was-validated');
                //         $('.selected-slots-user').removeClass('selected-slots-user');
                //         $('#selectedSlotsInputUser').val(0);
                //     }
                // });

                Swal.fire({
                    icon: 'success',
                    title: 'Booking Submitted!',
                    text: response.message || 'Your booking has been successfully submitted.',
                    timer: 2000,
                    showConfirmButton: false
                });

                form.reset();
                form.classList.remove('was-validated');
                $('.selected-slots-user').removeClass('selected-slots-user');
                $('#selectedSlotsInputUser').val(0);
            },
            error: function (xhr) {
                let message = 'Something went wrong. Please try again.';

                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    const firstError = Object.values(xhr.responseJSON.errors)[0][0];
                    message = firstError;
                }

                else if (xhr.responseJSON?.message) {
                    message = xhr.responseJSON.message;
                }
                else if (xhr.status === 409) {
                    message = xhr.responseText || message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Booking Failed',
                    text: message,
                    confirmButtonText: 'OK'
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
        let unitNumber = modal.find(".selectResidentType option:selected").data('unit')?.toString().trim();
        let selectResidentType = modal.find(".selectResidentType")
        let submitButton = modal.find("#submitButton" + activityId);
        let checkUnit = modal.find(".checkUnit");
        let selectedDate = modal.find("#dateField").val()?.trim();
        let name = modal.find("#name" + activityId);
        let contact_number = modal.find("#contact_number" + activityId);
        let radio = modal.find(":radio");
        let bookingType = modal.find("#bookingType").val();

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
                    if (bookingType === "Advanced Booking") {
                        if (count < maxBookings) {
                            unitStatus.addClass("text-success").text(statusText);
                            enableFields();
                        } else {
                            unitStatus.addClass("text-danger").text(statusText + " (Max)");
                            disableFields();
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
            name.prop('disabled', false);
            // contact_number.prop('disabled', false);
            radio.prop('disabled', false);
        }
    });


    $('#activitySelectBooking').on('change', function () {
        const activitySelect = $(this);
        const selectedActivityId = activitySelect.val();
        const selectedAmenityId = activitySelect.find(':selected').data('amenity-id');
        const dateFieldSearchFront = $('#dateFieldSearchFront');

        $('.all-slot-available-front').empty();
        $('#amenityIdBooking').val(selectedAmenityId);
        $('#unitStatus').text('0/0').attr('class', 'mt-1 text-muted');

        dateFieldSearchFront.val('').prop('disabled', false);
        $('.searchBtn').prop('disabled', true);

        if (dateFieldSearchFront[0]._flatpickr) {
            dateFieldSearchFront[0]._flatpickr.destroy();
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

    // When the resident select changes
    // $(".selectResidentType").on("change", function () {
    //     let select = $(this);
    //     let modal = select.closest(".modal");

    //     // Reset unit status
    //     let unitStatus = modal.find("#unitStatus");
    //     unitStatus.text("0/0").removeClass("text-success text-danger text-primary").addClass("text-muted");

    //     // Disable fields
    //     let activityId = modal.attr("id")?.replace("modalActivity", "");
    //     let submitButton = modal.find("#submitButton" + activityId);
    //     let name = modal.find("#name" + activityId);
    //     let radio = modal.find(":radio");

    //     submitButton.prop("disabled", true);
    //     name.prop("disabled", true);

    //     radio.prop("disabled", true);
    // });

    $(".selectResidentType").on("change", function () {
        let select = $(this);
        let modal = select.closest(".modal");

        // Get booking type
        let bookingType = modal.find("#bookingType").val();

        // Reset unit status
        let unitStatus = modal.find("#unitStatus");
        unitStatus.text("0/0")
            .removeClass("text-success text-danger text-primary")
            .addClass("text-muted");

        // Fields
        let activityId = modal.attr("id")?.replace("modalActivity", "");
        let submitButton = modal.find("#submitButton" + activityId);
        let name = modal.find("#name" + activityId);
        let radio = modal.find(":radio");

        // Disable name and radios
        name.prop("disabled", true);
        radio.prop("disabled", true);

        // ✅ Exception for 20hrs booking
        if (bookingType !== "20hrs") {
            submitButton.prop("disabled", true);
        }
    });

    $(document).on('click', '.activity-booking-details', function () {
        let b = $(this).data('id');
        showLoading();

        $.ajax({
            url: '/resident/activity-booking/details/' + b,
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
                $('#detail-activity-name').text(booking.activity?.activity_name ?? 'N/A');
                $('#detail-start-time').text(`${startTime} - ${endTime}` ?? 'N/A');
                $('#detail-contact').text(booking.contact ?? 'N/A');
                $('#detail-booking-date').text(formattedDate ?? 'N/A');
                $('#detail-transaction-no').data('booking-id', booking.id);


                let residentBadgeClass = '';
                if (booking.resident_type === 'TENANT') residentBadgeClass = 'badge badge-forge bg-danger';
                else if (booking.resident_type === 'OWNER') residentBadgeClass = 'badge badge-forge bg-primary';
                else residentBadgeClass = 'bg-secondary text-white';

                $('#detail-resident-type').html(
                    `<span class="badge ${residentBadgeClass}">${booking.resident_type ?? 'N/A'}</span>`
                );

                let statusText = '';
                let statusClass = '';

                if (bookingDate < today) {
                    statusText = 'Completed';
                    statusClass = 'badge bg-success';
                    $('#cancelAmenityBookingBtn').hide();
                } else {
                    switch (booking.booking_status) {
                        case 1: // Confirmed
                            statusText = 'Confirmed';
                            statusClass = 'badge bg-success';
                            $('#cancelAmenityBookingBtn').show();
                            break; // <-- stop here

                        case 2: // Cancelled
                            statusText = 'Cancelled';
                            statusClass = 'badge bg-danger';
                            $('#cancelAmenityBookingBtn').hide();
                            break;
                        case 3: // Penalty
                            statusText = 'Penalty';
                            statusClass = 'badge bg-warning';
                            $('#cancelAmenityBookingBtn').hide();
                            break;

                        case 4: // No Show
                            statusText = 'No Show';
                            statusClass = 'badge bg-dark';
                            $('#cancelAmenityBookingBtn').hide();
                            break;

                        default:
                            statusText = 'N/A';
                            statusClass = 'badge bg-secondary';
                            $('#cancelAmenityBookingBtn').hide();
                    }
                }

                $('#detail-booking-status').html(`<span class="${statusClass} badge badge-forge">${statusText}</span>`);

                $('#residentActivityBookingDetailsModal').modal('show');
            },
            error: function () {
                alert('Booking not found.');
            },
            complete: function () {
                hideLoading();
            }
        });
    });


    $(document).on('click', '#cancelAmenityBookingBtn', function () {

        const bookingEl = $('#detail-transaction-no');
        const bookingId = bookingEl.data('booking-id');

        // Step 1: Ask backend if confirmation is required
        $.ajax({
            url: `/resident/activity-booking/cancel/${bookingId}`,
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (res) {

                if (!res.success) {
                    Swal.fire('Error', res.message || 'Failed to cancel booking.', 'error');
                    return;
                }

                // If confirmation is required (penalty), show warning modal
                if (res.requires_confirmation) {
                    Swal.fire({
                        title: 'Cancel Booking',
                        html: 'Are you sure you want to cancel this booking?' + res.message,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, cancel it',
                        cancelButtonText: 'No, keep it'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Step 2: Send final confirmation to cancel
                            $.ajax({
                                url: `/resident/activity-booking/cancel/${bookingId}`,
                                method: 'POST',
                                data: {
                                    _token: $('meta[name="csrf-token"]').attr('content'),
                                    confirm: 1
                                },
                                success: function (res2) {
                                    Swal.fire('Cancelled!', res2.message, 'success').then(() => {
                                        $('#residentActivityBookingDetailsModal').modal('hide');
                                        if (typeof refreshBookingTable === 'function') refreshBookingTable();
                                    });
                                },
                                error: function () {
                                    Swal.fire('Error', 'Something went wrong while cancelling.', 'error');
                                }
                            });
                        }
                    });
                } else {
                    // No confirmation required, just cancel
                    Swal.fire('Cancelled!', res.message, 'success').then(() => {
                        $('#residentActivityBookingDetailsModal').modal('hide');
                        if (typeof refreshBookingTable === 'function') refreshBookingTable();
                    });
                }
            },
            error: function () {
                Swal.fire('Error', 'Something went wrong.', 'error');
            }
        });

    });



});
