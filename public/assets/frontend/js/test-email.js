$(document).ready(function () {

    $("#sentTestEmail").submit(function (event) {
        event.preventDefault();

        let form = $(this)[0];
        let email = $("#emailInput").val();
        let fromEmail = $("#fromEmail").val();
        if (!form.checkValidity()) {
            $(form).addClass('was-validated');
            return;
        }

        $(form).removeClass('was-validated');

        const $btn = $('#submitBtnTestMail');
        const originalWidth = $btn.outerWidth();
        $btn
            .attr('disabled', true)
            .html(`<div class="spinner-border spinner-border-sm text-light"></div>`)
            .css('width', originalWidth + 'px');
        $.ajax({
            url: $(this).attr('action'),
            type: "POST",
            data: {
                email: email,
                from_email: fromEmail,
                _token: $('input[name="_token"]').val()
            },
            success: function () {
                Swal.fire({
                    icon: "success",
                    title: "Email Sent!",
                    text: "Kindly check your inbox/spam/junk folder to view the email. If you did not receive the email, kindly contact lowriseadmin@twoserendra.com or (02)8252-5063.",
                });

                $("#sentTestEmail")[0].reset();
            },
            error: function (xhr) {
                Swal.fire({
                    icon: "error",
                    title: "Oops!",
                    text: xhr.responseJSON?.message || "Something went wrong.",
                });
                console.error(xhr.responseText);
            },
            complete: function () {
                $btn
                    .attr('disabled', false)
                    .html(`<span class="btn-text">Send</span>`)
                    .css('width', '');
            }
        });
    });




});
