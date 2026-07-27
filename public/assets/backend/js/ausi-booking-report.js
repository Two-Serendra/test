$(document).ready(function () {


     window.showLoading = function () {
        $('#loadingOverlay').css('display', 'flex').hide().fadeIn(150);
    }

    window.hideLoading = function () {
        $('#loadingOverlay').fadeOut(150);
    }
    $('#ausiBookingReportTable').on('click', '.report_ausi_booking', function () {
        let report_info_id = $(this).data("id");
        showLoading();

        $.get('/admin/admin-fetch-ausi-booking/' + report_info_id, function (data) {

            let bookingStatusBadge = `
    <span class="badge bg-${data.status_badge} text-white">
        ${data.display_status.toUpperCase()}
    </span>
`;


            $('#display_report_name').text(data.name);
            $('#display_report_unit').text(data.unit_no);

            // Resident badge
            let residentType = data.resident_type?.toUpperCase() ?? 'N/A';
            let residentBadge = `<span class="badge bg-secondary">${residentType}</span>`;

            if (residentType === 'TENANT') residentBadge = `<span class="badge bg-danger text-white">TENANT</span>`;
            if (residentType === 'OWNER') residentBadge = `<span class="badge bg-primary text-white">OWNER</span>`;

            $('#display_report_resident_type').html(residentBadge);

            $('#display_report_booking_date').text(data.booking_date);
            let chargedType = data.charged_type;
            let chargedBadge = `<span class="badge bg-secondary">N/A</span>`;

            $('#display_report_time_slot').text(data.booking_time_slot);
            $('#display_report_transaction_no').text(data.transaction_no);

            // Editable fields
            $('#srf_report_no').val(data.srf_no);
            $('#remarks_report_ausi').text(data.remarks ?? 'No remarks provided.');
            $('#report_info_id').val(report_info_id);
            let inspectionHtml = '';

            if (
                !data.inspection_results ||
                data.inspection_results.length === 0
            ) {

                inspectionHtml = `
            <div class="alert alert-warning mb-0">
                <i class="fa-solid fa-circle-exclamation me-2"></i>
                Unit has not been inspected yet.
            </div>
        `;

            } else {

                inspectionHtml += `
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Inspection Item</th>
                        <th>Result</th>
                    </tr>
                </thead>
                <tbody>
             `;

                data.inspection_results.forEach(function (result) {
                    let badgeClass =
                        result.status == 1
                            ? 'bg-primary'
                            : 'bg-danger';

                    let label =
                        result.status == 1
                            ? result.inspection_item.option_1
                            : result.inspection_item.option_2;

                    inspectionHtml += `
            <tr>
                <td>
                    ${result.inspection_item.item_name}
                </td>
                <td>
                 
                        ${label}
                  
                </td>
            </tr>
        `;
                });

                inspectionHtml += `
                </tbody>
            </table>
        </div>
    `;
            }

            $('#inspectionResultsContainerReport').html(inspectionHtml);
            $('#display_report_booking_status').html(bookingStatusBadge);
            hideLoading();
            $('#ausiEditReport').modal('show');

        })
            .fail(function () {
                alert("Data not found");
            });
    });

    $('#download-ausi-booking-reports').submit(function (e) {
        e.preventDefault();

        const $btn = $('#DownloadAusiBookingReportsBtn');
        const originalWidth = $btn.outerWidth();

        $btn.attr('disabled', true)
            .html('<div class="spinner-border spinner-border-sm text-light"></div>')
            .css('width', originalWidth + 'px');

        const formData = $(this).serialize();

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            xhrFields: {
                responseType: 'blob' // Important to handle file download
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response, status, xhr) {
                const filename = xhr.getResponseHeader('Content-Disposition')
                    .split('filename=')[1]
                    .replace(/"/g, '');

                const blob = new Blob([response], { type: 'text/csv' });
                const link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                $('#DownloadAusiBookingReports').modal('hide');
                $('#download-ausi-booking-reports')[0].reset();

            },
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Download Failed',
                    text: xhr.responseJSON?.message || 'Something went wrong. Please try again.',
                    confirmButtonColor: '#d33'
                });
            },
            complete: function () {
                $btn.attr('disabled', false).html('Download').css('width', '');
            }
        });
    });

    $('#updateAusiReportFormAdmin').submit(function (event) {
        event.preventDefault();

        if (!this.checkValidity()) {
            this.classList.add('was-validated');
            return;
        }
        this.classList.remove('was-validated');

        const $btn = $('#UpdateAusiBookingReportBtn');
        const originalWidth = $btn.outerWidth();
        $btn
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
                $('#ausiEditReport').modal('hide');
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
                    title: 'AUSI Booking Updated Successfully'
                });

                form.reset();
                $(form).removeClass('was-validated');
                refreshAusiReportTableBookings()
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                console.log(xhr.responseJSON);

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Something went wrong'
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


    function refreshAusiReportTableBookings() {
        $.ajax({
            url: '/admin/admin-get-updated-ausi-report-table',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                var bookings = response.data;
                var tableBody = $('#ausiBookingReportTable tbody');

                $('[data-bs-toggle="tooltip"]').tooltip('dispose');
                tableBody.empty();

                bookings.forEach(function (booking) {

                    var disableActions = [0, 2].includes(Number(booking.booking_status));

                    var actionButtons = `
    <div class="d-flex gap-1 justify-content-center">

        <button type="button"
            class="btn btn-primary report_ausi_booking btn-sm btn-equal"
            data-bs-toggle="tooltip"
            data-bs-placement="left"
            title="View"
            data-id="${booking.id}">
            <i class="fa-solid fa-eye"></i>
        </button>

        <button type="button"
            class="btn ${disableActions ? 'btn-secondary' : 'btn-success'} inspection_ausi_booking btn-sm"
            data-bs-toggle="tooltip"
            data-bs-placement="top"
            title="${disableActions ? booking.display_status : 'Inspect'}"
            data-id="${booking.id}"
            ${disableActions ? 'disabled' : ''}>
            <i class="fa-solid fa-clipboard-check"></i>
        </button>

        <button type="button"
            class="btn btn-sm btn-equal ${disableActions ? 'btn-secondary cancel-booking' : 'btn-danger admin-ausi-booking-cancel'}"
            data-bs-toggle="tooltip"
            data-bs-placement="right"
            title="${disableActions ? booking.display_status : 'Cancel'}"
            data-id="${booking.id}"
            ${disableActions ? 'disabled' : ''}>
            <i class="fa-solid fa-ban"></i>
        </button>

    </div>
`;

                    // Resident type
                    var resType = booking.resident_type
                        ? booking.resident_type.toLowerCase()
                        : '';

                    var residentTypeHtml = '';

                    if (resType === 'tenant') {
                        residentTypeHtml = `
            <span class="badge bg-danger text-uppercase">
                ${booking.resident_type}
            </span>
        `;
                    } else if (resType === 'owner') {
                        residentTypeHtml = `
            <span class="badge bg-primary text-uppercase">
                ${booking.resident_type}
            </span>
        `;
                    } else {
                        residentTypeHtml = `
            <span class="badge bg-secondary">N/A</span>
        `;
                    }

                    // Charged type
                    var chargedType = booking.charged_type == 1
                        ? `<span class="badge bg-primary">FREE</span>`
                        : `<span class="badge bg-danger">BILLABLE</span>`;

                    // Booking status
                    var bookingStatus = `
        <span class="badge bg-${booking.status_badge || 'secondary'} custom-badge">
            ${(booking.display_status || 'Unknown').toUpperCase()}
        </span>
    `;

                    // var emergency = booking.emergency == 1
                    //     ? `<span class="badge bg-danger">Yes</span>`
                    //     : `<span class="badge bg-secondary">No</span>`;

                    var createdBy = booking.createdBy?.name
                        ? booking.createdBy.name.toUpperCase()
                        : 'N/A';

                    var cancelledBy = booking.cancelledBy?.name
                        ? booking.cancelledBy.name.toUpperCase()
                        : 'N/A';

                    var cancelledAt = booking.cancelled_at ?? 'N/A';

                    var completedBy = booking.completedBy?.name
                        ? booking.completedBy.name.toUpperCase()
                        : 'N/A';

                    var completedAt = booking.cancelled_at ?? 'N/A';

                    var row = `
        <tr>
            <td>${booking.transaction_no ?? 'N/A'}</td>
            <td>${booking.name ?? 'N/A'}</td>
            <td>${residentTypeHtml}</td>
            <td>${booking.unit_no ?? 'N/A'}</td>
            <td>${booking.booking_date ?? 'N/A'}</td>
            <td>${booking.booking_time_slot ?? 'N/A'}</td>
            <td
                style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                data-bs-toggle="tooltip"
                title="${booking.remarks}">
                ${booking.remarks ?? 'N/A'}
            </td>

            <td>${bookingStatus}</td>

            <td>${createdBy}</td>
            <td>${booking.created_at ?? 'N/A'}</td>

            <td>${cancelledBy}</td>
            <td>${cancelledAt}</td>
                
             <td>${completedBy}</td>
            <td>${completedAt}</td>
            <td class="sticky-col sticky-col-color">
                ${actionButtons}
            </td>
        </tr>
    `;

                    tableBody.append(row);
                });

                $('[data-bs-toggle="tooltip"]').tooltip();
            },
            error: function (xhr, status, error) {
                console.error('Error refreshing pest control table:', error);
            }
        });
    }


    $(document).on('click', '.report_inspection_ausi_booking', function () {
        let bookingId = $(this).data('id');
        showLoading();
        $('#report_inspection_booking_id').val(bookingId);
        $('#report_inspectionChecklist').empty();
        $('#report_inspection_name').text('');
        $('#report_inspection_unit').text('');
        $.ajax({
            url: '/admin/admin/fetch-ausi-inspection/' + bookingId,
            type: 'GET',
            success: function (data) {
                $('#report_inspection_unit').text(data.unit_no);

                let html = '';

                data.inspection_items.forEach(function (item) {

                    html += `
    <div class="card mb-3 inspection-row">
        <div class="card-body">
            <h6 class="inspection-title">
               
                ${item.item_name}
            </h6>
            <div class="inspection-options">

                <label class="me-4">
                    <input 
                        type="radio"
                        class="inspection-radio"
                        name="inspection_${item.id}"
                        value="1"
                        required>

                    ${item.option_1}

                </label>

                <label>

                    <input 
                        type="radio"
                        class="inspection-radio"
                        name="inspection_${item.id}"
                        value="0"
                        required>

                    ${item.option_2}

                </label>
            </div>

        </div>

    </div>
    `;

                });

                $('#report_inspectionChecklist').html(html);

                $('#ReportUnitAusiInspectionModal').modal('show');
                hideLoading();

            },
            error: function () {

                Swal.fire(
                    'Error',
                    'Unable to load inspection',
                    'error'
                );
            }

        });

    });

    $('#saveInspectionReportBtn').click(function () {

        let bookingId = $('#report_inspection_booking_id').val();
        let valid = true;
        let inspections = [];
        let remarks = $('#report_inspection_remarks').val();
        $('.inspection-row').removeClass('invalid');
        $('.inspection-row').each(function () {

            let row = $(this);

            let checked = row.find('input[type="radio"]:checked');


            if (checked.length === 0) {

                row.addClass('invalid');

                valid = false;

            } else {

                let name = checked.attr('name');

                let itemId = name.replace('inspection_', '');


                inspections.push({

                    inspection_item_id: itemId,

                    status: checked.val()

                });
            }

        });

        if (!valid) {

            Swal.fire(
                'Incomplete Inspection',
                'Please answer all checklist items.',
                'warning'
            );

            return;

        }
        const $btn = $('#saveInspectionReportBtn');
        const originalWidth = $btn.outerWidth();
        $btn
            .attr('disabled', true)
            .html(`<div class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></div>`)
            .css('width', originalWidth + 'px');
        $.ajax({

            url: '/admin/admin-save-ausi-inspection-report',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },

            data: {
                ausi_booking_id: bookingId,
                remarks: remarks,
                inspections: inspections
            },

            success: function (response) {
                $('#ReportUnitAusiInspectionModal').modal('hide');
                Swal.fire(
                    'Completed',
                    'Inspection completed successfully.',
                    'success'
                );
                refreshAusiTableBookings();
            },

            error: function (xhr) {

                console.log(xhr.responseText);


                Swal.fire(
                    'Error',
                    'Failed to save inspection.',
                    'error'
                );

            },

            complete: function () {

                $btn
                    .attr('disabled', false)
                    .html(`<span class="btn-text">Complete Inspection</span>`)
                    .css('width', '');
            }
        });
    });


});