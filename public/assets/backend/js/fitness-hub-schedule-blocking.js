
$(document).ready(function () {


    $('.AddBlockingScheduleFitnessHub').on('click', function () {
        $('#addFitnessHubBlockingModal').modal('show');
    });

    $('#selectAllDays').on('change', function () {
        $('input[name="days[]"]').prop('checked', this.checked);
    });

    $('#FitnessHubScheduleBlocking').submit(function (event) {
        event.preventDefault();

        const startTime = $('#blocking_start_time').val();
        const endTime = $('#blocking_end_time').val();

        if (!this.checkValidity()) {
            this.classList.add('was-validated');
            return;
        }

        if (endTime <= startTime) {
            alert('End time must be greater than start time.');
            return;
        }
        this.classList.remove('was-validated');
        const $btn = $('#saveAFitnessHubScheduleBlockingBtn');
        const originalWidth = $btn.outerWidth();
        $btn
            .attr('disabled', true)
            .html(`<div class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></div>`)
            .css('width', originalWidth + 'px');


        var formData = new FormData(this);
        var form = this;

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
                $('#addFitnessHubBlockingModal').modal('hide');
                $('#FitnessHubScheduleBlocking')[0].reset();

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
                    title: 'Activity Added Successfully'
                });

                form.reset();
                $(form).removeClass('was-validated');
                refreshTableFitnessHubScheduleBlocking();
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
                    title: 'Failed to add activity'
                });
            },
            complete: function () {
                $btn
                    .attr('disabled', false)
                    .html(`<span class="btn-text">Create</span>`)
                    .css('width', '');
            }
        });
    });


    function stripHtml(html) {
        var tmp = document.createElement("DIV");
        tmp.innerHTML = html;
        return tmp.textContent || tmp.innerText || "";
    }


    function limitText(text, maxLength = 100) {
        if (!text) return "N/A";
        if (text.length <= maxLength) return text;
        return text.substring(0, maxLength) + "...";
    }

    function refreshTableFitnessHubScheduleBlocking() {

        $.ajax({
            url: '/admin/get-updated-fitness-hubs-schedule-blocking-table',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                console.log(response);
                var dateBlockings = response.data;
                var tableBody = $('#fitnessHubScheduleBlockingTable tbody');

                $('[data-bs-toggle="tooltip"]').tooltip('dispose');

                tableBody.empty();

                if (dateBlockings.length === 0) {
                    tableBody.append(`
                    <tr>
                        <td colspan="8" class="text-center">No Records Found</td>
                    </tr>
                `);
                    return;
                }

                dateBlockings.forEach(function (dateBlocking) {
                    var actionButtons = `<button type="button" class="btn btn-danger delete_block_schedule_fitness-hub btn-responsive btn-equal btn-sm"
                                    data-bs-toggle="tooltip" data-bs-placement="right" title="Delete"
                                    data-id="${dateBlocking.id}">
                                    <i class="fa-solid fa-trash"></i>
                                    </button>`;


                    var fitness_hub_name = dateBlocking.fitnessHub
                        ? dateBlocking.fitnessHub.fitness_hub_name.toUpperCase()
                        : 'N/A';

                    var day = dateBlocking.day
                        ? dateBlocking.day.toUpperCase()
                        : 'N/A';

                    var start_time = dateBlocking.start_time
                        ? moment(dateBlocking.start_time, "HH:mm:ss").format("h:mm A")
                        : 'N/A';

                    var end_time = dateBlocking.end_time
                        ? moment(dateBlocking.end_time, "HH:mm:ss").format("h:mm A")
                        : 'N/A';

                    var remarks = dateBlocking.remarks
                        ? dateBlocking.remarks.toUpperCase()
                        : 'N/A';

                    var repeat_weekly = dateBlocking.repeat_weekly == 1
                        ? `<span class="badge bg-success">Yes</span>`
                        : `<span class="badge bg-danger">No</span>`;

                    var created_at = dateBlocking.created_at
                        ? moment(dateBlocking.created_at).format("MMM D, YYYY h:mm A")
                        : 'N/A';

                    var updated_at = dateBlocking.updated_at
                        ? moment(dateBlocking.updated_at).format("MMM D, YYYY h:mm A")
                        : 'N/A';

                    var row = `
                    <tr>
                        <td>${fitness_hub_name}</td>
                        <td>${day}</td>
                        <td>${start_time}</td>
                        <td>${end_time}</td>
                        <td>${remarks}</td>
                        <td>${repeat_weekly}</td>
                        <td>${created_at}</td>
                        <td>${updated_at}</td>
                        <td>${actionButtons}</td>
                    </tr>
                `;

                    tableBody.append(row);
                });

                $('[data-bs-toggle="tooltip"]').tooltip();
            },

            error: function (xhr, status, error) {
                console.error('Error refreshing the table:', error);
            }
        });
    }


    $(document).on('click', '.delete_block_schedule_fitness-hub', function () {
        let blockId = $(this).data('id');
        Swal.fire({
            title: "Are you sure?",
            text: "This will permanently remove the blocked schedule.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/admin/admin-delete-blocked-schedule-fitness-hub',
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

                        refreshTableFitnessHubScheduleBlocking();
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

});
