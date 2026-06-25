$(function () {
    alert("🔥 JS VERSION 2026-06-15-034");
    const el = document.getElementById('resident_id_gt');

    logDebugGt("SELECT EXISTS: " + (el ? "YES" : "NO"));
    function logDebugGt(...args) {

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

    logDebugGt("🚀 Mobile Booking JS Loaded");

    window.ausiState = {
        date: null,
        unit: null
    };

    const $bookingSlots = $('.gt-booking-slot');

    flatpickr("#GtBookingDate", {
        dateFormat: "Y-m-d",
        minDate: new Date().fp_incr(1),
        disableMobile: true,
        onChange: function (selectedDates, dateStr) {
            window.ausiState.date = dateStr;
            console.log("DATE:", dateStr);
            logDebugGt("DATE CHANGED: " + dateStr);
            triggerUpdateGt();
        }

    });

    document.addEventListener('change', function (e) {
        if (e.target && e.target.id === 'resident_id_gt') {
            window.onResidentChange(e);
        }
    });

    window.onResidentChange = function (e) {
        const value = e.target.value;

        window.ausiState.unit = value;

        if (window.Alpine && Alpine.store('superapp')) {
            Alpine.store('superapp').selectedUnit = value;
        }

        console.log("UNIT:", value);
        logDebugGt("UNIT CHANGED: " + value);
        logDebugGt("CHANGE FIRED");

        triggerUpdateGt();
    };


    function triggerUpdateGt() {
        const date = window.ausiState.date;
        const unit = window.ausiState.unit;

        console.log("TRIGGER:", { date, unit });

        if (!date || !unit) {
            $(".gt-booking-slot").prop("disabled", true);
            hideLoading();
            return;
        }

        updateGtSlots(date, unit);
    }

    function updateGtSlots(date, unitName) {

        logDebugGt("ENTERED updateGtSlots");

        logDebugGt("STEP 1 OK");

        try {

            if (typeof logDebugGt === "function") {
                logDebugGt("DEBUG OK", { date, unitName });
            } else {
                logDebugGt("logDebugGt missing");
            }

            logDebugGt("STEP 2 OK");
            showLoading();
            resetGtSlots();

            logDebugGt("STEP 3 OK - BEFORE AJAX");

            $.ajax({

                url: "/gt-booked-slots-mobile",
                type: "GET",
                data: { date, unit_name: unitName },

                beforeSend: function () {
                    showLoading();
                },

                success: function (res) {

                    resetGtSlots();

                    disableBookedGtSlots(res.blocked_for_user || []);
                    disableGtPastSlots(date);

                    hideLoading();
                },

                error: function (xhr) {

                    console.log(xhr.responseText);
                    logDebugGt("ERROR " + xhr.status);

                    hideLoading();
                }

            });

        } catch (e) {

            logDebugGt("JS ERROR:\n" + e.message);

            logDebugGt("FULL ERROR:", e);
        }
    }

    window.resetGtSlots = function () {

        $('.gt-booking-slot').each(function () {

            $(this)
                .prop('checked', false)
                .prop('disabled', false);

            $('label[for="' + this.id + '"]')
                .removeClass('disabled btn-secondary')
                .addClass('btn-outline-primary')
                .css('cursor', 'pointer');
        });

    };

    function checkGtFormReady() {

        const date = window.ausiState.date;
        const unit = window.ausiState.unit;

        const ready = !!(date && unit);

        $("#saveUserGtBtn").prop("disabled", !ready);
    }

    function disableBookedGtSlots(bookedSlots) {

        bookedSlots.forEach(slot => {
            const $radio = $('.gt-booking-slot[data-slot="' + slot + '"]');

            if ($radio.length) {
                $radio.prop('disabled', true);

                $('label[for="' + $radio.attr('id') + '"]')
                    .removeClass('btn-outline-primary')
                    .addClass('btn-secondary disabled')
                    .css('cursor', 'not-allowed');
            }
        });
    }

    function disableGtPastSlots(selectedDate) {
        const now = new Date();
        const selected = new Date(selectedDate);
        if (now.toDateString() !== selected.toDateString()) return;
        const currentTime = now.getHours() * 60 + now.getMinutes();
        $('.gt-booking-slot').each(function () {
            const slotText = $(this).data('slot');

            if (!slotText) return;

            const match = slotText.match(/(\d+):(\d+)\s*(AM|PM)/i);

            if (!match) return;

            let hour = parseInt(match[1]);
            let minute = parseInt(match[2]);
            let period = match[3].toUpperCase();

            if (period === "PM" && hour !== 12) hour += 12;
            if (period === "AM" && hour === 12) hour = 0;

            const slotMinutes = hour * 60 + minute;

            if (slotMinutes < currentTime) {

                $(this).prop("disabled", true);

                $('label[for="' + this.id + '"]')
                    .addClass("btn-secondary disabled")
                    .removeClass("btn-outline-primary")
                    .css("cursor", "not-allowed");
            }
        });
    }

    function showLoading() {
        $("#slotLoading").removeClass("d-none");
    }

    function hideLoading() {
        $("#slotLoading").addClass("d-none");
    }


    logDebugGt(
        "SLOTS COUNT",
        document.querySelectorAll(".gt-booking-slot").length
    );

    $(document).ready(function () {
        logDebugGt("READY");
        logDebugGt(
            "SLOTS",
            document.querySelectorAll(".gt-booking-slot").length
        );
        $(".gt-booking-slot").prop("disabled", true);
    });

    window.resetGtBookingUI = function () {
        logDebugGt("RESET CALLED");
        const form = document.getElementById('userGtNewBookingMobile');

        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }
        if (window.ausiState) {
            window.ausiState.date = null;
            window.ausiState.unit = null;
        }
        if (window.Alpine && Alpine.store('superapp')) {
            Alpine.store('superapp').selectedUnit = '';
        }
        $('#resident_id_gt')
            .val('')
            .prop('selectedIndex', 0)
            .trigger('change');
        $('#mobile_email').val('');
        $('#mobile_unit_name').val('');
        $('#mobile_unit_role').val('');
        const fp = document.querySelector('#GtBookingDate')?._flatpickr;

        if (fp) {
            fp.clear();
        }

        $('.gt-booking-slot').each(function () {
            $(this)
                .prop('checked', false)
                .prop('disabled', true);

            $('label[for="' + this.id + '"]')
                .removeClass('btn-outline-primary')
                .removeClass('btn-secondary')
                .removeClass('disabled')
                .addClass('btn-secondary disabled')
                .css('cursor', 'not-allowed');
        });
        $('#slotLoading').addClass('d-none');

        $('#saveUserAusiBtn')
            .prop('disabled', true)
            .html('<span class="btn-text">Submit</span>');
        $bookingSlots.prop('disabled', true);
        logDebugGt("RESET FINISHED");
    };

    let isSubmitting = false;

    $(document).on('submit', '#userGtNewBookingMobile', function (event) {
        event.preventDefault();
        const form = this;
        logDebugGt("SUBMIT FIRED");

        const selectedDate = $('#GtBookingDate').val();
        const selectedUnit = $('#resident_id_gt').val();
        const selectedSlot = $('input[name="booking_time_slot"]:checked').val();
        const $submitBtn = $('#saveUserAusiBtn');

        form.classList.add('was-validated');

        if (!form.checkValidity()) {

            if (!selectedSlot) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Time Slot Required',
                    text: 'Please select a booking time slot.'
                });
            }

            return;
        }

        form.classList.remove('was-validated');
        const store = Alpine.store('superapp');
        const email = store?.user?.email || '';

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
            const store = Alpine.store('superapp');
            const email = store?.user?.email || '';
            const unit = $('#resident_id_gt').val();
            const role = $('#resident_id_gt option:selected').data('role') || '';
            $('#mobile_email').val(email);
            $('#mobile_unit_name').val(unit);
            $('#mobile_unit_role').val(role);

            console.log("SYNC CHECK:", {
                email,
                unit,
                role
            });

            const formData = new FormData(form);
            if (forceOverride) {
                formData.append('force_override', true);
            }

            lockSubmitBtn();
            // for (const pair of formData.entries()) {
            //    logDebugGt(pair[0] + " = " + pair[1]);
            // }

            $.ajax({
                url: $(form).attr('action'),
                type: $(form).attr('method'),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {

                    logDebugGt("SUCCESS");
                    logDebugGt(JSON.stringify(res, null, 2));

                    if (res.debug) {
                        res.debug.forEach(d => logDebugGt("🧠 " + d));
                    }
                    Swal.fire({
                        icon: 'success',
                        title: 'Booking Successful',
                        text: res.message ?? 'Your booking has been saved',
                        timer: 5000,
                        showConfirmButton: false
                    });
                    window.resetGtBookingUI();
                    window.resetGtSlots();
                },

                error: function (xhr) {
                    const res = xhr.responseJSON || {};
                    logDebugGt("ERROR", xhr.status);
                    logDebugGt("RESPONSE", res);

                    if (xhr.status === 422) {

                        const res = xhr.responseJSON || {};
                        const errors = res.errors || {};

                        let messages = [];

                        Object.keys(errors).forEach(field => {
                            errors[field].forEach(msg => {
                                messages.push(msg);
                            });
                        });

                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            html: messages.join('<br>')
                        });

                        logDebugGt("VALIDATION ERRORS", messages);

                        return;
                    }

                    if (xhr.status === 409 && res.type === 'slot_taken') {

                        Swal.fire({
                            icon: 'error',
                            title: 'Slot Already Taken',
                            text: res.message
                        });

                        updateGtSlots(selectedDate);
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
                                sendBooking(true);
                            } else {
                                unlockSubmitBtn();
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
                complete() {
                    unlockSubmitBtn();
                }
            });
        };
        sendBooking();
    });

    $(document).on('click', '.service-link', function () {

        $('#pageLoader').removeClass('d-none');

        const url = $(this).attr('href');

        setTimeout(function () {
            window.location.href = url;
        }, 100);

        return false;
    });
});