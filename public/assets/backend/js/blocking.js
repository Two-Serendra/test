$(document).ready(function () {
    $('#AddDateBlocking').on('hidden.bs.modal', function () {
        $('.modal-backdrop').remove();
    });

    $('.AddDateBlocking').on('click', function () {
        $('#AddDateBlocking').modal('show');

        $('#BlockingDateFieldStart, #BlockingDateFieldEnd').val('').prop('disabled', true);
    });

    $('#amenitySelectBlocking').on('change', function () {
        const selectedAmenityId = $(this).val();

        if (!selectedAmenityId) {
            console.warn("No amenity selected.");
            return;
        }
        $.ajax({
            url: '/admin/fetch-blocked-dates',
            method: 'GET',
            data: { amenity_id: selectedAmenityId },
            success: function (blockedDates) {
                console.log("Blocked Dates:", blockedDates);

                if ($('#BlockingDateFieldStart')[0]._flatpickr) {
                    $('#BlockingDateFieldStart')[0]._flatpickr.destroy();
                }
                if ($('#BlockingDateFieldEnd')[0]._flatpickr) {
                    $('#BlockingDateFieldEnd')[0]._flatpickr.destroy();
                }

                $('#BlockingDateFieldStart, #BlockingDateFieldEnd').prop('disabled', false);

                flatpickr("#BlockingDateFieldStart", {
                    enableTime: false,
                    dateFormat: "Y-m-d",
                    minDate: "today",
                    allowInput: false,
                    altInput: true,
                    altFormat: "F j, Y",
                    disable: blockedDates
                });

                flatpickr("#BlockingDateFieldEnd", {
                    enableTime: false,
                    dateFormat: "Y-m-d",
                    minDate: "today",
                    allowInput: false,
                    altInput: true,
                    altFormat: "F j, Y",
                    disable: blockedDates
                });
            },
            error: function (xhr) {
                console.error('Failed to fetch blocked dates:', xhr.responseText);
            }
        });
    });


    $('#NewDateBlocking').submit(function (event) {
        event.preventDefault();
        var form = this;
        const startDateStr = $(form).find('[name="date_blocking_start"]').val();
        const endDateStr = $(form).find('[name="date_blocking_end"]').val();

        console.log("Raw Start Date:", startDateStr);
        console.log("Raw End Date:", endDateStr);
        const startDate = new Date(Date.parse(startDateStr));
        const endDate = new Date(Date.parse(endDateStr));
        console.log("Converted Start Date:", startDate.toISOString());
        console.log("Converted End Date:", endDate.toISOString());
        if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Date Format',
                text: 'Please select valid dates.',
                showConfirmButton: true
            });
            return;
        }
        if (endDate < startDate) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Date Selection',
                text: 'End Date must be later than Start Date.',
                showConfirmButton: true
            });
            return;
        }

        const $btn = $('#saveDateBlockingBtn');
        const originalWidth = $btn.outerWidth();
        $btn
            .attr('disabled', true)
            .html(`<div class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></div>`)
            .css('width', originalWidth + 'px');

        var formData = new FormData(form);

        $.ajax({
            url: $(this).attr('action'),
            type: $(this).attr('method'),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                $('#AddDateBlocking').modal('hide');
                const Toast = Swal.mixin({
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                    customClass: {
                        popup: 'colored-toast'
                    },
                    didOpen: (toast) => {
                        toast.onmouseenter = Swal.stopTimer;
                        toast.onmouseleave = Swal.resumeTimer;
                    }
                });

                Toast.fire({
                    icon: 'success',
                    title: 'Blocked Successfully'
                });
                form.reset();
                $(form).removeClass('was-validated');
                refreshDateBlocking();

            },
            error: function (xhr, status, error) {
                const Toast = Swal.mixin({
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                    customClass: {
                        popup: 'colored-toast-error'
                    },
                    didOpen: (toast) => {
                        toast.onmouseenter = Swal.stopTimer;
                        toast.onmouseleave = Swal.resumeTimer;
                    }
                });

                Toast.fire({
                    icon: 'error',
                    title: 'Blocking Failed'
                });
            },
            complete: function () {
                $btn
                    .attr('disabled', false)
                    .html(`<span class="btn-text">Save</span>`)
                    .css('width', '');
            }
        });
    });



    $(document).on('click', '.delete_block_date', function () {
        let blockId = $(this).data('id');
        Swal.fire({
            title: "Are you sure?",
            text: "This will permanently remove the blocked date.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/admin/delete-blocked-date',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: { block_id: blockId },
                    success: function (response) {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: "top-end",
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true,
                            customClass: {
                                popup: 'colored-toast'
                            },
                            didOpen: (toast) => {
                                toast.onmouseenter = Swal.stopTimer;
                                toast.onmouseleave = Swal.resumeTimer;
                            }
                        });

                        Toast.fire({
                            icon: 'success',
                            title: 'Deleted Successfully'
                        });

                        refreshDateBlocking();
                    },
                    error: function (xhr, status, error) {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: "top-end",
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true,
                            customClass: {
                                popup: 'colored-toast-error'
                            },
                            didOpen: (toast) => {
                                toast.onmouseenter = Swal.stopTimer;
                                toast.onmouseleave = Swal.resumeTimer;
                            }
                        });

                        Toast.fire({
                            icon: 'error',
                            title: 'Deletion Failed'
                        });
                    }
                });
            }
        });
    });

    function refreshDateBlocking() {
        $.ajax({
            url: '/admin/get-updated-activities-blocking',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                var dateBlockings = response.data;
                var tableBody = $('#dateBlockingTable tbody');
                $('[data-bs-toggle="tooltip"]').tooltip('dispose');
                tableBody.empty();
                dateBlockings.forEach(function (dateBlocking) {

                    var actionButtons = `<button type="button" class="btn btn-danger delete_block_date btn-responsive btn-equal btn-sm"
                                    data-bs-toggle="tooltip" data-bs-placement="right" title="Delete"
                                    data-id="${dateBlocking.id}">
                                    <i class="fa-solid fa-trash"></i>
                                    </button>`;

                    // if (dateBlocking.blocking_status == 0) {
                    //     actionButtons += `<button type="button" class="btn btn-success confirm-booking btn-sm btn-equal mx-1" data-bs-toggle="tooltip" data-bs-placement="right" title="Cancel" data-id="${booking.id}">

                    //                         <i class="fa-solid fa-check-circle"></i>
                    //                     </button>`;
                    // } else {
                    //     actionButtons += `<button type="button" class="btn btn-danger cancel-booking btn-sm btn-equal mx-1" data-bs-toggle="tooltip" data-bs-placement="right" title="Confirm" data-id="${booking.id}">
                    //                          <i class="fa-solid fa-ban"></i>
                    //                     </button>`;
                    // }

                    var amenity_name = dateBlocking.amenity.amenity_name ? dateBlocking.amenity.amenity_name.toUpperCase() : 'N/A';
                    var remarks = dateBlocking.blocking_remarks ? dateBlocking.blocking_remarks.toUpperCase() : 'N/A';
                    var blocking_start = dateBlocking.date_blocking_start ? dateBlocking.date_blocking_start.toUpperCase() : 'N/A';
                    var blocking_end = dateBlocking.date_blocking_end ? dateBlocking.date_blocking_end.toUpperCase() : 'N/A';
                    // var blocking_status = dateBlocking.blocking_status == 1
                    //     ? `<span class="badge bg-success  border-success custom-badge">Booked</span>`
                    //     : `<span class="badge bg-danger border-danger custom-badge">Cancelled</span>`;

                    var row = $(`
                                <tr>
                                    <td>${amenity_name}</td>
                                    <td>${remarks}</td>        
                                    <td>${blocking_start}</td>
                                    <td>${blocking_end}</td>  
                                    <td>${actionButtons}</td>
                                </tr>
                            `);
                    tableBody.append(row);
                });
                $('[data-bs-toggle="tooltip"]').tooltip();
            },
            error: function (xhr, status, error) {
                console.error('Error refreshing the table:', error);
            }
        });
    }

});
