

$(document).ready(function () {


    let currentFunctionRoomDiscountPageUrl = '/admin/get-updated-function-room-discount-table';
    let currentSearchTerm = '';

    function refreshTableFunctionRoomDiscount(url = currentFunctionRoomDiscountPageUrl) {
        $.ajax({
            url: url,
            type: 'GET',
            data: { searchFunctionRoomDiscount: currentSearchTerm },
            dataType: 'json',
            success: function (response) {
                const discounts = response.data;
                const tableBody = $('#functionRoomDiscountTable tbody');
                const paginationContainer = $('.pagination-container-function-room-discounts');

                $('[data-bs-toggle="tooltip"]').tooltip('dispose');
                tableBody.empty();

                if (discounts.length === 0) {
                    tableBody.append(`
                    <tr>
                        <td colspan="6" class="text-center">No Discounts Found</td>
                    </tr>
                `);
                }

                discounts.forEach(function (discount) {
                    let discountBadge = ``;
                    if (discount.discount > 0) {
                        discountBadge = `<span class="badge bg-success">
                        ${parseFloat(discount.discount).toString().replace(/\.0+$/, '')}%
                    </span>`;
                    } else {
                        discountBadge = `<span class="badge bg-secondary">0%</span>`;
                    }

                    let actionButtons = `
                    <button type="button" class="btn btn-sm btn-icon btn-secondary edit_function_room_discounts"
                        data-bs-toggle="tooltip" title="Edit" data-id="${discount.id}">
                        <i class='bx bx-edit'></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-icon btn-danger delete_function_room_discounts"
                        data-bs-toggle="tooltip" title="Delete" data-id="${discount.id}">
                        <i class='bx bx-trash'></i>
                    </button>
                `;

                    tableBody.append(`
                    <tr>
                        <td>${discount.functionRoom ?? 'N/A'}</td>
                        <td>${discountBadge}</td>
                        <td>${discount.remarks ?? 'N/A'}</td>
                        <td>${discount.start_date ?? 'N/A'}</td>
                        <td>${discount.end_date ?? 'N/A'}</td>
                        <td>${actionButtons}</td>
                    </tr>
                `);
                });

                paginationContainer.html(response.links);

                // handle pagination clicks
                paginationContainer.find('a').off('click').on('click', function (e) {
                    e.preventDefault();
                    let pageUrl = $(this).attr('href');
                    if (currentSearchTerm) {
                        pageUrl = updateQueryStringParameter(pageUrl, 'searchFunctionRoomDiscount', currentSearchTerm);
                    }
                    currentFunctionRoomDiscountPageUrl = pageUrl;
                    refreshTableFunctionRoomDiscount(pageUrl);
                });

                $('[data-bs-toggle="tooltip"]').tooltip();
            },
            error: function (xhr, status, error) {
                console.error('Error refreshing the table:', error);
            }
        });
    }




    $('.selectpicker').selectpicker();

    $('.FunctionRoomDiscounts').on('click', function () {
        $('#adminCreateFunctionRoomDiscount').modal('show');
    });

    flatpickr("#start_date, #end_date", {
        dateFormat: "Y-m-d",
        minDate: new Date(),

    });

    $('#admin-new-function-room-discount').submit(function (event) {
        event.preventDefault();

        if (!this.checkValidity()) {
            this.classList.add('was-validated');
            return;
        }
        this.classList.remove('was-validated');

        const $btn = $('#saveFunctionRoomDiscountBtn');
        const originalWidth = $btn.outerWidth();
        $btn
            .attr('disabled', true)
            .html(`<div class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></div>`)
            .css('width', originalWidth + 'px');

        const formData = new FormData(this);
        const form = this;

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
                $('#adminCreateFunctionRoomDiscount').modal('hide');
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
                    title: 'Discount Created Successfully'
                });

                form.reset();
                $(form).removeClass('was-validated');
                refreshTableFunctionRoomDiscount();
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
                    title: 'Failed to create discount'
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


    $('#functionRoomDiscountTable').on('click', '.delete_function_room_discounts', function () {
        var functionRoomDiscountId = $(this).data('id');
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
                    url: '/admin/admin-delete_function_room_discounts',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        functionRoomDiscountId: functionRoomDiscountId,
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
                            title: 'Deleted Successfully'
                        });
                        refreshTableFunctionRoomDiscount();
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
                            title: 'Failed to delete'
                        });
                    },
                });
            }
        });
    });


    $('#functionRoomDiscountTable').on('click', '.edit_function_room_discounts', function () {
        var functionRoomDiscountId = $(this).data("id");

        $.get('/admin/admin-fetch-function-room-discount/' + functionRoomDiscountId, function (data) {
            $('#adminUpdateFuntionRoomDiscount').modal('show');
            $('#functionRoomDiscountId').val(functionRoomDiscountId);

            $('#edit_discount').val(data.discount);
            $('#edit_remarks').val(data.remarks);
            $('#edit_start_date').val(data.start_date);
            $('#edit_end_date').val(data.end_date);

            // Function Room name (readonly) + hidden id
            $('#edit_function_room_name').val(data.function_room_name);
            $('#edit_function_room_id').val(data.function_room_id);
        }).fail(function () {
            alert("Data not found");
        });
    });

    $('#adminUpdateFunctionRoomDiscount').submit(function (e) {
        e.preventDefault();

        const formData = $(this).serialize(); // gather form data

        const $btn = $('#updateFunctionRoomDiscountBtn');
        const originalWidth = $btn.outerWidth();
        $btn
            .attr('disabled', true)
            .html(`<div class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></div>`)
            .css('width', originalWidth + 'px');

        $.ajax({
            url: '/admin/admin-update-function-room-discount', // ✅ correct route
            type: 'POST',
            data: formData,
            success: function (response) {
                $('#adminUpdateFuntionRoomDiscount').modal('hide');

                const Toast = Swal.mixin({
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                    customClass: { popup: 'colored-toast' },
                    didOpen: (toast) => {
                        toast.onmouseenter = Swal.stopTimer;
                        toast.onmouseleave = Swal.resumeTimer;
                    }
                });

                Toast.fire({
                    icon: 'success',
                    title: 'Updated Successfully'
                });

                // refresh your discount table instead of refreshTableUser
                refreshTableFunctionRoomDiscount();
            },
            error: function () {
                const Toast = Swal.mixin({
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                    customClass: { popup: 'colored-toast-error' },
                    didOpen: (toast) => {
                        toast.onmouseenter = Swal.stopTimer;
                        toast.onmouseleave = Swal.resumeTimer;
                    }
                });

                Toast.fire({
                    icon: 'error',
                    title: 'Update Failed'
                });
            },
            complete: function () {
                $btn.attr('disabled', false).html('<span class="btn-text">Update</span>');
            }
        });
    });


});