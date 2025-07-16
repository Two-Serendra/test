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


    let currentImages = [];
    let currentIndexthumbnail = 0;
    const $modal = new bootstrap.Modal($('#imageGalleryModal')[0]);
    const $modalImg = $('#galleryModalImage');

    // Open modal on thumbnail click
    $('.thumbnail-gallery').on('click', function () {
        const tower = $(this).data('tower');
        currentIndexthumbnail = parseInt($(this).data('index'));

        // Get all images for this tower
        currentImages = $(`.thumbnail-gallery[data-tower="${tower}"]`).map(function () {
            return this.src;
        }).get();

        $modalImg.attr('src', currentImages[currentIndexthumbnail]);
        $modal.show();
    });

    // Previous button
    $('#prevImageBtn').on('click', function () {
        if (!currentImages.length) return;
        currentIndexthumbnail = (currentIndexthumbnail - 1 + currentImages.length) % currentImages.length;
        $modalImg.attr('src', currentImages[currentIndexthumbnail]);
    });

    // Next button
    $('#nextImageBtn').on('click', function () {
        if (!currentImages.length) return;
        currentIndexthumbnail = (currentIndexthumbnail + 1) % currentImages.length;
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



});


