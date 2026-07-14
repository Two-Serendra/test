$(function () {

    autoSelectHistoryResidence();
    // alert("🔥Ausi History JS VERSION 47");
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

    logDebugHistoryAusi("SELECT EXISTS: " + (el ? "YES" : "NO"));

    function logDebugHistoryAusi(...args) {

        const msg = args.map(a =>
            typeof a === 'object'
                ? JSON.stringify(a)
                : a
        ).join(' ');

        console.log(msg);

        const el = document.getElementById('debugPanelBookingHistoryAusi');

        if (el) {
            el.innerHTML += msg + "<br>";
            el.scrollTop = el.scrollHeight;
        }
    }

    document.addEventListener('change', function (e) {
        logDebugHistoryAusi("CHANGE EVENT: " + e.target.id);
        if (e.target && e.target.id === 'resident_id_ausi_booking_history') {
            window.onResidentChangeHistory(e);
        }
    });

    window.onResidentChangeHistory = function (e) {

        const unit = e.target.value;
        const email = $('#history_mobile_email').val();

        $("#historyWrapperAusi").removeClass("d-none");

        updateAusiHistoryBookingTable(
            unit,
            email
        );
    };

    function updateAusiHistoryBookingTable(unitName, email) {
        showHistoryLoadingAusi();
        $("#ausiHistoryTable").html("");
        $.ajax({

            url: "/get-ausi-booking-mobile/history",

            type: "GET",

            data: {
                unit_name: unitName,
                email: email

            },


            success: function (res) {

                logDebugHistoryAusi(
                    "HISTORY RESPONSE",
                    res
                );
                $("#historyWrapperAusi").removeClass('d-none');

                renderHistoryTableAusi(res.bookings);

                $("#historyPageLoading").fadeOut(200, function () {
                    $(this).remove();
                });

            },

            error: function (xhr) {

                logDebugHistoryAusi(
                    "ERROR",
                    xhr.responseText
                );

                $("#historyPageLoading").fadeOut(200);


            },
            complete: function () {

                hideHistoryLoadingAusi();

            }

        });

    }

    function showHistoryLoadingAusi() {

        $("#historyWrapperAusi").removeClass('d-none');

        $("#historyLoadingAusi")
            .removeClass("d-none");

    }


    function hideHistoryLoadingAusi() {

        $("#historyLoadingAusi")
            .addClass("d-none");

    }

    function renderHistoryTableAusi(bookings) {
        logDebugHistoryAusi("renderHistoryTableAusi");

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

                switch (Number(item.booking_status)) {

                    case 0:
                        statusText = "Cancelled";
                        badgeClass = "bg-danger";
                        break;

                    case 1:
                        statusText = "Scheduled";
                        badgeClass = "bg-warning text-dark";
                        break;

                    case 2:
                        statusText = "Completed";
                        badgeClass = "bg-primary";
                        break;

                    default:
                        statusText = "Unknown";
                        badgeClass = "bg-secondary";
                }

                let viewButton = `
                    <button
                        class="btn btn-sm rounded-pill px-3 btn-primary view-ausi-booking-btn"
                        data-id="${item.id}">
                        View
                    </button>
                `;

                let cancelButton = "";

                if (Number(item.booking_status) === 1) {

                    const bookingDateTime = new Date(
                        `${item.booking_date} ${convertTime(item.booking_time_slot)}`
                    );

                    const canCancel = now < bookingDateTime;

                    cancelButton = `
                        <button
                            class="btn btn-sm rounded-pill px-3 cancel-booking-btn
                                ${canCancel ? 'btn-danger' : 'btn-secondary'}"
                            ${canCancel ? '' : 'disabled'}
                            data-id="${item.id}">
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

                 <div class="mt-3 d-flex gap-2">
                    ${viewButton}
                    ${cancelButton}
                </div>

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
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Booking cancelled successfully',
                    showConfirmButton: false,
                    timer: 3000,
                    customClass: {
                        popup: 'swal2-success-toast'
                    }
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

    $(document).on('click', '.view-ausi-booking-btn', function () {
        let info_id = $(this).data('id');
        showHistoryLoadingAusi();
        $.get('/fetch-ausi-booking-mobile/' + info_id, function (data) {
            logDebugHistoryAusi("AUSI booking data fetched successfully");
            logDebugHistoryAusi(JSON.stringify(data));
            logDebugHistoryAusi("SUCCESS CALLBACK REACHED");
            // alert("view ausi is hitting");
            $('#view_transaction_no')
                .text(data.transaction_no);

            $('#view_name')
                .text(data.name);


            $('#view_unit')
                .text(data.unit_no);


            let residentType = data.resident_type?.toUpperCase() ?? 'N/A';

            logDebugHistoryAusi("Resident Type: " + residentType);

            $('#view_resident_type')
                .removeClass('text-primary text-danger text-secondary')
                .addClass(
                    residentType === 'OWNER'
                        ? 'text-primary'
                        : residentType === 'TENANT'
                            ? 'text-danger'
                            : 'text-secondary'
                )
                .text(residentType);


            $('#view_booking_date')
                .text(data.booking_date);

            $('#view_time_slot')
                .text(data.booking_time_slot);


            $('#view_remarks')
                .text(
                    data.remarks ?? 'No remarks provided.'
                );


            logDebugHistoryAusi(
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


                logDebugHistoryAusi(
                    "No inspection results found"
                );

                inspectionHtml = `
                <div class="alert alert-warning">
                    <i class="fa-solid fa-circle-exclamation me-2"></i>
                    Unit has not been inspected yet.
                </div>
            `;

            } else {


                logDebugHistoryAusi(
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


                    logDebugHistoryAusi(
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
            logDebugHistoryAusi(
                "Opening AUSI view modal"
            );

            logDebugHistoryAusi(
                "Modal Count: " +
                $('#ausiViewResultModal').length
            );

            logDebugHistoryAusi(
                "Modal Element: " +
                (document.getElementById('ausiViewResultModal') ? 'FOUND' : 'NOT FOUND')
            );
            hideHistoryLoadingAusi();
            $('#ausiViewResultModal').modal('show');
        })
            .fail(function (xhr, status, error) {

                logDebugHistoryAusi("FAILED TO FETCH AUSI BOOKING");
                logDebugHistoryAusi("Status: " + status);
                logDebugHistoryAusi("Error: " + error);
                logDebugHistoryAusi("HTTP Code: " + xhr.status);
                logDebugHistoryAusi("Response: " + xhr.responseText);


                alert("Unable to load booking details.");

            })
            .always(function () {

                logDebugHistoryAusi(
                    "AUSI booking request completed"
                );

                hideHistoryLoadingAusi();

            });

    });
});