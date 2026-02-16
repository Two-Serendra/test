$(document).ready(function () {

    window.loadBookings = function (page = 1) {
        let unitNo = $('#unit_no').val();
        let bookingType = $('#booking_type').val();

        $.ajax({
            url: '/resident-booking-history',
            type: "GET",
            cache: false,
            data: {
                unit_no: unitNo,
                booking_type: bookingType,
                page: page,
                _t: Date.now()
            },
            beforeSend: function () {
                $('#bookingTableContainer').html('<div class="text-center py-5">Loading...</div>');
            },
            success: function (response) {
                $('#bookingTableContainer').html(response);
            }
        });
    };



    // Reload table when dropdown changes
    $('#unit_no, #booking_type').on('change', function () {
        loadBookings();
    });

    // AJAX pagination
    $(document).on('click', '.pagination a', function (e) {
        e.preventDefault();
        let page = $(this).attr('href').split('page=')[1];
        loadBookings(page);
    });

});
