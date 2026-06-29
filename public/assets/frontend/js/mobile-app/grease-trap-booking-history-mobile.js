$(function () {

    autoSelectHistoryResidenceGt();
    alert("🔥GT History JS VERSION 38");
    function autoSelectHistoryResidenceGt() {

        const select = $('#resident_id_gt_booking_history');

        if (!select.length) {
            logDebugHistoryGt("HISTORY SELECT NOT FOUND");
            return;
        }

        const options = select.find('option');

        if (options.length <= 1) {
            setTimeout(autoSelectHistoryResidenceGt, 300);
            return;
        }

        const firstUnit = options.eq(1).val();

        if (!firstUnit) {
            logDebugHistoryGt("NO FIRST UNIT");
            return;
        }

        logDebugHistoryGt(
            "AUTO SELECT UNIT:",
            firstUnit
        );

        select.val(firstUnit);

        select.trigger('input');

        const email = $('#history_mobile_email_gt').val();

        updateGtHistoryBookingTable(
            firstUnit,
            email
        );

    }

    const el = document.getElementById('resident_id_gt_booking_history');

    logDebugHistoryGt("SELECT EXISTS: " + (el ? "YES" : "NO"));

    function logDebugHistoryGt(...args) {

        const msg = args.map(a =>
            typeof a === 'object'
                ? JSON.stringify(a)
                : a
        ).join(' ');

        console.log(msg);

        const el = document.getElementById('debugPanelBookingHistoryGt');

        if (el) {
            el.innerHTML += msg + "<br>";
            el.scrollTop = el.scrollHeight;
        }
    }

    document.addEventListener('change', function (e) {
        logDebugHistoryGt("CHANGE EVENT: " + e.target.id);
        if (e.target && e.target.id === 'resident_id_gt_booking_history') {
            window.onResidentChangeHistoryGt(e);
        }
    });

    window.onResidentChangeHistoryGt = function (e) {

        const unit = e.target.value;
        const email = $('#history_mobile_email_gt').val();

        $("#historyWrapperGt").removeClass("d-none");

        updateGtHistoryBookingTable(
            unit,
            email
        );
    };

    function updateGtHistoryBookingTable(unitName, email, page = 1) {
        logDebugHistoryGt(unitName);
        showHistoryLoadingGt();
        $("#gtHistoryTable").html("");
        $.ajax({

            url: "/get-grease-trap-booking-mobile/history",

            type: "GET",

            data: {
                unit_name: unitName,
                email: email,
                page: page

            },

            success: function (res) {

                logDebugHistoryGt(
                    "HISTORY RESPONSE",
                    res
                );
                $("#historyWrapperGt").removeClass('d-none');
                renderHistoryTableGt(res.bookings.data);
                renderGtPagination(res.bookings);

                $("#historyPageLoading").fadeOut(200, function () {
                    $(this).remove();
                });

            },

            error: function (xhr) {

                logDebugHistoryGt(
                    "ERROR",
                    xhr.responseText
                );

                $("#historyPageLoading").fadeOut(200);


            },
            complete: function () {

                hideHistoryLoadingGt();

            }

        });

    }

    function showHistoryLoadingGt() {

        $("#historyLoading").removeClass("d-none");

        $("#gtHistoryPagination .page-link")
            .addClass("disabled")
            .css("pointer-events", "none");
    }

    function hideHistoryLoadingGt() {

        $("#historyLoading").addClass("d-none");

        $("#gtHistoryPagination .page-link")
            .removeClass("disabled")
            .css("pointer-events", "");
    }

    function renderGtPagination(pagination) {

        let html = `
        <nav>
            <ul class="pagination mb-0">
    `;

        // Previous
        html += `
        <li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
            <a class="page-link gt-history-page"
               href="#"
               data-page="${pagination.current_page - 1}">
                &lsaquo;
            </a>
        </li>
    `;

        // Next
        html += `
        <li class="page-item ${pagination.current_page === pagination.last_page ? 'disabled' : ''}">
            <a class="page-link gt-history-page"
               href="#"
               data-page="${pagination.current_page + 1}">
                &rsaquo;
            </a>
        </li>
    `;

        html += `
            </ul>
        </nav>
    `;

        $('#gtHistoryPagination').html(html);
    }

    $(document).on('click', '.gt-history-page', function (e) {

        e.preventDefault();

        if ($(this).parent().hasClass('disabled')) {
            return;
        }

        const page = $(this).data('page');

        const unit =
            Alpine.store('superapp')?.selectedUnit ||
            $('#resident_id_gt_booking_history').val();

        const email = $('#history_mobile_email_gt').val();

        updateGtHistoryBookingTable(unit, email, page);

    });

    function renderHistoryTableGt(bookings) {
        logDebugHistoryGt("renderHistoryTableGt");

        let html = "";

        if (!bookings || bookings.length === 0) {

            html = `
            <div class="text-center py-5">
                <i class="bx bx-calendar-x fs-1 text-muted"></i>

                <p class="text-muted mt-2 mb-0">
                    No booking history found
                </p>
            </div>
        `;

        } else {

            const now = new Date();

            bookings.forEach(item => {

                let statusText = "";
                let badgeClass = "";

                if (Number(item.booking_status) === 1) {

                    const bookingDate = new Date(item.booking_date);

                    // Ignore the time portion like Carbon::isPast() on booking_date
                    bookingDate.setHours(0, 0, 0, 0);

                    const today = new Date();
                    today.setHours(0, 0, 0, 0);

                    if (bookingDate < today) {
                        statusText = "Completed";
                        badgeClass = "bg-primary";
                    } else {
                        statusText = "Booked";
                        badgeClass = "bg-primary";
                    }

                } else {

                    statusText = "Cancelled";
                    badgeClass = "bg-danger";

                }

                switch (Number(item.charged_type)) {
                    case 1:
                        charged_type = "Free";
                        badgeClassChargedType = "bg-primary";
                        break;

                    case 2:
                        charged_type = "Billable";
                        badgeClassChargedType = "bg-danger";
                        break;

                    default:
                        charged_type = "Unkown";
                        badgeClassChargedType = "bg-secondary";
                }

                let cancelButton = "";

                if (Number(item.booking_status) === 1) {

                    const startTime = convertTime(item.booking_time_slot); // e.g. "2:00PM"

                    const [year, month, day] = item.booking_date.split('-').map(Number);

                    const match = startTime.match(/(\d+):(\d+)(AM|PM)/i);

                    let hour = parseInt(match[1], 10);
                    const minute = parseInt(match[2], 10);
                    const period = match[3].toUpperCase();

                    if (period === "PM" && hour !== 12) hour += 12;
                    if (period === "AM" && hour === 12) hour = 0;

                    const bookingDateTime = new Date(year, month - 1, day, hour, minute);

                    const canCancel = now < bookingDateTime;

                    console.log("Now:", now);
                    console.log("Booking:", bookingDateTime);
                    console.log("Can Cancel:", canCancel);

                    cancelButton = `
        <button
            class="btn btn-sm rounded-pill px-3 cancel-mobile-gt-booking-btn
                ${canCancel ? 'btn-outline-danger' : 'btn-outline-secondary'}"
            ${canCancel ? '' : 'disabled'}
            data-id="${item.id}">
            <i class="bx bx-x-circle me-1"></i>
            Cancel
        </button>
    `;
                }

                html += `
                <div class="booking-card shadow-sm">

                    <div class="d-flex justify-content-between align-items-start mb-3">

                        <div>
                            <div class="fw-bold">
                                ${item.transaction_no}
                            </div>

                            <small class="text-muted">
                                Transaction No.
                            </small>
                        </div>

                        <span class="badge ${badgeClass} rounded-pill px-3 py-2">
                            ${statusText}
                        </span>

                    </div>

                    <div class="booking-details">

                        <div class="booking-detail-row">
                            <i class="bx bx-calendar"></i>
                            <span>${item.booking_date}</span>
                        </div>

                        <div class="booking-detail-row">
                            <i class="bx bx-time-five"></i>
                            <span>${item.booking_time_slot}</span>
                        </div>

                        <div class="booking-detail-row">
                              <span class="badge ${badgeClassChargedType} rounded-pill px-3 py-2">
                            ${charged_type}
                        </span>

                        </div>

                    </div>

                 <div class="mt-3 d-flex gap-2">
                    ${cancelButton}
                </div>

            </div>
            `;
            });
        }

        $("#gtHistoryTable").html(html);
    }

    function convertTime(slot) {
        if (!slot) return null;

        // Get first time only
        let time = slot.split('-')[0].trim();

        return time;
    }


    $(document).on('click', '.cancel-mobile-gt-booking-btn', function () {
        const bookingId = $(this).data('id');
        logDebugHistoryGt("Sending confirmed cancel request...");
        const email = $('#history_mobile_email_gt').val();
        $.ajax({
            url: '/grease-trap-booking-mobile/cancel/' + bookingId,
            type: 'POST',
            data: {
                email: email,
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
                        html: res.message,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, cancel it',
                        cancelButtonText: 'No, keep it'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: '/grease-trap-booking-mobile/cancel/' + bookingId,
                                type: 'POST',
                                data: {
                                    email: email,
                                    _token: $('meta[name="csrf-token"]').attr('content'),
                                    confirm: 1
                                },
                                success: function (res2) {
                                    Swal.fire('Cancelled!', res2.message, 'success')
                                        .then(() => {
                                            logDebugHistoryGt("Second response received");
                                            logDebugHistoryGt(JSON.stringify(res2));

                                            const unit =
                                                Alpine.store('superapp')?.selectedUnit ||
                                                $('#resident_id_gt_booking_history').val();


                                            const email =
                                                $('#history_mobile_email_gt').val();

                                            const currentPage =
                                                $('#gtHistoryPagination .active .page-link').data('page') || 1;

                                            updateGtHistoryBookingTable(unit, email, currentPage);
                                        });
                                },
                                error: function (xhr) {

                                    logDebugHistoryGt("AJAX ERROR");
                                    logDebugHistoryGt("Status: " + xhr.status);
                                    logDebugHistoryGt("Response: " + xhr.responseText);

                                    Swal.fire(
                                        'Error',
                                        'Something went wrong while cancelling.',
                                        'error'
                                    );
                                }
                            });
                        }
                    });
                } else {
                    Swal.fire('Cancelled!', res.message, 'success')
                        .then(() => {
                            const unit =
                                Alpine.store('superapp')?.selectedUnit ||
                                $('#resident_id_gt_booking_history').val();


                            const email =
                                $('#history_mobile_email_gt').val();

                            updateGtHistoryBookingTable(
                                unit,
                                email
                            );
                        });
                }
            },
            error: function (xhr) {

                logDebugHistoryGt("AJAX ERROR");
                logDebugHistoryGt("Status: " + xhr.status);
                logDebugHistoryGt("Response: " + xhr.responseText);

                Swal.fire(
                    'Error',
                    'Something went wrong while cancelling.',
                    'error'
                );
            }
        });
    });

    $(document).on('click', '.view-ausi-booking-btn', function () {
        let info_id = $(this).data('id');
        logDebugHistoryGt("VIEW AUSI BOOKING CLICKED");
        logDebugHistoryGt("Booking ID: " + info_id);
        logDebugHistoryGt("Before AJAX");
        $.get('/fetch-ausi-booking-mobile/' + info_id, function (data) {
            logDebugHistoryGt("AUSI booking data fetched successfully");
            logDebugHistoryGt(JSON.stringify(data));
            logDebugHistoryGt("SUCCESS CALLBACK REACHED");
            alert("view ausi is hitting");
            $('#view_transaction_no')
                .text(data.transaction_no);

            $('#view_name')
                .text(data.name);


            $('#view_unit')
                .text(data.unit_no);


            let residentType = data.resident_type?.toUpperCase() ?? 'N/A';

            logDebugHistoryGt("Resident Type: " + residentType);

            let residentBadgeClass = "bg-secondary";

            if (residentType === "OWNER") {
                residentBadgeClass = "bg-primary";
            } else if (residentType === "TENANT") {
                residentBadgeClass = "bg-danger";
            }

            $('#view_resident_type').html(`
    <span class="badge ${residentBadgeClass} rounded-pill px-3 py-2">
        ${residentType}
    </span>
`);


            $('#view_booking_date')
                .text(data.booking_date);

            $('#view_time_slot')
                .text(data.booking_time_slot);


            $('#view_remarks')
                .text(
                    data.remarks ?? 'No remarks provided.'
                );


            logDebugHistoryGt(
                "Booking Status: " +
                data.display_status
            );


            $('#view_booking_status').html(`
    <span class="badge bg-${data.status_badge} rounded-pill px-4 py-2 shadow-sm">
        ${data.display_status.toUpperCase()}
    </span>
`);
            let inspectionHtml = "";

            if (!data.inspection_results ||
                data.inspection_results.length === 0) {


                logDebugHistoryGt(
                    "No inspection results found"
                );

                inspectionHtml = `
                <div class="alert alert-warning">
                    <i class="fa-solid fa-circle-exclamation me-2"></i>
                    Unit has not been inspected yet.
                </div>
            `;

            } else {


                logDebugHistoryGt(
                    "Inspection results count: " +
                    data.inspection_results.length
                );


                inspectionHtml = `
            <div class="table-responsive">

            <table class="table table-sm table-bordered">

            <thead>
                <tr>
                    <th>
                        Item
                    </th>

                    <th>
                        Result
                    </th>
                </tr>
            </thead>

            <tbody>
            `;

                data.inspection_results.forEach(result => {


                    logDebugHistoryGt(
                        "Inspection Item: " +
                        result.inspection_item.item_name
                    );


                    let label =
                        result.status == 1
                            ? result.inspection_item.option_1
                            : result.inspection_item.option_2;

                    inspectionHtml += `
                <tr>

                    <td>
                        ${result.inspection_item.item_name}
                    </td>

                    <td>
                        ${label}
                    </td>

                </tr>
                `;

                });

                inspectionHtml += `
            </tbody>
            </table>

            </div>
            `;
            }
            $('#viewInspectionResults')
                .html(inspectionHtml);
            logDebugHistoryGt(
                "Opening AUSI view modal"
            );

            logDebugHistoryGt(
                "Modal Count: " +
                $('#ausiViewResultModal').length
            );

            logDebugHistoryGt(
                "Modal Element: " +
                (document.getElementById('ausiViewResultModal') ? 'FOUND' : 'NOT FOUND')
            );
            $('#ausiViewResultModal').modal('show');
        })
            .fail(function (xhr, status, error) {

                logDebugHistoryGt("FAILED TO FETCH AUSI BOOKING");
                logDebugHistoryGt("Status: " + status);
                logDebugHistoryGt("Error: " + error);
                logDebugHistoryGt("HTTP Code: " + xhr.status);
                logDebugHistoryGt("Response: " + xhr.responseText);


                alert("Unable to load booking details.");

            })
            .always(function () {

                logDebugHistoryGt(
                    "AUSI booking request completed"
                );

                hideLoading();

            });

    });
});