$(document).ready(function () {

    let offset = 8;
    $('#loadMoreBtn').on('click', function () {
        $.ajax({
            url: '/events',
            type: "GET",
            data: { offset: offset },
            success: function (data) {
                if (data.trim() === '') {
                    $('#loadMoreBtn').hide();
                } else {
                    $('#past-events').append(data);
                    offset += 8;
                }
            },
            error: function () {
                alert('Unable to load more events at the moment.');
            }
        });
    });

    const years = [2024, 2025, 2026, 2027, 2028, 2029, 2030];
    let allHolidays = [];

    // Fetch holidays for each year
    function fetchHolidays(year) {
        return $.get(`https://date.nager.at/api/v3/PublicHolidays/${year}/PH`)
            .then(function (data) {
                return data.map(item => item.date);
            });
    }

    // Fetch all holidays then init Flatpickr
    Promise.all(years.map(fetchHolidays)).then(function (results) {
        // Flatten the array of arrays into a single array
        allHolidays = [].concat(...results);

        // Init Flatpickr
        flatpickr("#dateFieldWorkPermit", {
            dateFormat: "Y-m-d",
            disable: [
                function (date) {
                    return date.getDay() === 0;
                },
                ...allHolidays
            ],
            minDate: "today"
        });
    }).catch(function (error) {
        console.error("Error fetching holidays:", error);
    });



    let viewer;

    $(document).on('click', '.360View', function () {
        const imageUrl = $(this).data('img');
        const roomName = $(this).data('name');

        $('#360ViewModal .modal-title').text(roomName);
        $('#pano').html('');
        const modal = $('#360ViewModal');

        modal.addClass('show').css({
            display: 'block',
            visibility: 'hidden'
        });


        viewer = pannellum.viewer('pano', {
            type: 'equirectangular',
            panorama: imageUrl,
            autoLoad: true,
            autoRotate: -1.5,
            showZoomCtrl: true,
            compass: false,
            hfov: 110
        });


        setTimeout(() => {
            modal.removeClass('show').css({
                display: '',
                visibility: ''
            });


            modal.modal('show');
        }, 100);
    });

    $('#360ViewModal').on('hidden.bs.modal', function () {
        if (viewer) {
            viewer.destroy();
            viewer = null;
        }
        $('#pano').html('');
    });




    const images = window.galleryImageList || [];
    let currentIndex = 0;

    function showImage(index) {
        if (index < 0) index = images.length - 1;
        if (index >= images.length) index = 0;

        currentIndex = index;
        const imageUrl = `/assets/images/gallery/${images[index]}`;
        $('#modalImage').attr('src', imageUrl);
        $('#galleryModal').modal('show');
    }

    $('.open-gallery-modal').click(function () {
        const index = parseInt($(this).data('index'));
        showImage(index);
    });

    $('.prev-btn').click(function () {
        showImage(currentIndex - 1);
    });

    $('.next-btn').click(function () {
        showImage(currentIndex + 1);
    });


    // =====================
    // GLOBAL GALLERY SCRIPT
    // =====================
    (function ($) {
        $(function () {

            // Standard gallery (with #galleryModal)
            if ($('#galleryModal').length) {
                const images = window.galleryImageList || [];
                let currentIndex = 0;

                function showImage(index) {
                    if (index < 0) index = images.length - 1;
                    if (index >= images.length) index = 0;

                    currentIndex = index;
                    const imageUrl = `/assets/images/gallery/${images[index]}`;
                    $('#modalImage').attr('src', imageUrl);
                    $('#galleryModal').modal('show');
                }

                $(document).on('click', '.open-gallery-modal', function () {
                    const index = parseInt($(this).data('index'));
                    showImage(index);
                });

                $(document).on('click', '.prev-btn', function () {
                    showImage(currentIndex - 1);
                });

                $(document).on('click', '.next-btn', function () {
                    showImage(currentIndex + 1);
                });
            }

            // Thumbnail gallery (with #imageGalleryModal)
            if ($('#imageGalleryModal').length) {
                let currentImages = [];
                let currentIndexthumbnail = 0;
                const modalEl = document.getElementById('imageGalleryModal');
                const modalInstance = new bootstrap.Modal(modalEl);
                const $modalImg = $('#galleryModalImage');

                $(document).on('click', '.thumbnail-gallery', function () {
                    const tower = $(this).data('tower');
                    currentIndexthumbnail = parseInt($(this).data('index'));

                    // Collect all images for this tower
                    currentImages = $(`.thumbnail-gallery[data-tower="${tower}"]`).map(function () {
                        return this.src;
                    }).get();

                    $modalImg.attr('src', currentImages[currentIndexthumbnail]);
                    modalInstance.show();
                });

                // Previous button
                $(document).on('click', '#prevImageBtn', function () {
                    if (!currentImages.length) return;
                    currentIndexthumbnail =
                        (currentIndexthumbnail - 1 + currentImages.length) % currentImages.length;
                    $modalImg.attr('src', currentImages[currentIndexthumbnail]);
                });

                // Next button
                $(document).on('click', '#nextImageBtn', function () {
                    if (!currentImages.length) return;
                    currentIndexthumbnail =
                        (currentIndexthumbnail + 1) % currentImages.length;
                    $modalImg.attr('src', currentImages[currentIndexthumbnail]);
                });

                // Swipe support (touch devices)
                let startX = 0;
                $modalImg.on('touchstart', function (e) {
                    startX = e.originalEvent.touches[0].clientX;
                });

                $modalImg.on('touchend', function (e) {
                    const endX = e.originalEvent.changedTouches[0].clientX;
                    const diffX = startX - endX;

                    if (diffX > 50) {
                        $('#nextImageBtn').click(); // Swipe left
                    } else if (diffX < -50) {
                        $('#prevImageBtn').click(); // Swipe right
                    }
                });
            }
        });
    })(jQuery);



    // $('#requestSoaForm').submit(function (event) {
    //     event.preventDefault();

    //     var isValid = true;
    //     $('.is-invalid').removeClass('is-invalid');

    //     var residence = $('select[name="resident_id"]').val();
    //     var year = $('select[name="year"]').val();
    //     var month = $('select[name="month"]').val();

    //     if (!residence) {
    //         isValid = false;
    //         $('select[name="resident_id"]').addClass('is-invalid');
    //     }
    //     if (!year) {
    //         isValid = false;
    //         $('select[name="year"]').addClass('is-invalid');
    //     }
    //     if (!month) {
    //         isValid = false;
    //         $('select[name="month"]').addClass('is-invalid');
    //     }

    //     if (!isValid) return;

    //     var formData = $(this).serialize();

    //     // UI reset
    //     $('#soaContainer').hide();
    //     $('#soaFrame').attr('src', '');
    //     $('#soaLoading').show();

    //     $.ajax({
    //         url: $(this).attr('action'),
    //         type: $(this).attr('method'),
    //         headers: {
    //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //         },
    //         data: formData,
    //         dataType: "json",
    //         success: function (data) {
    //             if (!data.soaUrl) {
    //                 $('#soaLoading').hide();
    //                 alert('No SOA URL returned.');
    //                 return;
    //             }

    //             // 🔹 Attach iframe load handler BEFORE setting src
    //             $('#soaFrame')
    //                 .off('load error')
    //                 .on('load', function () {
    //                     $('#soaLoading').hide();
    //                     $('#soaContainer').show();
    //                 })
    //                 .on('error', function () {
    //                     $('#soaLoading').hide();
    //                     alert('Failed to load SOA PDF.');
    //                 });

    //             // Start loading PDF
    //             $('#soaFrame').attr('src', data.soaUrl);
    //         },
    //         error: function () {
    //             $('#soaLoading').hide();
    //             alert('Failed to fetch SOA. Please check your input or network.');
    //         }
    //     });
    // });

    $('#requestSoaForm').submit(function (event) {
        event.preventDefault();

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

        // UI reset
        $('#soaContainer').hide();
        $('#soaFrame').attr('src', '');
        $('#soaLoading').show();
        $('#soaMobileLink').hide();

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
                    $('#soaContainer').hide();
                    $('#soaFrame').attr('src', '');
                    $('#soaLoading').hide();

                    $('#soaOpenBtn').attr('href', soaUrl);
                    $('#soaMobileLink').show();
                }
                else {
                    $('#soaFrame')
                        .off('load')
                        .on('load', function () {
                            $('#soaLoading').hide();
                            $('#soaContainer').show();
                        })
                        .attr('src', soaUrl);
                }

            },


            error: function (xhr, status, error) {
                $('#soaLoading').hide();
                console.error(xhr.responseText);
                alert('Error: ' + xhr.status + ' ' + error);
            }

        });
    });

    // Remove validation highlight
    $('select').on('change', function () {
        $(this).removeClass('is-invalid');
    });





});


