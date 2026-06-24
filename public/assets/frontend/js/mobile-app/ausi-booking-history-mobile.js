$(function () {

    autoSelectHistoryResidence();
    logDebugHistory("🔥Ausi History JS VERSION 32");
    function autoSelectHistoryResidence() {

        const select = $('#resident_id_ausi_booking_history');

        if (!select.length) {
            console.log("HISTORY SELECT NOT FOUND");
            return;
        }


        const options = select.find('option');

        console.log(
            "HISTORY OPTIONS:",
            options.length
        );


        // Alpine has not rendered yet
        if (options.length <= 1) {
            setTimeout(autoSelectHistoryResidence, 300);
            return;
        }


        const firstUnit = options.eq(1).val();


        if (!firstUnit) {
            console.log("NO FIRST UNIT");
            return;
        }


        console.log(
            "AUTO SELECT UNIT:",
            firstUnit
        );

        select.val(firstUnit);

        select.trigger('input');

        const email = $('#history_mobile_email').val();

        updateAusiHistoryBookingTable(
            firstUnit,
            email
        );

    }

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
        $("#ausiHistoryTable").html("");
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

                $("#historyPageLoading").fadeOut(200, function () {
                    $(this).remove();
                });

            },

            error: function (xhr) {

                logDebugHistory(
                    "ERROR",
                    xhr.responseText
                );

                $("#historyPageLoading").fadeOut(200);


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

                let viewButton = `
                    <button
                        class="btn btn-sm rounded-pill px-3 btn-outline-primary view-ausi-booking-btn"
                        data-id="${item.id}">
                        <i class="bx bx-show me-1"></i>
                        View
                    </button>
                `;

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

                    </div>

                    ${cancelButton
                        ? `
                        <div class="mt-3">
                          ${viewButton}
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
        const button = $(this);
        const bookingId = $(this).data('id');

        Swal.fire({

            title: "Cancel Booking?",

            text: "Are you sure you want to cancel this booking?",

            icon: "warning",

            showCancelButton: true,

            confirmButtonText: "Yes, cancel"

        }).then((result) => {


            if (result.isConfirmed) {
                button.prop('disabled', true);
                cancelAusiBooking(bookingId);

            }


        });


    });

    function cancelAusiBooking(id) {
        const email = $('#history_mobile_email').val();

        $.ajax({

            url: `/ausi-booking-mobile/cancel/${id}`,

            type: "POST",

            data: {
                email: email
            },

            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },


            success: function (res) {

                Swal.fire({
                    icon: "success",
                    title: "Cancelled",
                    text: "Your booking has been cancelled"
                });


                // keep current selected residence
                const unit =
                    Alpine.store('superapp')?.selectedUnit ||
                    $('#resident_id_ausi_booking_history').val();


                const email =
                    $('#history_mobile_email').val();


                console.log(
                    "RELOAD HISTORY AFTER CANCEL:",
                    unit,
                    email
                );


                updateAusiHistoryBookingTable(
                    unit,
                    email
                );

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

    $('#ausiHistoryTable').on('click', '.view-ausi-booking-btn', function () {

        let info_id = $(this).data('id');

        logDebugHistory("VIEW AUSI BOOKING CLICKED");
        logDebugHistory("Booking ID: " + info_id);

        showLoading();

        $.get('/fetch-ausi-booking-mobile/' + info_id, function (data) {

            logDebugHistory("AUSI booking data fetched successfully");
            logDebugHistory(JSON.stringify(data));


            $('#view_transaction_no')
                .text(data.transaction_no);

            $('#view_name')
                .text(data.name);


            $('#view_unit')
                .text(data.unit_no);


            let residentType = data.resident_type?.toUpperCase() ?? 'N/A';

            logDebugHistory("Resident Type: " + residentType);

            $('#view_resident_type').html(
                `<span class="badge bg-secondary">
                ${residentType}
            </span>`
            );


            $('#view_booking_date')
                .text(data.booking_date);

            $('#view_time_slot')
                .text(data.booking_time_slot);


            $('#view_remarks')
                .text(
                    data.remarks ?? 'No remarks provided.'
                );


            logDebugHistory(
                "Booking Status: " +
                data.display_status
            );


            $('#view_booking_status').html(`
            <span class="badge bg-${data.status_badge}">
                ${data.display_status.toUpperCase()}
            </span>
        `);



            let inspectionHtml = "";

            if (!data.inspection_results ||
                data.inspection_results.length === 0) {


                logDebugHistory(
                    "No inspection results found"
                );


                inspectionHtml = `
                <div class="alert alert-warning">
                    <i class="fa-solid fa-circle-exclamation me-2"></i>
                    Unit has not been inspected yet.
                </div>
            `;


            } else {


                logDebugHistory(
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


                    logDebugHistory(
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



            logDebugHistory(
                "Opening AUSI view modal"
            );


            $('#ausiViewResultModal')
                .modal('show');


        })
            .fail(function (xhr, status, error) {

                logDebugHistory("FAILED TO FETCH AUSI BOOKING");
                logDebugHistory("Status: " + status);
                logDebugHistory("Error: " + error);
                logDebugHistory("HTTP Code: " + xhr.status);
                logDebugHistory("Response: " + xhr.responseText);


                alert("Unable to load booking details.");

            })
            .always(function () {

                logDebugHistory(
                    "AUSI booking request completed"
                );

                hideLoading();

            });

    });
});