$(document).ready(function () {

    $("#sentTestEmail").submit(function (event) {
        event.preventDefault();

        let form = $(this)[0];   // FIXED
        let email = $("#emailInput").val();

        // HTML5 validation
        if (!form.checkValidity()) {
            $(form).addClass('was-validated');
            return;
        }

        $(form).removeClass('was-validated');

        let btn = $("#submitBtn");
        btn.prop("disabled", true).text("Sending...");

        $.ajax({
            url: $(this).attr('action'),
            type: "POST",
            data: {
                email: email,
                _token: $('input[name="_token"]').val()
            },
            success: function () {

                Swal.fire({
                    icon: "success",
                    title: "Email Sent!",
                    text: "The email has been sent successfully.",
                });

                $("#sentTestEmail")[0].reset();
                btn.prop("disabled", false).text("Send");
            },
            error: function (xhr) {
                Swal.fire({
                    icon: "error",
                    title: "Oops!",
                    text: xhr.responseJSON?.message || "Something went wrong.",
                });

                btn.prop("disabled", false).text("Send");
                console.error(xhr.responseText);
            }

        });
    });

});
