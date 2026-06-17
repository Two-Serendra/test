$(function () {

    alert("🔥Ausi History JS VERSION 2");
    document.addEventListener('change', function (e) {
        if (e.target && e.target.id === 'resident_id_ausi_booking_history') {
            window.onResidentChange(e);
        }
    });

    window.onResidentChange = function (e) {
        const value = e.target.value;

        window.ausiState.unit = value;

        if (window.Alpine && Alpine.store('superapp')) {
            Alpine.store('superapp').selectedUnit = value;
        }

        console.log("UNIT:", value);
        alert("UNIT CHANGED HISTORY: " + value);
        alert("CHANGE FIRED HISTORY");

        triggerUpdate();
    };


    function triggerUpdate() {
        const unit = window.ausiState.unit;

        console.log("TRIGGER:", { unit });
        updateAusiHistoryBookingTable(unit);
    }

    function updateAusiHistoryBookingTable(unitName) {

        alert("ENTERED History Booking Table");
        try {
            $.ajax({

                url: "/get-ausi-booking-mobile/history",
                type: "GET",
                data: { unit_name: unitName },


                success: function (res) {

                },

                error: function (xhr) {

                    console.log(xhr.responseText);
                    alert("ERROR " + xhr.status);

                    hideLoading();
                }

            });

        } catch (e) {

            alert("JS ERROR:\n" + e.message);

            logDebug("FULL ERROR:", e);
        }
    }
});