$(document).ready(function () {

    $('input[name="timeOption"]').on('change', function () {
        if ($('#manualTime').is(':checked')) {
            $('#manualTimeInputs').show();
            $('#sameTimeInputs').hide();
            $('#manualTimeInputs input[type="time"]').prop('disabled', false);
            $('#sameStartTime, #sameEndTime').prop('disabled', true);
        } else if ($('#sameTime').is(':checked')) {
            $('#manualTimeInputs').hide();
            $('#sameTimeInputs').show();
            $('#manualTimeInputs input[type="time"]').prop('disabled', true);
            $('#sameStartTime, #sameEndTime').prop('disabled', false);
        }
    });

    $('#sameStartTime, #sameEndTime').on('input', function () {
        if ($('#sameTime').is(':checked')) {
            const startTime = $('#sameStartTime').val();
            const endTime = $('#sameEndTime').val();
            $('#manualTimeInputs input[name$="[start]"]').val(startTime);
            $('#manualTimeInputs input[name$="[end]"]').val(endTime);
        }
    });

    $('#activityImage').on('change', function () {
        const file = this.files[0];
        const previewContainer = $('#imagePreviewContainer');
        const previewImage = $('#imagePreviewActivity');
        const placeholderText = previewContainer.find('span');

        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                placeholderText.hide();
                previewImage.attr('src', e.target.result).show();
            };
            reader.readAsDataURL(file);
        } else {
            previewImage.hide();
            placeholderText.show();
        }
    });


    $('#activitiesForm').submit(function (event) {
        event.preventDefault();

        const startTime = $('#start_time').val();
        const endTime = $('#end_time').val();

        if (!this.checkValidity()) {
            this.classList.add('was-validated');
            return;
        }

        if (endTime <= startTime) {
            alert('End time must be greater than start time.');
            return;
        }
        this.classList.remove('was-validated');
        const $btn = $('#saveActivityBtn');
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
                $('#activityAdd').modal('hide');
                $('#imagePreviewActivity').attr('src', '#').hide();
                $('#activitiesForm')[0].reset(); // Reset the form
                $('#sameTime').prop('checked', true).trigger('change');

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
                refreshTableActivities();
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


    $('.AddActivity').on('click', function () {
        $('#activityAdd').modal('show');
    });

    $('#activityAdd').on('hidden.bs.modal', function () {
        $('.modal-backdrop').remove();
    });


    $('#activityTable').on('click', '.editInfo_id_activity', function () {
        var act_id = $(this).data("id");

        $.get('/admin/fetch/activity/' + act_id, function (data) {
            $('#activityEdit').modal('show');
            $('#act_id').val(data.id);
            $('#hidden_amenity_id_activity').val(data.amenity_id);
            $('#edit_amenity_select').val(data.amenity_id);
            $('#edit_activity_name').val(data.activity_name);
            $('#edit_activity_description').val(data.activity_description);
            $('#currentImageFileNameActivity').val(data.activity_image);
            $('input[name="edit_activity_max_booking"]').each(function () {
                $(this).prop('checked', $(this).val() == data.activity_max_booking);
            });
            $('input[name="edit_activity_space"]').each(function () {
                $(this).prop('checked', $(this).val() == data.activity_space);
            });
            if (data.activity_image) {
                const imagePath = '/assets/images/activities/' + data.activity_image;
                $('#edit_imagePreviewActivity').attr('src', imagePath).show();
            } else {
                $('#edit_imagePreviewActivity').hide();
            }

            let allSame = true;
            let firstStart = null;
            let firstEnd = null;

            if (data.schedules) {
                // console.log(data.schedules);
                if (typeof data.schedules === 'object') {
                    Object.keys(data.schedules).forEach((day, index) => {
                        const start = data.schedules[day].start;
                        const end = data.schedules[day].end;

                        // console.log("Start:", start, "End:", end);
                        $(`input[name="times[${day}][start]"]`).val(start ? start : '').attr('value', start ? start : '');
                        $(`input[name="times[${day}][end]"]`).val(end ? end : '').attr('value', end ? end : '');



                        if (index === 0) {
                            firstStart = start;
                            firstEnd = end;
                        } else if (start !== firstStart || end !== firstEnd) {
                            allSame = false;
                        }
                    });
                }

                if (allSame) {

                    $('#edit_sameTime').prop('checked', true);
                    $('#edit_manualTimeInputs').hide();
                    $('#edit_sameTimeInputs').show();
                    $('#edit_sameStartTime').val(firstStart);
                    $('#edit_sameEndTime').val(firstEnd);
                } else {

                    $('#edit_manualTime').prop('checked', true);
                    $('#edit_manualTimeInputs').show();
                    $('#edit_sameTimeInputs').hide();
                }
            }
        }).fail(function () {
            alert("Data not found");
        });
    });


    $('input[name="edit_timeOption"]').on('change', function () {
        if ($('#edit_manualTime').is(':checked')) {
            $('#edit_manualTimeInputs').show();
            $('#edit_sameTimeInputs').hide();
            $('#edit_manualTimeInputs input[type="time"]');
            $('#edit_sameStartTime, #edit_sameEndTime');
        } else if ($('#edit_sameTime').is(':checked')) {
            $('#edit_manualTimeInputs').hide();
            $('#edit_sameTimeInputs').show();
            $('#edit_manualTimeInputs input[type="time"]');
            $('#edit_sameStartTime, #edit_sameEndTime');
        }
    });


    $('#edit_sameStartTime, #edit_sameEndTime').on('input', function () {
        if ($('#edit_sameTime').is(':checked')) {
            const startTime = $('#edit_sameStartTime').val();
            const endTime = $('#edit_sameEndTime').val();
            $('input[name^="times["][name$="[start]"]').val(startTime);
            $('input[name^="times["][name$="[end]"]').val(endTime);
        }
    });




    $('#updateFormActivity').on('submit', function (e) {
        e.preventDefault();
        var formData = new FormData(this);

        $.ajax({
            url: '/admin/update-activities',
            type: "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                $('#activityEdit').modal('hide');
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
                    title: 'Updated Successfully'
                });

                refreshTableActivities();

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
                    title: 'Update failed'
                });
            }
        });
    });

    function refreshTableActivities() {
        $.ajax({
            url: '/admin/get-updated-activities-table',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                var activities = response.data;
                var tableBody = $('#activityTable tbody');
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
                    var activity_description = activity.activity_description ? activity.activity_description.toUpperCase() : 'N/A';
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


    $('#activityTable').on('click', '.deactivate_activity', function () {
        var activity_id = $(this).data("id");
        $('#activity_id').val(activity_id);
        $.get('/admin/fetch/activity_add_remarks/' + activity_id, function (data) {
            $('#activityRemarks').modal('show');
        })
            .fail(function () {
                alert("Data not found");
            });
    });

    $('#addActivityRemarks').on('submit', function (e) {
        e.preventDefault();
        var formData = new FormData(this);
        if (!this.checkValidity()) {
            this.classList.add('was-validated');
            return;
        }

        this.classList.remove('was-validated');
        formData.append('activity_id', $('#activity_id').val());
        formData.append('activity_status', 0);

        $.ajax({
            url: '/admin/deactivate-activities',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                $('#activityRemarks').modal('hide');
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
                    title: 'Status Updated Successfully'
                });

                refreshTableActivities();
                $('#addActivityRemarks')[0].reset();
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
                    title: 'Status Update Failed'
                });
            }
        });
    });

    $('#activityTable').on('click', '.activate-activity', function () {
        var activityId = $(this).data('id');
        $.ajax({
            url: '/admin/activate-activities',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                activity_id: activityId,
                activity_status: 1
            },
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
                    title: 'Activated Successfully'
                });

                refreshTableActivities();
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
                    title: 'Activate Failed'
                });
            }
        });
    });

    $('#activityTable').on('click', '.delete_activity', function () {
        var activityId = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/admin/delete-activities',
                    type: 'get',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        activity_id: activityId,
                    },
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

                        refreshTableActivities();
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
                            title: 'Activity Deletion Failed'
                        });
                    }
                });
            }
        });
    });




    $('#activityEdit').on('hidden.bs.modal', function () {
        $('#updateFormActivity')[0].reset();
    });
});
