$(document).ready(function () {

    flatpickr("#DownloadStartDate", {
        enableTime: false,
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "F j, Y",
        allowInput: false
    });

    flatpickr("#DownloadEndDate", {
        enableTime: false,
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "F j, Y",
        allowInput: false
    });

    function showSpinner() {
        $('#global-loading').addClass('show');
    }

    // Hide spinner
    function hideSpinner() {
        $('#global-loading').removeClass('show');
    }

    $('.DownloadFunctionRoomBookingRecords').on('click', function () {
        $('#DownloadFunctionRoomBookingRecords').modal('show');
    });

    $('#functionRoomBookingsTable').on('click', '.view-records-booking-btn', function () {
        const bookingId = $(this).data('id');
        showSpinner();

        // Reset modal fields
        const fields = [
            '#detail-transaction-no', '#detail-unit', '#detail-name', '#detail-contact', '#detail-resident-type',
            '#detail-function-room', '#detail-purpose', '#detail-status', '#detail-booking-date', '#detail-start-time',
            '#detail-end-time', '#detail-pax', '#detail-payment-mode', '#detail-authorization', '#detail-suppliers',
            '#detail-rate', '#detail-breakdown', '#detail-grand-total'
        ];
        $(fields.join(',')).html('<span class="text-muted">Loading...</span>');

        const approveBtn = $('#approveBookingBtn');
        const rejectBtn = $('.reject-btn');
        approveBtn.data('id', bookingId);

        function parseTimeToDate(timeStr) {
            if (!timeStr) return null;
            const ampmMatch = timeStr.match(/(\d{1,2}):(\d{2})(?::\d{2})?\s*([AaPp][Mm])/);
            if (ampmMatch) {
                let h = parseInt(ampmMatch[1], 10);
                const m = parseInt(ampmMatch[2], 10);
                const ampm = ampmMatch[3].toUpperCase();
                if (ampm === 'PM' && h !== 12) h += 12;
                if (ampm === 'AM' && h === 12) h = 0;
                return new Date(1970, 0, 1, h, m, 0);
            }
            const twentyFour = timeStr.match(/(\d{1,2}):(\d{2})(?::(\d{2}))?/);
            if (twentyFour) {
                const h = parseInt(twentyFour[1], 10);
                const m = parseInt(twentyFour[2], 10);
                const s = twentyFour[3] ? parseInt(twentyFour[3], 10) : 0;
                return new Date(1970, 0, 1, h, m, s);
            }
            const d = new Date(`1970-01-01T${timeStr}`);
            return isNaN(d.getTime()) ? null : d;
        }

        $.get(`/admin/admin-function-room-bookings/${bookingId}/details`, function (response) {
            if (!response.success) {
                hideSpinner();
                return alert('Failed to load booking details.');
            }

            const booking = response.booking;

            // Fill basic fields
            $('#detail-transaction-no').text(booking.transaction_no ?? 'N/A');
            $('#detail-unit').text(booking.unit_no ?? 'N/A');
            $('#detail-name').text(booking.user?.name ?? 'N/A');
            $('#detail-contact').text(booking.contact_number ?? 'N/A');
            const residentTypeBadge = booking.resident_type === 'TENANT'
                ? '<span class="badge bg-danger">Tenant</span>'
                : booking.resident_type === 'OWNER'
                    ? '<span class="badge bg-primary">Owner</span>'
                    : `<span class="badge bg-secondary">${booking.resident_type ?? 'N/A'}</span>`;
            $('#detail-resident-type').html(residentTypeBadge);
            $('#detail-function-room').text(booking.function_room?.function_room_name ?? 'N/A');
            $('#detail-purpose').text(booking.purpose_of_event ?? 'N/A');

            // Booking status badge
            const today = new Date(); today.setHours(0, 0, 0, 0);
            const bookingDate = booking.function_room_booking_date ? new Date(booking.function_room_booking_date) : null;
            let statusBadge = '';
            switch (booking.booking_status) {
                case 0: statusBadge = '<span class="badge bg-warning">Incomplete</span>'; break;
                case 1: statusBadge = bookingDate && bookingDate < today ?
                    '<span class="badge bg-secondary">Completed</span>' :
                    '<span class="badge bg-success">Confirmed</span>'; break;
                case 2: statusBadge = '<span class="badge bg-danger">Cancelled</span>'; break;
                default: statusBadge = '<span class="badge bg-dark">Unknown</span>';
            }
            $('#detail-status').html(statusBadge);

            // Other details
            $('#detail-booking-date').text(booking.function_room_booking_date ?? 'N/A');
            $('#detail-start-time').text(booking.event_start_time ?? 'N/A');
            $('#detail-end-time').text(booking.event_end_time ?? 'N/A');
            $('#detail-pax').text(booking.pax ?? 'N/A');
            $('#detail-payment-mode').text(booking.payment_mode ?? 'N/A');

            // Authorization
            $('#detail-authorization').html(response.authorization_file_url
                ? `<a href="${response.authorization_file_url}" target="_blank" class="custom-link">View</a>`
                : '<span class="text-muted">N/A</span>');

            // Suppliers
            if (booking.suppliers && booking.suppliers.length) {
                let html = '';
                booking.suppliers.forEach(s => {
                    html += `<div>${s.name} ${s.attachment_url ? `<a href="${s.attachment_url}" target="_blank" class="custom-link">View</a>` : ''}</div>`;
                });
                $('#detail-suppliers').html(html);
            } else $('#detail-suppliers').html('<span class="text-muted">N/A</span>');

            // Reset defaults
            approveBtn.removeClass('d-none').text('Approve').prop('disabled', true);
            rejectBtn.removeClass('d-none').text('Reject').prop('disabled', true);

            // 1️⃣ If the current user already rejected
            if (response.current_user_status === 2) {
                approveBtn.prop('disabled', false).text('Approve'); // allow reversal
                rejectBtn.prop('disabled', true).text('Rejected');
            }
            // 2️⃣ If the current user already approved
            else if (response.current_user_status === 1) {
                approveBtn.prop('disabled', true).text('Approved');
                rejectBtn.prop('disabled', true).text('Reject');
            }
            // 3️⃣ If someone before rejected → but NOT this user
            else if (response.rejectedByPrevious && response.current_user_status === 0) {
                approveBtn.prop('disabled', true).text('Waiting');
                rejectBtn.prop('disabled', true).text('Rejected by ' + response.rejectedByRole);
            }
            // 4️⃣ Current user can still act
            else if (response.show_approve_button) {
                approveBtn.prop('disabled', false).text('Approve');
                rejectBtn.prop('disabled', false).text('Reject');
            }
            // 5️⃣ Fallback waiting/view only
            else if (response.show_view_button) {
                approveBtn.prop('disabled', true).text(response.waiting_reason || 'Waiting');
                rejectBtn.prop('disabled', true).text('Reject');
            }





            // === RATE + BREAKDOWN LOGIC ===
            const durationHoursBackend = parseFloat(booking.duration_hours ?? booking.duration_in_hours ?? NaN);
            const ratePerHourBackend = parseFloat(booking.final_rate ?? booking.function_room?.function_room_rate ?? NaN);
            const roomTotalBackend = parseFloat(booking.room_total ?? NaN);

            let hours = !isNaN(durationHoursBackend) ? durationHoursBackend : 1;
            if (!durationHoursBackend) {
                const startDate = parseTimeToDate(booking.event_start_time);
                const endDate = parseTimeToDate(booking.event_end_time);
                if (startDate && endDate) {
                    if (endDate <= startDate) endDate.setDate(endDate.getDate() + 1);
                    hours = Math.round(((endDate - startDate) / (1000 * 60 * 60)) * 100) / 100;
                    if (hours <= 0) hours = 1;
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
            } else $('#detail-rate').html('<span class="text-muted">N/A</span>');

            // Breakdown table
            let breakdownHtml = '';
            let addonsTotal = 0;

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

            if (booking.add_ons && booking.add_ons.length) {
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

            if (!breakdownHtml) breakdownHtml = `<tr><td colspan="4" class="text-center text-muted">No charges</td></tr>`;
            $('#detail-breakdown').html(breakdownHtml);

            const grandTotal = Math.round((roomLineTotal + addonsTotal) * 100) / 100;
            $('#detail-grand-total').text("₱" + Number(grandTotal).toLocaleString(undefined, { minimumFractionDigits: 2 }));

            hideSpinner();
            $('#functionRoomBookingDetailsRecordModal').modal('show');

        }).fail(function () {
            hideSpinner();
            alert('Something went wrong.');
        });
    });


    $('#download-function-room-booking-records').submit(function (e) {
        e.preventDefault();

        const $btn = $('#DownloadFunctionRoomBookingRecordsBtn');
        const originalWidth = $btn.outerWidth();

        $btn.attr('disabled', true)
            .html('<div class="spinner-border spinner-border-sm text-light"></div>')
            .css('width', originalWidth + 'px');

        const formData = $(this).serialize();

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            xhrFields: {
                responseType: 'blob' // Important to handle file download
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response, status, xhr) {
                const filename = xhr.getResponseHeader('Content-Disposition')
                    .split('filename=')[1]
                    .replace(/"/g, '');

                const blob = new Blob([response], { type: 'text/csv' });
                const link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                $('#DownloadFunctionRoomBookingRecords').modal('hide');
            },
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Download Failed',
                    text: xhr.responseJSON?.message || 'Something went wrong. Please try again.',
                    confirmButtonColor: '#d33'
                });
            },
            complete: function () {
                $btn.attr('disabled', false).html('Download').css('width', '');
            }
        });
    });
});
