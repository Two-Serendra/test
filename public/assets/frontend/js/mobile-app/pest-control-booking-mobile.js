$(function () {
    alert("🔥 JS VERSION 2026-06-15-002");
    const el = document.getElementById('resident_id_pc');

    logDebug("SELECT EXISTS: " + (el ? "YES" : "NO"));
    function logDebug(...args) {

        const msg = args.map(a =>
            typeof a === 'object'
                ? JSON.stringify(a)
                : a
        ).join(' ');

        console.log(msg);

        const el = document.getElementById('debugPanelPc');

        if (el) {
            el.innerHTML += msg + "<br>";
            el.scrollTop = el.scrollHeight;
        }
    }

    logDebug("🚀 Mobile Booking JS Loaded");

    window.pcState = {
        date: null,
        unit: null
    };

    const $bookingSlots = $('.pc-booking-slot');

    flatpickr("#PestControlBookingDate", {
        dateFormat: "Y-m-d",
        minDate: new Date().fp_incr(1),
        disableMobile: true,
        onChange: function (selectedDates, dateStr) {
            window.pcState.date = dateStr;
            console.log("DATE:", dateStr);
            logDebug("DATE CHANGED: " + dateStr);
            triggerUpdate();
        }

    });

    document.addEventListener('change', function (e) {
        if (e.target && e.target.id === 'resident_id_pc') {
            window.onResidentChange(e);
        }
    });

    window.onResidentChange = function (e) {
        const value = e.target.value;

        window.pcState.unit = value;

        if (window.Alpine && Alpine.store('superapp')) {
            Alpine.store('superapp').selectedUnit = value;
        }

        console.log("UNIT:", value);
        logDebug("UNIT CHANGED: " + value);
        logDebug("CHANGE FIRED");

        triggerUpdate();
    };


    function triggerUpdate() {
        const date = window.pcState.date;
        const unit = window.pcState.unit;
        logDebug("DATE=" + date);
        logDebug("UNIT=" + unit);


        if (!date || !unit) {
            $(".pc-booking-slot").prop("disabled", true);
            hideLoading();
            return;
        }

        updateSlots(date, unit);
    }

    function updateSlots(date, unitName) {

        logDebug("ENTERED updateSlots");

        logDebug("STEP 1 OK");

        try {

            if (typeof logDebug === "function") {
                logDebug("DEBUG OK", { date, unitName });
            } else {
                logDebug("logDebug missing");
            }

            logDebug("STEP 2 OK");
            showLoading();
            resetSlots();

            logDebug("STEP 3 OK - BEFORE AJAX");

            $.ajax({

                url: "/pest-control-booked-slots-mobile",
                type: "GET",
                data: { date, unit_name: unitName },

                beforeSend: function () {a
                    showLoading();
                },

                success: function (res) {

                    resetSlots();

                    disableBookedSlots(res.blocked_for_user || []);
                    disablePastSlots(date);

                    hideLoading();
                },

                error: function (xhr) {

                    console.log(xhr.responseText);
                    logDebug("ERROR " + xhr.status);

                    hideLoading();
                }

            });

        } catch (e) {

            logDebug("JS ERROR:\n" + e.message);

            logDebug("FULL ERROR:", e);
        }
    }

    window.resetSlots = function () {

        $('.pc-booking-slot').each(function () {

            $(this)
                .prop('checked', false)
                .prop('disabled', false);

            $('label[for="' + this.id + '"]')
                .removeClass('disabled btn-secondary')
                .addClass('btn-outline-primary')
                .css('cursor', 'pointer');
        });

    };

    function checkFormReady() {

        const date = window.pcState.date;
        const unit = window.pcState.unit;

        const ready = !!(date && unit);

        $("#saveUserPcBtn").prop("disabled", !ready);
    }

    function disableBookedSlots(bookedSlots) {

        bookedSlots.forEach(slot => {
            const $radio = $('.pc-booking-slot[data-slot="' + slot + '"]');

            if ($radio.length) {
                $radio.prop('disabled', true);

                $('label[for="' + $radio.attr('id') + '"]')
                    .removeClass('btn-outline-primary')
                    .addClass('btn-secondary disabled')
                    .css('cursor', 'not-allowed');
            }
        });
    }

    function disablePastSlots(selectedDate) {
        const now = new Date();
        const selected = new Date(selectedDate);
        if (now.toDateString() !== selected.toDateString()) return;
        const currentTime = now.getHours() * 60 + now.getMinutes();
        $('.pc-booking-slot').each(function () {
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


    logDebug(
        "SLOTS COUNT",
        document.querySelectorAll(".pc-booking-slot").length
    );

    $(document).ready(function () {
        logDebug("READY");
        logDebug(
            "SLOTS",
            document.querySelectorAll(".pc-booking-slot").length
        );
        $(".pc-booking-slot").prop("disabled", true);
    });

    window.resetAusiBookingUI = function () {
        logDebug("RESET CALLED");
        const form = document.getElementById('userPcNewBookingMobile');

        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }
        if (window.pcState) {
            window.pcState.date = null;
            window.pcState.unit = null;
        }
        if (window.Alpine && Alpine.store('superapp')) {
            Alpine.store('superapp').selectedUnit = '';
        }
        $('#resident_id_pc')
            .val('')
            .prop('selectedIndex', 0)
            .trigger('change');
        $('#mobile_email_pc').val('');
        $('#mobile_unit_name').val('');
        $('#mobile_unit_role').val('');
        const fp = document.querySelector('#PestControlBookingDate')?._flatpickr;

        if (fp) {
            fp.clear();
        }

        $('.pc-booking-slot').each(function () {
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

        $('#saveUserPcBtn')
            .prop('disabled', true)
            .html('<span class="btn-text">Submit</span>');
        $bookingSlots.prop('disabled', true);
        logDebug("RESET FINISHED");
    };

    let isSubmitting = false;

    $(document).on('submit', '#userPcNewBookingMobile', function (event) {
        event.preventDefault();
        const form = this;
        logDebug("SUBMIT FIRED");

        const selectedDate = $('#PestControlBookingDate').val();
        const selectedUnit = $('#resident_id_pc').val();
        const selectedSlot = $('input[name="booking_time_slot"]:checked').val();
        const $submitBtn = $('#saveUserPcBtn');

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
            const unit = $('#resident_id_pc').val();
            const role = $('#resident_id_pc option:selected').data('role') || '';
            $('#mobile_email_pc').val(email);
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
            //    logDebug(pair[0] + " = " + pair[1]);
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

                    logDebug("SUCCESS");
                    logDebug(JSON.stringify(res, null, 2));

                    if (res.debug) {
                        res.debug.forEach(d => logDebug("🧠 " + d));
                    }
                    Swal.fire({
                        icon: 'success',
                        title: 'Booking Successful',
                        text: res.message ?? 'Your booking has been saved',
                        timer: 5000,
                        showConfirmButton: false
                    });
                    window.resetAusiBookingUI();
                    window.resetSlots();
                },

                error: function (xhr) {
                    const res = xhr.responseJSON || {};
                    logDebug("ERROR", xhr.status);
                    logDebug("RESPONSE", res);

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

                        logDebug("VALIDATION ERRORS", messages);

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