$(document).ready(function () {
    $('.add-residence').on('click', function () {
        $('#addResidence').modal('show');
    });

    $('#addRowBtn').on('click', function () {
        let newRow = `
            <tr class="residence-row">
                <td>
                    <select name="resident_type[]" required class="form-select">
                        <option value="" disabled selected>Select</option>
                        <option value="Owner">Owner</option>
                        <option value="Tenant">Tenant</option>
                    </select>
                </td>
                <td>
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
                <td>
                    <input type="text" name="unit_no[]" class="form-control" required>
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm btn-forge remove-residence">
                        <i class="bx bx-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#residenceTableBody').append(newRow);
    });

    $(document).on('click', '.remove-residence', function () {
        $(this).closest('tr').remove();
    });


    $('#add-residence-form').on('submit', function (e) {
        e.preventDefault();
        if (!this.checkValidity()) {
            this.classList.add('was-validated');
            return;
        }
        this.classList.remove('was-validated');

        const $form = $(this);
        const formData = $form.serialize();
        const $btn = $('#addResidenceBtn');
        const $spinner = $btn.find('.spinner-border');
        const $btnText = $btn.find('.btn-text');

        // Disable and toggle UI
        $btn.attr('disabled', true);
        $spinner.removeClass('d-none');
        $btnText.addClass('d-none');

        $.ajax({
            type: 'POST',
            url: $form.attr('action'),
            data: formData,
            success: function () {
                Swal.fire({
                    icon: 'success',
                    title: 'Submitted!',
                    text: 'Submission complete. Please wait for approval.',
                    confirmButtonColor: '#3085d6',
                }).then(() => {
                    $('#addResidence').modal('hide');
                    $form[0].reset();
                });
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops!',
                    text: 'Something went wrong. Please try again.',
                });
            },
            complete: function () {
                $btn.attr('disabled', false);
                $spinner.addClass('d-none');
                $btnText.removeClass('d-none');
            }
        });
    });
});