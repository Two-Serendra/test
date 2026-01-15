$(document).ready(function () {
    let isGeneratingSoa = false;
    $('#requestSoaForm').submit(function (event) {
        event.preventDefault();
        if (isGeneratingSoa) return;

        var isValid = true;
        $('.is-invalid').removeClass('is-invalid');

        var residence = $('select[name="resident_id"]').val();
        var year = $('select[name="year"]').val();
        var month = $('select[name="month"]').val();

        if (!residence) {
            isValid = false;
            $('select[name="resident_id"]').addClass('is-invalid');
        }
        if (!year) {
            isValid = false;
            $('select[name="year"]').addClass('is-invalid');
        }
        if (!month) {
            isValid = false;
            $('select[name="month"]').addClass('is-invalid');
        }

        if (!isValid) return;

        var formData = $(this).serialize();


        setGenerating(true);
        resetSoAView();
        $.ajax({
            url: $(this).attr('action'),
            type: $(this).attr('method'),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
            dataType: "json",

            success: function (data) {
                if (!data.token) {
                    $('#soaLoading').hide();
                    alert('Failed to generate SOA');
                    return;
                }
                const soaUrl = '/soa/view/' + data.token;

                if (isMobile()) {
                    $('#soaOpenBtn').attr('href', soaUrl);
                    $('#soaMobileLink').fadeIn(150);
                    setTimeout(() => setGenerating(false), 300)
                }
                else {
                    $('#soaFrame')
                        .off('load')
                        .on('load', function () {
                            $('#soaContainer').fadeIn(150);
                            setGenerating(false);
                        })
                        .attr('src', soaUrl);
                }

            },


            error: function (xhr, status, error) {
                setGenerating(false);
                console.error(xhr.responseText);
                alert('Error: ' + xhr.status + ' ' + error);
            }

        });
    });

    $('#requestSoaForm select').on('change', function () {
        $(this).removeClass('is-invalid');
    });


    function resetSoAView() {
        if (isGeneratingSoa) return;

        $('#soaContainer').hide();
        $('#soaMobileLink').hide();
        $('#soaFrame').attr('src', '');
        $('#soaOpenBtn').attr('href', '#');
    }


    function setGenerating(isGenerating) {
        isGeneratingSoa = isGenerating;

        const btn = $('#generateBtn');
        btn.prop('disabled', isGenerating)
            .toggleClass('disabled opacity-75', isGenerating);

        if (isGenerating) {
            $('#soaLoading').stop(true, true).fadeIn(150);
        } else {
            $('#soaLoading').stop(true, true).fadeOut(150);
        }
    }
});