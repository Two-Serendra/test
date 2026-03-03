$(document).ready(function () {




    flatpickr("#GreaseTrapBookingDate", {
        dateFormat: "Y-m-d",
        minDate: new Date().fp_incr(1)
    });


    const $bookingDate = $('#GreaseTrapBookingDate');
    const $bookingSlots = $('.booking-slot');
    const $submitBtn = $('#saveUserGreaseTrapBtn');

    function updateSlots(date) {
        if (!date) return;

        $.ajax({
            url: '/grease-trap/booked-slots',
            type: 'GET',
            data: { date },
            success: function (res) {
                resetSlots();
                disableBookedSlots(res.booked_slots);
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Unable to load available slots.'
                });
            }
        });
    }

    function resetSlots() {
        $bookingSlots.each(function () {
            $(this).prop('disabled', false).prop('checked', false);
            $('label[for="' + this.id + '"]')
                .removeClass('disabled btn-secondary')
                .addClass('btn-outline-primary')
                .css('cursor', 'pointer');
        });
    }

    function disableBookedSlots(bookedSlots) {
        bookedSlots.forEach(slot => {
            const $radio = $('.booking-slot[data-slot="' + slot + '"]');

            if ($radio.length) {
                $radio.prop('disabled', true);
                $('label[for="' + $radio.attr('id') + '"]')
                    .removeClass('btn-outline-primary')
                    .addClass('btn-secondary disabled')
                    .css('cursor', 'not-allowed');
            }
        });
    }

    $bookingSlots.prop('disabled', true);


    $bookingDate.on('change', function () {
        const selectedDate = $(this).val();
        updateSlots(selectedDate);
    });

    //V1

    // $('#userGreaseTrapNewBooking').on('submit', function (event) {
    //     event.preventDefault();

    //     const form = this;
    //     const $submitBtn = $('#saveUserGreaseTrapBtn');
    //     const $bookingDate = $('#GreaseTrapBookingDate');

    //     if (!form.checkValidity()) {
    //         form.classList.add('was-validated');
    //         return;
    //     }
    //     form.classList.remove('was-validated');

    //     const originalWidth = $submitBtn.outerWidth();
    //     $submitBtn
    //         .attr('disabled', true)
    //         .html(`<div class="spinner-border spinner-border-sm text-light"></div>`)
    //         .css('width', originalWidth + 'px');

    //     const sendBooking = (forcePayment = false) => {
    //         const formData = new FormData(form);
    //         if (forcePayment) {
    //             formData.append('force_payment', true);
    //         }

    //         $.ajax({
    //             url: $(form).attr('action'),
    //             type: $(form).attr('method'),
    //             headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
    //             data: formData,
    //             processData: false,
    //             contentType: false,
    //             success: function (response) {
    //                 Swal.fire({
    //                     icon: 'success',
    //                     title: 'Booking Submitted!',
    //                     text: response.message || 'Your booking has been successfully submitted.',
    //                     timer: 2000,
    //                     showConfirmButton: false
    //                 });

    //                 const selectedDate = $bookingDate.val();
    //                 form.reset();
    //                 $(form).removeClass('was-validated');

    //                 flatpickr("#GreaseTrapBookingDate", {
    //                     dateFormat: "Y-m-d",
    //                     minDate: new Date().fp_incr(1)
    //                 });

    //                 updateSlots(selectedDate);
    //             },
    //             error: function (xhr) {
    //                 if (xhr.status === 409 && xhr.responseJSON?.requires_payment) {
    //                     // User exceeded free bookings, ask for confirmation
    //                     Swal.fire({
    //                         icon: 'warning',
    //                         title: 'Free Booking Limit Reached',
    //                         text: xhr.responseJSON.message,
    //                         showCancelButton: true,
    //                         confirmButtonText: 'Yes, continue with payment',
    //                         cancelButtonText: 'Cancel',
    //                         confirmButtonColor: '#3085d6',
    //                         cancelButtonColor: '#d33'
    //                     }).then((result) => {
    //                         if (result.isConfirmed) {
    //                             sendBooking(true); // Resubmit with force_payment
    //                         } else {
    //                             $submitBtn
    //                                 .attr('disabled', false)
    //                                 .html(`<span class="btn-text">Submit</span>`)
    //                                 .css('width', '');
    //                         }
    //                     });
    //                     return;
    //                 }

    //                 if (xhr.status === 409 && xhr.responseJSON?.requires_payment) {
    //                     const remaining = xhr.responseJSON.remaining_free_bookings;

    //                     Swal.fire({
    //                         icon: 'warning',
    //                         title: 'Free Booking Limit Reached',
    //                         html: `You have <strong>${remaining}</strong> free bookings remaining.<br>
    //            This booking will require payment. Do you want to continue?`,
    //                         showCancelButton: true,
    //                         confirmButtonText: 'Yes, continue with payment',
    //                         cancelButtonText: 'Cancel',
    //                         confirmButtonColor: '#3085d6',
    //                         cancelButtonColor: '#d33'
    //                     }).then((result) => {
    //                         if (result.isConfirmed) {
    //                             sendBooking(true); 
    //                         } else {
    //                             $submitBtn
    //                                 .attr('disabled', false)
    //                                 .html(`<span class="btn-text">Submit</span>`)
    //                                 .css('width', '');
    //                         }
    //                     });
    //                     return;
    //                 }


    //                 if (xhr.status === 422) {
    //                     let msg = 'Please check the form fields.';
    //                     if (xhr.responseJSON?.errors) {
    //                         msg = Object.values(xhr.responseJSON.errors)
    //                             .map(e => e[0])
    //                             .join('\n');
    //                     }

    //                     Swal.fire({
    //                         icon: 'error',
    //                         title: 'Validation Error',
    //                         text: msg,
    //                         confirmButtonText: 'OK',
    //                         confirmButtonColor: '#d33'
    //                     });
    //                     return;
    //                 }

    //                 Swal.fire({
    //                     toast: true,
    //                     position: "top-end",
    //                     icon: 'error',
    //                     title: 'Something went wrong. Please try again later.',
    //                     timer: 3000,
    //                     showConfirmButton: false
    //                 });
    //             },
    //             complete: function () {
    //                 $submitBtn
    //                     .attr('disabled', false)
    //                     .html(`<span class="btn-text">Submit</span>`)
    //                     .css('width', '');
    //             }
    //         });
    //     };

    //     sendBooking(false);
    // });

    $('#userGreaseTrapNewBooking').on('submit', function (event) {
        event.preventDefault();

        const form = this;
        const $submitBtn = $('#saveUserGreaseTrapBtn');
        const $bookingDate = $('#GreaseTrapBookingDate');
        const selectedDate = $bookingDate.val();

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

        const sendBooking = (forcePayment = false) => {
            const formData = new FormData(form);
            if (forcePayment) {
                formData.append('force_payment', true);
            }

            lockSubmitBtn();

            $.ajax({
                url: $(form).attr('action'),
                type: $(form).attr('method'),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: formData,
                processData: false,
                contentType: false,

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

                    flatpickr('#GreaseTrapBookingDate', {
                        dateFormat: 'Y-m-d',
                        minDate: new Date().fp_incr(1)
                    });
                },

                error(xhr) {
                    const res = xhr.responseJSON || {};

                    if (res.requires_payment) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Free Booking Limit Reached',
                            text: res.message,
                            showCancelButton: true,
                            confirmButtonText: 'Yes, continue with payment',
                            cancelButtonText: 'Cancel',
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33'
                        }).then(result => {
                            if (result.isConfirmed) {
                                sendBooking(true);
                            } else {
                                unlockSubmitBtn();
                            }
                        });
                        return;
                    }

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


    $(document).on('click', '.grease-trap-booking-cancel', function () {
        const bookingId = $(this).data('id');

        $.ajax({
            url: '/grease-trap-booking/cancel/' + bookingId,
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (res) {
                if (!res.success) {
                    Swal.fire('Error', res.message || 'Failed to cancel booking.', 'error');
                    return;
                }


                if (res.requires_confirmation) {
                    Swal.fire({
                        title: 'Cancel Booking',
                        html:
                            'Are you sure you want to cancel this booking?' +
                            res.message,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, cancel it',
                        cancelButtonText: 'No, keep it'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: '/grease-trap-booking/cancel/' + bookingId,
                                type: 'POST',
                                data: {
                                    _token: $('meta[name="csrf-token"]').attr('content'),
                                    confirm: 1
                                },
                                success: function (res2) {
                                    Swal.fire('Cancelled!', res2.message, 'success')
                                        .then(() => {
                                            let page = $('.pagination .active span').text() || 1;
                                            loadBookings(page);
                                        });
                                },
                                error: function () {
                                    Swal.fire('Error', 'Something went wrong while cancelling.', 'error');
                                }
                            });
                        }
                    });
                } else {

                    Swal.fire('Cancelled!', res.message, 'success')
                        .then(() => {
                            let page = $('.pagination .active span').text() || 1;
                            loadBookings(page);
                        });
                }
            },
            error: function () {
                Swal.fire('Error', 'Something went wrong while cancelling.', 'error');
            }
        });
    });


});