$(function () {

    console.log("🚀 Mobile Booking JS Loaded");

    flatpickr("#AusiBookingDate", {
        dateFormat: "Y-m-d",
        minDate: new Date().fp_incr(1),
        onChange: function (selectedDates, dateStr) {
            updateSlots(dateStr);
        }
    });

    function updateSlots(date) {

        console.log("updateSlots", date);

        if (!date) return;

        const residentId =
            document.querySelector(
                'select[name="resident_id_ausi"]'
            )?.value;

        console.log("resident", residentId);

        if (!residentId) return;

        showLoading();

        resetSlots();

        $.ajax({

            url: "/ausi-booked-slots",

            type: "GET",

            data: {

                date: date,

                resident_id: residentId

            },

            success: function (res) {

                resetSlots();

                disableBookedSlots(
                    res.blocked_for_user || []
                );

                disablePastSlots(date);

            },

            error: function (xhr) {

                console.log(xhr);

            },

            complete: function () {

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