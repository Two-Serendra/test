$(document).ready(function () {

    $('.AdminAddUser').on('click', function () {
        $('#AdminCreateUser').modal('show');

    });

    $('#editAdminUserModal').on('hidden.bs.modal', function () {
        $('.modal-backdrop').remove();
    });

    $('#admin-new-user').submit(function (event) {
        event.preventDefault();
        var formData = new FormData(this);
        var form = this;
        $('#saveUserBtn').attr('disabled', true);
        $('#saveUserBtn .spinner-border').removeClass('d-none');
        $('#saveUserBtn .btn-text').text('Creating...');

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
                $('#AdminCreateUser').modal('hide');
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
                    title: 'User Created Successfully'
                });

                form.reset();
                $(form).removeClass('was-validated');
                refreshTableUser();
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
                    title: 'Failed to create user'
                });
            }
        });
    });

    $('#userTable').on('click', '.edit_admin_user', function () {
        let userId = $(this).data('id');

        $.ajax({
            url: '/admin/admin-fetch-user/' + userId,
            type: 'GET',
            success: function (response) {
                $('#editAdminUserModal #user_id').val(response.id);
                $('#editAdminUserModal #name').val(response.name);
                $('#editAdminUserModal #email').val(response.email);
                $('#editAdminUserModal').modal('show');
            }
        });
    });

    $('#admin-update-user').submit(function (e) {
        e.preventDefault();

        let formData = $(this).serialize(); // No need to spoof method
        let userId = $('#user_id').val();
        $('#updateUserBtn').attr('disabled', true);
        $('#updateUserBtn .spinner-border').removeClass('d-none');
        $('#updateUserBtn .btn-text').text('Updating...');

        $.ajax({
            url: '/admin/admin-update-user/' + userId,
            type: 'POST',
            data: formData,
            success: function (response) {
                $('#editAdminUserModal').modal('hide');
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

                refreshTableUser();
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
                    title: 'Update Failed'
                });
            }
        });
    });

    $('#userTable').on('click', '.delete_user', function () {
        var userId = $(this).data('id');
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
                    url: '/admin/admin-delete-user',  
                    type: 'POST',                       
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')  // get token from meta tag
                    },
                    data: {
                        user_id: userId,
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
                            title: 'User Deleted Successfully'
                        });
                        refreshTableUser();
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
                            title: 'Failed to delete service'
                        });
                    },
                });
            }
        });
    });

    function refreshTableUser() {
        $.ajax({
            url: '/admin/get-updated-users-table',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                var users = response.data;
                var tableBody = $('#userTable tbody');
                $('[data-bs-toggle="tooltip"]').tooltip('dispose');
                tableBody.empty();

                users.forEach(function (user) {
                    var actionButtons = `
                    <button type="button" class="btn btn-sm btn-icon btn-primary edit_user"
                        data-bs-toggle="tooltip" data-bs-placement="left" title="Edit" data-id="${user.id}">
                        <i class='bx bx-edit'></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-icon btn-danger delete_user"
                        data-bs-toggle="tooltip" data-bs-placement="right" title="Delete" data-id="${user.id}">
                        <i class='bx bx-trash'></i>
                    </button>`;

                    var name = user.name ? user.name.toUpperCase() : 'N/A';
                    var email = user.email ? user.email : 'N/A';

                    // Mirror backend roles logic
                    var roles = {
                        0: { label: 'User', class: 'bg-secondary border-secondary' },
                        1: { label: 'Super Admin', class: 'bg-dark border-dark' },
                        2: { label: 'Admin', class: 'bg-success border-success' },
                        3: { label: 'Engineering', class: 'bg-warning border-warning text-dark' },
                        4: { label: 'Security', class: 'bg-danger border-danger' }
                    };

                    var role = roles.hasOwnProperty(user.role_id)
                        ? `<span class="badge ${roles[user.role_id].class} custom-badge">${roles[user.role_id].label}</span>`
                        : `<span class="badge bg-secondary custom-badge">Unknown</span>`;

                    var row = $(`
                    <tr>
                        <td>${name}</td>
                        <td>${email}</td>
                        <td>${role}</td>
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
