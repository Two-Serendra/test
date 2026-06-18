$(function () {

    logDebugHistory("🔥Ausi History JS VERSION 23");

    const el = document.getElementById('resident_id_ausi_booking_history');

    logDebugHistory("SELECT EXISTS: " + (el ? "YES" : "NO"));

    function logDebugHistory(...args) {

        const msg = args.map(a =>
            typeof a === 'object'
                ? JSON.stringify(a)
                : a
        ).join(' ');

        console.log(msg);

        const el = document.getElementById('debugPanelBookingHistory');

        if (el) {
            el.innerHTML += msg + "<br>";
            el.scrollTop = el.scrollHeight;
        }
    }

    document.addEventListener('change', function (e) {
        logDebugHistory("CHANGE EVENT: " + e.target.id);
        if (e.target && e.target.id === 'resident_id_ausi_booking_history') {
            window.onResidentChangeHistory(e);
        }
    });

    window.onResidentChangeHistory = function (e) {

        const unit = e.target.value;
        const email = $('#history_mobile_email').val();

        $("#historyWrapper").removeClass("d-none");

        updateAusiHistoryBookingTable(
            unit,
            email
        );
    };

    function updateAusiHistoryBookingTable(unitName, email) {
        showHistoryLoading();
        $.ajax({

            url: "/get-ausi-booking-mobile/history",

            type: "GET",

            data: {
                unit_name: unitName,
                email: email

            },


            success: function (res) {

                logDebugHistory(
                    "HISTORY RESPONSE",
                    res
                );
                $("#historyWrapper").removeClass('d-none');

                renderHistoryTable(res.bookings);

            },


            error: function (xhr) {

                logDebugHistory(
                    "ERROR",
                    xhr.responseText
                );

            },
            complete: function () {

                hideHistoryLoading();

            }

        });

    }

    function showHistoryLoading() {

        $("#historyWrapper").removeClass('d-none');

        $("#historyLoading")
            .removeClass("d-none");

    }


    function hideHistoryLoading() {

        $("#historyLoading")
            .addClass("d-none");

    }

    function renderHistoryTable(bookings) {
        logDebugHistory("renderHistoryTable");

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

                if (item.booking_status == 2) {

                    statusText = "Cancelled";
                    badgeClass = "bg-danger";

                } else if (item.booking_status == 1) {

                    const bookingStart = new Date(
                        `${item.booking_date} ${convertTime(item.booking_time_slot)}`
                    );

                    const bookingEnd = new Date(bookingStart);
                    bookingEnd.setMinutes(bookingEnd.getMinutes() + 30);

                    if (now >= bookingStart && now <= bookingEnd) {

                        statusText = "Ongoing";
                        badgeClass = "bg-warning text-dark";

                    } else if (now > bookingEnd) {

                        statusText = "Completed";
                        badgeClass = "bg-success";

                    } else {

                        statusText = "Confirmed";
                        badgeClass = "bg-primary";
                    }
                }

                let cancelButton = "";

                if (item.booking_status == 1) {

                    const bookingStart = new Date(
                        `${item.booking_date} ${convertTime(item.booking_time_slot)}`
                    );

                    const hoursDiff =
                        (bookingStart - now) / (1000 * 60 * 60);

                    const canCancel =
                        statusText === "Confirmed" && hoursDiff >= 12;

                    cancelButton = `
        <button
            class="btn btn-sm rounded-pill px-3 cancel-booking-btn
                   ${canCancel ? 'btn-outline-danger' : 'btn-outline-secondary'}"
            ${canCancel ? '' : 'disabled'}
            data-id="${item.id}">
            <i class="bx bx-x-circle me-1"></i>
            Cancel Booking
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

                    </div>

                    ${cancelButton
                        ? `
                        <div class="mt-3">
                            ${cancelButton}
                        </div>
                    `
                        : ""
                    }

                </div>
            `;
            });
        }

        $("#ausiHistoryTable").html(html);
    }

    function convertTime(slot) {
        if (!slot) return null;

        // Get first time only
        let time = slot.split('-')[0].trim();

        return time;
    }

    $(document).on('click', '.cancel-booking-btn', function () {

        const bookingId = $(this).data('id');


        Swal.fire({

            title: "Cancel Booking?",

            text: "Are you sure you want to cancel this booking?",

            icon: "warning",

            showCancelButton: true,

            confirmButtonText: "Yes, cancel"

        }).then((result) => {


            if (result.isConfirmed) {

                cancelAusiBooking(bookingId);

            }


        });


    });

    function cancelAusiBooking(id) {

        $.ajax({

            url: `/ausi-booking-mobile/cancel/${id}`,

            type: "POST",

            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },


            success: function (res) {

                Swal.fire({

                    icon: "success",

                    title: "Cancelled",

                    text: "Your booking has been cancelled"

                });


                // reload current selected unit history
                const unit = $('#resident_id_ausi_booking_history').val();
                const email = $('#history_mobile_email').val();

                updateAusiHistoryBookingTable(unit, email);

            },


            error: function (xhr) {

                Swal.fire({

                    icon: "error",

                    title: "Unable to cancel",

                    text: xhr.responseJSON?.message ?? "Something went wrong"

                });

            }

        });

    }
});