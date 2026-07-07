$(function () {

    autoSelectHistoryResidenceGt();
    // alert("🔥GT History JS VERSION 41");
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

    // logDebugHistoryGt("SELECT EXISTS: " + (el ? "YES" : "NO"));

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
        <div class="d-flex justify-content-center align-items-center gap-3">

            <button
                class="btn btn-primary btn-sm rounded-circle gt-history-page"
                data-page="${pagination.current_page - 1}"
                ${pagination.current_page === 1 ? 'disabled' : ''}>
                &lsaquo;
            </button>

            <span class="fw-bold text-primary fs-5">
                ${pagination.current_page}
            </span>

            <button
                class="btn btn-primary btn-sm rounded-circle gt-history-page"
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
                    No booking found
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
        const email = $('#history_mobile_email_gt').val();

        Swal.fire({
            title: 'Cancel Booking?',
            text: 'Are you sure you want to cancel this booking?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, cancel it',
            cancelButtonText: 'No'
        }).then((result) => {

            if (!result.isConfirmed) {
                return;
            }

            logDebugHistoryGt("Sending cancel request...");

            $.ajax({
                url: '/grease-trap-booking-mobile/cancel/' + bookingId,
                type: 'POST',
                data: {
                    email: email,
                    confirm: true,
                    _token: $('meta[name="csrf-token"]').attr('content'),

                },
                success: function (res) {

                    if (!res.success) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: res.message || 'Failed to cancel booking.',
                            showConfirmButton: false,
                            timer: 3000,
                            customClass: {
                                popup: 'swal2-success-toast'
                            }
                        });
                        return;
                    }

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: res.message,
                        showConfirmButton: false,
                        timer: 3000,
                        customClass: {
                            popup: 'swal2-success-toast'
                        }
                    });

                    const unit =
                        Alpine.store('superapp')?.selectedUnit ||
                        $('#resident_id_gt_booking_history').val();

                    const email =
                        $('#history_mobile_email_gt').val();

                    const currentPage =
                        $('#gtHistoryPagination .active .page-link').data('page') || 1;

                    updateGtHistoryBookingTable(
                        unit,
                        email,
                        currentPage
                    );
                },

                error: function (xhr) {

                    logDebugHistoryGt("AJAX ERROR");
                    logDebugHistoryGt("Status: " + xhr.status);
                    logDebugHistoryGt("Response: " + xhr.responseText);

                    const res = xhr.responseJSON || {};

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: res.message || 'Something went wrong while cancelling.',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            });

        });

    });
});