$(document).ready(function () {

    window.showLoading = function () {
        $('#loadingOverlay').css('display', 'flex').hide().fadeIn(150);
    }

    window.hideLoading = function () {
        $('#loadingOverlay').fadeOut(150);
    }
    
    $('.addFitnessHubBtn').on('click', function () {
        $('#addFitnessHubModal').modal('show');
    });


    $('#fitnessHubImage').on('change', function () {
        const file = this.files[0];
        const previewContainer = $('#imagePreviewContainer');
        const previewImage = $('#imagePreviewFitnessHub');
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

    $('#fitnessHubsForm').submit(function (event) {
        event.preventDefault();

        const form = this;

        const startTime = $('#StartTime').val();
        const endTime = $('#EndTime').val();
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        if (startTime && endTime && endTime <= startTime) {
            alert('End time must be greater than start time.');
            $('#EndTime').addClass('is-invalid');
            return;
        } else {
            $('#EndTime').removeClass('is-invalid');
        }

        form.classList.remove('was-validated');

        const $btn = $('#saveFitnessHubBtn');
        const originalWidth = $btn.outerWidth();

        $btn
            .attr('disabled', true)
            .html(`<div class="spinner-border spinner-border-sm text-light"></div>`)
            .css('width', originalWidth + 'px');

        let formData = new FormData(form);

        $.ajax({
            url: $(form).attr('action'),
            type: $(form).attr('method'),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
            processData: false,
            contentType: false,

            success: function (response) {

                $('#addFitnessHubModal').modal('hide');

                $('#fitnessHubsForm')[0].reset();
                $('#imagePreviewFitnessHub').hide();

                const Toast = Swal.mixin({
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });

                Toast.fire({
                    icon: 'success',
                    title: 'Fitness Hub Added Successfully'
                });

                form.reset();
                $(form).removeClass('was-validated');

                refreshTableFitnessHubs();
            },

            error: function (xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    let firstError = Object.values(errors)[0][0];

                    Swal.fire({
                        icon: 'warning',
                        title: 'Validation Error',
                        text: firstError
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed to add fitness hub'
                    });
                }
            },

            complete: function () {
                $btn
                    .attr('disabled', false)
                    .html(`<span class="btn-text">Create</span>`)
                    .css('width', '');
            }
        });
    });
    function limitText(text, maxLength = 100) {
        if (!text) return "N/A";
        if (text.length <= maxLength) return text;
        return text.substring(0, maxLength) + "...";
    }

    function stripHtml(html) {
        var tmp = document.createElement("DIV");
        tmp.innerHTML = html;
        return tmp.textContent || tmp.innerText || "";
    }
    function refreshTableFitnessHubs() {
        $.ajax({
            url: '/admin/get-updated-fitness-hubs-table',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                var fitnessHubs = response.data;
                var tableBody = $('#fitnessHubsTable tbody');
                $('[data-bs-toggle="tooltip"]').tooltip('dispose');
                tableBody.empty();
                fitnessHubs.forEach(function (fitnessHub) {

                    var actionButtons = `<button type="button" class="btn btn-primary editInfo_id_fitnessHub btn-equal btn-sm" data-bs-toggle="tooltip" data-bs-placement="left" title="Edit" data-id="${fitnessHub.id}">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>`;

                    if (fitnessHub.status == 1) {
                        actionButtons += `
        <button type="button"
            class="btn btn-danger deactivate_fitnessHub btn-equal btn-sm"
            data-id="${fitnessHub.id}">
            <i class="fa-solid fa-ban"></i>
        </button>`;
                    } else {
                        actionButtons += `
        <button type="button"
            class="btn btn-success activate-fitnessHub btn-equal btn-sm"
            data-id="${fitnessHub.id}">
            <i class="fa-solid fa-check-circle"></i>
        </button>`;
                    }
                    actionButtons += ` <button type="button" class="btn btn-danger delete_activity btn-equal btn-responsive btn-sm"
                                        data-bs-toggle="tooltip" data-bs-placement="right" title="Delete"
                                        data-id="${fitnessHub.id}" style="margin-right:2px;">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>`;

                    var fitnessHub_name = fitnessHub.name ? fitnessHub.name.toUpperCase() : 'N/A';
                    var fitnessHub_description = limitText(stripHtml(fitnessHub.description), 100);
                    var fitnessHub_remarks = fitnessHub.remarks ? fitnessHub.remarks.toUpperCase() : 'N/A';
                    var fitnessHub_max_booking = fitnessHub.max_booking ? fitnessHub.max_booking.toUpperCase() : 'N/A';

                    var fitnessHub_image = fitnessHub.image
                        ? `<img src="/assets/images/fitness-hubs/${fitnessHub.image}" style="width:100px;">`
                        : 'N/A';

                    var fitnessHub_status = fitnessHub.status == 1
                        ? `<span class="badge bg-primary">Active</span>`
                        : `<span class="badge bg-danger">Inactive</span>`;

                    var row = $(`
                            <tr>
                                <td>${fitnessHub_name}</td>
                                <td>${fitnessHub_image}</td>
                                <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${fitnessHub_description}</td>
                                <td>${fitnessHub_remarks}</td>   
                                <td>${fitnessHub_status}</td>
                                <td>${fitnessHub_max_booking}</td>   
                                <td>${fitnessHub.start_time_formatted ?? 'N/A'}</td>
                                <td>${fitnessHub.end_time_formatted ?? 'N/A'}</td>
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

    $('#fitnessHubsTable').on('click', '.editInfo_id_fitnessHub', function () {
        var fitnessHubId = $(this).data('id');
        showLoading();



        $.ajax({
            url: '/admin/fetch/fitness-hub/' + fitnessHubId,
            type: 'GET',
            dataType: 'json',

            success: function (fitnessHub) {

                $('#fitnessHubId').val(fitnessHub.id);

                $('#editFitnessHubName').val(fitnessHub.fitness_hub_name);
                $('#editFitnessHubDescription').summernote('code', fitnessHub.fitness_hub_description);

                // radio button
                $('input[name="edit_fitness_hub_max_booking"][value="' + fitnessHub.fitness_hub_max_booking + '"]').prop('checked', true);

                $('#editStartTime').val(fitnessHub.fitness_hub_start_time);
                $('#editEndTime').val(fitnessHub.fitness_hub_end_time);

                // IMAGE PREVIEW
                if (fitnessHub.fitness_hub_image) {
                    $('#editImagePreviewFitnessHub')
                        .attr('src', '/assets/images/fitness-hubs/' + fitnessHub.fitness_hub_image)
                        .show();

                    $('#editImagePreviewContainer span').hide();
                } else {
                    $('#editImagePreviewFitnessHub').hide();
                    $('#editImagePreviewContainer span').show();
                }


                $('#editFitnessHubModal').modal('show');
            },

            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Failed to fetch fitness hub details'
                });
            },
            complete: function () {
                hideLoading();
            }

        });
    });


    $('#updateFitnessHubsForm').on('submit', function (e) {
        e.preventDefault();

        const $btn = $('#updateFitnessHubBtn');
        const originalWidth = $btn.outerWidth();

        $btn
            .attr('disabled', true)
            .html(`<div class="spinner-border spinner-border-sm text-light"></div>`)
            .css('width', originalWidth + 'px');



        var formData = new FormData(this);

        $.ajax({
            url: '/admin/update-fitness-hub',
            type: "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                $('#editFitnessHubModal').modal('hide');
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

                refreshTableFitnessHubs();

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
            },

            complete: function () {
                $btn
                    .attr('disabled', false)
                    .html(`<span class="btn-text">Update</span>`)
                    .css('width', '');
            }
        });
    });

    $('#fitnessHubsTable').on('click', '.deactivate_fitnessHub', function () {
        var id = $(this).data('id');

        $('#fitnessHub_id').val(id); // ✅ REQUIRED
        $('#fitnessHubRemarksInput').val('');
        $('#fitnessHubRemarks').modal('show');
    });

    $('#deactivateFitnessHubForm').on('submit', function (e) {
        e.preventDefault();

        const $btn = $('#deactivateFitnessHubBtn');
        const originalWidth = $btn.outerWidth();

        $btn
            .attr('disabled', true)
            .html(`<div class="spinner-border spinner-border-sm text-light"></div>`)
            .css('width', originalWidth + 'px');

        var formData = new FormData(this);
        if (!this.checkValidity()) {
            this.classList.add('was-validated');
            return;
        }

        $.ajax({
            url: '/admin/deactivate-fitness-hub',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                $('#fitnessHubRemarks').modal('hide');
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

                refreshTableFitnessHubs();
                $('#addFitnessHubRemarks')[0].reset();
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
            },

            complete: function () {
                $btn
                    .attr('disabled', false)
                    .html(`<span class="btn-text">Save</span>`)
                    .css('width', '');
            }
        });
    });

    $('#fitnessHubsTable').on('click', '.activate-fitnessHub', function () {
        var fitnessHubId = $(this).data('id');
        $.ajax({
            url: '/admin/activate-fitness-hub',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                fitness_hub_id: fitnessHubId,
                fitness_hub_status: 1
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

                refreshTableFitnessHubs();
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

    $('.NewDateBlockingFitnessHubBtn').on('click', function () {
        $('#BlockingDateFieldStartFitnessHub, #BlockingDateFieldEndFitnessHub').val('').prop('disabled', true);
        $('#NewDateBlockingFitnessHubModal').modal('show');
    });

    $('#fitnessHubSelectBlocking').on('change', function () {
        const selectedfitnessHubId = $(this).val();

        if (!selectedfitnessHubId) {
            console.warn("No amenity selected.");
            return;
        }
        $.ajax({
            url: '/admin/fetch-date-blocking-fitness-hub',
            method: 'GET',
            data: { fitness_hub_id: selectedfitnessHubId },
            success: function (blockedDates) {
                console.log("Blocked Dates:", blockedDates);

                if ($('#BlockingDateFieldStartFitnessHub')[0]._flatpickr) {
                    $('#BlockingDateFieldStartFitnessHub')[0]._flatpickr.destroy();
                }
                if ($('#BlockingDateFieldEndFitnessHub')[0]._flatpickr) {
                    $('#BlockingDateFieldEndFitnessHub')[0]._flatpickr.destroy();
                }

                $('#BlockingDateFieldStartFitnessHub, #BlockingDateFieldEndFitnessHub').prop('disabled', false);

                flatpickr("#BlockingDateFieldStartFitnessHub", {
                    enableTime: false,
                    dateFormat: "Y-m-d",
                    minDate: "today",
                    allowInput: false,
                    altInput: true,
                    altFormat: "F j, Y",
                    disable: blockedDates
                });

                flatpickr("#BlockingDateFieldEndFitnessHub", {
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


    $('#NewDateBlockingFitnessHub').submit(function (event) {
        event.preventDefault();
        var form = this;
        const startDateStr = $(form).find('[name="date_blocking_start"]').val();
        const endDateStr = $(form).find('[name="date_blocking_end"]').val();

        const startDate = new Date(startDateStr);
        const endDate = new Date(endDateStr);

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

        const $btn = $('#saveDateBlockingFitnessHubBtn');
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
                $('#NewDateBlockingFitnessHubModal').modal('hide');
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
                if ($('#BlockingDateFieldStartFitnessHub')[0]._flatpickr) {
                    $('#BlockingDateFieldStartFitnessHub')[0]._flatpickr.clear();
                }
                if ($('#BlockingDateFieldEndFitnessHub')[0]._flatpickr) {
                    $('#BlockingDateFieldEndFitnessHub')[0]._flatpickr.clear();
                }
                $(form).removeClass('was-validated');
                refreshDateBlockingFitnessHub();

            },
            error: function (xhr) {
                let message = 'Blocking Failed';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message
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

    $('#fitnessHubSelectBlocking').on('change', function () {
        $('#BlockingDateFieldStartFitnessHub').val('');
        $('#BlockingDateFieldEndFitnessHub').val('');
    });


    function refreshDateBlockingFitnessHub() {
        $.ajax({
            url: '/admin/get-updated-fitness-hub-blocking',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                var dateBlockings = response.data;
                var tableBody = $('#fitnessHubDateBlockingTable tbody');
                $('[data-bs-toggle="tooltip"]').tooltip('dispose');
                tableBody.empty();
                dateBlockings.forEach(function (dateBlocking) {
                    console.log(dateBlockings);
                    var actionButtons = `<button type="button" class="btn btn-danger delete_block_date_fitness-hub btn-responsive btn-equal btn-sm"
                                    data-bs-toggle="tooltip" data-bs-placement="right" title="Delete"
                                    data-id="${dateBlocking.id}">
                                    <i class="fa-solid fa-trash"></i>
                                    </button>`;
                    var fitness_hub_name = dateBlocking.fitness_hub_name
                        ? dateBlocking.fitness_hub_name.toUpperCase()
                        : 'N/A';
                    var remarks = dateBlocking.blocking_remarks ? dateBlocking.blocking_remarks.toUpperCase() : 'N/A';
                    var blocking_start = dateBlocking.date_blocking_start ? dateBlocking.date_blocking_start.toUpperCase() : 'N/A';
                    var blocking_end = dateBlocking.date_blocking_end ? dateBlocking.date_blocking_end.toUpperCase() : 'N/A';


                    var row = $(`
                                <tr>
                                    <td>${fitness_hub_name}</td>
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

    $(document).on('click', '.delete_block_date_fitness-hub', function () {
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
                    url: '/admin/delete-blocked-date-fitness-hub',
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

                        refreshDateBlockingFitnessHub();
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