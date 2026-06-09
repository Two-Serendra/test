function initAusiDatePicker() {
    const el = document.getElementById('AusiBookingDate');

    if (!el || el._flatpickr) return;

    if (typeof flatpickr === 'undefined') return;

    el._flatpickr = flatpickr(el, {
        dateFormat: "Y-m-d",
        minDate: new Date().fp_incr(1),
        onChange: function (_, dateStr) {
            window.updateSlots(dateStr);
        }
    });

    el.addEventListener('change', function () {
        window.updateSlots(this.value);
    });
}


$(document).ready(function () {

    flatpickr("#AusiBookingDate", {
        dateFormat: "Y-m-d",
        minDate: new Date().fp_incr(1),
        onChange: function (selectedDates, dateStr) {
            window.updateSlots(dateStr);
        }
    });

    const $bookingDate = $('#AusiBookingDate');
    const $bookingSlots = $('.ausi-booking-slot');
    const $submitBtn = $('#saveUserAusiBtn');

    window.updateSlots = function (date) {

        const store = Alpine.store('superapp');

        const residentId = store?.selectedUnit;

        if (!date || !residentId) {
            console.log("missing data", { date, residentId });
            return;
        }

        showLoading();
        resetSlots();

        $.ajax({
            url: '/ausi-booked-slots',
            type: 'GET',
            data: { date, resident_id: residentId },

            success: function (res) {
                resetSlots();
                disableBookedSlots(res.blocked_for_user || []);
                disablePastSlots(date);
            },

            complete: function () {
                hideLoading();
            }
        });
    };

    function resetSlots() {
        $('.ausi-booking-slot').each(function () {
            $(this).prop('disabled', false).prop('checked', false);

            $('label[for="' + this.id + '"]')
                .removeClass('disabled btn-secondary')
                .addClass('btn-outline-primary')
                .css('cursor', 'pointer');
        });
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


    function showLoading() {
        $('#slotLoading').removeClass('d-none');
        $('.ausi-booking-slot').prop('disabled', true);
    }

    function hideLoading() {
        $('#slotLoading').addClass('d-none');
    }

    function disablePastSlots(selectedDate) {
        if (!selectedDate) return;

        const now = new Date();

        $('.ausi-booking-slot').each(function () {
            const slot = $(this).data('slot');
            const startTime = slot.split(' - ')[0];
            const normalizedStartTime = startTime.replace('NN', 'PM');

            const slotDateTime = new Date(`${selectedDate} ${normalizedStartTime}`);

            if (slotDateTime <= now) {
                $(this).prop('disabled', true);

                $('label[for="' + $(this).attr('id') + '"]')
                    .removeClass('btn-outline-primary')
                    .addClass('btn-secondary disabled')
                    .css('cursor', 'not-allowed');
            }
        });
    }

    $(document).on('submit', '#userAusiNewBooking', function (event) {

        alert("SUBMIT FIRED");
        event.preventDefault();

        const form = this;
        const $submitBtn = $('#saveUserAusiBtn');
        const $bookingDate = $('#AusiBookingDate');
        const selectedDate = $bookingDate.val();

        const selectedSlot = $('input[name="booking_time_slot"]:checked').val();

        if (!selectedSlot) {
            $('.slot-error').show();

            Swal.fire({
                icon: 'warning',
                title: 'Time Slot Required',
                text: 'Please select a booking time slot.'
            });

            return;
        } else {
            $('.slot-error').hide();
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
                    alert("AJAX START");
                },

                success(response) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Booking Submitted!',
                        text: response.message || 'Your booking has been successfully submitted.',
                        timer: 2000,
                        showConfirmButton: false
                    });


                    form.reset();
                    $(form).removeClass('was-validated');

                    resetSlots();
                    $bookingSlots.prop('disabled', true);

                    flatpickr('#AusiBookingDate', {
                        dateFormat: 'Y-m-d',
                        minDate: new Date().fp_incr(1)
                    });
                },


                error(xhr) {
                    const res = xhr.responseJSON || {};

                    alert("STATUS: " + xhr.status);
                    alert(xhr.responseText.substring(0, 500));

                    if (xhr.status === 422) {
                        let msg = 'Please check the form fields.';
                        if (res.errors) {
                            msg = Object.values(res.errors).map(e => e[0]).join('\n');
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text: msg,
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#d33'
                        });
                        return;
                    }

                    if (xhr.status === 409) {

                        if (res.type === 'slot_taken') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Time Slot Taken',
                                text: res.message || 'This time slot is already booked just now.',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#d33'
                            });

                            updateSlots(selectedDate);
                            return;
                        }

                        if (res.type === 'unit_already_booked') {

                            Swal.fire({
                                icon: 'warning',
                                title: 'Booking Already Exists',
                                text: res.message,
                                showCancelButton: true,
                                confirmButtonText: 'Yes, book anyway',
                                cancelButtonText: 'Cancel',
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33'
                            }).then((result) => {

                                if (result.isConfirmed) {
                                    sendBooking(true);
                                } else {
                                    unlockSubmitBtn();
                                }
                            });

                            return;
                        }
                    }
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: 'Something went wrong. Please try again later.',
                        timer: 3000,
                        showConfirmButton: false
                    });
                },


                complete() {
                    unlockSubmitBtn();
                }
            });
        };
        sendBooking();
    });

    $(document).on('click', '.ausi-booking-cancel', function () {

        const bookingId = $(this).data('id');
        // console.log("Cancel clicked", bookingId);

        Swal.fire({
            title: 'Cancel Booking',
            text: 'Are you sure you want to cancel this booking?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, cancel it',
            cancelButtonText: 'No, keep it'
        }).then((result) => {
            if (result.isConfirmed) {

                $.ajax({
                    url: '/ausi-booking/cancel/' + bookingId,
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (res) {

                        Swal.fire('Cancelled!', res.message, 'success')
                            .then(() => {
                                let page = $('.pagination .active span').text() || 1;
                                loadBookings(page);
                            });

                    },
                    error: function (xhr) {
                        Swal.fire('Error',
                            xhr.responseJSON?.message || 'Something went wrong while cancelling.',
                            'error'
                        );
                    }
                });

            }
        });
    });

}); 