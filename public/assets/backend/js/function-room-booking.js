$(document).ready(function () {

    const linkedRooms = {
        5: [6],
        6: [5]
    };

    let flatpickrInstance;
    let disabledDatesGlobal = [];



    let currentFunctionRoomBookingPageUrl = '/admin/admin-get-updated-function-room-bookings-table';
    let currentFunctionRoomBookingPage = 1;
    let currentFunctionRoomBookingSearchTerm = '';

    function refreshFunctionRoomBookingsTable(url = currentFunctionRoomBookingPageUrl) {

        const pageMatch = url.match(/page=(\d+)/);
        const currentPage = pageMatch ? pageMatch[1] : 1;

        $.ajax({
            url: url,
            type: 'GET',
            data: {
                searchFunctionRoomBooking: currentFunctionRoomBookingSearchTerm,
                page: currentPage
            },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function (response) {
                const bookings = response.data;
                const tableBody = $('#functionRoomBookingsTable tbody');
                const paginationContainerFunctionRoomBooking = $('.pagination-container-function-room-booking');
                paginationContainerFunctionRoomBooking.html(response.links);
                const pageMatch = url.match(/page=(\d+)/);
                currentFunctionRoomBookingPage = pageMatch ? pageMatch[1] : 1;

                tableBody.empty();

                if (!bookings.length) {
                    tableBody.append(`
                    <tr>
                        <td colspan="25" class="text-center">No Bookings Found</td>
                    </tr>
                `);
                    return;
                }

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
                        <td>${b.base_rate ? '₱' + parseFloat(b.base_rate).toFixed(2) : 'N/A'}</td>
                        <td>${b.discount > 0
                            ? `<span class="badge bg-success">${parseFloat(b.discount).toString().replace(/\.0+$/, '')
                            }%</span>`
                            : '<span class="badge bg-secondary">0%</span>'
                        }</td>
                        <td>${b.discount_remarks || 'N/A'}</td>
                        <td>${b.final_rate || 'N/A'}</td>
                        <td>${b.payment_mode || 'N/A'}</td>
                        <td>${renderStatusBadge(b.booking_status)}</td>
                `;

                    // Supplier Column
                    if ([1, 3, 7, 6].includes(USER_ROLE)) {
                        row += `<td>${b.has_suppliers
                            ? '<span class="badge bg-success">Yes</span>'
                            : '<span class="badge bg-secondary">No</span>'}</td>`;
                    }

                    // Approval helper
                    function approvalColumns(type, requiresAuth = false, requiresSupplier = false) {
                        let cols = '';
                        let approval = b[`${type}_approval`];
                        let remarks = b[`${type}_remarks`] || null;
                        let approver = b[`${type}_approver`] || '<span class="badge bg-warning">Waiting</span>';
                        let actionAt = b[`${type}_action_at`] || '<span class="badge bg-warning">Waiting</span>';

                        // 🔹 If this approver is NOT required → show N/A (same as Blade)
                        if ((requiresAuth && !b.authorization_file) || (requiresSupplier && !b.has_suppliers)) {
                            if ([1, 6].includes(USER_ROLE)) {
                                cols += `<td><span class="badge bg-secondary">N/A</span></td>`;
                                cols += `<td>${remarks ? `<span class="badge bg-info">${remarks}</span>` : '<span class="badge bg-secondary">N/A</span>'}</td>`;
                                cols += `<td><span class="badge bg-secondary">N/A</span></td>`;
                                cols += `<td><span class="badge bg-secondary">N/A</span></td>`;
                            } else {
                                cols += `<td><span class="badge bg-secondary">N/A</span></td>`;
                                cols += `<td>${remarks ? remarks : '<span class="badge bg-secondary">N/A</span>'}</td>`;
                            }
                        } else {
                            // 🔹 Normal approval flow
                            if ([1, 6].includes(USER_ROLE)) {
                                cols += `<td>${renderApprovalBadge(approval)}</td>`;
                                cols += `<td>${remarks ? `<span class="badge bg-info">${remarks}</span>` : '<span class="badge bg-secondary">N/A</span>'}</td>`;
                                cols += `<td>${approver}</td>`;
                                cols += `<td>${actionAt}</td>`;
                            } else {
                                cols += `<td>${renderApprovalBadge(approval)}</td>`;
                                cols += `<td>${remarks ? remarks : '<span class="badge bg-secondary">N/A</span>'}</td>`;
                            }
                        }

                        return cols;
                    }
                    // Concierge (no auth required)
                    row += approvalColumns('concierge');
                    // Admin (requires authorization file)
                    row += approvalColumns('admin', true);
                    // Finance (requires authorization file)
                    row += approvalColumns('finance');
                    // Engineering (requires supplier)
                    row += approvalColumns('engineering', false, true);
                    // Manager (no additional requirements)
                    row += approvalColumns('manager');

                    // Created/Updated + Actions
                    row += `
                        <td>${b.created_at}</td>
                        <td>${b.updated_at}</td>
                        <td class="sticky-action-col">
                            <button class="btn btn-sm btn-info view-booking-btn mb-2" data-id="${b.id}" style="width: 60px;">View</button>
                            ${USER_ROLE == 6 ? `<button class="btn btn-sm btn-warning edit-booking-btn" data-id="${b.id}" style="width: 60px;">Edit</button>` : ''}
                        </td>
                    </tr>
                `;

                    tableBody.append(row);
                });

                paginationContainerFunctionRoomBooking.html(response.links);
                paginationContainerFunctionRoomBooking.find('a').on('click', function (e) {
                    e.preventDefault();
                    const newUrl = $(this).attr('href');
                    if (newUrl) {
                        currentFunctionRoomBookingPageUrl = newUrl; // ✅ already includes ?page=2, etc.
                        refreshFunctionRoomBookingsTable(currentFunctionRoomBookingPageUrl);
                    }
                });

            },
            error: function (xhr) {
                console.error('Error:', xhr.responseText);
            }
        });
    }

    window.refreshFunctionRoomBookingsTable = refreshFunctionRoomBookingsTable;
    window.currentFunctionRoomBookingPageUrl = currentFunctionRoomBookingPageUrl;
    window.currentFunctionRoomBookingPage = currentFunctionRoomBookingPage;
    window.currentFunctionRoomBookingSearchTerm = currentFunctionRoomBookingSearchTerm;


    $('.AdminAddFunctionRoomBooking').on('click', function () {
        $('#adminFunctionRoomBookingModal').modal('show');
        $(".addonQty").each(function () {
            $(this).prop("disabled", true).val(0);
        });

        $(".text-muted[id^='addonAvailable']").text("Available: -");
    });

    $(document).on("change", ".adminAddOnsFields", function () {
        let addonId = $(this).attr("id").replace("addon", "");
        let qtyInput = $("input[data-addon-id='" + addonId + "']");

        if ($(this).is(":checked")) {
            qtyInput.prop("disabled", false).prop("required", true);
            if (parseInt(qtyInput.val()) === 0) qtyInput.val(1);
        } else {
            qtyInput.prop("disabled", true).prop("required", false).val(0);
        }
    });

    $(document).on("input", ".addonQty", function () {
        let max = parseInt($(this).attr("max")) || 0;
        let val = parseInt($(this).val()) || 0;

        if (val > max) $(this).val(max);
        else if (val < 0) $(this).val(0);
    });

    $('#functionRoomSelect').on('change', function () {
        const $selected = $(this).find('option:selected');
        const roomId = $selected.val();
        const roomName = $selected.text().trim();
        const capacity = parseInt($selected.data('capacity') || 0, 10);

        const selectedRoomId = $(this).val();

        // Hide options first
        $('#linkedRoomOptionWrapper').addClass('d-none');
        $('#adminBookLinkedRoom').prop('checked', false);

        if (!selectedRoomId) return;

        const linked = linkedRooms[selectedRoomId] || [];

        if (linked.length > 0) {
            const linkedNames = linked.map(id => {
                const option = $(`#functionRoomSelect option[value="${id}"]`);
                return option.text().trim();
            }).join(', ');

            $('#linkedRoomLabel').text(`You may also book: ${linkedNames}?`);
            $('#linkedRoomOptionWrapper').removeClass('d-none');
        }


        // 🏠 Update room info
        $('#roomCapacity').val(capacity);
        $('#roomCapacityDisplay').text(capacity);
        $('#paxInput').attr('max', capacity);

        // 🧾 Update modal title
        const nameOnly = $selected.data('name') || roomName.split('(')[0].trim();
        $('#adminModalTitle').text(nameOnly ? `${nameOnly} — Booking` : 'Function Room Booking');

        // 🔢 Validate pax
        const currentPax = parseInt($('#paxInput').val() || '0', 10);
        $('#capacityError').toggleClass('d-none', !(capacity > 0 && currentPax > capacity));

        // 🚀 Fetch booked/blocked dates
        if (roomId) {
            $('#adminFunctionRoomBookingDate').val('').prop('disabled', true);

            $.ajax({
                url: `/admin/admin-function-room-booked-dates/${roomId}`,
                method: 'GET',
                success: function (disabledDates) {
                    console.log('Disabled dates:', disabledDates);
                    disabledDatesGlobal = disabledDates;

                    // Destroy old instance
                    if (flatpickrInstance) flatpickrInstance.destroy();

                    // ✅ Initialize Flatpickr ONCE
                    flatpickrInstance = flatpickr("#adminFunctionRoomBookingDate", {
                        dateFormat: "Y-m-d",
                        minDate: "today",
                        disable: disabledDatesGlobal,
                        onChange: function (selectedDates, dateStr) {
                            if (!dateStr) return;

                            // 🧮 Fetch add-ons availability dynamically
                            $.get('/admin/admin-get-function-room-addons-availability', {
                                date: dateStr,
                                room_id: roomId
                            }, function (res) {
                                $(".addonQty").each(function () {
                                    let addonId = $(this).data("addon-id");
                                    let available = res[addonId] ?? 0;
                                    let checkbox = $("#addon" + addonId);

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

                    $('#adminFunctionRoomBookingDate').prop('disabled', false);
                },
                error: function () {
                    console.error('Failed to load booked dates.');
                    $('#adminFunctionRoomBookingDate').prop('disabled', false);
                }
            });
        } else {
            $('#adminFunctionRoomBookingDate').val('').prop('disabled', true);
        }
    });

    // ✅ Prevent selecting disabled dates manually
    $('#adminFunctionRoomBookingDate').on('change', function () {
        const selectedDate = $(this).val();
        if (disabledDatesGlobal.includes(selectedDate)) {
            $('#dateError').removeClass('d-none');
            $(this).val('');
        } else {
            $('#dateError').addClass('d-none');
        }
    });


    // ✅ Auto-initialize if a room is already selected (edit mode or reopen modal)
    (function initSelectedRoom() {
        const $sel = $('#functionRoomSelect').find('option:selected');
        if ($sel.length && $sel.val() !== '') {
            $('#functionRoomSelect').trigger('change');
        }
    })();

    $('#searchFormFunctionRoomBookings').on('submit', function (e) {
        e.preventDefault();
        currentFunctionRoomBookingSearchTerm = $('#searchInputFunctionRoomBooking').val();
        currentFunctionRoomBookingPageUrl = '/admin/admin-get-updated-function-room-bookings-table'; // reset to first page
        refreshFunctionRoomBookingsTable();
    });


    let adminSupplierIndex = 1;

    $('#adminHasSuppliers').on('change', function () {
        if ($(this).is(':checked')) {
            $('#adminSupplierSection').removeClass('d-none');

            // if empty, add back the first supplier row
            if ($('#adminSuppliersWrapper').children().length === 0) {
                let firstRow = `
            <div class="row g-2 supplier-item mb-2">
                <div class="col-md-4">
                    <input type="text" name="suppliers[0][name]" class="form-control" placeholder="Supplier Name">
                </div>
                <div class="col-md-6">
                    <input type="file" name="suppliers[0][attachment]" class="form-control" accept="image/*,.pdf">
                </div>
            </div>`;
                $('#adminSuppliersWrapper').html(firstRow);
                adminSupplierIndex = 1;
            }

        } else {
            $('#adminSupplierSection').addClass('d-none');
            $('#adminSuppliersWrapper').html('');
            adminSupplierIndex = 1;
        }
    });

    $('#adminAddSupplier').on('click', function () {
        let newRow = `
        <div class="row g-2 supplier-item mb-2">
            <div class="col-md-4">
                <input type="text" name="suppliers[${adminSupplierIndex}][name]" class="form-control" placeholder="Name">
            </div>
            <div class="col-md-6">
                <input type="file" name="suppliers[${adminSupplierIndex}][attachment]" class="form-control" accept="image/*,.pdf">
            </div>
            <div class="col-md-2 d-flex align-items-center">
                <button type="button" class="btn btn-danger adminRemoveSupplier">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </div>`;
        $('#adminSuppliersWrapper').append(newRow);
        adminSupplierIndex++;
    });

    $(document).on('click', '.adminRemoveSupplier', function () {
        $(this).closest('.supplier-item').remove();
    });



    $('#adminFunctionRoomNewBooking').on('submit', function (event) {
        event.preventDefault();

        const form = this;
        let isValid = true;
        const startTime = $('#startTime').val();
        const endTime = $('#endTime').val();

        // Convert time to minutes for comparison
        function convertToMinutes(time) {
            if (!time) return null;
            const [hours, minutes] = time.split(':').map(Number);
            return hours * 60 + minutes;
        }

        const startMinutes = convertToMinutes(startTime);
        const endMinutes = convertToMinutes(endTime);

        // Validate time logic
        if (startMinutes !== null && endMinutes !== null && endMinutes <= startMinutes) {
            $('#timeError').removeClass('d-none');
            $('#endTime').addClass('is-invalid');
            isValid = false;
        } else {
            $('#timeError').addClass('d-none');
            $('#endTime').removeClass('is-invalid');
        }

        if (!isValid) return;

        // Validate required fields
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }
        form.classList.remove('was-validated');

        // Submit button spinner
        const $btn = $('#saveUserFunctionRoomBtn');
        const originalWidth = $btn.outerWidth();
        $btn
            .attr('disabled', true)
            .html(`<div class="spinner-border spinner-border-sm text-light"></div>`)
            .css('width', originalWidth + 'px');

        // Prepare form data
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
                $('#adminFunctionRoomBookingModal').modal('hide');

                Swal.fire({
                    icon: 'success',
                    title: 'Booking Saved!',
                    text: response.message || 'The booking has been successfully saved.',
                    timer: 2000,
                    showConfirmButton: false
                });

                form.reset();
                $(form).removeClass('was-validated');
                $('#adminSupplierSection, #adminAuthorizationUploadWrapper').addClass('d-none');

                // Optional: refresh table if you use DataTables
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
                        title: 'Not Enough Add-ons',
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
                    .html(`<span class="btn-text">SUBMIT</span>`)
                    .css('width', '');
            }
        });
    });

    function formatTime(time) {
        if (!time) return 'N/A';
        const date = new Date(time);
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }


    function roundToHour(time) {
        let [hours, minutes] = time.split(':').map(Number);
        if (isNaN(hours)) return ''; // handle empty input
        if (minutes >= 30) hours = (hours + 1) % 24;
        return (hours < 10 ? '0' : '') + hours + ':00';
    }

    $('#startTime, #endTime').on('change blur', function () {
        const rounded = roundToHour($(this).val());
        $(this).val(rounded);
    });

    // Optional: Prevent manual entry of minutes
    $('#startTime, #endTime').on('input', function () {
        const val = $(this).val();
        if (!/^[0-9]{2}:(00)$/.test(val)) {
            $(this).val(roundToHour(val));
        }
    });







    $('#residentSelectAdmin').select2({
        placeholder: '-- Search Resident --',
        allowClear: true,
        dropdownParent: $('#adminFunctionRoomBookingModal'), // ✅ this is the fix
        ajax: {
            url: '/admin/admin-search-residents',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    term: params.term || ''
                };
            },
            processResults: function (data) {
                return {
                    results: data.results
                };
            },
            cache: true
        }
    });


    $('#residentSelectAdmin').on('select2:select', function (e) {
        const data = e.params.data;

        $('#unitNoAdmin').val(data.unit_no);
        $('#residentTypeAdmin').val(data.resident_type);

        $('input[name="admin_payment_mode"]').prop('checked', false);

        adminHideAuthorization();
    });

    $('input[name="admin_payment_mode"]').on('change', adminCheckAuthorization);

    function adminHideAuthorization() {
        const $wrapper = $('#adminAuthorizationUploadWrapper');
        const $fileInput = $('input[name="admin_authorization_file"]');

        $wrapper.addClass('d-none');
        $('#adminAuthorizationLabel').text('');
        $('#adminAuthorizationNote').text('');
        $fileInput.prop('required', false);
        $fileInput.val('');
    }

    function adminCheckAuthorization() {
        const residentType = ($('#residentTypeAdmin').val() || '').trim().toLowerCase();
        const unitNo = ($('#unitNoAdmin').val() || '').trim();
        const paymentMode = ($('input[name="admin_payment_mode"]:checked').val() || '').trim();

        const $wrapper = $('#adminAuthorizationUploadWrapper');
        const $fileInput = $('input[name="admin_authorization_file"]');

        adminHideAuthorization();

        if (!residentType) return;

        // --- CASE 1: Tenant + Charge to Account → SHOW upload ---
        if (residentType === 'tenant' && paymentMode === 'Charge to Account') {
            $('#adminAuthorizationLabel').text('CTA Authorization Letter *');
            $('#adminAuthorizationNote').text('Required because you are booking as a tenant with CTA.');
            $wrapper.removeClass('d-none');
            $fileInput.prop('required', true);
            return;
        }

        // --- CASE 2: Owner + Has Tenant → ALWAYS show upload regardless of payment mode ---
        if (residentType === 'owner' && unitNo) {
            $.get('/admin/admin-check-unit-tenant/' + encodeURIComponent(unitNo))
                .done(function (response) {
                    if (response && response.hasTenant) {
                        $('#adminAuthorizationLabel').text('Tenant Authorization Letter *');
                        $('#adminAuthorizationNote').text('Required because the unit is tenanted.');
                        $wrapper.removeClass('d-none');
                        $fileInput.prop('required', true);
                    } else {
                        adminHideAuthorization();
                    }
                })
                .fail(function () {
                    console.error('Failed to check unit tenancy.');
                    adminHideAuthorization();
                });
        }
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
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        booking_id: bookingId

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
                        refreshFunctionRoomBookingsTable(currentFunctionRoomBookingPageUrl);
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


    $('.reject-btn').on('click', function () {
        const bookingId = $('#approveBookingBtn').data('id');
        const department = $('#approveBookingBtn').data('type');

        // Close the booking modal first
        $('#functionRoomBookingDetailsModal').modal('hide');

        Swal.fire({
            title: 'Reject Booking',
            input: 'textarea',
            inputLabel: 'Remarks (required)',
            inputPlaceholder: 'Enter reason for rejection...',
            inputAttributes: { 'aria-label': 'Enter reason for rejection' },
            showCancelButton: true,
            confirmButtonText: 'Reject',
            confirmButtonColor: '#d33',
            didOpen: () => {
                Swal.getInput().focus();
            },
            preConfirm: (remarks) => {
                if (!remarks) {
                    Swal.showValidationMessage('Remarks are required!');
                    return false;
                }
                return remarks;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "/admin/admin-function-room-bookings-rejection",
                    type: 'POST',
                    data: {
                        booking_id: bookingId,
                        department: department,
                        remarks: result.value,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        Swal.fire({
                            title: 'Rejected!',
                            text: response.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        refreshFunctionRoomBookingsTable(currentFunctionRoomBookingPageUrl);
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

    $('#functionRoomBookingsTable').on('click', '.view-booking-btn', function () {
        const bookingId = $(this).data('id');
        showSpinner();
        const fields = [
            '#detail-transaction-no', '#detail-unit', '#detail-name', '#detail-contact', '#detail-resident-type',
            '#detail-function-room', '#detail-purpose', '#detail-status', '#detail-booking-date', '#detail-start-time',
            '#detail-end-time', '#detail-pax', '#detail-payment-mode', '#detail-authorization', '#detail-suppliers',
            '#detail-rate', 'detail-discount', '#detail-breakdown', '#detail-grand-total'
        ];
        $(fields.join(',')).html('<span class="text-muted">Loading...</span>');

        const approveBtn = $('#approveBookingBtn');
        const rejectBtn = $('.reject-btn');
        approveBtn.data('id', bookingId);

        function parseTimeToDate(timeStr) {
            if (!timeStr) return null;
            timeStr = String(timeStr).trim();
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

        function formatTimeStr(timeStr) {
            const d = parseTimeToDate(timeStr);
            if (!d) return (timeStr ?? 'N/A');
            let h = d.getHours();
            const m = d.getMinutes();
            const ampm = h >= 12 ? 'PM' : 'AM';
            h = h % 12;
            if (h === 0) h = 12;
            return `${h}:${String(m).padStart(2, '0')} ${ampm}`;
        }

        function computeHours(startStr, endStr) {
            const s = parseTimeToDate(startStr);
            const e = parseTimeToDate(endStr);
            if (!s || !e) return 1;
            const start = new Date(s.getTime());
            const end = new Date(e.getTime());
            if (end <= start) {
                end.setDate(end.getDate() + 1);
            }
            const diffMinutes = (end - start) / (1000 * 60);
            let hours = Math.round((diffMinutes / 60) * 100) / 100; // 2 decimals
            if (!isFinite(hours) || hours <= 0) hours = 1;
            return hours;
        }

        function currency(num) {
            return Number(num || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        $.get(`/admin/admin-function-room-bookings/${bookingId}/details`, function (response) {

            approveBtn.removeClass('d-none').text('Approve').prop('disabled', true);
            rejectBtn.removeClass('d-none').text('Reject').prop('disabled', true);


            if (response.current_user_status === 2) {
                approveBtn.prop('disabled', false).text('Approve'); // allow reversal
                rejectBtn.prop('disabled', true).text('Rejected');
            }

            else if (response.current_user_status === 1) {
                approveBtn.prop('disabled', true).text('Approved');
                rejectBtn.prop('disabled', true).text('Reject');
            }
            else if (response.rejectedByPrevious && response.current_user_status === 0) {
                approveBtn.prop('disabled', true).text('Waiting');
                rejectBtn.prop('disabled', true).text('Rejected by ' + response.rejectedByRole);
            }

            else if (response.show_approve_button) {
                approveBtn.prop('disabled', false).text('Approve');
                rejectBtn.prop('disabled', false).text('Reject');
            }


            else if (response.show_view_button) {
                approveBtn.prop('disabled', true).text(response.waiting_reason || 'Waiting');
                rejectBtn.prop('disabled', true).text('Reject');
            }

            const main = response.booking;

            const linked = Array.isArray(response.linked_bookings) && response.linked_bookings.length ? response.linked_bookings : [];
            $('#detail-transaction-no').text(main.transaction_no ?? 'N/A');
            $('#detail-unit').text(main.unit_no ?? 'N/A');
            if (main.user?.name) {
                $('#detail-name').text(main.user.name);
            } else if (main.created_by_name) {
                $('#detail-name').text('Booked by: ' + main.created_by_name);
            } else {
                $('#detail-name').text('N/A');
            }
            $('#detail-contact').text(main.contact_number ?? 'N/A');
            $('#detail-purpose').text(main.purpose_of_event ?? 'N/A');

            const residentBadge = main.resident_type === 'TENANT'
                ? '<span class="badge badge-forge bg-danger">TENANT</span>'
                : main.resident_type === 'OWNER'
                    ? '<span class="badge badge-forge bg-primary">OWNER</span>'
                    : `<span class="badge bg-secondary">${main.resident_type ?? 'N/A'}</span>`;
            $('#detail-resident-type').html(residentBadge);
            $('#detail-payment-mode').text(main.payment_mode ?? 'N/A');


            let statusHtml = '';
            if (linked.length) {
                const allConfirmed = linked.every(b => b.booking_status == 1);
                const allCancelled = linked.every(b => b.booking_status == 2);
                if (allConfirmed) statusHtml = '<span class="badge badge-forge bg-success">Confirmed</span>';
                else if (allCancelled) statusHtml = '<span class="badge badge-forge bg-danger">Cancelled</span>';
                else statusHtml = '<span class="badge badge-forge bg-warning text-white">Waiting</span>';
            } else {
                if (main.booking_status == 1) statusHtml = '<span class="badge badge-forge bg-success">Confirmed</span>';
                else if (main.booking_status == 2) statusHtml = '<span class="badge badge-forge bg-danger">Cancelled</span>';
                else statusHtml = '<span class="badge badge-forge bg-warning text-white">Waiting</span>';
            }
            $('#detail-status').html(statusHtml);

            const today = new Date(); today.setHours(0, 0, 0, 0);
            const mainBookingDate = main.function_room_booking_date ? new Date(main.function_room_booking_date) : null;
            if (main.booking_status == 1) {
                if (mainBookingDate && mainBookingDate < today) {
                    $('#cancel-booking-btn').addClass('d-none');
                } else {
                    $('#cancel-booking-btn').removeClass('d-none').data('id', main.id).data('start-time', main.event_start_time);
                }
            } else if (main.booking_status == 0) {
                $('#cancel-booking-btn').removeClass('d-none').data('id', main.id).data('start-time', main.event_start_time);
            } else {
                $('#cancel-booking-btn').addClass('d-none');
            }

            if (linked.length) {
                let roomsHtml = '';
                linked.forEach(b => {
                    const name = (b.function_room && b.function_room.function_room_name) || (b.functionRoom && b.functionRoom.function_room_name) || 'Function Room';
                    roomsHtml += `<span class="badge bg-primary me-1">${name}</span>`;
                });
                $('#detail-function-rooms').html(roomsHtml);
            } else {
                const name = (main.function_room && main.function_room.function_room_name) || (main.functionRoom && main.functionRoom.function_room_name) || 'N/A';
                $('#detail-function-rooms').html(`<span class="badge bg-primary">${name}</span>`);
            }

            if (response.authorization_file_url) {
                $('#detail-authorization').html(`<a href="${response.authorization_file_url}" target="_blank" class="text-decoration-none">View</a>`);
            } else {
                $('#detail-authorization').html('<span class="text-muted">N/A</span>');
            }

            if (main.suppliers && main.suppliers.length) {
                let supHtml = '';
                main.suppliers.forEach(s => {
                    supHtml += `<div>${s.name} ${s.attachment_url ? ` - <a href="${s.attachment_url}" target="_blank" class="text-decoration-none">View</a>` : ''}</div>`;
                });
                $('#detail-suppliers').html(supHtml);
            } else {
                $('#detail-suppliers').html('<span class="text-muted">N/A</span>');
            }

            const roomsToRender = linked.length ? linked : [main];
            let roomListHtml = '';
            roomsToRender.forEach(b => {
                const fr = b.function_room || b.functionRoom || {};
                const roomName = fr.function_room_name || fr.name || 'Function Room';
                const startRaw = b.event_start_time;
                const endRaw = b.event_end_time;
                const hours = computeHours(startRaw, endRaw);
                const ratePerHour = parseFloat(b.final_rate ?? fr.function_room_rate ?? fr.rate ?? 0) || 0;
                const baseRate = parseFloat(fr.function_room_rate ?? ratePerHour) || ratePerHour;
                const roomTotal = Math.round(hours * ratePerHour * 100) / 100;

                const discountValue = parseFloat(b.discount ?? 0) || 0;
                const discountRemarks = b.discount_remarks ?? '';
                const startFmt = startRaw ? formatTimeStr(startRaw) : 'N/A';
                const endFmt = endRaw ? formatTimeStr(endRaw) : 'N/A';
                const bookingDateFmt = b.function_room_booking_date ? new Date(b.function_room_booking_date).toLocaleDateString(undefined, { month: 'long', day: '2-digit', year: 'numeric' }) : 'N/A';

                roomListHtml += `
                <div class="border-bottom pb-2 mb-3">
                    <h6 class="fw-bold">${roomName}</h6>

                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Booking Date:</div>
                        <div class="col-8">${bookingDateFmt}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Time:</div>
                        <div class="col-8">${startFmt} - ${endFmt}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Pax:</div>
                        <div class="col-8">${b.pax ?? 'N/A'}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Rate:</div>
                        <div class="col-8">`;

                if (baseRate > ratePerHour) {
                    roomListHtml += `<small class="text-muted"><s>₱${currency(baseRate)}/hr</s></small>
                    &nbsp; → &nbsp;
                    <small class="fw-bold">₱${currency(ratePerHour)}/hr</small>
                    &nbsp; × &nbsp;
                    <small>${hours} hr${hours > 1 ? 's' : ''}</small>
                    &nbsp; = &nbsp;
                    <strong>₱${currency(roomTotal)}</strong>`;
                } else {
                    roomListHtml += `<small>₱${currency(ratePerHour)}/hr</small>
                    &nbsp; × &nbsp;
                    <small>${hours} hr${hours > 1 ? 's' : ''}</small>
                    &nbsp; = &nbsp;
                    <strong>₱${currency(roomTotal)}</strong>`;
                }

                roomListHtml += `</div></div>`;
                roomListHtml += `<div class="row mb-2"><div class="col-4 fw-bold">Discount:</div><div class="col-8">`;
                if (discountValue > 0) {
                    const dvStr = Number.isInteger(discountValue) ? discountValue.toFixed(0) : (discountValue % 1 === 0 ? discountValue.toFixed(0) : discountValue.toFixed(2).replace(/\.00$/, ''));
                    roomListHtml += `<strong class="text-danger">${dvStr}%</strong>`;
                    if (discountRemarks) roomListHtml += ` <span style="margin-left:6px;color:#555;">${discountRemarks}</span>`;
                } else {
                    roomListHtml += `<span class="text-muted">No discount</span>`;
                }
                roomListHtml += `</div></div></div>`;
            });

            $('#detail-room-list').html(roomListHtml);

            let functionRoomsTotal = 0;
            let addonsTotal = 0;
            let breakdownRows = '';


            roomsToRender.forEach(b => {
                const fr = b.function_room || b.functionRoom || {};
                const roomName = fr.function_room_name || fr.name || 'Function Room';
                const hours = computeHours(b.event_start_time, b.event_end_time);
                const ratePerHour = parseFloat(b.final_rate ?? fr.function_room_rate ?? fr.rate ?? 0) || 0;
                const baseRate = parseFloat(fr.function_room_rate ?? ratePerHour) || ratePerHour;
                const roomTotal = Math.round(hours * ratePerHour * 100) / 100;
                functionRoomsTotal += roomTotal;

                breakdownRows += `
                <tr>
                    <td>${roomName}</td>
                    <td class="text-center">${hours} hr${hours > 1 ? 's' : ''}</td>
                    <td class="text-end">`;
                if (baseRate > ratePerHour) {
                    breakdownRows += `<small class="text-muted"><s>₱${currency(baseRate)}</s></small>&nbsp;→&nbsp;<small class="fw-bold">₱${currency(ratePerHour)}</small>`;
                } else {
                    breakdownRows += `₱${currency(ratePerHour)}`;
                }
                breakdownRows += `</td><td class="text-end">₱${currency(roomTotal)}</td></tr>`;
            });

            roomsToRender.forEach(b => {
                const addOns = b.addOns || b.add_ons || [];
                addOns.forEach(addon => {
                    const pivot = addon.pivot || {};
                    const qty = Number(pivot.quantity ?? pivot.qty ?? addon.qty ?? 0) || 0;
                    const price = Number(pivot.price ?? addon.price ?? addon.price ?? 0) || 0;
                    const lineTotal = Math.round(qty * price * 100) / 100;
                    addonsTotal += lineTotal;

                    breakdownRows += `
                    <tr>
                        <td>${addon.item ?? addon.name ?? 'Add-on'}</td>
                        <td class="text-center">${qty}</td>
                        <td class="text-end">₱${currency(price)}</td>
                        <td class="text-end">₱${currency(lineTotal)}</td>
                    </tr>
                `;
                });
            });

            if (!breakdownRows) {
                breakdownRows = `<tr><td colspan="4" class="text-center text-muted">No charges</td></tr>`;
            }

            $('#detail-breakdown').html(breakdownRows);
            const functionRoomsSubtotalStr = currency(functionRoomsTotal);
            const addonsTotalStr = currency(addonsTotal);
            const grandTotalStr = currency(Math.round((functionRoomsTotal + addonsTotal) * 100) / 100);

            $('#detail-breakdown-footer').html(`
            <tr class="table-light fw-bold">
                <td colspan="3" class="text-end">Function Rooms Subtotal</td>
                <td class="text-end">₱${functionRoomsSubtotalStr}</td>
            </tr>
            ${addonsTotal > 0 ? `
            <tr class="table-light fw-bold">
                <td colspan="3" class="text-end">Add-Ons Subtotal</td>
                <td class="text-end">₱${addonsTotalStr}</td>
            </tr>` : ''}
            <tr class="table-dark fw-bold">
                <td colspan="3" class="text-end">Grand Total</td>
                <td class="text-end">₱${grandTotalStr}</td>
            </tr>
        `);

            hideSpinner();
            $('#functionRoomBookingDetailsModal').modal('show');

        }).fail(function () {
            hideSpinner();
            alert('Something went wrong.');
        });
    });

    $('#functionRoomBookingsTable').on('click', '.edit-booking-btn', function () {
        let bookingId = $(this).data("id");
        showSpinner();
        $.get('/admin/admin-bookings/' + bookingId + '/edit', function (res) {
            const booking = res.booking;
            const addons = res.addons;
            $('#booking_id').val(booking.id);
            $('#function_room_id').val(booking.function_room_id);
            const residentName = booking.user?.name ?? '';
            const unitNo = booking.unit_no ?? '';
            const residentType = booking.resident_type
                ? booking.resident_type.charAt(0).toUpperCase() + booking.resident_type.slice(1)
                : '';

            const displayValue = `${residentName} - Unit ${unitNo} - ${residentType}`;

            const roomId = booking.function_room_id;
            const linked = linkedRooms[roomId] || [];
            if (linked.length > 0) {
                const linkedRoomId = linked[0];
                const linkedRoomName = res.linked_room_name;

                $('#editLinkedRoomWrapper').removeClass('d-none');
                $('#editLinkedRoomLabel').text(`Also book linked room: ${linkedRoomName}?`);

                const alreadyBookedLinkedRoom = res.already_booked_linked_room === true;

                if (alreadyBookedLinkedRoom) {
                    $('#editLinkedRoomCheckbox').prop('checked', true);
                    $('#editLinkedRoomInput').val(1);
                } else {
                    $('#editLinkedRoomCheckbox').prop('checked', false);
                    $('#editLinkedRoomInput').val(0);
                }
            } else {
                $('#editLinkedRoomWrapper').addClass('d-none');
                $('#editLinkedRoomInput').val(0);
            }
            $('#function_room_id').val(booking.function_room_id);
            $('#editTransactionNo').text(booking.transaction_no ?? 'N/A');
            $('#editResidentDisplay')
                .text(displayValue)
                .data('type', booking.resident_type)
                .data('unit', booking.unit_no);
            if (res.authorization_file) {
                $('#authorizationPreview').removeClass('d-none');
                $('#authorizationViewLink').attr('href', res.authorization_file);
                $('input[name="authorization_file"]').data('existing', true);
            } else {
                $('#authorizationPreview').addClass('d-none');
                $('input[name="authorization_file"]').data('existing', false);
            }


            $('#editFunctionRoomName').text(booking.function_room.function_room_name);
            $('#roomCapacity').val(booking.function_room.function_room_capacity);
            $('#editRoomCapacity').text(booking.function_room.function_room_capacity);
            $('#editFunctionRoomBookingDate').val(booking.function_room_booking_date); // 👈 IMPORTANT
            $('#editDiscount').val(parseFloat(booking.discount ?? 0));
            $('#editDiscountRemarks').val(booking.discount_remarks);

            $('#editPurposeOfEvent').val(booking.purpose_of_event);
            $('#editStartTime').val(booking.event_start_time);
            $('#editEndTime').val(booking.event_end_time);
            $('#editPaxInput')
                .val(booking.pax)
                .attr('max', booking.function_room.function_room_capacity);
            $('#editContactNumber').val(booking.contact_number);

            const paymentModes = ['Charge to Account', 'Advance Payment'];
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

            let addonHtml = '';
            addons.forEach(addon => {
                const booked = booking.add_ons.find(a => a.id == addon.id);
                const qty = booked ? booked.pivot.qty : 0;

                addonHtml += `
                <div class="col-12">
                    <div class="border rounded p-2 h-100">
                        <div class="form-check">
                            <input type="hidden" name="addons[${addon.id}][selected]" value="0">
                            <input type="checkbox" class="form-check-input editAddOnsFields" 
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
            if (booking.has_suppliers && booking.suppliers.length > 0) {
                $('#hasSuppliers').prop('checked', true);
                $('#supplierSection').removeClass('d-none');

                let supplierHtml = '';
                booking.suppliers.forEach((s, index) => {
                    supplierHtml += `
        <div class="row align-items-center g-2 supplier-item mb-2">
            <input type="hidden" name="suppliers[${index}][id]" value="${s.id}">
            
            <div class="col-md-4">
                <input type="text" 
                       name="suppliers[${index}][name]" 
                       class="form-control" 
                       placeholder="Supplier Name" 
                       value="${s.name ?? ''}">
            </div>

            <div class="col-md-6 d-flex align-items-center gap-2">
                ${s.attachment_url
                            ? `<a href="${s.attachment_url}" target="_blank" class="btn btn-sm btn-outline-primary">View</a>`
                            : `<span class="text-muted small">No file</span>`}
                <input type="file" 
                       name="suppliers[${index}][attachment]" 
                       class="form-control form-control-sm flex-grow-1">
            </div>

            <div class="col-md-2 text-end">
                <button type="button" class="btn btn-sm btn-danger removeSupplier w-100">Remove</button>
            </div>
        </div>
    `;
                });
                $('#suppliersWrapper').html(supplierHtml);
            } else {
                $('#hasSuppliers').prop('checked', false);
                $('#supplierSection').addClass('d-none');
                $('#suppliersWrapper').html(`
        <div class="row align-items-center g-2 supplier-item mb-2">
            <div class="col-md-4">
                <input type="text" 
                       name="suppliers[0][name]" 
                       class="form-control" 
                       placeholder="Supplier Name">
            </div>
            <div class="col-md-6 d-flex align-items-center gap-2">
                <input type="file" 
                       name="suppliers[0][attachment]" 
                       class="form-control form-control-sm flex-grow-1">
            </div>
            <div class="col-md-2 text-end">
                <button type="button" class="btn btn-sm btn-danger removeSupplier w-100">Remove</button>
            </div>
        </div>
    `);


            }
            $.get('/admin/admin-function-room/' + booking.function_room_id + '/booked-dates', function (disabledDates) {
                let dateInput = document.getElementById("editFunctionRoomBookingDate");
                if (dateInput._flatpickr) dateInput._flatpickr.destroy();

                console.log(booking.function_room_booking_date);

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
            checkAuthorizationEdit();
            $('#editLinkedRoomCheckbox').trigger('change');
        }).fail(function () {
            hideSpinner();
            Swal.fire('Error', 'Unable to fetch booking data.', 'error');
        });
    });


    $('#editLinkedRoomCheckbox').on('change', function () {
        $('#editLinkedRoomInput').val(this.checked ? 1 : 0);
    });


    $(document).on('change', '.editAddOnsFields', function () {
        const isChecked = $(this).is(':checked');
        const addonId = $(this).attr('id').replace('addon', '');
        const qtyInput = $(`input[data-addon-id="${addonId}"]`);

        if (isChecked) {
            qtyInput.prop('disabled', false).prop('required', true);
            if (qtyInput.val() == 0) qtyInput.val(1);
        } else {
            qtyInput.prop('disabled', true).prop('required', false).val(0);
        }
    });


    // --- Event delegation for dynamic payment mode radios ---
    $(document).on('change', '#editPaymentModeWrapper input[name="payment_mode"]', checkAuthorizationEdit);


    // --- Event delegation for suppliers toggle ---
    $('#hasSuppliers').on('change', function () {
        $('#supplierSection').toggleClass('d-none', !this.checked);
    });

    function checkAuthorizationEdit() {
        const $residentDisplay = $('#editResidentDisplay');
        const residentType = $residentDisplay.data('type')?.toLowerCase();
        const paymentMode = $('input[name="payment_mode"]:checked').val();

        const $wrapper = $('#editAuthorizationUploadWrapper');
        const $fileInput = $('input[name="authorization_file"]');
        const hasExisting = $fileInput.data('existing') === true || $fileInput.data('existing') === 'true';

        const $preview = $('#authorizationPreview');
        const $viewLink = $('#authorizationViewLink');

        // Reset state
        $wrapper.addClass('d-none');
        $fileInput.prop('required', false);
        $('#editAuthorizationLabel').text('');
        $('#editAuthorizationNote').text('');

        // Hide preview by default
        $preview.addClass('d-none');
        $viewLink.attr('href', '#');
        if (residentType === 'tenant' && paymentMode === 'Charge to Account') {
            $('#editAuthorizationLabel').text('CTA Authorization Letter *');
            $('#editAuthorizationNote').text('Required because you are booking as a tenant with CTA.');
            $wrapper.removeClass('d-none');

            if (hasExisting) {
                $preview.removeClass('d-none');
            } else {
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
        const discountValue = $('#editDiscount').val()?.trim();
        const discountRemarks = $('#editDiscountRemarks').val()?.trim();

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

        // ✅ Validate discount remarks only if discount is greater than 0
        if (parseFloat(discountValue) > 0 && !discountRemarks) {
            $('#editDiscountRemarks').addClass('is-invalid');
            // Optional: show inline error message
            if ($('#discountRemarksError').length === 0) {
            } else {
                $('#discountRemarksError').removeClass('d-none');
            }
            isValid = false;
        } else {
            $('#editDiscountRemarks').removeClass('is-invalid');
            $('#discountRemarksError').addClass('d-none');
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





    function renderApprovalBadge(status) {
        // Convert string to number if needed
        status = Number(status);

        if (status === 1) return '<span class="badge bg-success">Approved</span>';
        if (status === 2) return '<span class="badge bg-danger">Rejected</span>';
        return '<span class="badge bg-warning">Waiting</span>'; // 0 or any other
    }



    function renderStatusBadge(status) {
        if (status == 1) return '<span class="badge bg-success">Confirmed</span>';
        if (status == 2) return '<span class="badge bg-danger">Cancelled</span>';
        return '<span class="badge bg-warning">Waiting</span>';
    }
});