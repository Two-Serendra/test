
$(document).ready(function () {

    $('.AddBlockingSchedule').on('click', function () {
        $('#addActivityBlockingModal').modal('show');
    });

    $('#selectAllDays').on('change', function () {
        $('input[name="days[]"]').prop('checked', this.checked);
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

    function refreshTableActivityScheduleBlocking() {

        $.ajax({
            url: '/admin/get-updated-activity-schedule-blocking-table',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                console.log(response);
                var dateBlockings = response.data;
                var tableBody = $('#activityScheduleBlockingTable tbody');

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

                    var activity_name = dateBlocking.activity
                        ? dateBlocking.activity.activity_name.toUpperCase()
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
                        <td>${activity_name}</td>
                        <td>${day}</td>
                        <td>${start_time}</td>
                        <td>${end_time}</td>
                        <td>${remarks}</td>
                        <td>${repeat_weekly}</td>
                        <td>${created_at}</td>
                        <td>${updated_at}</td>
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

});


