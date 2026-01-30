
$(document).ready(function () {
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
});