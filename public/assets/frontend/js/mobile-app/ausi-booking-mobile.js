$(function () {
    alert("🔥 JS VERSION 2026-06-15-008");
    const el = document.getElementById('resident_id_ausi');

    alert("SELECT EXISTS: " + (el ? "YES" : "NO"));
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

    window.ausiState = {
        date: null,
        unit: null
    };

    const $bookingSlots = $('.ausi-booking-slot');

    flatpickr("#AusiBookingDate", {
        dateFormat: "Y-m-d",
        minDate: new Date().fp_incr(1),
        onChange: function (selectedDates, dateStr) {
            window.ausiState.date = dateStr;
            console.log("DATE:", dateStr);
            alert("DATE CHANGED: " + dateStr);
            triggerUpdate();
        }
    });

    document.addEventListener('change', function (e) {
        if (e.target && e.target.id === 'resident_id_ausi') {
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
        alert("UNIT CHANGED: " + value);
        alert("CHANGE FIRED");

        triggerUpdate();
    };


    function triggerUpdate() {
        const date = window.ausiState.date;
        const unit = window.ausiState.unit;

        console.log("TRIGGER:", { date, unit });

        if (!date || !unit) {
            $(".ausi-booking-slot").prop("disabled", true);
            hideLoading();
            return;
        }

        updateSlots(date, unit);
    }

    function updateSlots(date, unitName) {

        alert("ENTERED updateSlots");

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

                url: "/ausi-booked-slots-mobile",
                type: "GET",
                data: { date, unit_name: unitName },

                beforeSend: function () {
                    showLoading();
                },

                success: function (res) {

                    resetSlots();

                    if (res.blocked_for_user) {
                        disableBookedSlots(res.blocked_for_user);
                    }

                    disablePastSlots(date);

                    hideLoading();
                },

                error: function (xhr) {

                    console.log(xhr.responseText);
                    alert("ERROR " + xhr.status);

                    hideLoading();
                }

            });

        } catch (e) {

            alert("JS ERROR:\n" + e.message);

            logDebug("FULL ERROR:", e);
        }
    }

    window.resetSlots = function () {

        $('.ausi-booking-slot').each(function () {

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

        const date = window.ausiState.date;
        const unit = window.ausiState.unit;

        const ready = !!(date && unit);

        $("#saveUserAusiBtn").prop("disabled", !ready);
    }

    function disableBookedSlots(bookedSlots) {

        if (!Array.isArray(bookedSlots)) return;

        bookedSlots.forEach(function (slot) {

            const radio = $('.ausi-booking-slot[data-slot="' + slot + '"]');

            if (!radio.length) {
                console.warn("Slot not found:", slot);
                return;
            }

            radio.prop("disabled", true);

            const label = $('label[for="' + radio.attr("id") + '"]');

            label
                .removeClass("btn-outline-primary")
                .addClass("btn-secondary disabled")
                .css("cursor", "not-allowed");
        });
    }

    function disablePastSlots(selectedDate) {

        const now = new Date();
        const selected = new Date(selectedDate);

        if (now.toDateString() !== selected.toDateString()) return;

        const currentTime = now.getHours() * 60 + now.getMinutes();

        $('.ausi-booking-slot').each(function () {

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

    window.resetAusiBookingUI = function () {

        console.log("RESET CALLED");
        alert("RESET CALLED");

        const form = document.getElementById('userAusiNewBookingMobile');

        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }

        // Reset global state
        if (window.ausiState) {
            window.ausiState.date = null;
            window.ausiState.unit = null;
        }

        // Reset Alpine
        if (window.Alpine && Alpine.store('superapp')) {
            Alpine.store('superapp').selectedUnit = '';
        }

        // Reset dropdown
        $('#resident_id_ausi')
            .val('')
            .prop('selectedIndex', 0)
            .trigger('change');

        // Reset hidden fields
        $('#mobile_email').val('');
        $('#mobile_unit_name').val('');
        $('#mobile_unit_role').val('');

        // Reset flatpickr
        const fp = document.querySelector('#AusiBookingDate')?._flatpickr;

        if (fp) {
            fp.clear();
        }

        // Reset slots
        $('.ausi-booking-slot').each(function () {

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

        // Hide loading
        $('#slotLoading').addClass('d-none');

        // Reset submit button
        $('#saveUserAusiBtn')
            .prop('disabled', true)
            .html('<span class="btn-text">Submit</span>');

        console.log("RESET FINISHED");
    };

    let isSubmitting = false;

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
            const unit = $('#resident_id_ausi').val();
            const role = $('#resident_id_ausi option:selected').data('role') || '';
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
            //     alert(pair[0] + " = " + pair[1]);
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
                        timer: 2000,
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
});