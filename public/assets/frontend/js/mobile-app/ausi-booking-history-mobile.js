$(function () {

    alert("🔥Ausi History JS VERSION 4");

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

    window.onResidentChange = function (e) {
        const unit = e.target.value;

        const email = Alpine.store('superapp').user?.email;

        window.ausiState.unit = unit;
        window.ausiState.email = email;


        logDebugHistory("HISTORY FILTER:", {
            email: email,
            unit: unit
        });


        updateAusiHistoryBookingTable(unit, email);
    };


    function updateAusiHistoryBookingTable(unitName, email) {

        logDebugHistory("ENTERED HISTORY TABLE");

        $.ajax({

            url: "/get-ausi-booking-mobile/history",
            type: "GET",

            data: {
                unit_name: unitName,
                email: email
            },


            success: function (res) {

                logDebugHistory("HISTORY RESPONSE:", res);

                renderHistoryTable(res.bookings);

            },


            error: function (xhr) {

                logDebugHistory(xhr.responseText);

                alert("ERROR " + xhr.status);
            }

        });

    }

    function renderHistoryTable(bookings) {
        logDebugHistory("renderHistoryTable")
        let html = "";


        if (!bookings.length) {

            html = `
            <tr>
                <td colspan="3" class="text-center">
                    No booking history found
                </td>
            </tr>
        `;

        }
        else {

            bookings.forEach(item => {

                html += `
                <tr>
                    <td>${item.booking_date}</td>
                    <td>${item.booking_time_slot}</td>
                    <td>
                        <span class="badge bg-primary">
                            ${item.status}
                        </span>
                    </td>
                </tr>
            `;

            });

        }


        $("#ausiHistoryTable").html(html);

    }
});