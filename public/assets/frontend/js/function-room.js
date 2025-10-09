$(document).ready(function () {
    flatpickr("#functionRoomBookingDate", {
        dateFormat: "Y-m-d",
        minDate: new Date().fp_incr(6)
    });

    $("#functionRoomBookingModal").on("show.bs.modal", function () {

        $(".addonQty").each(function () {
            $(this).prop("disabled", true).val(0);
        });

        $(".text-muted[id^='addonAvailable']").text("Available: -");
    });

    $(document).on("change", ".addOnsFields", function () {
        let addonId = $(this).attr("id").replace("addon", "");
        let qtyInput = $("input[data-addon-id='" + addonId + "']");

        if ($(this).is(":checked")) {
            qtyInput.prop("disabled", false);
            qtyInput.prop("required", true);

            if (parseInt(qtyInput.val()) === 0) {
                qtyInput.val(1);
            }
        } else {
            qtyInput.prop("disabled", true);
            qtyInput.prop("required", false).val(0);
        }
    });

    $(document).on("input", ".addonQty", function () {
        let max = parseInt($(this).attr("max")) || 0;
        let val = parseInt($(this).val()) || 0;

        if (val > max) {
            $(this).val(max);
        } else if (val < 0) {
            $(this).val(0);
        }
    });

    $('.BookFunctionRoomBtn').on('click', function () {
        $("#loadingOverlay").fadeIn();

        var functionRoomName = $(this).data("name");
        var roomId = $(this).data("id");

        $.ajax({
            url: "/check-auth",
            method: "GET",
            success: function (response) {
                if (response.authenticated) {
                    $.ajax({
                        url: `/function-room/${roomId}/booked-dates`,
                        type: 'GET',
                        success: function (disabledDates) {
                            let dateInput = document.getElementById("functionRoomBookingDate");
                            if (dateInput._flatpickr) dateInput._flatpickr.destroy();

                            flatpickr("#functionRoomBookingDate", {
                                dateFormat: "Y-m-d",
                                minDate: new Date().fp_incr(6),
                                disable: disabledDates,
                                onChange: function (selectedDates, dateStr) {
                                    if (!dateStr) return;

                                    $.ajax({
                                        url: '/function-room/addons-availability',
                                        type: 'GET',
                                        data: {
                                            date: dateStr,
                                            room_id: $("#userFunctionRoomNewBooking input[name=function_room_id]").val()
                                        },
                                        success: function (res) {
                                            $(".addonQty").each(function () {
                                                let addonId = $(this).data("addon-id");
                                                let available = res[addonId] ?? 0;
                                                let checkbox = $("#addon" + addonId);

                                                // Update available text
                                                $("#addonAvailable" + addonId).text("Available: " + available);

                                                if (available <= 0) {
                                                    // Disable everything if no stock
                                                    $(this).prop("disabled", true).val(0).prop("required", false);
                                                    checkbox.prop("disabled", true).prop("checked", false);
                                                } else {
                                                    // Enable checkbox, but keep qty disabled until checked
                                                    $(this).attr("max", available).val(0).prop("disabled", true).prop("required", false);
                                                    checkbox.prop("disabled", false).prop("checked", false);
                                                }
                                            });
                                        }

                                    });
                                }
                            });

                            $("#functionRoomBookingModal").modal("show");
                        },
                        error: function () {
                            alert("Failed to load booked dates.");
                        },
                        complete: function () {
                            $("#loadingOverlay").fadeOut();
                        }
                    });
                } else {
                    let redirectUrl = encodeURIComponent(window.location.href);
                    window.location.href = "/login?redirect=" + redirectUrl;
                    $("#loadingOverlay").fadeOut();
                }
            },
            error: function () {
                alert("Something went wrong. Please try again.");
                $("#loadingOverlay").fadeOut();
            }
        });
    });



    let supplierIndex = 1;

    $('#hasSuppliers').on('change', function () {
        if ($(this).is(':checked')) {
            $('#supplierSection').removeClass('d-none');

            // if empty, add back the first supplier row
            if ($('#suppliersWrapper').children().length === 0) {
                let firstRow = `
            <div class="row g-2 supplier-item mb-2">
                <div class="col-md-4">
                    <input type="text" name="suppliers[0][name]" class="form-control" placeholder="Supplier Name">
                </div>
                <div class="col-md-4">
                    <input type="file" name="suppliers[0][attachment]" class="form-control" accept="image/*,.pdf">
                </div>
            </div>`;
                $('#suppliersWrapper').html(firstRow);
                supplierIndex = 1;
            }

        } else {
            $('#supplierSection').addClass('d-none');
            $('#suppliersWrapper').html('');
            supplierIndex = 1;
        }
    });

    $('#addSupplier').on('click', function () {
        let newRow = `
        <div class="row g-2 supplier-item mb-2">
            <div class="col-md-4">
                <input type="text" name="suppliers[${supplierIndex}][name]" class="form-control" placeholder="Name">
            </div>
            <div class="col-md-6">
                <input type="file" name="suppliers[${supplierIndex}][attachment]" class="form-control" accept="image/*,.pdf">
            </div>
            <div class="col-md-2 d-flex align-items-center">
                <button type="button" class="btn btn-sm btn-danger removeSupplier customBtn">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </div>`;
        $('#suppliersWrapper').append(newRow);
        supplierIndex++;
    });

    $(document).on('click', '.removeSupplier', function () {
        $(this).closest('.supplier-item').remove();
    });


    $('#paxInput').on('input', function () {
        let pax = parseInt($(this).val());
        let max = parseInt($('#roomCapacity').val());
        if (pax > max) {
            $('#capacityError').removeClass('d-none');
            $(this).val(max);
        } else {
            $('#capacityError').addClass('d-none');
        }
    });

    function checkAuthorization() {
        let selectedOption = $('#residentSelect option:selected');
        let residentType = selectedOption.data('type');
        let unitNo = selectedOption.data('unit');
        let paymentMode = $('input[name="payment_mode"]:checked').val();

        let $wrapper = $('#authorizationUploadWrapper');
        let $fileInput = $('input[name="authorization_file"]');

        // Reset state
        $wrapper.addClass('d-none');
        $('#authorizationLabel').text('');
        $('#authorizationNote').text('');
        $fileInput.prop('required', false);

        // ✅ Tenant booking with CTA
        if (residentType === 'tenant' && paymentMode === 'Charge to Account') {
            $('#authorizationLabel').text('CTA Authorization Letter *');
            $('#authorizationNote').text('Required because you are booking as a tenant with CTA.');
            $wrapper.removeClass('d-none');
            $fileInput.prop('required', true);

            // ✅ Owner with a tenant
        } else if (residentType === 'owner' && unitNo) {
            $.get('/check-unit-tenant/' + unitNo, function (response) {
                if (response.hasTenant) {
                    $('#authorizationLabel').text('Tenant Authorization Letter *');
                    $('#authorizationNote').text('Required because the unit is tenanted.');
                    $wrapper.removeClass('d-none');
                    $fileInput.prop('required', true);
                }
            });
        }
    }

    $('#residentSelect').on('change', checkAuthorization);
    $('input[name="payment_mode"]').on('change', checkAuthorization);

    $('#userFunctionRoomNewBooking').submit(function (event) {
        event.preventDefault();

        const form = this;
        let isValid = true;
        const startTime = $('#startTime').val();
        const endTime = $('#endTime').val();

        function convertToMinutes(time) {
            if (!time) return null;
            const [hours, minutes] = time.split(':').map(Number);
            return hours * 60 + minutes;
        }

        const startMinutes = convertToMinutes(startTime);
        const endMinutes = convertToMinutes(endTime);

        if (startMinutes !== null && endMinutes !== null && endMinutes <= startMinutes) {
            $('#timeError').removeClass('d-none');
            $('#endTime').addClass('is-invalid');
            isValid = false;
        } else {
            $('#timeError').addClass('d-none');
            $('#endTime').removeClass('is-invalid');
        }

        if (!isValid) return;

        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }
        form.classList.remove('was-validated');

        const $btn = $('#saveUserFunctionRoomBtn');
        const originalWidth = $btn.outerWidth();
        $btn
            .attr('disabled', true)
            .html(`<div class="spinner-border spinner-border-sm text-light"></div>`)
            .css('width', originalWidth + 'px');

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
                $('#functionRoomBookingModal').modal('hide');

                Swal.fire({
                    icon: 'success',
                    title: 'Booking Submitted!',
                    text: response.message || 'Your booking has been successfully submitted.',
                    timer: 2000,
                    showConfirmButton: false
                });

                form.reset();
                $(form).removeClass('was-validated');
                $(form).find('#supplierSection, #authorizationUploadWrapper').hide();
            },
            error: function (xhr) {
                // 409 → Conflict (double booking OR date blocked)
                if (xhr.status === 409) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Booking Conflict',
                        text: xhr.responseJSON.message,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#d33'
                    });
                    return;
                }

                // 422 → Add-ons stock insufficient
                if (xhr.status === 422) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Not Enough Add-ons',
                        text: xhr.responseJSON.message,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#d33'
                    });
                    return;
                }

                // Fallback error
                Swal.fire({
                    toast: true,
                    position: "top-end",
                    icon: 'error',
                    title: 'Something went wrong. Please try again later.',
                    timer: 3000,
                    showConfirmButton: false
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


    $('input[name="contact_number"]').on('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 11);
    })

    // Show spinner
    function showSpinner() {
        $('#global-loading').addClass('show');
    }

    // Hide spinner
    function hideSpinner() {
        $('#global-loading').removeClass('show');
    }


    $('.function-room-booking-details').on('click', function () {
        const bookingId = $(this).data('id');
        showSpinner();

        // tiny helper: parse times like "15:00:00", "15:00", "3:00 PM", "03:00 PM"
        function parseTimeToDate(timeStr) {
            if (!timeStr) return null;
            // Trim
            timeStr = timeStr.trim();

            // AM/PM format
            const ampmMatch = timeStr.match(/(\d{1,2}):(\d{2})(?::\d{2})?\s*([AaPp][Mm])/);
            if (ampmMatch) {
                let h = parseInt(ampmMatch[1], 10);
                const m = parseInt(ampmMatch[2], 10);
                const ampm = ampmMatch[3].toUpperCase();
                if (ampm === 'PM' && h !== 12) h += 12;
                if (ampm === 'AM' && h === 12) h = 0;
                return new Date(1970, 0, 1, h, m, 0);
            }

            // 24-hour format (HH:mm or HH:mm:ss)
            const twentyFour = timeStr.match(/(\d{1,2}):(\d{2})(?::(\d{2}))?/);
            if (twentyFour) {
                const h = parseInt(twentyFour[1], 10);
                const m = parseInt(twentyFour[2], 10);
                const s = twentyFour[3] ? parseInt(twentyFour[3], 10) : 0;
                return new Date(1970, 0, 1, h, m, s);
            }

            // fallback: try Date parse (might still fail)
            const d = new Date(`1970-01-01T${timeStr}`);
            return isNaN(d.getTime()) ? null : d;
        }

        $.get(`/view-function-room-bookings/${bookingId}/details`, function (response) {
            if (!response.success) {
                hideSpinner();
                return alert('Failed to load booking details.');
            }

            const booking = response.booking;
            $('#detail-transaction-no').text(booking.transaction_no ?? 'N/A');
            $('#detail-unit').text(booking.unit_no ?? 'N/A');
            $('#detail-name').text(booking.user?.name ?? 'N/A');
            $('#detail-contact').text(booking.contact_number ?? 'N/A');

            const residentTypeBadge = booking.resident_type === 'TENANT'
                ? '<span class="badge badge-forge bg-danger">Tenant</span>'
                : booking.resident_type === 'OWNER'
                    ? '<span class="badge badge-forge bg-primary">Owner</span>'
                    : `<span class="badge badge-forge bg-secondary">${booking.resident_type ?? 'N/A'}</span>`;
            $('#detail-resident-type').html(residentTypeBadge);

            $('#detail-function-room').text(booking.function_room?.function_room_name ?? 'N/A');
            $('#detail-purpose').text(booking.purpose_of_event ?? 'N/A');


            let statusBadge = '';
            const today = new Date(); today.setHours(0, 0, 0, 0);
            const bookingDate = booking.function_room_booking_date ? new Date(booking.function_room_booking_date) : null;
            if (booking.booking_status == 1) {
                if (bookingDate && bookingDate < today) {
                    statusBadge = '<span class="badge badge-forge bg-secondary">Completed</span>';
                    $('#cancel-booking-btn').addClass('d-none');
                } else {
                    statusBadge = '<span class="badge badge-forge bg-success">Confirmed</span>';
                    $('#cancel-booking-btn').removeClass('d-none').data('id', booking.id).data('start-time', booking.event_start_time);
                }
            } else if (booking.booking_status == 0) {
                statusBadge = '<span class="badge badge-forge bg-warning">Waiting</span>';
                $('#cancel-booking-btn').removeClass('d-none').data('id', booking.id).data('start-time', booking.event_start_time);
            } else if (booking.booking_status == 2) {
                statusBadge = '<span class="badge badge-forge bg-danger">Cancelled</span>';
                $('#cancel-booking-btn').addClass('d-none');
            } else {
                statusBadge = '<span class="badge badge-forge bg-dark">Unknown</span>';
                $('#cancel-booking-btn').addClass('d-none');
            }
            $('#detail-status').html(statusBadge);
            $('#detail-booking-date').text(booking.function_room_booking_date ?? 'N/A');
            $('#detail-start-time').text(booking.event_start_time ?? 'N/A');
            $('#detail-end-time').text(booking.event_end_time ?? 'N/A');
            $('#detail-pax').text(booking.pax ?? 'N/A');
            $('#detail-payment-mode').text(booking.payment_mode ?? 'N/A');

            if (response.authorization_file_url) {
                $('#detail-authorization').html(`<a href="${response.authorization_file_url}" target="_blank" class="custom-link">View</a>`);
            } else {
                $('#detail-authorization').html('<span class="text-muted">N/A</span>');
            }


            if (booking.suppliers && booking.suppliers.length > 0) {
                let suppliersHtml = '';
                booking.suppliers.forEach(s => {
                    suppliersHtml += `<div>${s.name} ${s.attachment_url ? `<a href="${s.attachment_url}" target="_blank" class="custom-link">View</a>` : ''}</div>`;
                });
                $('#detail-suppliers').html(suppliersHtml);
            } else {
                $('#detail-suppliers').html('<span class="text-muted">N/A</span>');
            }

            const durationHoursBackend = parseFloat(booking.duration_hours ?? booking.duration_in_hours ?? NaN);
            const ratePerHourBackend = parseFloat(booking.final_rate ?? booking.function_room?.function_room_rate ?? NaN);
            const roomTotalBackend = parseFloat(booking.room_total ?? NaN);

            // Compute duration (front-end fallback) — robust parsing
            let hours = !isNaN(durationHoursBackend) ? durationHoursBackend : 1;
            if (isNaN(hours)) hours = 1;


            if (!durationHoursBackend) {
                const startDate = parseTimeToDate(booking.event_start_time);
                const endDate = parseTimeToDate(booking.event_end_time);
                if (startDate && endDate) {

                    if (endDate <= startDate) {
                        endDate.setDate(endDate.getDate() + 1);
                    }
                    hours = Math.round(((endDate - startDate) / (1000 * 60 * 60)) * 100) / 100; // 2 decimal places
                    if (hours <= 0) hours = 1;
                } else {
                    hours = 1;
                }
            }


            const ratePerHour = !isNaN(ratePerHourBackend) ? ratePerHourBackend : (parseFloat(booking.final_rate) || 0);
            const roomLineTotal = !isNaN(roomTotalBackend) ? roomTotalBackend : Math.round((ratePerHour * hours) * 100) / 100;

            if (ratePerHour && ratePerHour > 0) {
                const baseRate = parseFloat(booking.function_room?.function_room_rate ?? ratePerHour);
                if (baseRate > ratePerHour) {
                    $('#detail-rate').html(`
            <div>
                <small class="text-muted"><s>₱${Number(baseRate).toLocaleString(undefined, { minimumFractionDigits: 2 })}/hr</s></small>
                &nbsp; → &nbsp;
                <small class="fw-bold">₱${Number(ratePerHour).toLocaleString(undefined, { minimumFractionDigits: 2 })}/hr</small>
                &nbsp; × &nbsp;
                <small>${Number(hours).toLocaleString(undefined, { minimumFractionDigits: (hours % 1 ? 2 : 0) })} hr(s)</small>
                &nbsp; = &nbsp;
                <strong class="fw-bold">₱${Number(roomLineTotal).toLocaleString(undefined, { minimumFractionDigits: 2 })}</strong>
            </div>
        `);
                } else {

                    $('#detail-rate').html(`
            <div>
                <small class="text-muted">₱${Number(ratePerHour).toLocaleString(undefined, { minimumFractionDigits: 2 })}/hr</small>
                &nbsp; × &nbsp;
                <small>${Number(hours).toLocaleString(undefined, { minimumFractionDigits: (hours % 1 ? 2 : 0) })} hr(s)</small>
                &nbsp; = &nbsp;
                <strong>₱${Number(roomLineTotal).toLocaleString(undefined, { minimumFractionDigits: 2 })}</strong>
            </div>
        `);
                }
            } else {
                $('#detail-rate').html('<span class="text-muted">N/A</span>');
            }

            let discountValue = booking.discount ?? 0;
            const discountRemarks = booking.discount_remarks ?? '';

            // Convert and format
            discountValue = parseFloat(discountValue);
            if (discountValue % 1 === 0) {
                discountValue = discountValue.toFixed(0);
            } else {
                discountValue = discountValue.toFixed(1).replace(/\.0$/, '');
            }

            if (discountValue > 0) {
                $('#detail-discount').html(`
        <div style="margin-top: 6px;">
            <strong class="text-danger">${discountValue}%</strong>
            ${discountRemarks ? `<span style="margin-left: 6px; color: #555;">${discountRemarks}</span>` : ''}
        </div>
    `);
            } else {
                $('#detail-discount').html('<span class="text-muted" style="margin-top: 6px;">No discount</span>');
            }

            let breakdownHtml = '';
            let addonsTotal = 0;

            // Room row
            if (ratePerHour && ratePerHour > 0) {
                breakdownHtml += `
                <tr>
                    <td>${booking.function_room?.function_room_name ?? 'Function Room'} (${Number(hours).toLocaleString(undefined, { minimumFractionDigits: (hours % 1 ? 2 : 0) })} hr${hours > 1 ? 's' : ''})</td>
                    <td class="text-center">${Number(hours).toLocaleString(undefined, { minimumFractionDigits: (hours % 1 ? 2 : 0) })}</td>
                    <td class="text-end">₱${Number(ratePerHour).toLocaleString(undefined, { minimumFractionDigits: 2 })}/hr</td>
                    <td class="text-end">₱${Number(roomLineTotal).toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>
                </tr>
            `;
            }


            if (booking.add_ons && booking.add_ons.length > 0) {
                booking.add_ons.forEach(addon => {

                    const pivot = addon.pivot || {};
                    const qty = parseFloat(pivot.quantity ?? pivot.qty ?? addon.qty ?? 0) || 0;
                    const price = parseFloat(pivot.price ?? addon.price ?? 0) || 0;
                    const lineTotal = Math.round(qty * price * 100) / 100;
                    addonsTotal += lineTotal;

                    breakdownHtml += `
                    <tr>
                        <td>${addon.item ?? addon.name ?? 'Add-on'}</td>
                        <td class="text-center">${qty}</td>
                        <td class="text-end">₱${Number(price).toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>
                        <td class="text-end">₱${Number(lineTotal).toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>
                    </tr>
                `;
                });


                breakdownHtml += `
                <tr class="table-light fw-bold">
                    <td colspan="3" class="text-end">Add-ons Subtotal</td>
                    <td class="text-end">₱${Number(addonsTotal).toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>
                </tr>
            `;
            }

            if (!breakdownHtml) {
                breakdownHtml = `<tr><td colspan="4" class="text-center text-muted">No charges</td></tr>`;
            }

            $('#detail-breakdown').html(breakdownHtml);

            // Grand total
            const grandTotal = Math.round((roomLineTotal + addonsTotal) * 100) / 100;
            $('#detail-grand-total').text("₱" + Number(grandTotal).toLocaleString(undefined, { minimumFractionDigits: 2 }));

            hideSpinner();
            $('#userViewfunctionRoomBookingDetailsModal').modal('show');

        }).fail(function () {
            hideSpinner();
            alert('Something went wrong.');
        });
    });


    $('#cancel-booking-btn').on('click', function () {
        const bookingId = $(this).data('id');
        const startTime = $(this).data('start-time');

        const bookingStart = new Date(startTime);
        const now = new Date();
        const hoursDiff = (bookingStart - now) / (1000 * 60 * 60);

        let swalText = "Are you sure you want to cancel this booking?";
        if (hoursDiff < 24) {
            swalText = "⚠️ This booking starts in less than 24 hours. Cancelling will incur a ₱1000 penalty. Continue?";
        }

        Swal.fire({
            title: 'Cancel Booking',
            text: swalText,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, cancel it'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/booking/' + bookingId + '/cancel',
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (res) {
                        $("#functionRoomBookingModal").modal("hide");
                        if (res.success) {
                            Swal.fire('Cancelled!', 'The booking has been cancelled.', 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Error', res.message || 'Failed to cancel booking.', 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'Something went wrong while cancelling.', 'error');
                    }
                });
            }
        });
    });
});


