$(function () {
    alert("🔥GT BOOKING MOBILE JS VERSION 2026-06-15-020");
    const el = document.getElementById('resident_id_gt');

    logDebugGt("SELECT EXISTS: " + (el ? "YES" : "NO"));
    function logDebugGt(...args) {

        const msg = args.map(a =>
            typeof a === 'object'
                ? JSON.stringify(a)
                : a
        ).join(' ');

        console.log(msg);

        const el = document.getElementById('debugPanelGt');

        if (el) {
            el.innerHTML += msg + "<br>";
            el.scrollTop = el.scrollHeight;
        }
    }

    // logDebugGt("🚀 Mobile Booking JS Loaded");

    window.gtState = {
        date: null,
        unit: null
    };

    const $bookingSlots = $('.gt-booking-slot');
    initGtCalendar();
    function initGtCalendar() {

        logDebugGt("Loading disabled dates...");

        $.get('/grease-trap-disabled-dates-mobile')

            .done(function (res) {

                logDebugGt("Disabled dates loaded");
                logDebugGt(res);

                flatpickr("#GtBookingDate", {
                    dateFormat: "Y-m-d",
                    minDate: new Date().fp_incr(1),
                    disableMobile: true,
                    disable: res.disabled_dates || [],

                    onReady: function () {
                        logDebugGt("Flatpickr READY");
                    },

                    onChange: function (selectedDates, dateStr) {

                        window.gtState.date = dateStr;

                        logDebugGt("DATE=" + dateStr);

                        triggerUpdateGt();
                    }
                });

            })

            .fail(function (xhr) {

                logDebugGt("FAILED");
                logDebugGt(xhr.status);
                logDebugGt(xhr.responseText);

            });

    }

    function reloadDisabledGtDates() {

        logDebugGt("Reloading disabled dates...");

        $.get('/grease-trap-disabled-dates-mobile')

            .done(function (res) {

                logDebugGt("Reload success");
                logDebugGt(res);

                const fp = document.querySelector("#GtBookingDate")._flatpickr;

                if (!fp) {
                    logDebugGt("Flatpickr instance NOT FOUND");
                    return;
                }

                fp.set('disable', res.disabled_dates || []);

                logDebugGt("Disabled dates updated");

            })

            .fail(function (xhr) {

                logDebugGt("Reload failed");
                logDebugGt(xhr.status);
                logDebugGt(xhr.responseText);

            });

    }

    document.addEventListener('change', function (e) {
        if (e.target && e.target.id === 'resident_id_gt') {
            window.onResidentChangeGt(e);
        }
    });

    window.onResidentChangeGt = function (e) {
        const value = e.target.value;

        window.gtState.unit = value;

        if (window.Alpine && Alpine.store('superapp')) {
            Alpine.store('superapp').selectedUnit = value;
        }

        console.log("UNIT:", value);
        logDebugGt("UNIT CHANGED: " + value);
        logDebugGt("CHANGE FIRED");

        triggerUpdateGt();
    };


    function triggerUpdateGt() {

        try {
            logDebugGt("TRIGGER START");
            const date = window.gtState.date;
            const unit = window.gtState.unit;

            logDebugGt("DATE=" + date);
            logDebugGt("UNIT=" + unit);

            if (!date || !unit) {
                $(".gt-booking-slot").prop("disabled", true);
                hideLoadingGt();
                return;
            }
            updateGtSlots(date, unit);

        } catch (e) {

            console.error(e);

            logDebugGt("TRIGGER ERROR");
            logDebugGt(e.message);
        }
    }
    function updateGtSlots(date, unit) {

        logDebugGt("ENTERED updateGtSlots");

        logDebugGt("STEP 1 OK");

        try {

            if (typeof logDebugGt === "function") {
                logDebugGt("DEBUG OK", { date, unit });
            } else {
                logDebugGt("logDebugGt missing");
            }

            logDebugGt("STEP 2 OK");
            showLoadingGt();
            resetGtSlots();

            logDebugGt("STEP 3 OK - BEFORE AJAX");

            $.ajax({

                url: "/grease-trap-booked-slots-mobile",
                type: "GET",
                data: {
                    date: date,
                    unit_name: unit
                },

                beforeSend: function () {
                    showLoadingGt();
                },
                success: function (res) {
                    resetGtSlots();
                    disableBookedGtSlots(res.blocked_slots || []);
                    disableGtPastSlots(date);
                    logDebugGt("SUCCESS", res);

                    hideLoadingGt();
                },
                error: function (xhr) {

                    logDebugGt(xhr.responseText);
                    logDebugGt("ERROR " + xhr.status);

                    hideLoadingGt();
                }

            });

        } catch (e) {

            logDebugGt("JS ERROR:\n" + e.message);
            logDebugGt("FULL ERROR:", e);
        }
    }

    window.resetGtSlots = function () {
        logDebugGt("RESET SLOTS CALLED");

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
        const date = window.gtState.date;
        const unit = window.gtState.unit;
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

    function showLoadingGt() {
        $("#slotLoadingGt").removeClass("d-none");
    }

    function hideLoadingGt() {
        $("#slotLoadingGt").addClass("d-none");
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
        if (window.gtState) {
            window.gtState.date = null;
            window.gtState.unit = null;
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
        $('#slotLoadingGt').addClass('d-none');

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
        const $submitBtn = $('#saveUserGtBtn');
        const selectedSlot =
            $('input[name="booking_time_slot"]:checked').val();

        if (!selectedSlot) {

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
        const unit = $('#resident_id_gt').val();
        const role =
            $('#resident_id_gt option:selected').data('role') || '';

        $('#mobile_email').val(email);
        $('#mobile_unit_name').val(unit);
        $('#mobile_unit_role').val(role);

        const originalWidth = $submitBtn.outerWidth();

        const lockSubmitBtn = () => {
            $submitBtn
                .prop('disabled', true)
                .html('<div class="spinner-border spinner-border-sm text-light"></div>')
                .css('width', originalWidth + 'px');
        };

        const unlockSubmitBtn = () => {
            $submitBtn
                .prop('disabled', false)
                .html('<span class="btn-text">SUBMIT</span>')
                .css('width', '');
        };

        const sendBooking = (forcePayment = false) => {
            const store = Alpine.store('superapp');
            const email = store?.user?.email || '';
            const formData = new FormData(form);

            for (const pair of formData.entries()) {
                logDebugGt(pair[0] + " = " + pair[1]);
            }
            if (forcePayment) {
                formData.append('force_payment', true);
            }

            lockSubmitBtn();

            $.ajax({

                url: $(form).attr('action'),
                type: $(form).attr('method'),
                data: formData,
                headers: {
                    'X-CSRF-TOKEN':
                        $('meta[name="csrf-token"]').attr('content')
                },

                data: formData,
                processData: false,
                contentType: false,

                success: function (res) {

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

                    window.resetGtBookingUI();

                    resetGtSlots();
                    reloadDisabledGtDates();


                    $('.gt-booking-slot').prop('disabled', true);

                    window.gtState = {
                        date: null,
                        unit: null
                    };
                },

                error: function (xhr) {

                    const res = xhr.responseJSON || {};

                    if (xhr.status === 422) {

                        let messages = [];

                        Object.keys(res.errors || {}).forEach(field => {
                            res.errors[field].forEach(msg => {
                                messages.push(msg);
                            });
                        });

                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            html: messages.join('<br>')
                        });

                        return;
                    }

                    if (xhr.status === 409) {

                        if (res.requires_payment) {

                            Swal.fire({
                                icon: 'warning',
                                title: 'Payment Required',
                                text: res.message,
                                showCancelButton: true,
                                confirmButtonText: 'Continue'
                            }).then((result) => {

                                if (result.isConfirmed) {
                                    sendBooking(true);
                                }
                            });

                            return;
                        }

                        if (res.type === 'slot_taken') {

                            Swal.fire({
                                icon: 'error',
                                title: 'Slot Taken',
                                text: res.message
                            });

                            updateGtSlots(
                                $('#GtBookingDate').val(),
                                $('#resident_id_gt').val()
                            );

                            return;
                        }

                        if (res.type === 'unit_already_booked') {

                            Swal.fire({
                                icon: 'warning',
                                title: 'Already Booked',
                                text: res.message
                            });

                            return;
                        }
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: res.message || 'Something went wrong.'
                    });
                },

                complete: function () {
                    unlockSubmitBtn();
                }
            });
        };

        Swal.fire({
            title: 'Submit Booking?',
            text: 'Are you sure you want to submit this grease trap booking?',
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