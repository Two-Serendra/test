<!DOCTYPE html>
<html lang="en">

<head>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta charset="utf-8">
    <title>Two Serendra</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="keywords"
        content="Two Serendra, BGC condominiums, Bonifacio Global City condos, Taguig real estate, luxury condos Philippines, Ayala Land Premier">
    <meta name="description"
        content="Welcome to Two Serendra, a signature development by Ayala Land Inc., where the tranquility of resort-style living meets the dynamism of Bonifacio Global City (BGC).">

    <!-- Favicon -->
    <link href="{{ asset('assets/images/favicon-16x16.png') }}" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Roboto:wght@700;800&display=swap"
        rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.4/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <!-- Libraries Stylesheet -->
    <link href="{{ asset('assets/frontend/lib/animate/animate.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/frontend/lib/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet">


    <!-- Customized Bootstrap Stylesheet -->

    <link href="{{ asset('assets/frontend/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/frontend/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/frontend/css/custom.css') }}" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <!-- <link href="https://fonts.googleapis.com/css2?family=Satisfy&display=swap" rel="stylesheet"> -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">


    <link href="https://fonts.googleapis.com/css2?family=Shantell+Sans:ital,wght@0,300..800;1,300..800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script&family=Poppins&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">



</head>

<body class="d-flex flex-column min-vh-100" x-data>
    @include('layouts.includes.topbar')
    @include('layouts.includes.navbar')
    <main class="flex-grow-1">
        <div class="wrapper">
            <div class="main">
                <div class="main-panel">
                    <div id="app">
                        <div id="notifications-container"></div>
                    </div>

                    @yield('content')
                </div>
            </div>
        </div>
    </main>

    <footer class="mt-auto">
        @include('layouts.includes.footer')
        @include('layouts.includes.copyright')
    </footer>



    <a href="#" class="btn btn-lg btn-lg-square back-to-top text-light" style="background-color: #004d1a;"><i
            class="bi bi-arrow-up"></i></a>

    <script>
        function isMobile() {
            return /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
        }
    </script>




    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script src="{{ asset('assets/frontend/lib/wow/wow.min.js') }}"></script>
    <script src="{{ asset('assets/frontend/lib/easing/easing.min.js') }}"></script>
    <script src="{{ asset('assets/frontend/lib/waypoints/waypoints.min.js') }}"></script>
    <script src="{{ asset('assets/frontend/lib/counterup/counterup.min.js') }}"></script>
    <script src="{{ asset('assets/frontend/lib/owlcarousel/owl.carousel.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.js"></script>
    <script src="https://js.pusher.com/8.2/pusher.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.15.2/echo.iife.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl)
            })
        });
    </script>

    <!-- Template Javascript -->
    <script src="{{ asset('assets/frontend/js/main.js') }}"></script>
    <script src="{{ asset('assets/frontend/js/custom.js')}}?v=2 }}"></script>
    <script src="{{ asset('assets/frontend/js/work-permit.js') }}"></script>
    <script src="{{ asset('assets/frontend/js/profile.js') }}"></script>
    <script src="{{ asset('assets/frontend/js/function-room.js') }}"></script>
    <script src="{{ asset('assets/frontend/js/test-email.js') }}"></script>
    <script src="{{ asset('assets/frontend/js/soa.js') }}?v=2"></script>
    <script src="{{ asset('assets/frontend/js/resident-booking-history.js') }}"></script>
    <script src="{{ asset('assets/frontend/js/grease-trap.js') }}"></script>
    <script src="{{ asset('assets/frontend/js/pest-control.js') }}"></script>
    <script src="{{ asset('assets/frontend/js/activity-booking.js') }}"></script>
    <script src="{{ asset('assets/frontend/js/resident-booking-history.js') }}"></script>
    <script src="{{ asset('assets/frontend/js/fitness-hub-booking.js') }}"></script>
    <script src="{{ asset('assets/frontend/js/ausi-booking.js') }}"></script>

    <script>
        Pusher.logToConsole = false;

        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: "{{ env('PUSHER_APP_KEY') }}",
            cluster: "{{ env('PUSHER_APP_CLUSTER') }}",
            forceTLS: false, // local without SSL
            authEndpoint: "/broadcasting/auth",
            auth: {
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                }
            }
        });



    </script>



    <script>
        // $(document).ready(function () {
        //     toastr.options = {
        //         closeButton: true,
        //         progressBar: true,
        //         positionClass: "toast-top-right",
        //         timeOut: 0, // 0 means it won't auto-dismiss
        //         extendedTimeOut: 0
        //     };
        //     toastr.info("🔧 Adjusting Toastr position — test notification!");
        // });

        $(document).ready(function () {

            document.querySelectorAll('.unitStatusInfo').forEach(function (el) {
                const tooltip = el.querySelector('.tooltipText');
                el.addEventListener('mouseenter', () => {
                    tooltip.style.visibility = 'visible';
                });
                el.addEventListener('mouseleave', () => {
                    tooltip.style.visibility = 'hidden';
                });
            });

            if (localStorage.getItem("redirect_after_login")) {
                const url = localStorage.getItem("redirect_after_login");
                localStorage.removeItem("redirect_after_login");
                window.location.href = url;
            }

            /**
             * Add a notification to the dropdown
             * @param {Object} notification
             */
            // function addNotification(notification) {
            //     const payload = notification.data || notification;
            //     const notifId = notification.id || payload.notification_id;
            //     const bookingId = payload.booking_id;
            //     const notifShowUrl = notifId ? `/notifications/${notifId}` : '#';

            //     const notifItem = `
            //     <a href="${notifShowUrl}" 
            //     class="dropdown-item text-start mark-as-read fw-bold"
            //     data-id="${notifId}" data-url="${notifShowUrl}"
            //     style="white-space: normal; text-wrap: wrap;">
            //     <i class="bx bx-bell me-2"></i> ${payload.message}
            //     <br>
            //     <small class="text-muted">Just now</small>
            //     </a>
            //     `;

            //     const notifMenu = $('#notifDropdownMenu');
            //     if (notifMenu.find('.dropdown-item').length > 0) {
            //         notifMenu.prepend('<div class="dropdown-divider"></div>' + notifItem);
            //     } else {
            //         notifMenu.prepend(notifItem);
            //     }
            //     const items = notifMenu.find('.dropdown-item');
            //     if (items.length > 5) {
            //         items.slice(5).remove();
            //     }

            //     let badge = $('#notifDropdown .badge');
            //     if (badge.length) {
            //         badge.text(parseInt(badge.text()) + 1);
            //     } else {
            //         $('#notifDropdown').append(`
            // <span class="position-absolute top-0 start-100 badge rounded-pill bg-danger"
            //       style="transform: translate(-60%, -35%);">1</span>
            //     `);
            //     }
            // }


            function addNotification(notification) {
                const payload = notification.data || notification;
                const notifId = notification.id || payload.notification_id;
                const notifShowUrl = notifId ? `/notifications/${notifId}` : '#';

                const notifItem = `
        <a href="${notifShowUrl}" 
        class="dropdown-item text-start mark-as-read fw-bold"
        data-id="${notifId}" data-url="${notifShowUrl}"
        style="white-space: normal; text-wrap: wrap;">
        <i class="bx bx-bell me-2"></i> ${payload.message}
        <br>
        <small class="text-muted">Just now</small>
        </a>
    `;

                const notifMenu = $('#notifDropdownMenu');

                if (notifMenu.find('.dropdown-item').length > 0) {
                    notifMenu.prepend('<div class="dropdown-divider"></div>' + notifItem);
                } else {
                    notifMenu.prepend(notifItem);
                }

                // Count items
                const unreadItems = notifMenu.find('.dropdown-item.fw-bold');
                const readItems = notifMenu.find('.dropdown-item.notification-read');
                const totalItems = unreadItems.length + readItems.length;

                // Keep only 5 items
                if (totalItems > 5) {

                    // Remove READ first
                    if (readItems.length > 0) {
                        readItems.last().remove();
                    }
                    // If no read, remove oldest UNREAD (rare but safe)
                    else {
                        unreadItems.last().remove();
                    }
                }

                // Badge counter
                let badge = $('#notifDropdown .badge');
                if (badge.length) {
                    badge.text(parseInt(badge.text()) + 1);
                } else {
                    $('#notifDropdown').append(`
            <span class="position-absolute top-0 start-100 badge rounded-pill bg-danger"
                  style="transform: translate(-60%, -35%);">1</span>
        `);
                }
            }




            if (typeof window.Echo !== 'undefined') {
                window.Echo.private(`App.Models.User.{{ auth()->id() }}`)
                    .notification((notification) => {
                        // console.log('🔔 New Notification:', notification);
                        addNotification(notification);

                        toastr.options = {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-top-right",
                            timeOut: 4000
                        };

                        toastr.info(notification.data?.message || 'You have a new notification');
                    });
            } else {
                // console.warn('❌ Echo is not defined. Check your bootstrap.js/Vite setup.');
            }

            // Mark notification as read
            $(document).on('click', '.mark-as-read', function (e) {
                e.preventDefault();

                const notifId = $(this).data('id');
                const url = $(this).data('url');

                $.ajax({
                    url: `/notifications/${notifId}/read`,
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function (res) {
                        if (res.success) {
                            $(`.mark-as-read[data-id="${notifId}"]`).removeClass('fw-bold').addClass('notification-read');

                            // Update badge
                            let badge = $('#notifDropdown .badge');
                            if (badge.length) {
                                let count = parseInt(badge.text());
                                if (count > 1) {
                                    badge.text(count - 1);
                                } else {
                                    badge.remove();
                                }
                            }

                            // Redirect if URL is set
                            if (url && url !== '#') {
                                window.location.href = url;
                            }
                        }
                    }
                });
            });

        });


    </script>

    <div id="loadingOverlay" class="loading-overlay">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</body>
</html>