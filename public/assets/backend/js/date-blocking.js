$(document).ready(function () {
    $('.AddFunctionRoomDateBlocking').on('click', function () {
        $('#AddDateBlockingFunctionRoom').modal('show');
    });

    $('#functionRoomSelectBlocking').on('change', function () {
        const selectedFunctionRoomId = $(this).val();

        if (!selectedFunctionRoomId) {
            console.warn("No function room selected.");
            return;
        }
        $.ajax({
            url: '/admin/admin-fetch-function-room-blocked-dates',
            method: 'GET',
            data: { function_room_id: selectedFunctionRoomId },
            success: function (blockedDates) {
                console.log("Blocked Dates:", blockedDates);

                if ($('#FunctionRoomBlockingDateFieldStart')[0]._flatpickr) {
                    $('#FunctionRoomBlockingDateFieldStart')[0]._flatpickr.destroy();
                }
                if ($('#FunctionRoomBlockingDateFieldEnd')[0]._flatpickr) {
                    $('#FunctionRoomBlockingDateFieldEnd')[0]._flatpickr.destroy();
                }

                $('#FunctionRoomBlockingDateFieldStart, #FunctionRoomBlockingDateFieldEnd').prop('disabled', false);

                flatpickr("#FunctionRoomBlockingDateFieldStart", {
                    enableTime: false,
                    dateFormat: "Y-m-d",
                    minDate: "today",
                    allowInput: false,
                    altInput: true,
                    altFormat: "F j, Y",
                    disable: blockedDates
                });

                flatpickr("#FunctionRoomBlockingDateFieldEnd", {
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


    $('#function-room-date-blocking').submit(function (event) {
        event.preventDefault();
        var form = this;
        const startDateStr = $(form).find('[name="function_room_date_blocking_start"]').val();
        const endDateStr = $(form).find('[name="function_room_date_blocking_end"]').val();
        const startDate = new Date(Date.parse(startDateStr));
        const endDate = new Date(Date.parse(endDateStr));
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
                $('#AddDateBlockingFunctionRoom').modal('hide');
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
                refreshFunctionRoomDateBlocking();
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
                // Restore button to original state
                $btn
                    .attr('disabled', false)
                    .html(`<span class="btn-text">Save</span>`)
                    .css('width', '');
            }
        });
    });


    function refreshFunctionRoomDateBlocking() {
        $.ajax({
            url: '/admin/admin-get-updated-function-room-date-blocking',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                var functionRoomDateBlockings = response.data;
                var tableBody = $('#functionRoomDateBlockingTable tbody');
                $('[data-bs-toggle="tooltip"]').tooltip('dispose');
                tableBody.empty();
                functionRoomDateBlockings.forEach(function (functionRoomDateBlocking) {

                    var actionButtons = `<button type="button" class="btn btn-danger delete_block_date btn-responsive btn-equal btn-sm"
                                    data-bs-toggle="tooltip" data-bs-placement="right" title="Delete"
                                    data-id="${functionRoomDateBlocking.id}">
                                    <i class="fa-solid fa-trash"></i>
                                    </button>`;



                    var function_room_name = functionRoomDateBlocking.functionRoom.function_room_name ? functionRoomDateBlocking.functionRoom.function_room_name.toUpperCase() : 'N/A';
                    var blocking_remarks = functionRoomDateBlocking.blocking_remarks ? functionRoomDateBlocking.blocking_remarks.toUpperCase() : 'N/A';

                    var date_blocking_start = functionRoomDateBlocking.date_blocking_start ? functionRoomDateBlocking.date_blocking_start.toUpperCase() : 'N/A';
                    var date_blocking_end = functionRoomDateBlocking.date_blocking_end ? functionRoomDateBlocking.date_blocking_end.toUpperCase() : 'N/A';
                    var row = $(`
                                <tr>
                                    <td>${function_room_name}</td>
                                    <td>${blocking_remarks}</td>        
                                    <td>${date_blocking_start}</td>
                                    <td>${date_blocking_end}</td>  
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