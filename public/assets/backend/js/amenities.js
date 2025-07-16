
$(document).ready(function () {


    $('.AdminAddAmenity').on('click', function () {
        $('#adminCreateAmenities').modal('show');
    });

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

    $('#admin-new-amenities').submit(function (event) {
        console.log('Event bound');
        event.preventDefault();

        const startTime = $('#startTime').val();
        const endTime = $('#endTime').val();



        if (!this.checkValidity()) {
            this.classList.add('was-validated');
            return;
        }
        this.classList.remove('was-validated');


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
                $('#adminCreateAmenities').modal('hide');
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
            }
        });
    });


    function refreshTableAmenities() {
        $.ajax({
            url: '/get-updated-amenities-table',
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

