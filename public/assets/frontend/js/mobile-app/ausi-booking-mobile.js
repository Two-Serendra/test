$(function () {
    alert("🔥 JS VERSION 2026-06-15-009");
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

            // Save the selected date
            window.ausiState.date = dateStr;

            console.log("DATE:", dateStr);
            alert("DATE CHANGED: " + dateStr);

            // Try updating if both date and unit are available
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
                    alert("AJAX STARTED");
                },

                success: function (res) {

                    alert("SUCCESS");

                    logDebug("API RESPONSE:", res);

                    resetSlots();
                    if (res.blocked_for_user && Array.isArray(res.blocked_for_user)) {
                        disableBookedSlots(res.blocked_for_user);
                    }
                    disablePastSlots(date);
                    logDebug("BLOCKED SLOTS:", res.blocked_for_user);
                },

                error: function (xhr) {
                    alert("ERROR " + xhr.status);
                    console.log(xhr.responseText);
                },

                complete: function () {
                    alert("COMPLETE");
                }
            });

        } catch (e) {

            alert("JS ERROR:\n" + e.message);

            console.error("FULL ERROR:", e);
        }
    }

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
        });
        checkFormReady();
    }

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

        $(".ausi-booking-slot").prop(
            "disabled",
            true
        );
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

    function resetAusiBookingUI() {
        const form = document.getElementById('userAusiNewBookingMobile');
        isResetting = true;

        form.reset();
        form.classList.remove('was-validated');
        const store = Alpine.store('superapp');
        store.selectedUnit = null;
        Alpine.store('superapp').selectedUnit = '';
        $('#mobile_email').val('');
        $('#mobile_unit_name').val('');
        $('#mobile_unit_role').val('');
        const fp = document.querySelector("#AusiBookingDate")?._flatpickr;
        if (fp) fp.clear();
        $(".ausi-booking-slot").each(function () {
            $(this)
                .prop("disabled", true)
                .prop("checked", false);

            $('label[for="' + this.id + '"]')
                .removeClass("btn-secondary disabled")
                .addClass("btn-outline-primary")
                .css("cursor", "pointer");
        });
        $("#slotLoading").addClass("d-none");
        $("#saveUserAusiBtn")
            .prop("disabled", true)
            .html('<span class="btn-text">SUBMIT</span>');

        isResetting = false;
    }



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
            isSubmitting = true;

            $submitBtn
                .prop('disabled', true)
                .html(`<div class="spinner-border spinner-border-sm text-light"></div>`);
        };

        const unlockSubmitBtn = () => {
            isSubmitting = false;

            $submitBtn
                .prop('disabled', false)
                .html(`<span class="btn-text">SUBMIT</span>`)
                .css('width', '');
        };

        const sendBooking = (forceOverride = false) => {
            const store = Alpine.store('superapp');
            $('#mobile_email').val(store?.user?.email || '');
            const formData = new FormData(form);
            if (forceOverride) {
                formData.append('force_override', true);
            }

            lockSubmitBtn();
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
                    resetAusiBookingUI();
                    $bookingSlots.prop('disabled', true);
                    resetSlots()
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
        }
        sendBooking();
    });
});