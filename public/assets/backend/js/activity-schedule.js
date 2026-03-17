
$(document).ready(function () {

    $('.AddBlockingSchedule').on('click', function () {
        $('#addActivityBlockingModal').modal('show');
    });


    $('#ActivityScheduleBlocking').submit(function (event) {
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
        const $btn = $('#saveActivityScheduleBlockingBtn');
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
                $('#addActivityBlockingModal').modal('hide');
                $('#ActivityScheduleBlocking')[0].reset();

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
                refreshTableActivityScheduleBlocking();
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



    function refreshTableActivityScheduleBlocking() {
        $.ajax({
            url: '/admin/get-updated-activity-schedule-blocking-table',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                var activities = response.data;
                var tableBody = $('#activityScheduleBlockingTable tbody');
                $('[data-bs-toggle="tooltip"]').tooltip('dispose');
                tableBody.empty();
                activities.forEach(function (activity) {

                    var actionButtons = `<button type="button" class="btn btn-primary editInfo_id_activity btn-equal btn-sm" data-bs-toggle="tooltip" data-bs-placement="left" title="Edit" data-id="${activity.id}">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>`;

                    if (activity.activity_status == 1) {
                        actionButtons += `<button type="button" class="btn btn-danger deactivate_activity btn-equal btn-responsive btn-sm mx-1" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Deactivate" data-id="${activity.id}" style="margin: 0 4px 0 4px;">
                                        <i class="fa-solid fa-ban"></i>
                                    </button>`;
                    } else {
                        actionButtons += `<button type="button" class="btn btn-success activate-activity btn-equal btn-responsive btn-sm mx-1" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Activate" data-id="${activity.id}" style="margin: 0 4px 0 1px;">
                                    <i class="fa-solid fa-check-circle"></i>

                        </button>`;
                    }
                    actionButtons += `<button type="button" class="btn btn-danger delete_activity btn-equal btn-responsive btn-sm"
                                        data-bs-toggle="tooltip" data-bs-placement="right" title="Delete"
                                        data-id="{{ $activity->id }}" style="margin-right:2px;">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>`;

                    var amenity_name = activity.amenity_name ? activity.amenity_name.toUpperCase() : 'N/A';
                    var activity_name = activity.activity_name ? activity.activity_name.toUpperCase() : 'N/A';
                    var activity_description = limitText(stripHtml(activity.activity_description), 100);
                    var activity_remarks = activity.activity_remarks ? activity.activity_remarks.toUpperCase() : 'N/A';
                    var activity_max_booking = activity.activity_max_booking ? activity.activity_max_booking.toUpperCase() : 'N/A';
                    var activity_space = activity.activity_space ? activity.activity_space.toUpperCase() : 'N/A';
                    var activity_image = activity.activity_image
                        ? `<img src="/assets/images/activities/${activity.activity_image}" alt="Amenity Image" style="width: 100px; height: auto;">`
                        : 'N/A';
                    var activity_status = activity.activity_status == 1
                        ? `<span class="badge bg-success custom-badge">Active</span>`
                        : `<span class="badge bg-danger custom-badge">Inactive</span>`;

                    var row = $(`
                            <tr>
                                <td>${amenity_name}</td>
                                <td>${activity_name}</td>
                                <td>${activity_image}</td>
                                <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${activity_description}</td>
                                <td>${activity_status}</td>
                                <td>${activity_remarks}</td>   
                                <td>${activity_max_booking}</td>   
                                <td>${activity_space}</td>  
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


