$(function () {
    alert("🔥 JS VERSION 2026-06-15-045");
    const el = document.getElementById('resident_id_ausi_mobile');

    logDebug("SELECT EXISTS: " + (el ? "YES" : "NO"));
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

    document.addEventListener('change', function (e) {
        if (e.target && e.target.id === 'resident_id_ausi_mobile') {
            window.onResidentChangeAusiMobile(e);
        }
    });

    window.onResidentChangeAusiMobile = function (e) {
        const value = e.target.value;

        window.ausiState.unit = value;

        if (window.Alpine && Alpine.store('superapp')) {
            Alpine.store('superapp').selectedUnit = value;
        }
        logDebug("UNIT CHANGED: " + value);
        logDebug("CHANGE FIRED");
        triggerUpdate();
    };


    flatpickr("#AusiBookingDateMobile", {
        dateFormat: "Y-m-d",
        minDate: new Date().fp_incr(1),
        disableMobile: true,
        onChange: function (selectedDates, dateStr) {
            window.ausiState.date = dateStr;
            console.log("DATE:", dateStr);
            logDebug("DATE CHANGED: " + dateStr);
            triggerUpdate();
        }

    });


    function triggerUpdate() {
        const date = window.ausiState.date;
        const unit = window.ausiState.unit;
        logDebug("DATE=" + date);
        logDebug("UNIT=" + unit);

        if (!date || !unit) {
            $(".ausi-booking-slot").prop("disabled", true);
            hideLoadingAusi();
            return;
        }

        updateSlots(date, unit);
    }

    function updateSlots(date, unit) {

        logDebug("ENTERED updateSlots");

        logDebug("STEP 1 OK");

        try {

            if (typeof logDebug === "function") {
                logDebug("DEBUG OK", { date, unit });
            } else {
                logDebug("logDebug missing");
            }

            logDebug("STEP 2 OK");
            showLoadingAusi();
            resetSlotsAusiMobile();

            logDebug("STEP 3 OK - BEFORE AJAX");

            $.ajax({

                url: "/ausi-booked-slots-mobile",
                type: "GET",
                data: { date, unit_name: unit },

                beforeSend: function () {
                    showLoadingAusi();
                },

                success: function (res) {

                    resetSlotsAusiMobile();

                    disableBookedSlots(res.blocked_for_user || []);
                    disablePastSlots(date);

                    hideLoadingAusi();
                },

                error: function (xhr) {

                    console.log(xhr.responseText);
                    logDebug("ERROR " + xhr.status);

                    hideLoadingAusi();
                }

            });

        } catch (e) {

            logDebug("JS ERROR:\n" + e.message);

            logDebug("FULL ERROR:", e);
        }
    }

    window.resetSlotsAusiMobile = function () {

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

        bookedSlots.forEach(slot => {
            const $radio = $('.ausi-booking-slot[data-slot="' + slot + '"]');

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

    function showLoadingAusi() {
        $("#slotLoadingAusi").removeClass("d-none");
    }

    function hideLoadingAusi() {
        $("#slotLoadingAusi").addClass("d-none");
    }


    // logDebug(
    //     "SLOTS COUNT",
    //     document.querySelectorAll(".ausi-booking-slot").length
    // );

    $(document).ready(function () {
        logDebug("READY");
        logDebug(
            "SLOTS",
            document.querySelectorAll(".ausi-booking-slot").length
        );
        $(".ausi-booking-slot").prop("disabled", true);
    });

    window.resetAusiBookingUI = function () {
        logDebug("RESET CALLED");
        const form = document.getElementById('userAusiNewBookingMobile');

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
        $('#resident_id_ausi_mobile')
            .val('')
            .prop('selectedIndex', 0)
            .trigger('change');
        $('#mobile_email').val('');
        $('#mobile_unit_name').val('');
        $('#mobile_unit_role').val('');
        const fp = document.querySelector('#AusiBookingDateMobile')?._flatpickr;

        if (fp) {
            fp.clear();
        }

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
        $('#slotLoading').addClass('d-none');

        $('#saveUserAusiBtn')
            .prop('disabled', true)
            .html('<span class="btn-text">Submit</span>');
        $bookingSlots.prop('disabled', true);
        logDebug("RESET FINISHED");
    };

    let isSubmitting = false;

    $(document).on('submit', '#userAusiNewBookingMobile', function (event) {
        event.preventDefault();
        const form = this;
        logDebug("SUBMIT FIRED");

        const selectedDate = $('#AusiBookingDateMobile').val();
        const selectedUnit = $('#resident_id_ausi_mobile').val();
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
            const unit = $('#resident_id_ausi_mobile').val();
            const role = $('#resident_id_ausi_mobile option:selected').data('role') || '';
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
            //    logDebug(pair[0] + " = " + pair[1]);
            // }

            logDebug("===== REQUEST DEBUG =====");
            logDebug("CSRF Token:");
            logDebug($('meta[name="csrf-token"]').attr('content'));

            logDebug("Document Cookies:");
            logDebug(document.cookie);

            logDebug("Request URL:");
            logDebug($(form).attr('action'));

            logDebug("Method:");
            logDebug($(form).attr('method'));

            $.ajax({
                url: $(form).attr('action'),
                type: $(form).attr('method'),
                xhrFields: {
                    withCredentials: true
                },
                crossDomain: true,
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
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Booking submitted successfully',
                        showConfirmButton: false,
                        timer: 3000,
                        customClass: {
                            popup: 'swal2-success-toast'
                        }
                    });
                    window.resetAusiBookingUI();
                    window.resetSlotsAusiMobile();
                },

                error: function (xhr, textStatus, errorThrown) {

                    logDebug("========== AJAX ERROR ==========");
                    logDebug("Status Code: " + xhr.status);
                    logDebug("Status Text: " + xhr.statusText);
                    logDebug("Text Status: " + textStatus);
                    logDebug("Error Thrown: " + errorThrown);

                    logDebug("Response Headers:");
                    logDebug(xhr.getAllResponseHeaders());

                    logDebug("Response Text:");
                    logDebug(xhr.responseText);

                    if (xhr.responseJSON) {
                        logDebug("Response JSON:");
                        logDebug(JSON.stringify(xhr.responseJSON, null, 2));
                    }

                    const res = xhr.responseJSON || {};

                    if (xhr.status === 419) {

                        logDebug("❌ CSRF TOKEN MISMATCH DETECTED");

                        Swal.fire({
                            icon: 'error',
                            title: 'Session Expired',
                            text: 'CSRF Token Mismatch (419)'
                        });

                        return;
                    }

                    if (xhr.status === 422) {

                        const errors = res.errors || {};
                        let messages = [];

                        Object.keys(errors).forEach(field => {
                            errors[field].forEach(msg => {
                                messages.push(msg);
                            });
                        });

                        logDebug("Validation Errors:");
                        logDebug(messages.join("\n"));

                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            html: messages.join('<br>')
                        });

                        return;
                    }

                    if (xhr.status === 409 && res.type === 'slot_taken') {

                        logDebug("Slot Taken:");
                        logDebug(res.message);

                        Swal.fire({
                            icon: 'error',
                            title: 'Slot Already Taken',
                            text: res.message
                        });

                        updateSlots(selectedDate);
                        return;
                    }

                    if (xhr.status === 409 && res.type === 'unit_already_booked') {

                        logDebug("Already Booked:");
                        logDebug(res.message);

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
        Swal.fire({
            title: 'Submit Booking?',
            text: 'Are you sure you want to submit this AUSI booking?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Proceed',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                sendBooking();
            }
        });
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