$(document).ready(function () {


    $('#searchFormFunctionRoomBookings').on('submit', function (e) {
        e.preventDefault();
        currentFunctionRoomBookingSearchTerm = $('#searchInputFunctionRoomBooking').val();
        currentFunctionRoomBookingPageUrl = '/admin/admin-get-updated-function-room-bookings-table'; // reset to first page
        refreshFunctionRoomBookingsTable();
    });

    let currentFunctionRoomBookingPageUrl = '/admin/admin-get-updated-function-room-bookings-table';
    let currentFunctionRoomBookingSearchTerm = '';

    function renderStatusBadge(status) {
        if (status == 1) return '<span class="badge bg-success">Confirmed</span>';
        if (status == 2) return '<span class="badge bg-danger">Cancelled</span>';
        return '<span class="badge bg-warning">Waiting</span>';
    }

    function refreshFunctionRoomBookingsTable(url = currentFunctionRoomBookingPageUrl) {
        $.ajax({
            url: url,
            type: 'GET',
            data: { searchFunctionRoomBooking: currentFunctionRoomBookingSearchTerm },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function (response) {
                console.log($('#searchInputFunctionRooms').val());
                const bookings = response.data;
                const tableBody = $('#functionRoomBookingsTable tbody');
                const paginationContainerFunctionRoomBooking = $('.pagination-container-function-room-booking');

                $('[data-bs-toggle="tooltip"]').tooltip('dispose');
                tableBody.empty();

                if (!bookings.length) {
                    tableBody.append(`
                    <tr>
                        <td colspan="25" class="text-center">No Bookings Found</td>
                    </tr>
                `);
                } else {
                    bookings.forEach(function (b) {
                        let row = `
                    <tr>
                        <td>${b.transaction_no}</td>
                        <td>${b.unit_no}</td>
                        <td>${b.user?.name || 'N/A'}</td>
                        <td>${b.resident_type === 'TENANT'
                                ? '<span class="badge bg-danger">TENANT</span>'
                                : b.resident_type === 'OWNER'
                                    ? '<span class="badge bg-primary">OWNER</span>'
                                    : `<span class="badge bg-secondary">${b.resident_type}</span>`}
                        </td>
                        <td>${b.function_room?.function_room_name || 'N/A'}</td>
                        <td>${b.purpose_of_event || 'N/A'}</td>
                        <td>${b.function_room_booking_date || 'N/A'}</td>
                        <td>${b.event_start_time || 'N/A'}</td>
                        <td>${b.event_end_time || 'N/A'}</td>
                        <td>${b.contact_number || 'N/A'}</td>
                        <td>${b.pax || 'N/A'}</td>
                        <td>${b.payment_mode || 'N/A'}</td>
                        <td>${renderStatusBadge(b.booking_status)}</td>
                    `;

                        // Supplier column for roles 1,3,7
                        if ([1, 3, 7].includes(USER_ROLE)) {
                            row += `<td>${b.has_suppliers
                                ? '<span class="badge bg-success">Yes</span>'
                                : '<span class="badge bg-secondary">No</span>'}</td>`;
                        }

                        // Admin approval
                        if (USER_ROLE === 1) {
                            if (b.authorization_file) {
                                row += `
                                <td>${b.admin_approval ? '<span class="badge bg-success">Approved</span>' : '<span class="badge bg-warning">Waiting</span>'}</td>
                                <td>${b.admin_approver || '<span class="badge bg-warning">Waiting</span>'}</td>
                                <td>${b.admin_approved_at || '<span class="badge bg-warning">Waiting</span>'}</td>
                            `;
                            } else {
                                row += `<td><span class="badge bg-secondary">N/A</span></td>
                                    <td><span class="badge bg-secondary">N/A</span></td>
                                    <td><span class="badge bg-secondary">N/A</span></td>`;
                            }
                        } else {
                            row += `<td>${!b.authorization_file
                                ? '<span class="badge bg-secondary">N/A</span>'
                                : b.admin_approval
                                    ? '<span class="badge bg-success">Approved</span>'
                                    : '<span class="badge bg-warning">Waiting</span>'}</td>`;
                        }

                        // Finance approval
                        if (USER_ROLE === 1) {
                            row += `<td>${b.finance_approval ? '<span class="badge bg-success">Approved</span>' : '<span class="badge bg-warning">Waiting</span>'}</td>
                                <td>${b.finance_approver || '<span class="badge bg-warning">Waiting</span>'}</td>
                                <td>${b.finance_approved_at || '<span class="badge bg-warning">Waiting</span>'}</td>`;
                        } else {
                            row += `<td>${b.finance_approval ? '<span class="badge bg-success">Approved</span>' : '<span class="badge bg-warning">Waiting</span>'}</td>`;
                        }

                        // Engineering approval
                        if (USER_ROLE === 1) {
                            if (b.has_suppliers) {
                                row += `<td>${b.engineering_approval ? '<span class="badge bg-success">Approved</span>' : '<span class="badge bg-warning">Waiting</span>'}</td>
                                    <td>${b.engineering_approver || '<span class="badge bg-warning">Waiting</span>'}</td>
                                    <td>${b.engineering_approved_at || '<span class="badge bg-warning">Waiting</span>'}</td>`;
                            } else {
                                row += `<td><span class="badge bg-secondary">N/A</span></td>
                                    <td><span class="badge bg-secondary">N/A</span></td>
                                    <td><span class="badge bg-secondary">N/A</span></td>`;
                            }
                        } else {
                            row += `<td>${b.has_suppliers
                                ? (b.engineering_approval ? '<span class="badge bg-success">Approved</span>' : '<span class="badge bg-warning">Waiting</span>')
                                : '<span class="badge bg-secondary">N/A</span>'}</td>`;
                        }

                        // Manager approval
                        if (USER_ROLE === 1) {
                            row += `<td>${b.manager_approval ? '<span class="badge bg-success">Approved</span>' : '<span class="badge bg-warning">Waiting</span>'}</td>
                                <td>${b.manager_approver || '<span class="badge bg-warning">Waiting</span>'}</td>
                                <td>${b.manager_approved_at || '<span class="badge bg-warning">Waiting</span>'}</td>`;
                        } else {
                            row += `<td>${b.manager_approval ? '<span class="badge bg-success">Approved</span>' : '<span class="badge bg-warning">Waiting</span>'}</td>`;
                        }

                        // Created/Updated and View button
                        row += `<td>${b.created_at}</td>
                            <td>${b.updated_at}</td>
                            <td><button class="btn btn-sm btn-info view-booking-btn" data-id="${b.id}">View</button></td>
                        </tr>`;

                        tableBody.append(row);
                    });
                }

                paginationContainerFunctionRoomBooking.html(response.links);

                $('.pagination-container-function-room-booking').find('a').off('click').on('click', function (e) {
                    e.preventDefault();
                    const pageUrl = $(this).attr('href');
                    currentFunctionRoomBookingPageUrl = pageUrl;
                    refreshFunctionRoomTable(pageUrl);
                });

                $('[data-bs-toggle="tooltip"]').tooltip();
            },
            error: function (xhr, status, error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error loading bookings',
                    text: xhr.status + ': ' + xhr.statusText
                });
            }
        });
    }




    // function renderStatusBadge(status) {
    //     if (status === 1) return '<span class="badge bg-success">Confirmed</span>';
    //     if (status === 2) return '<span class="badge bg-danger">Cancelled</span>';
    //     return '<span class="badge bg-warning">Waiting</span>';
    // }

    function formatTime(time) {
        if (!time) return 'N/A';
        const date = new Date(time);
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    $('.approve-btn').on('click', function () {
        const approveBtn = $(this); // Store button reference
        const bookingId = approveBtn.data('id');
        const approvalType = approveBtn.data('type');

        Swal.fire({
            title: 'Are you sure?',
            text: `You are approving this booking as ${approvalType}.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, approve it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#functionRoomBookingDetailsModal').modal('hide');

                $.ajax({
                    url: "/admin/admin-function-room-bookings-approval",
                    type: 'GET',
                    data: {
                        booking_id: bookingId,
                        approval_type: approvalType
                    },
                    success: function (response) {
                        Swal.fire({
                            title: 'Approved!',
                            text: response.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        approveBtn.prop('disabled', true).text('Approved');
                        refreshFunctionRoomTable();
                    },
                    error: function (xhr) {
                        Swal.fire({
                            title: 'Error!',
                            text: xhr.responseJSON ? xhr.responseJSON.message : 'Something went wrong.',
                            icon: 'error'
                        });
                    }
                });
            }
        });
    });

    // Show spinner
    function showSpinner() {
        $('#global-loading').addClass('show');
    }

    // Hide spinner
    function hideSpinner() {
        $('#global-loading').removeClass('show');
    }

    $('.view-booking-btn').on('click', function () {
        const bookingId = $(this).data('id');
        showSpinner();

        // reset modal fields to "Loading..."
        $('#detail-transaction-no, #detail-unit, #detail-name, #detail-contact, #detail-resident-type, #detail-function-room, #detail-purpose, #detail-status, #detail-booking-date, #detail-start-time, #detail-end-time, #detail-pax, #detail-payment-mode, #detail-authorization, #detail-suppliers, #detail-rate, #detail-breakdown, #detail-grand-total')
            .html('<span class="text-muted">Loading...</span>');

        const approveBtn = $('#approveBookingBtn');
        approveBtn.addClass('d-none').prop('disabled', false).text('Approve');
        approveBtn.data('id', bookingId);

        function parseTimeToDate(timeStr) {
            if (!timeStr) return null;
            timeStr = timeStr.trim();
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

            // Basic fields
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

            // Status logic
            let statusBadge = '';
            const today = new Date(); today.setHours(0, 0, 0, 0);
            const bookingDate = booking.function_room_booking_date ? new Date(booking.function_room_booking_date) : null;
            if (booking.booking_status == 1) {
                if (bookingDate && bookingDate < today) {
                    statusBadge = '<span class="badge bg-secondary">Completed</span>';
                } else {
                    statusBadge = '<span class="badge bg-success">Confirmed</span>';
                }
            } else if (booking.booking_status == 0) {
                statusBadge = '<span class="badge bg-warning">Waiting</span>';
            } else if (booking.booking_status == 2) {
                statusBadge = '<span class="badge bg-danger">Cancelled</span>';
            } else {
                statusBadge = '<span class="badge bg-dark">Unknown</span>';
            }
            $('#detail-status').html(statusBadge);

            $('#detail-booking-date').text(booking.function_room_booking_date ?? 'N/A');
            $('#detail-start-time').text(booking.event_start_time ?? 'N/A');
            $('#detail-end-time').text(booking.event_end_time ?? 'N/A');
            $('#detail-pax').text(booking.pax ?? 'N/A');
            $('#detail-payment-mode').text(booking.payment_mode ?? 'N/A');

            // Authorization
            if (response.authorization_file_url) {
                $('#detail-authorization').html(`<a href="${response.authorization_file_url}" target="_blank" class="custom-link">View</a>`);
            } else {
                $('#detail-authorization').html('<span class="text-muted">N/A</span>');
            }

            // Suppliers
            if (booking.suppliers && booking.suppliers.length > 0) {
                let suppliersHtml = '';
                booking.suppliers.forEach(s => {
                    suppliersHtml += `<div>${s.name} ${s.attachment_url ? `<a href="${s.attachment_url}" target="_blank" class="custom-link">View</a>` : ''}</div>`;
                });
                $('#detail-suppliers').html(suppliersHtml);
            } else {
                $('#detail-suppliers').html('<span class="text-muted">N/A</span>');
            }

            // Approve button logic
            if (booking.booking_status == 1) {
                approveBtn.addClass('d-none');
            } else if (response.show_approve_button) {
                approveBtn.removeClass('d-none').prop('disabled', false).text('Approve');
            } else if (response.waiting_for_approval) {
                approveBtn.removeClass('d-none').prop('disabled', true).text('Waiting');
            } else if (response.is_approved) {
                approveBtn.removeClass('d-none').prop('disabled', true).text('Approved');
            } else {
                approveBtn.removeClass('d-none').prop('disabled', true).text('Waiting');
            }

            // === RATE + BREAKDOWN (same as user-side) ===
            const durationHoursBackend = parseFloat(booking.duration_hours ?? booking.duration_in_hours ?? NaN);
            const ratePerHourBackend = parseFloat(booking.final_rate ?? booking.function_room?.function_room_rate ?? NaN);
            const roomTotalBackend = parseFloat(booking.room_total ?? NaN);

            // Compute duration (fallback to parsing)
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
                <small class="text-success">₱${Number(ratePerHour).toLocaleString(undefined, { minimumFractionDigits: 2 })}/hr</small>
                &nbsp; × &nbsp;
                <small>${Number(hours).toLocaleString(undefined, { minimumFractionDigits: (hours % 1 ? 2 : 0) })} hr(s)</small>
                &nbsp; = &nbsp;
                <strong class="text-success">₱${Number(roomLineTotal).toLocaleString(undefined, { minimumFractionDigits: 2 })}</strong>
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

            // === Breakdown Table ===
            let breakdownHtml = '';
            let addonsTotal = 0;

            // Function Room row
            if (ratePerHour && ratePerHour > 0) {
                breakdownHtml += `
        <tr>
            <td>Function Room Rate (${Number(hours).toLocaleString(undefined, { minimumFractionDigits: (hours % 1 ? 2 : 0) })} hr${hours > 1 ? 's' : ''})</td>
            <td class="text-center">${Number(hours).toLocaleString(undefined, { minimumFractionDigits: (hours % 1 ? 2 : 0) })}</td>
            <td class="text-end">₱${Number(ratePerHour).toLocaleString(undefined, { minimumFractionDigits: 2 })}/hr</td>
            <td class="text-end">₱${Number(roomLineTotal).toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>
        </tr>
    `;
            }

            // Add-ons rows
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

            // No charges fallback
            if (!breakdownHtml) {
                breakdownHtml = `<tr><td colspan="4" class="text-center text-muted">No charges</td></tr>`;
            }

            $('#detail-breakdown').html(breakdownHtml);

            // Grand Total
            const grandTotal = Math.round((roomLineTotal + addonsTotal) * 100) / 100;
            $('#detail-grand-total').text("₱" + Number(grandTotal).toLocaleString(undefined, { minimumFractionDigits: 2 }));


            hideSpinner();
            $('#functionRoomBookingDetailsModal').modal('show');
        }).fail(function () {
            hideSpinner();
            alert('Something went wrong.');
        });
    });



    $('.AdminAddFunctionRoomBooking').on('click', function () {
        $('#adminFunctionRoomBookingModal').modal('show');
    });

    // $('.DownloadFunctionRoomBooking').on('click', function () {
    //     $('#DownloadFunctionRoomBookingModal').modal('show');
    // });

    $('#functionRoomBookingsTable').on('click', '.edit-booking-btn', function () {
        let bookingId = $(this).data("id");

        showSpinner();

        $.get('/admin/admin-bookings/' + bookingId + '/edit', function (res) {
            const booking = res.booking;
            const addons = res.addons;
            $('#booking_id').val(booking.id);
            $('#function_room_id').val(booking.function_room_id);
            // --- Resident Info ---
            const residentName = booking.user?.name ?? '';
            const unitNo = booking.unit_no ?? '';
            const residentType = booking.resident_type
                ? booking.resident_type.charAt(0).toUpperCase() + booking.resident_type.slice(1)
                : '';

            const displayValue = `${residentName} - Unit ${unitNo} - ${residentType}`;

            $('#editTransactionNo').text(booking.transaction_no ?? 'N/A');
            $('#editResidentDisplay')
                .text(displayValue)
                .data('type', booking.resident_type)
                .data('unit', booking.unit_no);

            // --- Authorization file ---
            if (res.authorization_file) {
                $('#authorizationPreview').removeClass('d-none');
                $('#authorizationViewLink').attr('href', res.authorization_file);
                $('input[name="authorization_file"]').data('existing', true);
            } else {
                $('#authorizationPreview').addClass('d-none');
                $('input[name="authorization_file"]').data('existing', false);
            }

            // --- Booking Info ---
            $('#editFunctionRoomName').text(booking.function_room.function_room_name);
            $('#roomCapacity').val(booking.function_room.function_room_capacity);
            $('#editRoomCapacity').text(booking.function_room.function_room_capacity);

            $('#editPurposeOfEvent').val(booking.purpose_of_event);
            $('#editStartTime').val(booking.event_start_time);
            $('#editEndTime').val(booking.event_end_time);
            $('#editPaxInput')
                .val(booking.pax)
                .attr('max', booking.function_room.function_room_capacity);
            $('#editContactNumber').val(booking.contact_number);

            // --- Payment Mode ---
            const paymentModes = ['Charge to account', 'Advance Payment'];
            let paymentHtml = '';
            paymentModes.forEach(pm => {
                const checked = pm === booking.payment_mode ? 'checked' : '';
                paymentHtml += `
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="payment_mode" value="${pm}" ${checked} required>
                    <label class="form-check-label">${pm}</label>
                </div>
            `;
            });
            $('#editPaymentModeWrapper').html(paymentHtml);

            // --- Add-ons ---
            let addonHtml = '';
            addons.forEach(addon => {
                const booked = booking.add_ons.find(a => a.id == addon.id);
                const qty = booked ? booked.pivot.qty : 0;

                addonHtml += `
                <div class="col-12">
                    <div class="border rounded p-2 h-100">
                        <div class="form-check">
                            <input type="hidden" name="addons[${addon.id}][selected]" value="0">
                            <input type="checkbox" class="form-check-input addOnsFields" 
                                name="addons[${addon.id}][selected]" value="1" 
                                id="addon${addon.id}" ${qty > 0 ? 'checked' : ''} 
                                data-max="${addon.qty}">
                            <label class="form-check-label fw-bold" for="addon${addon.id}">
                                ${addon.item} (₱${parseFloat(addon.price).toFixed(2)})
                            </label>
                        </div>
                        <div class="mt-2">
                            <label class="form-label small mb-1">Quantity</label>
                            <input type="number" name="addons[${addon.id}][qty]" 
                                class="form-control form-control-sm addonQty" 
                                min="1" value="${qty}" max="${addon.qty}" 
                                data-addon-id="${addon.id}" ${qty == 0 ? 'disabled' : ''}>
                        </div>
                        <small class="text-muted d-block mt-2" id="addonAvailable${addon.id}">
                            Available: ${addon.qty}
                        </small>
                    </div>
                </div>
            `;
            });
            $('#editAddonsWrapper').html(addonHtml);

            // --- Suppliers ---
            if (booking.has_suppliers && booking.suppliers.length > 0) {
                $('#hasSuppliers').prop('checked', true);
                $('#supplierSection').removeClass('d-none');

                let supplierHtml = '';
                booking.suppliers.forEach((s, index) => {
                    supplierHtml += `
                    <div class="row g-2 supplier-item mb-2">
                        <div class="col-md-4">
                            <input type="text" name="suppliers[${index}][name]" class="form-control" 
                                placeholder="Supplier Name" value="${s.name}">
                        </div>
                        <div class="col-md-6">
                            <a href="/assets/frontend/uploads/function-room-bookings/suppliers/${s.attachment}" 
                               target="_blank" class="btn btn-sm btn-outline-primary mb-1">View File</a>
                            <input type="file" name="suppliers[${index}][attachment]" class="form-control mt-1">
                        </div>
                        <div class="col-md-2 d-flex align-items-center">
                            <button type="button" class="btn btn-sm btn-danger removeSupplier">Remove</button>
                        </div>
                    </div>
                `;
                });
                $('#suppliersWrapper').html(supplierHtml);
            } else {
                $('#hasSuppliers').prop('checked', false);
                $('#supplierSection').addClass('d-none');
                $('#suppliersWrapper').html(`
                <div class="row g-2 supplier-item mb-2">
                    <div class="col-md-4">
                        <input type="text" name="suppliers[0][name]" class="form-control" placeholder="Supplier Name">
                    </div>
                    <div class="col-md-6">
                        <input type="file" name="suppliers[0][attachment]" class="form-control">
                    </div>
                </div>
            `);
            }

            // --- Flatpickr ---
            $.get('/admin/admin-function-room/' + booking.function_room_id + '/booked-dates', function (disabledDates) {
                let dateInput = document.getElementById("editFunctionRoomBookingDate");
                if (dateInput._flatpickr) dateInput._flatpickr.destroy();

                flatpickr("#editFunctionRoomBookingDate", {
                    dateFormat: "Y-m-d",
                    minDate: new Date().fp_incr(6),
                    defaultDate: booking.function_room_booking_date,
                    disable: disabledDates.filter(d => d !== booking.function_room_booking_date),
                    onChange: function (selectedDates, dateStr) {
                        if (!dateStr) return;

                        $.get('/admin/admin-function-room/addons-availability', {
                            date: dateStr,
                            room_id: booking.function_room_id
                        }, function (res) {
                            $(".addonQty").each(function () {
                                const addonId = $(this).data("addon-id");
                                const available = res[addonId] ?? 0;
                                const checkbox = $("#addon" + addonId);

                                $("#addonAvailable" + addonId).text("Available: " + available);

                                if (available <= 0) {
                                    $(this).prop("disabled", true).val(0).prop("required", false);
                                    checkbox.prop("disabled", true).prop("checked", false);
                                } else {
                                    $(this).attr("max", available).val(0).prop("disabled", true).prop("required", false);
                                    checkbox.prop("disabled", false).prop("checked", false);
                                }
                            });
                        });
                    }
                });
            });

            hideSpinner();
            $('#adminEditBookingModal').modal('show');
            checkAuthorizationEdit(); // initial check
        }).fail(function () {
            hideSpinner();
            Swal.fire('Error', 'Unable to fetch booking data.', 'error');
        });
    });


    // --- Event delegation for dynamic payment mode radios ---
    $('#editPaymentModeWrapper').on('change', 'input[name="payment_mode"]', checkAuthorizationEdit);

    // --- Event delegation for suppliers toggle ---
    $('#hasSuppliers').on('change', function () {
        $('#supplierSection').toggleClass('d-none', !this.checked);
    });

    function checkAuthorizationEdit() {
        const $residentDisplay = $('#editResidentDisplay');
        const residentType = $residentDisplay.data('type')?.toLowerCase();
        const paymentMode = $('input[name="payment_mode"]:checked').val();

        const $wrapper = $('#authorizationUploadWrapper');
        const $fileInput = $('input[name="authorization_file"]');
        const hasExisting = $fileInput.data('existing') === true || $fileInput.data('existing') === 'true';

        // Reset
        $wrapper.addClass('d-none');
        $fileInput.prop('required', false);
        $('#authorizationLabel').text('');
        $('#authorizationNote').text('');

        if (residentType === 'tenant' && paymentMode === 'Charge to account') {
            $('#authorizationLabel').text('CTA Authorization Letter *');
            $('#authorizationNote').text('Required because you are booking as a tenant with CTA.');
            $wrapper.removeClass('d-none');

            // ✅ Only require if no existing file
            if (!hasExisting) {
                $fileInput.prop('required', true);
            }
        }
    }


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
                <button type="button" class="btn btn-danger removeSupplier">
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

    $('#adminEditBookingForm').submit(function (event) {
        event.preventDefault();
        const form = this;
        let isValid = true;
        const startTime = $('#editStartTime').val();
        const endTime = $('#editEndTime').val();
        const pax = parseInt($('#editPaxInput').val() || 0);
        const roomCapacity = parseInt($('#editRoomCapacity').text() || 0);

        function convertToMinutes(time) {
            if (!time) return null;
            const [hours, minutes] = time.split(':').map(Number);
            return hours * 60 + minutes;
        }

        const startMinutes = convertToMinutes(startTime);
        const endMinutes = convertToMinutes(endTime);


        if (startMinutes !== null && endMinutes !== null && endMinutes <= startMinutes) {
            $('#timeError').removeClass('d-none');
            $('#editEndTime').addClass('is-invalid');
            isValid = false;
        } else {
            $('#timeError').addClass('d-none');
            $('#editEndTime').removeClass('is-invalid');
        }

        if (pax > roomCapacity) {
            $('#capacityError').removeClass('d-none');
            $('#editPaxInput').addClass('is-invalid');
            isValid = false;
        } else {
            $('#capacityError').addClass('d-none');
            $('#editPaxInput').removeClass('is-invalid');
        }

        if (!isValid) return;

        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }
        form.classList.remove('was-validated');

        const $btn = $('#updateFunctionRoomBookingBtn');
        const originalWidth = $btn.outerWidth();
        $btn
            .attr('disabled', true)
            .html(`<div class="spinner-border spinner-border-sm text-light"></div>`)
            .css('width', originalWidth + 'px');

        const formData = new FormData(form);

        $.ajax({
            url: $(form).attr('action'),
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                $('#adminEditBookingModal').modal('hide');
                form.reset();
                const Toast = Swal.mixin({
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                    customClass: {
                        popup: 'colored-toast'
                    },
                    didOpen: (toast) => {
                        toast.onmouseenter = Swal.stopTimer;
                        toast.onmouseleave = Swal.resumeTimer;
                    }
                });

                Toast.fire({
                    icon: 'success',
                    title: 'Updated Successfully'
                });

                form.reset();
                $(form).removeClass('was-validated');
                refreshFunctionRoomBookingsTable();
            },
            error: function (xhr) {
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

                if (xhr.status === 422) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: xhr.responseJSON.message,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#d33'
                    });
                    return;
                }

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
                    .html(`<span class="btn-text">Update</span>`)
                    .css('width', '');
            }
        });
    });


});