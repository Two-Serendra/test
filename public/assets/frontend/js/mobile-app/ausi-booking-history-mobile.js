$(function () {

    alert("🔥Ausi History JS VERSION 7");

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
        const store = Alpine.store('superapp');
        const email = store?.user?.email || '';

        logDebugHistory("HISTORY FILTER", {
            unit: unit,
            email: email
        });


        updateAusiHistoryBookingTable(unit, email);

    };

    function updateAusiHistoryBookingTable(unitName) {

        $.ajax({

            url: "/ausi-booking-mobile/history",

            type: "GET",

            data: {
                unit_name: unitName
            },


            success: function (res) {

                logDebugHistory(
                    "HISTORY RESPONSE",
                    res
                );

                renderHistoryTable(res.bookings);

            },


            error: function (xhr) {

                logDebugHistory(
                    "ERROR",
                    xhr.responseText
                );

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