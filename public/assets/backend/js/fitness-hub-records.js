$(document).ready(function () {
    $('#historyFitnessHubTable').on('click', '.viewFitnessHubRecordDetailsBtn', function () {
        var booking_id = $(this).data("id");
        showLoading();
        $.ajax({
            url: '/admin/fetch/fitness-hub-booking/' + booking_id,
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
                $('#detail-activity-name').text(booking.fitness_hub_name ?? 'N/A');
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

                $('#viewFitnessHubRecordModal').modal('show');
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

