$(function () {

    function logDebug(...args) {

        const msg = args.map(a =>
            typeof a === 'object'
                ? JSON.stringify(a)
                : a
        ).join(' ');

        console.log(msg);

        const el = document.getElementById('debugPanel');

        if (el) {
            el.innerHTML += msg + "<br>";
            el.scrollTop = el.scrollHeight;
        }
    }

    logDebug("🚀 Mobile Booking JS Loaded");

    flatpickr("#AusiBookingDate", {
        dateFormat: "Y-m-d",
        minDate: new Date().fp_incr(1),
        onChange: function (selectedDates, dateStr) {
            updateSlots(dateStr);
        }
    });

    document.addEventListener('change', (e) => {
        if (e.target.id === 'resident_id_ausi') {
            Alpine.store('superapp').selectedUnit = e.target.value;
            logDebug("SYNC STORE", e.target.value);
        }
    });

    function updateSlots(date) {

        logDebug("updateSlots", date);

        const unitName = document.querySelector('#resident_id_ausi')?.value || '';

        logDebug("unitName", unitName);

        if (!date || !unitName) return;

        logDebug("STORE TEST", Alpine.store('superapp'));
        logDebug("SELECTED UNIT", Alpine.store('superapp')?.selectedUnit);

        showLoading();
        resetSlots();

        $.ajax({
            url: "/mobile/ausi-booked-slots",
            type: "GET",
            data: {
                date,
                unit_name: unitName
            },

            success: function (res) {
                logDebug("SUCCESS", res);

                resetSlots();
                disableBookedSlots(res.blocked_for_user || []);
                disablePastSlots(date);
            },

            error: function (xhr) {
                logDebug("ERROR", {
                    status: xhr.status,
                    response: xhr.responseText
                });
            },

            complete: function () {
                logDebug("COMPLETE");
                hideLoading();
            }
        });
    }

    $(document).on(
        "change",
        "#AusiBookingDate",
        function () {

            updateSlots($(this).val());

        }
    );

    $(document).on(
        "change",
        "select[name='resident_id_ausi']",
        function () {

            const date =
                $("#AusiBookingDate").val();

            if (date) {

                updateSlots(date);

            }

        }
    );

    function resetSlots() {

        $(".ausi-booking-slot").each(function () {

            $(this)
                .prop("disabled", false)
                .prop("checked", false);

            $('label[for="' + this.id + '"]')
                .removeClass(
                    "disabled btn-secondary"
                )
                .addClass("btn-outline-primary")
                .css("cursor", "pointer");

        });

    }

    function disableBookedSlots(bookedSlots) {

        bookedSlots.forEach(function (slot) {

            const radio =
                $('.ausi-booking-slot[data-slot="' + slot + '"]');

            if (!radio.length) return;

            radio.prop("disabled", true);

            $('label[for="' + radio.attr("id") + '"]')
                .removeClass("btn-outline-primary")
                .addClass("btn-secondary disabled")
                .css("cursor", "not-allowed");

        });

    }

    function showLoading() {

        $("#slotLoading").removeClass("d-none");

        $(".ausi-booking-slot").prop(
            "disabled",
            true
        );

    }

    function hideLoading() {

        $("#slotLoading").addClass("d-none");

    }

    function disablePastSlots(selectedDate) {

        if (!selectedDate) return;

        const now = new Date();

        $(".ausi-booking-slot").each(function () {

            const slot =
                $(this).data("slot");

            const start =
                slot.split(" - ")[0]
                    .replace("NN", "PM");

            const slotDate =
                new Date(
                    `${selectedDate} ${start}`
                );

            if (slotDate <= now) {

                $(this).prop(
                    "disabled",
                    true
                );

                $('label[for="' + $(this).attr("id") + '"]')
                    .removeClass("btn-outline-primary")
                    .addClass("btn-secondary disabled")
                    .css("cursor", "not-allowed");

            }

        });

    }

});