$(function () {

    alert("🔥Ausi History JS VERSION 14");

    const el = document.getElementById('resident_id_ausi_booking_history');

    alert("SELECT EXISTS: " + (el ? "YES" : "NO"));

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
        alert("CHANGE EVENT: " + e.target.id);
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
        logDebugHistory("renderHistoryTable")
        let html = "";


        if (!bookings || bookings.length === 0) {

            html = `
<tr>
    <td colspan="5">

        <div class="py-4 text-center">

            <i class="bx bx-calendar-x fs-1 text-muted"></i>

            <p class="text-muted mt-2 mb-0">
                No booking history found
            </p>

        </div>

    </td>
</tr>
`;

        }
        else {

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
                        badgeClass = "bg-warning";

                    }
                    else if (now > bookingEnd) {

                        statusText = "Completed";
                        badgeClass = "bg-success";

                    }
                    else {

                        statusText = "Confirmed";
                        badgeClass = "bg-primary";

                    }

                }

                let cancelButton = "";

                if (item.booking_status == 1) {

                    const bookingStart = new Date(
                        `${item.booking_date} ${convertTime(item.booking_time_slot)}`
                    );

                    const hoursDiff = (bookingStart - now) / (1000 * 60 * 60);


                    if (hoursDiff >= 12) {
                        cancelButton = `
<button 
    class="btn btn-sm btn-outline-danger rounded-pill px-3 cancel-booking-btn"
    data-id="${item.id}">
    Cancel
</button>
`;

                    } else {

                        cancelButton = `
<button 
    class="btn btn-sm btn-outline-secondary rounded-pill px-3"
    disabled>
    Cancel
</button>
`;
                    }

                }

                html += `
            <tr>
                <td>${item.transaction_no}</td>
                <td>${item.booking_date}</td>
                <td>${item.booking_time_slot}</td>

                <td>
                    <span class="badge ${badgeClass} rounded-pill px-3 py-2">
    ${statusText}
</span>
                </td>

               <td>

   <div class="d-flex gap-2 justify-content-center flex-wrap">

    <a href="/ausi-booking-details/${item.id}"
       class="btn btn-sm btn-primary rounded-pill px-3">
        View
    </a>

    ${cancelButton}

</div>
            </tr>
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