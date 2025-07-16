$(document).ready(function () {
    $('.AdminAddWorkPermit').on('click', function () {
        $('#AdminCreateWorkPermit').modal('show');
    });

    $('.DownloadWalkinPermit').on('click', function () {
        $('#DownloadWalkinWorkPemit').modal('show');
    });

    flatpickr("#DownloadStartDate,#DownloadEndDate", {
        enableTime: false,
        dateFormat: "Y-m-d",
        time_24hr: false,
        allowInput: true,
        defaultHour: 8,
        defaultMinute: 0
    });



    const years = [2024, 2025, 2026, 2027, 2028, 2029, 2030];
    let allHolidays = [];

    // Fetch holidays for each year
    function fetchHolidays(year) {
        return $.get(`https://date.nager.at/api/v3/PublicHolidays/${year}/PH`)
            .then(function (data) {
                return data.map(item => item.date);
            });
    }

    // Fetch all holidays then init Flatpickr
    Promise.all(years.map(fetchHolidays)).then(function (results) {
        // Flatten the array of arrays into a single array
        allHolidays = [].concat(...results);

        // Init Flatpickr
        flatpickr("#work_permit_booking_date,#update_work_permit_booking_date", {
            dateFormat: "Y-m-d",
            disable: [
                function (date) {
                    return date.getDay() === 0;
                },
                ...allHolidays
            ],
            minDate: "today"
        });
    }).catch(function (error) {
        console.error("Error fetching holidays:", error);
    });

    $('#adminNewWorkPermit').submit(function (event) {
        console.log('Event bound');
        event.preventDefault();
        if (!this.checkValidity()) {
            this.classList.add('was-validated');
            return;
        }

        this.classList.remove('was-validated');

        $('#saveWorkPermitBtn').attr('disabled', true);
        $('#saveWorkPermitBtn .spinner-border').removeClass('d-none');
        $('#saveWorkPermitBtn .btn-text').text('Creating...');

        var formData = new FormData(this);
        var form = this;

        $.ajax({
            url: $(form).attr('action'),
            type: $(form).attr('method'), // ✅ fixed here
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                $('#AdminCreateWorkPermit').modal('hide');
                $('#adminNewWorkPermit')[0].reset();

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
                    },
                    target: 'body'
                });

                Toast.fire({
                    icon: 'success',
                    title: 'Service Added Successfully'
                });

                form.reset();
                $(form).removeClass('was-validated');
                refreshTableWalkinWorkPermit()
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
                    title: 'Failed to add service'
                });
            },
            complete: function () {
                $('#saveWorkPermitBtn').attr('disabled', false);
                $('#saveWorkPermitBtn .spinner-border').addClass('d-none');
                $('#saveWorkPermitBtn .btn-text').text('Create');
            }
        });
    });

    $('#adminUpdateWorkPermit').submit(function (event) {
        console.log('Event bound');
        event.preventDefault();
        if (!this.checkValidity()) {
            this.classList.add('was-validated');
            return;
        }

        this.classList.remove('was-validated');

        $('#updateWorkPermitBtn').attr('disabled', true);
        $('#updateWorkPermitBtn .spinner-border').removeClass('d-none');
        $('#updateWorkPermitBtn .btn-text').text('Updating...');

        var formData = new FormData(this);
        var form = this;

        $.ajax({
            url: $(form).attr('action'),
            type: $(form).attr('method'), // ✅ fixed here
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                $('#walkinWorkPermitUpdateModal').modal('hide');
                $('#adminUpdateWorkPermit')[0].reset();

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
                    },
                    target: 'body'
                });

                Toast.fire({
                    icon: 'success',
                    title: 'Permit Updated Successfully'
                });

                form.reset();
                $(form).removeClass('was-validated');
                refreshTableWalkinWorkPermit()
            },
            error: function (xhr, status, error) {
                const Toast = Swal.mixin({
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
                    title: 'Failed to update permit'
                });


            },
            complete: function () {
                // Re-enable button + hide spinner
                $('#updateWorkPermitBtn').attr('disabled', false);
                $('#updateWorkPermitBtn .spinner-border').addClass('d-none');
                $('#updateWorkPermitBtn .btn-text').text('Update');
            }
        });
    });

    $('#walkinWorkPermitsTable').on('click', '.edit_walkinWorkPermit', function () {
        var info_id = $(this).data("id");
        $.get('/admin/admin-fetch-walkin-work-permit/' + info_id, function (data) {
            $('#walkinWorkPermitUpdateModal').modal('show');
            $('#update_permit_type').val(data.permit_type);
            $('#update_section').val(data.section);
            $('#update_unit_no').val(data.unit_no);
            $('#update_no_days').val(data.no_days);
            $('#update_work_permit_booking_date').val(data.work_permit_booking_date);
            $('#update_under_renovation').val(data.under_renovation);
            $('#update_description').val(data.description);
            $('#update_contractor').val(data.contractor);
            $('#update_approved_by').val(data.approved_by);
            $('#info_id').val(info_id);
            if (data.under_renovation === "Yes") {
                $('#Yes').prop('checked', true);
            } else if (data.under_renovation === "No") {
                $('#No').prop('checked', true);
            }
        }).fail(function () {
            alert("Data not found");
        });
    });

    $('#walkinWorkPermitsTable').on('click', '.delete_walkinWorkPermit', function () {
        var walkinWorkPermitId = $(this).data('id');
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
                    url: '/admin/admin-delete-walkin-work-permit',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        walkinWorkPermit_id: walkinWorkPermitId,
                        _method: 'DELETE'
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
                            },
                            target: 'body'
                        });

                        Toast.fire({
                            icon: 'success',
                            title: 'Permit Deleted Successfully'
                        });
                        refreshTableWalkinWorkPermit()
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
                            title: 'Failed to delete Work Permit'
                        });
                    },
                });
            }
        });
    });

    $('#downloadWalkinPermitBtn').on('click', function () {
        var fromDate = $('#DownloadStartDate').val();
        var toDate = $('#DownloadEndDate').val();

        var from = new Date(fromDate);
        var to = new Date(toDate);

        if (from > to) {
            Swal.fire({
                icon: 'warning',
                title: 'Invalid Date Range',
                text: '"Date From" cannot be later than "Date To".',
                confirmButtonColor: '#3085d6',
            });
            return;
        }

        if (fromDate && toDate) {
            $.ajax({
                url: '/admin/admin-download-walkin-work-permit',
                method: 'POST',
                data: {
                    from_date: fromDate,
                    to_date: toDate,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function (response, status, xhr) {
                    var disposition = xhr.getResponseHeader('Content-Disposition');
                    var filename = "2s_Booking_History_" + fromDate + "_to_" + toDate + ".csv";
                    if (disposition && disposition.indexOf('attachment') !== -1) {
                        var matches = /filename="([^"]*)"/.exec(disposition);
                        if (matches != null && matches[1]) {
                            filename = matches[1];
                        }
                    }
                    var blob = new Blob([response], { type: 'text/csv' });
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    $('#DownloadWalkinWorkPemit').modal('hide');

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
                        title: 'Download successful!'
                    });

                    $('#DownloadStartDate').val('');
                    $('#DownloadEndDate').val('');
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
                        title: 'An unexpected error occurred while exporting.'
                    });
                }
            });
        } else {
            Swal.fire({
                icon: 'info',
                title: 'Missing Dates',
                text: 'Please select both "Date From" and "Date To".',
                confirmButtonColor: '#3085d6',
            });
        }
    });


    function refreshTableWalkinWorkPermit() {
        $.ajax({
            url: '/admin/get-updated-walkin-work-permit-table',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                var walkins = response.data;
                var tableBody = $('#walkinWorkPermitsTable tbody');
                var paginationContainer = $('.pagination-container');

                $('[data-bs-toggle="tooltip"]').tooltip('dispose');
                tableBody.empty();

                walkins.forEach(function (walkin) {
                    var actionButtons = `
                    <button type="button" class="btn btn-sm btn-icon btn-primary edit_walkinWorkPermit"
                        data-bs-toggle="tooltip" data-bs-placement="left" title="Edit" data-id="${walkin.id}">
                        <i class='bx bx-edit'></i>
                    </button>

                    <button type="button" class="btn btn-sm btn-icon btn-danger delete_walkinWorkPermit"
                        data-bs-toggle="tooltip" data-bs-placement="right" title="Delete" data-id="${walkin.id}">
                        <i class='bx bx-trash'></i>
                    </button>`;

                    var permit_type = walkin.permit_type ? walkin.permit_type.toUpperCase() : 'N/A';
                    var unit_no = walkin.unit_no ? walkin.unit_no.toUpperCase() : 'N/A';
                    var section = walkin.section ? walkin.section.toUpperCase() : 'N/A';
                    var no_days = walkin.no_days ? walkin.no_days.toString().toUpperCase() : 'N/A';
                    var work_permit_booking_date = walkin.work_permit_booking_date ? walkin.work_permit_booking_date.toUpperCase() : 'N/A';
                    var under_renovation = walkin.under_renovation ? walkin.under_renovation.toUpperCase() : 'N/A';
                    var description = walkin.description ? walkin.description.toUpperCase() : 'N/A';
                    var contractor = walkin.contractor ? walkin.contractor.toUpperCase() : 'N/A';
                    var approved_by = walkin.approved_by ? walkin.approved_by.toUpperCase() : 'N/A';

                    var row = $(`<tr>
                    <td>${permit_type}</td>     
                    <td>${unit_no}</td>
                    <td>${section}</td>   
                    <td>${no_days}</td> 
                    <td>${work_permit_booking_date}</td> 
                    <td>${under_renovation}</td> 
                    <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${description}</td>
                    <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${contractor}</td> 
                    <td>${approved_by}</td>         
                    <td>${actionButtons}</td>                                                          
                </tr>`);

                    tableBody.append(row);
                });

                // 🔥 Append pagination
                paginationContainer.html(response.links);

                $('[data-bs-toggle="tooltip"]').tooltip();
            },
            error: function (xhr, status, error) {
                console.error('Error refreshing the table:', error);
            }
        });
    }


    $(document).on('click', '.pagination-container-walkin a', function (e) {
        e.preventDefault();
        let url = $(this).attr('href');

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                var tableBody = $('#walkinWorkPermitsTable tbody');
                var paginationContainer = $('.pagination-container');
                var walkins = response.data;

                tableBody.empty();

                walkins.forEach(function (walkin) {
                    var actionButtons = `
                    <button type="button" class="btn btn-sm btn-icon btn-primary edit_walkinWorkPermit"
                        data-bs-toggle="tooltip" data-bs-placement="left" title="Edit" data-id="${walkin.id}">
                        <i class='bx bx-edit'></i>
                    </button>

                    <button type="button" class="btn btn-sm btn-icon btn-danger delete_walkinWorkPermit"
                        data-bs-toggle="tooltip" data-bs-placement="right" title="Delete" data-id="${walkin.id}">
                        <i class='bx bx-trash'></i>
                    </button>`;

                    var permit_type = walkin.permit_type ? walkin.permit_type.toUpperCase() : 'N/A';
                    var unit_no = walkin.unit_no ? walkin.unit_no.toUpperCase() : 'N/A';
                    var section = walkin.section ? walkin.section.toUpperCase() : 'N/A';
                    var no_days = walkin.no_days ? walkin.no_days.toString().toUpperCase() : 'N/A';
                    var work_permit_booking_date = walkin.work_permit_booking_date ? walkin.work_permit_booking_date.toUpperCase() : 'N/A';
                    var under_renovation = walkin.under_renovation ? walkin.under_renovation.toUpperCase() : 'N/A';
                    var description = walkin.description ? walkin.description.toUpperCase() : 'N/A';
                    var contractor = walkin.contractor ? walkin.contractor.toUpperCase() : 'N/A';
                    var approved_by = walkin.approved_by ? walkin.approved_by.toUpperCase() : 'N/A';

                    var row = $(`<tr>
                    <td>${permit_type}</td>     
                    <td>${unit_no}</td>
                    <td>${section}</td>   
                    <td>${no_days}</td> 
                    <td>${work_permit_booking_date}</td> 
                    <td>${under_renovation}</td> 
                    <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${description}</td>
                    <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${contractor}</td> 
                    <td>${approved_by}</td>         
                    <td>${actionButtons}</td>                                                          
                </tr>`);

                    tableBody.append(row);
                });

                paginationContainer.html(response.links);
                $('[data-bs-toggle="tooltip"]').tooltip();
            },
            error: function (xhr, status, error) {
                console.error('Pagination fetch error:', error);
            }
        });
    });
});