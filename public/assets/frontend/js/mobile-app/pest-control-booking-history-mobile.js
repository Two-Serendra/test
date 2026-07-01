$(function () {

    autoSelectHistoryResidencePc();
    alert("🔥PC History JS VERSION 10");
    function autoSelectHistoryResidencePc() {

        const select = $('#resident_id_pc_booking_history');

        if (!select.length) {
            logDebugHistoryPc("HISTORY SELECT NOT FOUND");
            return;
        }

        const options = select.find('option');

        if (options.length <= 1) {
            setTimeout(autoSelectHistoryResidencePc, 300);
            return;
        }

        const firstUnit = options.eq(1).val();

        if (!firstUnit) {
            logDebugHistoryPc("NO FIRST UNIT");
            return;
        }

        logDebugHistoryPc(
            "AUTO SELECT UNIT:",
            firstUnit
        );

        select.val(firstUnit);

        select.trigger('input');

        const email = $('#history_mobile_email_pc').val();

        updatePcHistoryBookingTable(
            firstUnit,
            email
        );

    }

    const el = document.getElementById('resident_id_pc_booking_history');

    logDebugHistoryPc("SELECT EXISTS: " + (el ? "YES" : "NO"));

    function logDebugHistoryPc(...args) {

        const msg = args.map(a =>
            typeof a === 'object'
                ? JSON.stringify(a)
                : a
        ).join(' ');

        console.log(msg);

        const el = document.getElementById('debugPanelBookingHistoryPc');

        if (el) {
            el.innerHTML += msg + "<br>";
            el.scrollTop = el.scrollHeight;
        }
    }

    document.addEventListener('change', function (e) {
        logDebugHistoryPc("CHANGE EVENT: " + e.target.id);
        if (e.target && e.target.id === 'resident_id_pc_booking_history') {
            window.onResidentChangeHistoryPc(e);
        }
    });

    window.onResidentChangeHistoryPc = function (e) {

        const unit = e.target.value;
        const email = $('#history_mobile_email_pc').val();

        $("#historyWrapperPc").removeClass("d-none");

        updatePcHistoryBookingTable(
            unit,
            email
        );
    };

    function updatePcHistoryBookingTable(unitName, email, page = 1) {
        showHistoryLoadingPc();
        $("#pcHistoryTable").html("");
        $.ajax({

            url: "/get-pest-control-booking-mobile/history",

            type: "GET",

            data: {
                unit_name: unitName,
                email: email,
                page: page

            },

            success: function (res) {

                logDebugHistoryPc(
                    "HISTORY RESPONSE",
                    res
                );
                $("#historyWrapperPc").removeClass('d-none');
                renderHistoryTablePc(res.bookings.data);
                renderPcPagination(res.bookings);

                $("#historyPageLoadingPc").fadeOut(200, function () {
                    $(this).remove();
                });

            },

            error: function (xhr) {

                logDebugHistoryPc(
                    "ERROR",
                    xhr.responseText
                );

                $("#historyPageLoadingPc").fadeOut(200);


            },
            complete: function () {

                hideHistoryLoadingPc();

            }

        });

    }

    function showHistoryLoadingPc() {

        $("#historyLoadingPc").removeClass("d-none");

        $("#pcHistoryPagination .page-link")
            .addClass("disabled")
            .css("pointer-events", "none");
    }

    function hideHistoryLoadingPc() {

        $("#historyLoadingPc").addClass("d-none");

        $("#pcHistoryPagination .page-link")
            .removeClass("disabled")
            .css("pointer-events", "");
    }

    function renderPcPagination(pagination) {

        let html = `
        <div class="d-flex justify-content-center align-items-center gap-3">

            <button
                class="btn btn-primary btn-sm rounded-circle pc-history-page"
                data-page="${pagination.current_page - 1}"
                ${pagination.current_page === 1 ? 'disabled' : ''}>
                &lsaquo;
            </button>

            <span class="fw-bold text-primary fs-5">
                ${pagination.current_page}
            </span>

            <button
                class="btn btn-primary btn-sm rounded-circle pc-history-page"
                data-page="${pagination.current_page + 1}"
                ${pagination.current_page === pagination.last_page ? 'disabled' : ''}>
                &rsaquo;
            </button>

        </div>

        <div class="mt-2 text-muted small text-center">
            Showing
            <strong>${pagination.from ?? 0}</strong>
            to
            <strong>${pagination.to ?? 0}</strong>
            of
            <strong>${pagination.total}</strong>
            results
        </div>
    `;

        $('#pcHistoryPagination').html(html);
    }

    $(document).on('click', '.pc-history-page', function (e) {

        e.preventDefault();

        if ($(this).parent().hasClass('disabled')) {
            return;
        }

        const page = $(this).data('page');

        const unit =
            Alpine.store('superapp')?.selectedUnit ||
            $('#resident_id_pc_booking_history').val();

        const email = $('#history_mobile_email_pc').val();

        updatePcHistoryBookingTable(unit, email, page);

    });

    function renderHistoryTablePc(bookings) {
        logDebugHistoryPc("renderHistoryTablePc");
        logDebugHistoryPc("BOOKINGS:", bookings);
        logDebugHistoryPc("IS ARRAY:", Array.isArray(bookings));
        logDebugHistoryPc("LENGTH:", bookings?.length);
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
            logDebugHistoryPc("Before foreach");
            bookings.forEach(item => {
                logDebugHistoryPc("Inside foreach", item);
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

                    const match = startTime.match(/(\d+):(\d+)\s*(AM|PM)/i);

                    if (!match) {
                        logDebugHistoryPc("Invalid booking time:", startTime);
                        return;
                    }

                    let hour = parseInt(match[1], 10);
                    const minute = parseInt(match[2], 10);
                    const period = match[3].toUpperCase();

                    if (period === "PM" && hour !== 12) hour += 12;
                    if (period === "AM" && hour === 12) hour = 0;

                    const bookingDateTime = new Date(year, month - 1, day, hour, minute);

                    const canCancel = now < bookingDateTime;

                    logDebugHistoryPc("Now:", now);
                    logDebugHistoryPc("Booking:", bookingDateTime);
                    logDebugHistoryPc("Can Cancel:", canCancel);

                    cancelButton = `
        <button
            class="btn btn-sm rounded-pill px-3 cancel-mobile-pc-booking-btn
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
        logDebugHistoryPc("Finished rendering");
        $("#pcHistoryTable").html(html);
    }

    function convertTime(slot) {
        if (!slot) return null;

        // Get first time only
        let time = slot.split('-')[0].trim();

        return time;
    }


    $(document).on('click', '.cancel-mobile-pc-booking-btn', function () {
        const bookingId = $(this).data('id');
        logDebugHistoryPc("Sending confirmed cancel request...");
        const email = $('#history_mobile_email_pc').val();
        $.ajax({
            url: '/pest-control-booking-mobile/cancel/' + bookingId,
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
                                url: '/pest-control-booking-mobile/cancel/' + bookingId,
                                type: 'POST',
                                data: {
                                    email: email,
                                    _token: $('meta[name="csrf-token"]').attr('content'),
                                    confirm: 1
                                },
                                success: function (res2) {
                                    Swal.fire('Cancelled!', res2.message, 'success')
                                        .then(() => {
                                            logDebugHistoryPc("Second response received");
                                            logDebugHistoryPc(JSON.stringify(res2));

                                            const unit =
                                                Alpine.store('superapp')?.selectedUnit ||
                                                $('#resident_id_pc_booking_history').val();


                                            const email =
                                                $('#history_mobile_email_pc').val();

                                            const currentPage =
                                                $('#pcHistoryPagination .active .page-link').data('page') || 1;

                                            updatePcHistoryBookingTable(unit, email, currentPage);
                                        });
                                },
                                error: function (xhr) {

                                    logDebugHistoryPc("AJAX ERROR");
                                    logDebugHistoryPc("Status: " + xhr.status);
                                    logDebugHistoryPc("Response: " + xhr.responseText);

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
                                $('#resident_id_pc_booking_history').val();


                            const email =
                                $('#history_mobile_email_pc').val();

                            updatePcHistoryBookingTable(
                                unit,
                                email
                            );
                        });
                }
            },
            error: function (xhr) {

                logDebugHistoryPc("AJAX ERROR");
                logDebugHistoryPc("Status: " + xhr.status);
                logDebugHistoryPc("Response: " + xhr.responseText);

                Swal.fire(
                    'Error',
                    'Something went wrong while cancelling.',
                    'error'
                );
            }
        });
    });
});