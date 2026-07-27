
$(document).ready(function () {
    window.showLoading = function () {
        $('#loadingOverlay').css('display', 'flex').hide().fadeIn(150);
    }

    window.hideLoading = function () {
        $('#loadingOverlay').fadeOut(150);
    }
    
    $('#DownloadHistory').on('hidden.bs.modal', function () {
        $('.modal-backdrop').remove();
    });

    flatpickr("#DownloadStartDate,#DownloadEndDate", {
        enableTime: false,
        dateFormat: "Y-m-d",
        time_24hr: false,
        allowInput: true,
        defaultHour: 8,
        defaultMinute: 0
    });


    $('#downloadHistoryBtn').on('click', function () {
        var fromDate = $('#DownloadStartDate').val();
        var toDate = $('#DownloadEndDate').val();

        var from = new Date(fromDate);
        var to = new Date(toDate);

        if (from > to) {
            alert('The "Date From" cannot be greater than the "Date To".');
            return;
        }

        const $btn = $('#downloadHistoryBtn');
        const originalWidth = $btn.outerWidth();
        $btn
            .attr('disabled', true)
            .html(`<div class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></div>`)
            .css('width', originalWidth + 'px');

        if (fromDate && toDate) {
            $.ajax({
                url: '/admin/download-history',
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
                    $('#DownloadHistory').modal('hide');


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
                },
                complete: function () {
                    $btn
                        .attr('disabled', false)
                        .html(`<span class="btn-text">Download</span>`)
                        .css('width', '');
                }
            });
        } else {
            alert('Please select both "Date From" and "Date To"');
        }
    });


    $('#historyTable').on('click', '.viewActivityBookingDetailsBtn', function () {
        var booking_id = $(this).data("id");
        showLoading();
        $.ajax({
            url: '/admin/fetch/activity-booking-report/' + booking_id,
            method: 'GET',
            success: function (response) {
                const booking = response.booking;
                const withinPenalty = $('#detail-transaction-no').data('within-penalty');

                const bookingDate = new Date(booking.booking_date);
                const today = new Date();
                today.setHours(0, 0, 0, 0); // Reset time for accurate comparison
                const optionsDate = { year: 'numeric', month: 'long', day: '2-digit' };
                const formattedDate = bookingDate.toLocaleDateString(undefined, optionsDate);

                // Format start/end times
                const formatTime = (timeStr) => {
                    if (!timeStr) return 'N/A';
                    const d = new Date(`1970-01-01T${timeStr}`);
                    let hours = d.getHours();
                    const minutes = d.getMinutes();
                    const ampm = hours >= 12 ? 'PM' : 'AM';
                    hours = hours % 12 || 12;
                    return `${hours}:${minutes.toString().padStart(2, '0')} ${ampm}`;
                };

                const startTime = booking.booking_start_time;
                const endTime = booking.booking_end_time;

                $('#detail-transaction-no').text(booking.transaction_no)
                    .data('booking-id', booking.id)
                    .data('within-penalty', withinPenalty);
                $('#detail-booking-type').text(booking.booking_type ?? 'N/A');
                $('#detail-name').text(booking.name ?? booking.created_by_name ?? 'N/A');
                $('#detail-unit').text(booking.unit ?? 'N/A');
                $('#detail-activity-name').text(booking.activity_name ?? 'N/A');
                $('#detail-start-time').text(`${startTime} - ${endTime}` ?? 'N/A');
                $('#detail-contact').text(booking.contact ?? 'N/A');
                $('#detail-booking-date').text(formattedDate ?? 'N/A');
                $('#detail-transaction-no').data('booking-id', booking.id);


                let residentBadgeClass = '';
                if (booking.resident_type === 'TENANT') residentBadgeClass = 'fw-semibold text-danger';
                else if (booking.resident_type === 'OWNER') residentBadgeClass = 'fw-semibold text-primary';
                else residentBadgeClass = 'text-secondary text-white';

                $('#detail-resident-type').html(
                    `<span class="fw-semibold ${residentBadgeClass}">${booking.resident_type ?? 'N/A'}</span>`
                );

                let statusText = '';
                let statusClass = '';
                let cancelledAtText = ''; // 👈 NEW

                const formatDateTime = (dateStr) => {
                    if (!dateStr) return '';
                    const d = new Date(dateStr);

                    const options = {
                        year: 'numeric',
                        month: 'short',
                        day: '2-digit',
                        hour: 'numeric',
                        minute: '2-digit'
                    };

                    return d.toLocaleString(undefined, options);
                };

                // PRIORITY: real booking status first
                switch (booking.booking_status) {
                    case 1:
                        if (bookingDate < today) {
                            statusText = 'Completed';
                            statusClass = 'text-primary';
                            $('#cancelAmenityBookingBtn').hide();
                        } else {
                            statusText = 'Confirmed';
                            statusClass = 'text-primary';
                            $('#cancelAmenityBookingBtn').show();
                        }
                        break;

                    case 2:
                        statusText = 'Cancelled';
                        statusClass = 'text-danger';
                        if (booking.cancelled_at) {
                            cancelledAtText = `<small class="text-muted"> at ${formatDateTime(booking.cancelled_at)}</small>`;
                        }
                        $('#cancelAmenityBookingBtn').hide();
                        break;

                    case 3:
                        statusText = 'Late cancel';
                        statusClass = 'text-warning';

                        if (booking.cancelled_at) {
                            cancelledAtText = `<small class="text-muted"> at ${formatDateTime(booking.cancelled_at)}</small>`;
                        }
                        $('#cancelAmenityBookingBtn').hide();
                        break;

                    case 4:
                        statusText = 'No Show';
                        statusClass = 'text-dark';
                        $('#cancelAmenityBookingBtn').hide();
                        break;

                    default:
                        // ONLY mark as completed if no specific status
                        if (bookingDate < today) {
                            statusText = 'Completed';
                            statusClass = 'text-primary';
                        } else {
                            statusText = 'N/A';
                            statusClass = 'text-secondary';
                        }
                        $('#cancelAmenityBookingBtn').hide();
                }

                // Reset
                $('#detail-penalty-display').removeClass('text-danger text-primary fw-semibold');

                // No penalty
                if (!booking.has_penalty || booking.penalty_amount == 0) {
                    $('#detail-penalty-display').text('-');

                } else {
                    const amount = parseFloat(booking.penalty_amount).toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });

                    if (booking.penalty_waived) {
                        // Waived
                        $('#detail-penalty-display')
                            .html(`₱${amount} <span class="text-primary">(Waived)</span>`)
                            .addClass('fw-semibold');

                    } else {
                        // Not waived
                        $('#detail-penalty-display')
                            .text(`₱${amount}`)
                            .addClass('text-danger fw-semibold');
                    }
                }
                $('#detail-booking-status').html(`
                    <span class="${statusClass}">${statusText}</span>
                    ${cancelledAtText}
                `);

                $('#bookingEdit').modal('show');
            },
            error: function () {
                alert('Booking not found.');
            },
            complete: function () {
                hideLoading();
            }
        });
    });
});