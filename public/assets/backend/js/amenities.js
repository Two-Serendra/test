
$(document).ready(function () {

    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    $('.addAmenity').on('click', function () {
        $('#amenityAdd').modal('show');
    });
    $('#amenityAdd').on('hidden.bs.modal', function () {
        $('.modal-backdrop').remove();
    });

    $('#addAmenity').tooltip();

    $('#amenityImage').change(function () {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#imagePreview').attr('src', e.target.result);
                $('#imagePreview').show();
            };
            reader.readAsDataURL(file);
        } else {
            $('#imagePreview').hide();
        }
    });

    $('#edit_amenity_image').change(function () {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#edit_imagePreview').attr('src', e.target.result);
                $('#edit_imagePreview').show();
            };
            reader.readAsDataURL(file);
        } else {
            $('#edit_imagePreview').hide();
        }
    });


    $('#amenitiesForm').submit(function (event) {
        event.preventDefault();

        if (!this.checkValidity()) {
            this.classList.add('was-validated');
            return;
        }
        this.classList.remove('was-validated');


        const $btn = $('#saveAmenityBtn');
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
                $('#amenityAdd').modal('hide');
                $('#imagePreview').attr('src', '#').hide();

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
                    title: 'Amenity Added Successfully'
                });

                form.reset();
                $(form).removeClass('was-validated');
                refreshTableAmenities();
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
                    title: 'Failed to add amenity'
                });
            },
            complete: function () {
                // Restore button to original state
                $btn
                    .attr('disabled', false)
                    .html(`<span class="btn-text">Create</span>`)
                    .css('width', '');
            }
        });
    });


    $('#amenityAdd').on('hidden.bs.modal', function () {
        $('.modal-backdrop').remove();
    });

    $('#amenityTable').on('click', '.editInfo_id_amenity', function () {
        var info_id = $(this).data("id");
        $.get('/admin/fetch/amenity/' + info_id, function (data) {
            $('#amenityEdit').modal('show');
            $('#edit_amenity_name').val(data.amenity_name);
            $('#edit_amenity_description').val(data.amenity_description);
            $('#currentImageFileName').val(data.amenity_image);
            $('#info_id').val(info_id);
            const imagePath = '/assets/images/amenities/' + data.amenity_image;
            $('#edit_imagePreview').attr('src', imagePath).show();
        })
            .fail(function () {
                alert("Data not found");
            });
    });


    $('#updateFormAmenity').on('submit', function (e) {
        e.preventDefault();
        var formData = new FormData(this);

        $.ajax({
            url: '/admin/update-amenities',
            type: "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                $('#amenityEdit').modal('hide');
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

                refreshTableAmenities();
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

    $('#amenityTable').on('click', '.add_remarks_amenity', function () {
        var info_id = $(this).data("id");
        $('#info_id').val(info_id);
        $.get('/admin/fetch/amenity_add_remarks/' + info_id, function (data) {
            $('#amenityRemarks').modal('show');
        })
            .fail(function () {
                alert("Data not found");
            });
    });


    $('#addAmenityRemarks').on('submit', function (e) {
        e.preventDefault();
        var formData = new FormData(this);
        formData.append('amenity_id', $('#info_id').val());
        formData.append('status_id', 0);

        $.ajax({
            url: '/admin/add-remarks-amenities',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                $('#amenityRemarks').modal('hide');
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

                refreshTableAmenities();
                $('#addAmenityRemarks')[0].reset();
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

    $('#amenityTable').on('click', '.show-amenities', function () {
        var amenityId = $(this).data('id');
        // console.log("amenityId:", amenityId);
        $.ajax({
            url: '/admin/show-amenities',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                amenity_id: amenityId,
                status_id: 1
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
                    title: 'Show Amenity Successfully'
                });

                refreshTableAmenities();
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
                    title: 'Show Amenity Failed'
                });
            }
        });
    });


    function refreshTableAmenities() {
        $.ajax({
            url: '/admin/get-updated-amenities-table',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                var amenities = response.data;
                var tableBody = $('#amenityTable tbody');
                $('[data-bs-toggle="tooltip"]').tooltip('dispose');
                tableBody.empty();
                amenities.forEach(function (amenity) {
                    var actionButtons = `<button type="button" class="btn btn-primary editInfo_id_amenity btn-equal btn-sm" data-bs-toggle="tooltip" data-bs-placement="left" title="Edit" data-id="${amenity.id}">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>`;

                    if (amenity.amenity_status == 1) {
                        actionButtons += `<button type="button" class="btn btn-danger add_remarks_amenity mx-1 btn-equal btn-responsive btn-sm" data-bs-toggle="tooltip" data-bs-placement="right" title="Deactivate" data-id="${amenity.id}">
                                        <i class="fa-solid fa-ban"></i>
                                        </button>`;
                    } else {
                        actionButtons += `<button type="button" class="btn btn-success show-amenities mx-1 btn-equal btn-responsive btn-sm" data-bs-toggle="tooltip" data-bs-placement="right" title="Activate" data-id="${amenity.id}">
                                        <i class="fa-solid fa-check-circle"></i>
                                        </button>`;
                    }
                    var amenity_name = amenity.amenity_name ? amenity.amenity_name.toUpperCase() : 'N/A';
                    var amenity_description = amenity.amenity_description ? amenity.amenity_description.toUpperCase() : 'N/A';
                    var amenity_remarks = amenity.amenity_remarks ? amenity.amenity_remarks.toUpperCase() : 'N/A';

                    var amenity_image = amenity.amenity_image
                        ? `<img src="/assets/images/amenities/${amenity.amenity_image}" alt="Amenity Image" style="width: 100px; height: auto;">`
                        : 'N/A';
                    var amenity_status = amenity.amenity_status == 1
                        ? `<span class="badge bg-success custom-badge">Active</span>`
                        : `<span class="badge bg-danger custom-badge">Inactive</span>`;
                    var row = $(`
                                <tr>
                                    <td>${amenity_name}</td>
                                    <td>${amenity_image}</td>
                                    <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${amenity_description}</td>
                                    <td>${amenity_remarks}</td>
                                    <td>${amenity_status}</td>
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

