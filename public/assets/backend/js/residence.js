$(document).ready(function () {

    function initUserSelect($select) {
        $select.select2({
            placeholder: 'Search by email...',
            width: '100%',
            dropdownParent: $('#adminAddResidence'), // ✅ This is critical
            minimumInputLength: 1,
            ajax: {
                url: '/admin/admin-users-emails',
                dataType: 'json',
                delay: 250,
                processResults: function (data) {
                    return {
                        results: data.map(user => ({
                            id: user.id,
                            text: user.email
                        }))
                    };
                },
                cache: true
            }
        });
    }

    $('#adminAddResidence').on('shown.bs.modal', function () {
        initUserSelect($('.user-email-select').first());
    });


    let currentResidencePageUrl = '/admin/get-updated-residence-table';
    let currentResidenceSearchTerm = '';



    // Get current page number from the URL
    function getCurrentPageNumber() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('page') || 1;
    }
    function stripAndLimit(text, maxLength) {
        const div = document.createElement("div");
        div.innerHTML = text;
        const strippedText = div.textContent || div.innerText || "";
        const trimmed = strippedText.trim();
        return trimmed.length > maxLength ? trimmed.substring(0, maxLength) + "..." : trimmed;
    }

    function refreshTableResidence(url = currentResidencePageUrl) {
        const page = getCurrentPageNumber();
        $.ajax({
            url: `/admin/get-updated-residence-table?page=${page}`,
            type: 'GET',
            data: {
                searchResidenceRequest: currentResidenceSearchTerm,
            },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function (response) {
                const residences = response.data;
                const tableBody = $('#adminResidenceTable tbody');
                const paginationContainerResidence = $('.pagination-container-residence');

                $('[data-bs-toggle="tooltip"]').tooltip('dispose');
                tableBody.empty();

                residences.forEach(function (residence) {
                    const email = residence.user?.email ?? 'N/A';
                    const resident_type = (residence.resident_type ?? 'N/A').toUpperCase();
                    const section = (residence.section ?? 'N/A').toUpperCase();
                    const unit_no = (residence.unit_no ?? 'N/A').toUpperCase();
                    const status = (residence.status ?? 'N/A').toUpperCase();
                    const remarks = stripAndLimit(residence.remarks ?? 'N/A', 40);

                    let typeBadge = `<span class="badge badge-custom-secondary">${resident_type}</span>`;
                    if (resident_type === 'OWNER') {
                        typeBadge = `<span class="badge badge-custom-success">${resident_type}</span>`;
                    } else if (resident_type === 'TENANT') {
                        typeBadge = `<span class="badge badge-custom-danger">${resident_type}</span>`;
                    }

                    let statusBadge = `<span class="badge badge-custom-secondary">${status}</span>`;
                    if (status === 'PENDING') {
                        statusBadge = `<span class="badge badge-custom-warning">${status}</span>`;
                    } else if (status === 'ACTIVE') {
                        statusBadge = `<span class="badge badge-custom-success">${status}</span>`;
                    } else if (status === 'INACTIVE') {
                        statusBadge = `<span class="badge badge-custom-danger">${status}</span>`;
                    }

                    // ✅ Button logic (same as Blade)
                    let approveDisabled = status === 'ACTIVE' ? 'disabled' : '';
                    let denyDisabled = status === 'DENIED' ? 'disabled' : '';

                    let approveTitle = status === 'ACTIVE' ? 'Already approved' : 'Approve';
                    let denyTitle = status === 'DENIED' ? 'Already denied' : 'Deny';

                    const actionButtons = `
                    <button type="button" class="btn btn-sm btn-icon btn-secondary admin_edit_residence"
                        data-bs-toggle="tooltip" data-bs-placement="left" title="Edit"
                        data-id="${residence.id}">
                        <i class='bx bx-edit'></i>
                    </button>

                    <button type="button" class="btn btn-sm btn-icon btn-primary update-status"
                        data-bs-toggle="tooltip" data-bs-placement="right" 
                        title="${approveTitle}" data-status="ACTIVE" data-id="${residence.id}" ${approveDisabled}>
                        <i class='bx bxs-check-circle'></i>
                    </button>

                    <button type="button" class="btn btn-sm btn-icon btn-danger update-status"
                        data-bs-toggle="tooltip" data-bs-placement="right" 
                        title="${denyTitle}" data-status="DENIED" data-id="${residence.id}" ${denyDisabled}>
                        <i class='bx bx-x-circle'></i>
                    </button>
                `;

                                const row = `
                    <tr>
                        <td style="text-transform: none;">${email}</td>
                        <td>${typeBadge}</td>
                        <td>${section}</td>
                        <td>${unit_no}</td>
                        <td>${statusBadge}</td>
                        <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${remarks}</td>
                        <td>${actionButtons}</td>
                    </tr>`;
                    tableBody.append(row);
                });

                paginationContainerResidence.html(response.links);

                // Pagination click binding
                $('.pagination-container-residence').find('a').off('click').on('click', function (e) {
                    e.preventDefault();
                    let pageUrl = $(this).attr('href');

                    if (currentResidenceSearchTerm) {
                        pageUrl = updateQueryStringParameter(pageUrl, 'searchResidenceRequest', currentResidenceSearchTerm);
                    }

                    currentResidencePageUrl = pageUrl;
                    refreshTableResidence(pageUrl);
                });

                $('[data-bs-toggle="tooltip"]').tooltip();
            },
            error: function (xhr, status, error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error loading data',
                    html: `<pre>${xhr.status} - ${xhr.responseText}</pre>`
                });
            }
        });
    }

    function updateQueryStringParameter(uri, key, value) {
        let re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
        let separator = uri.indexOf('?') !== -1 ? "&" : "?";
        if (uri.match(re)) {
            return uri.replace(re, '$1' + key + "=" + value + '$2');
        } else {
            return uri + separator + key + "=" + value;
        }
    }

    // Search form
    $('#searchFormResidence').on('submit', function (e) {
        e.preventDefault();
        currentResidenceSearchTerm = $('#searchInputResidence').val();
        currentResidencePageUrl = '/admin/get-updated-residence-table'; // reset to first page on new search
        refreshTableResidence();
    });



    $('.AdminAddResidence').on('click', function () {
        $('#adminAddResidence').modal('show');
    });



    $('#adminAddResidenceRowBtn').on('click', function () {
        let newAdminResidenceRow = `
        <tr class="admin-residence-row">
            <td style="width: 30%;">
                <select name="user_id[]" class="form-select user-email-select" required>
                    <option value="" disabled selected>Select a user</option>
                </select>
                <div class="invalid-feedback">Required</div>
            </td>
            <td style="width: 20%;">
                <select name="resident_type[]" required class="form-select">
                    <option value="" disabled selected>Select</option>
                    <option value="Owner">Owner</option>
                    <option value="Tenant">Tenant</option>
                </select>
            </td>
            <td style="width: 20%;">
                <select name="section[]" required class="form-select">
                    <option value="" disabled selected>Select</option>
                    <option value="Almond">Almond</option>
                    <option value="Belize">Belize</option>
                    <option value="Callery">Callery</option>
                    <option value="Dolce">Dolce</option>
                    <option value="Aston">Aston</option>
                    <option value="Red Oak">Red Oak</option>
                    <option value="Meranti">Meranti</option>
                    <option value="Sequoia">Sequoia</option>
                </select>
            </td>
            <td style="width: 20%;">
                <input type="text" name="unit_no[]" class="form-control" required>
                <div class="invalid-feedback">Required</div>
            </td>
            <td style="width: 10%;">
                <button type="button" class="btn btn-danger btn-sm btn-forge remove-residence">
                    <i class="bx bx-trash"></i>
                </button>
            </td>
        </tr>     
    `;
        $('#adminResidenceTableBody').append(newAdminResidenceRow);

        initUserSelect($('.user-email-select').last());
    });

    $(document).on('click', '.remove-residence', function () {
        $(this).closest('tr').remove();
    });

    $('#admin-add-residence-form').on('submit', function (e) {
        e.preventDefault();

        if (!this.checkValidity()) {
            this.classList.add('was-validated');
            return;
        }
        this.classList.remove('was-validated');

        const $btn = $('#addAddResidenceBtn');
        const originalWidth = $btn.outerWidth();

        // Fix the button width, disable, show spinner, hide text
        $btn
            .attr('disabled', true)
            .css('width', originalWidth + 'px');
        $btn.find('.spinner-border').removeClass('d-none');
        $btn.find('.btn-text').hide();

        const formData = new FormData(this);
        const $form = $(this);

        $.ajax({
            url: $form.attr('action'),
            type: $form.attr('method'),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                $('#adminAddResidence').modal('hide');
                $form[0].reset();
                $form.removeClass('was-validated');
                $form.find('select').val(null).trigger('change');

                Swal.fire({
                    toast: true,
                    icon: 'success',
                    title: 'Residence Added Successfully',
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                    customClass: {
                        popup: 'colored-toast'
                    }
                });

                refreshTableResidence();
            },
            error: function () {
                Swal.fire({
                    toast: true,
                    icon: 'error',
                    title: 'Failed to add residence',
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                    customClass: {
                        popup: 'colored-toast-error'
                    }
                });
            },
            complete: function () {
                // Restore button: enable, hide spinner, show text, reset width
                $btn
                    .attr('disabled', false)
                    .css('width', '');
                $btn.find('.spinner-border').addClass('d-none');
                $btn.find('.btn-text').show().text('Create');
            }
        });
    });


    $(document).on('click', '.update-status', function () {
        const id = $(this).data('id');
        const status = $(this).data('status');

        if (status === 'DENIED') {
            Swal.fire({
                title: 'Reason for Denial',
                input: 'textarea',
                inputLabel: 'Remarks',
                inputPlaceholder: 'Enter reason for denial...',
                inputAttributes: {
                    'aria-label': 'Enter reason for denial'
                },
                showCancelButton: true,
                confirmButtonText: 'Submit',
                cancelButtonText: 'Cancel',
                preConfirm: (remarks) => {
                    if (!remarks) {
                        Swal.showValidationMessage('Remarks are required to deny.');
                    }
                    return remarks;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    sendStatusUpdate(id, status, result.value);
                }
            });
        } else {
            Swal.fire({
                title: `Are you sure?`,
                text: `You are about to approve this request.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, approve it',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    sendStatusUpdate(id, status, null);
                }
            });
        }
    });

    $('#adminResidenceTable').on('click', '.admin_edit_residence', function () {
        var info_id = $(this).data("id");

        $.get('/admin/admin-fetch-residence/' + info_id, function (data) {
            $('#adminUpdateResidence').modal('show');
            $('#update_residence_user_id').val(data.user_id);
            $('#update_residence_email').val(data.user_email);
            $('#update_residence_type').val(data.resident_type);
            $('#update_residence_section').val(data.residence_section);
            $('#update_residence_unit_no').val(data.residence_unit_no);
            $('#update_residence_status').val(data.residence_status);
            $('#update_residence_remarks').val(data.residence_remarks);
            $('#info_id').val(info_id);
        }).fail(function () {
            alert("Data not found");
        });
    });

    $('#admin-update-residence-form').submit(function (event) {
        event.preventDefault();

        if (!this.checkValidity()) {
            this.classList.add('was-validated');
            return;
        }
        this.classList.remove('was-validated');

        const $btnResidenceUpdate = $('#updateResidenceBtn');
        const originalWidth = $btnResidenceUpdate.outerWidth();
        $btnResidenceUpdate
            .attr('disabled', true)
            .html(`<div class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></div>`)
            .css('width', originalWidth + 'px');

        var formData = new FormData(this);
        var form = this;

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
                $('#adminUpdateResidence').modal('hide');
                form.reset();
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
                form.reset();
                $(form).removeClass('was-validated');
                refreshTableResidence();
            },
            error: function () {
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
                    title: 'Failed to update residence'
                });
            },
            complete: function () {
                $btnResidenceUpdate
                    .attr('disabled', false)
                    .html(`<span class="btn-text">Update</span>`)
                    .css('width', '');

                form.reset();
                $(form).removeClass('was-validated');
            }
        });
    });


    function sendStatusUpdate(id, status, remarks = null) {
        $.ajax({
            url: `/admin/admin-residence-request/${id}/status`,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                status: status,
                remarks: remarks
            },
            success: function (response) {
                Swal.fire({
                    toast: true,
                    icon: 'success',
                    title: response.message || 'Status updated successfully',
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                    customClass: {
                        popup: 'colored-toast'
                    }
                });

                refreshTableResidence();
            },
            error: function () {
                Swal.fire({
                    toast: true,
                    icon: 'error',
                    title: 'Failed to update status',
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                    customClass: {
                        popup: 'colored-toast-error'
                    }
                });
            }
        });
    }

    // function refreshTableResidence(page = 1) {
    //     $.ajax({
    //         url: '/admin/get-updated-residence-table?page=' + page,
    //         type: 'GET',
    //         dataType: 'json',
    //         success: function (response) {
    //             var residences = response.data;
    //             var tableBody = $('#adminResidenceTable tbody');
    //             $('[data-bs-toggle="tooltip"]').tooltip('dispose');
    //             tableBody.empty();
    //             residences.forEach(function (residence) {
    //                 var email = residence.user && residence.user.email ? residence.user.email : 'N/A';
    //                 var resident_type = (residence.resident_type ?? 'N/A').toUpperCase();
    //                 var section = (residence.section ?? 'N/A').toUpperCase();
    //                 var unit_no = (residence.unit_no ?? 'N/A').toUpperCase();
    //                 var status = (residence.status ?? 'N/A').toUpperCase();
    //                 var remarks = residence.remarks ?? 'N/A';
    //                 var typeBadge = '<span class="badge badge-custom-secondary">' + resident_type + '</span>';
    //                 if (resident_type === 'OWNER') {
    //                     typeBadge = '<span class="badge badge-custom-success">' + resident_type + '</span>';
    //                 } else if (resident_type === 'TENANT') {
    //                     typeBadge = '<span class="badge badge-custom-danger">' + resident_type + '</span>';
    //                 }

    //                 var statusBadge = '<span class="badge badge-custom-secondary">' + status + '</span>';
    //                 if (status === 'PENDING') {
    //                     statusBadge = '<span class="badge badge-custom-warning">' + status + '</span>';
    //                 } else if (status === 'ACTIVE') {
    //                     statusBadge = '<span class="badge badge-custom-success">' + status + '</span>';
    //                 } else if (status === 'INACTIVE') {
    //                     statusBadge = '<span class="badge badge-custom-danger">' + status + '</span>';
    //                 }

    //                 var actionButtons = `
    //                     <button type="button" class="btn btn-sm btn-icon btn-secondary admin_edit_residence"
    //                         data-bs-toggle="tooltip" data-bs-placement="left" title="Edit"
    //                         data-id="${residence.id}">
    //                         <i class='bx bx-edit'></i>
    //                     </button>

    //                     <button type="button" class="btn btn-sm btn-icon btn-primary update-status"
    //                         data-bs-toggle="tooltip" data-bs-placement="right" title="Approve"
    //                         data-status="ACTIVE" data-id="${residence.id}">
    //                         <i class='bx bxs-check-circle'></i>
    //                     </button>

    //                     <button type="button" class="btn btn-sm btn-icon btn-danger update-status"
    //                         data-bs-toggle="tooltip" data-bs-placement="right" title="Denied"
    //                         data-status="DENIED" data-id="${residence.id}">
    //                         <i class='bx bx-x-circle'></i>
    //                     </button>`;

    //                 var row = $(`
    //                     <tr>
    //                         <td style="text-transform: none;">${email}</td>
    //                         <td>${typeBadge}</td>
    //                         <td>${section}</td>
    //                         <td>${unit_no}</td>
    //                         <td>${statusBadge}</td>
    //                         <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${remarks}</td>
    //                         <td>${actionButtons}</td>
    //                     </tr>
    //                 `);

    //                 tableBody.append(row);
    //             });
    //             pagination.html(response.pagination);

    //             $('[data-bs-toggle="tooltip"]').tooltip();
    //         },
    //         error: function (xhr, status, error) {
    //             console.error('Status:', status);
    //             console.error('Error:', error);
    //             console.error('Response:', xhr.responseText);
    //             Swal.fire({
    //                 icon: 'error',
    //                 title: 'Error loading events',
    //                 html: `<pre>${xhr.status}</pre>`
    //             });
    //         }
    //     });
    // }

});