$(function () {

    function logDebug(...args) {

        const msg = args.map(a =>
            typeof a === 'object'
                ? JSON.stringify(a)
                : a
        ).join(' ');

        console.log(msg);

        const el = document.getElementById('debugPanel');

        if (el) {
            el.innerHTML += msg + "<br>";
            el.scrollTop = el.scrollHeight;
        }
    }

    logDebug("🚀 Mobile Booking JS Loaded");

    flatpickr("#AusiBookingDate", {
        dateFormat: "Y-m-d",
        minDate: new Date().fp_incr(1),
        onChange: function (selectedDates, dateStr) {
            updateSlots(dateStr);
        }
    });

    document.addEventListener('change', (e) => {
        if (e.target.id === 'resident_id_ausi') {
            Alpine.store('superapp').selectedUnit = e.target.value;
            logDebug("SYNC STORE", e.target.value);
        }
    });

    function updateSlots(date) {

        logDebug("updateSlots", date);

        const unitName = document.querySelector('#resident_id_ausi')?.value || '';

        logDebug("unitName", unitName);

        if (!date || !unitName) {

            $(".ausi-booking-slot").prop("disabled", true);

            return;
        }

        logDebug("STORE TEST", Alpine.store('superapp'));
        logDebug("SELECTED UNIT", Alpine.store('superapp')?.selectedUnit);

        showLoading();
        resetSlots();

        $.ajax({
            url: "/ausi-booked-slots-mobile",
            type: "GET",
            data: {
                date,
                unit_name: unitName
            },

            success: function (res) {
                logDebug("SUCCESS", res);

                resetSlots();
                disableBookedSlots(res.blocked_for_user || []);
                disablePastSlots(date);
            },

            error: function (xhr) {
                logDebug("ERROR", {
                    status: xhr.status,
                    response: xhr.responseText
                });
            },

            complete: function () {
                logDebug("COMPLETE");
                hideLoading();
            }
        });
    }

    function checkFormReady() {

        const unit = $('#resident_id_ausi').val();
        const date = $('#AusiBookingDate').val();
        const slot = $('input[name="booking_time_slot"]:checked').val();

        const isReady = !!(unit && date && slot);

        $('#saveUserAusiBtn').prop('disabled', !isReady);
    }

    $(document).on('change', '#resident_id_ausi, #AusiBookingDate, input[name="booking_time_slot"]', checkFormReady);

    $(document).on(
        "change",
        "#AusiBookingDate",
        function () {

            updateSlots($(this).val());

        }
    );

    $(document).on(
        "change",
        "select[name='resident_id_ausi']",
        function () {

            const date =
                $("#AusiBookingDate").val();

            if (date) {

                updateSlots(date);

            }

        }
    );

    function resetSlots() {

        $(".ausi-booking-slot").each(function () {

            $(this)
                .prop("disabled", false)
                .prop("checked", false);

            $('label[for="' + this.id + '"]')
                .removeClass(
                    "disabled btn-secondary"
                )
                .addClass("btn-outline-primary")
                .css("cursor", "pointer");
            checkFormReady();

        });

    }

    function disableBookedSlots(bookedSlots) {

        bookedSlots.forEach(function (slot) {

            const radio =
                $('.ausi-booking-slot[data-slot="' + slot + '"]');

            if (!radio.length) return;

            radio.prop("disabled", true);

            $('label[for="' + radio.attr("id") + '"]')
                .removeClass("btn-outline-primary")
                .addClass("btn-secondary disabled")
                .css("cursor", "not-allowed");
        });

    }

    function showLoading() {

        $("#slotLoading").removeClass("d-none");

        $(".ausi-booking-slot").prop(
            "disabled",
            true
        );

    }

    function hideLoading() {

        $("#slotLoading").addClass("d-none");
    }

    function disablePastSlots(selectedDate) {

        if (!selectedDate) return;
        const now = new Date();

        $(".ausi-booking-slot").each(function () {
            const slot =
                $(this).data("slot");

            const start =
                slot.split(" - ")[0]
                    .replace("NN", "PM");

            const slotDate =
                new Date(
                    `${selectedDate} ${start}`
                );

            if (slotDate <= now) {

                $(this).prop(
                    "disabled",
                    true
                );

                $('label[for="' + $(this).attr("id") + '"]')
                    .removeClass("btn-outline-primary")
                    .addClass("btn-secondary disabled")
                    .css("cursor", "not-allowed");

            }
        });

    }


    logDebug(
        "SLOTS COUNT",
        document.querySelectorAll(".ausi-booking-slot").length
    );

    $(document).ready(function () {

        logDebug("READY");

        logDebug(
            "SLOTS",
            document.querySelectorAll(".ausi-booking-slot").length
        );

        $(".ausi-booking-slot").prop("disabled", true);

    });
    $(document).on('change', '#resident_id_ausi', function () {

        const selected = $(this).find(':selected');

        const name = selected.data('name');
        const role = selected.data('role');

        $('#mobile_unit_name').val(name || '');
        $('#mobile_unit_role').val(role || '');

        const store = Alpine.store('superapp');
        $('#mobile_email').val(store?.user?.email || '');
    });

    $(document).on('submit', '#userAusiNewBookingMobile', function (event) {
        event.preventDefault();
        const form = this;
        logDebug("SUBMIT FIRED");
        const selectedDate = $('#AusiBookingDate').val();
        const selectedUnit = $('#resident_id_ausi').val();
        const selectedSlot = $('input[name="booking_time_slot"]:checked').val();
        const $submitBtn = $('#saveUserAusiBtn');

        if (!selectedUnit) {
            logDebug("NO UNIT SELECTED");
            return;
        }

        if (!selectedDate) {
            logDebug("NO DATE SELECTED");
            return;
        }

        if (!selectedSlot) {
            logDebug("NO SLOT SELECTED");

            Swal.fire({
                icon: 'warning',
                title: 'Time Slot Required',
                text: 'Please select a booking time slot.'
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
                .html(`<span class="btn-text">Submit</span>`)
                .css('width', '');
        };

        const sendBooking = (forceOverride = false) => {
            alert("sending ajax");
            const formData = new FormData(form);
            if (forceOverride) {
                formData.append('force_override', true);
            }

            lockSubmitBtn();

            alert($(form).attr('action'));
            alert($(form).attr('method'));
            for (const pair of formData.entries()) {
                alert(pair[0] + " = " + pair[1]);
            }

            $.ajax({
                url: $(form).attr('action'),
                type: $(form).attr('method'),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: formData,
                processData: false,
                contentType: false,

                beforeSend: function () {
                    logToPanel("🚀 REQUEST SENT TO: " + form.action);
                },
                success: function (res) {

                    logToPanel("SUCCESS");
                    logToPanel(JSON.stringify(res, null, 2));

                    if (res.debug) {
                        res.debug.forEach(d => logToPanel("🧠 " + d));
                    }
                    Swal.fire({
                        icon: 'success',
                        title: 'Booking Successful',
                        text: res.message ?? 'Your booking has been saved',
                        timer: 2000,
                        showConfirmButton: false
                    });

                    form.reset();
                    resetSlots();

                },

                error: function (xhr) {

                    const res = xhr.responseJSON || {};

                    logDebug("ERROR", xhr.status);
                    logDebug("RESPONSE", res);

                    if (xhr.status === 422) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text: 'Please check required fields'
                        });
                        return;
                    }

                    if (xhr.status === 409 && res.type === 'slot_taken') {

                        Swal.fire({
                            icon: 'error',
                            title: 'Slot Already Taken',
                            text: res.message
                        });

                        updateSlots(selectedDate);
                        return;
                    }

                    if (xhr.status === 409 && res.type === 'unit_already_booked') {

                        Swal.fire({
                            icon: 'warning',
                            title: 'Already Booked',
                            text: res.message,
                            showCancelButton: true,
                            confirmButtonText: 'Book Anyway'
                        }).then((result) => {

                            if (result.isConfirmed) {
                                send(true);
                            } else {
                                unlockBtn();
                            }

                        });

                        return;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Something went wrong'
                    });

                },

                complete: function () {
                    unlockBtn();
                }
            });
        }
        sendBooking();
    });
});