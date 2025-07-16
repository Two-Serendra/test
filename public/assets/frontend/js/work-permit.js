$(document).ready(function () {

    function validateField(field) {
        if (!$(field).is(':visible') || $(field).is(':disabled')) return;

        const value = $(field).val();
        const isValid = field.checkValidity() && value !== '';
        if (field.type === 'date' && !value) {
            $(field).removeClass('is-valid').addClass('is-invalid');
            return;
        }

        if (isValid) {
            $(field).removeClass('is-invalid').addClass('is-valid');
        } else {
            $(field).removeClass('is-valid').addClass('is-invalid');
        }
    }

    $('#workPermitForm input, #workPermitForm select, #workPermitForm textarea').on('change blur input', function () {
        validateField(this);
    });

    $('#workPermitForm').submit(function (event) {
        event.preventDefault();

        // Validate all fields before checking form validity
        $('#workPermitForm input, #workPermitForm select, #workPermitForm textarea').each(function () {
            validateField(this);
        });

        let formValid = this.checkValidity();

        if (!formValid) {
            this.classList.add('was-validated');
            return;
        }

        this.classList.remove('was-validated');

        let formData = new FormData(this);
        const form = this;

        $('#submitWorkPermitBtn').attr('disabled', true);
        $('#submitWorkPermitBtn .spinner-border').removeClass('d-none');
        $('#submitWorkPermitBtn .btn-text').text('Submitting...');

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
                Swal.fire({
                    icon: 'success',
                    title: 'Submitted Successfully',
                    text: 'Work permit has been submitted.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    form.reset();
                    $(form).removeClass('was-validated');
                    $('#workPermitForm .is-valid, #workPermitForm .is-invalid').removeClass('is-valid is-invalid');
                    $('#submitWorkPermitBtn').attr('disabled', false);
                    $('#submitWorkPermitBtn .spinner-border').addClass('d-none');
                    $('#submitWorkPermitBtn .btn-text').text('SUBMIT');
                });
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Failed to submit',
                    text: 'There was an error submitting your request. Please try again.',
                    confirmButtonText: 'OK'
                });

                $('#submitWorkPermitBtn').attr('disabled', false);
                $('#submitWorkPermitBtn .spinner-border').addClass('d-none');
                $('#submitWorkPermitBtn .btn-text').text('SUBMIT');
            }
        });
    });

});
