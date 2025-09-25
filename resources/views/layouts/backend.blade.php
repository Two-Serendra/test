<!DOCTYPE html>
<html lang="en" class="light-style layout-navbar-fixed" dir="ltr" data-theme="theme-default"
    data-assets-path="{{ asset('assets/backend/') }}/" data-template="vertical-menu-template-free">

<head>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta charset="utf-8">
    <title>Two Serendra</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <link href="{{ asset('assets/images/favicon-16x16.png') }}" rel="icon">

    <!-- Favicon -->
    <!-- <link rel="icon" type="image/x-icon" href="../assets/img/favicon/favicon.ico" /> -->

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />


    <link href="{{ asset('assets/backend/vendor/fonts/boxicons.css')}}" rel="stylesheet">
    <link href="{{ asset('assets/backend/css/custom.css')}}" rel="stylesheet">

    <link href="{{ asset('assets/backend/vendor/css/core.css')}}" class="template-customizer-core-css" rel="stylesheet">
    <link href="{{ asset('assets/backend/vendor/css/theme-default.css')}}" class="template-customizer-theme-css"
        rel="stylesheet">
    <link href="{{ asset('assets/backend/css/demo.css')}}" rel="stylesheet">
    <link href="{{ asset('assets/backend/vendor/libs/perfect-scrollbar/perfect-scrollbar.css')}}" rel="stylesheet">
    <link href="{{ asset('assets/backend/vendor/libs/apex-charts/apex-charts.css')}}" rel="stylesheet">
    <script src="{{ asset('assets/backend/vendor/js/helpers.js')}}"></script>
    <script src="{{ asset('assets/backend/js/config.js')}}"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

</head>

<body>

    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Sidebar -->
            @include('layouts.includes.sidebar')

            <div class="layout-page">
                <!-- Navbar -->
                @include('layouts.includes.admin-topbar')

                <!-- Content wrapper -->
                <div class="content-wrapper">

                    <div class="container-fluid flex-grow-1 container-p-y">
                        @yield('content')
                    </div>

                </div>
            </div>
        </div>
    </div>






    <!-- JavaScript Libraries -->
    <script src="{{ asset('assets/backend/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/backend/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/backend/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/backend/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/backend/vendor/js/menu.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!-- <script src="{{ asset('assets/backend/vendor/libs/apex-charts/apexcharts.js') }}"></script> -->
    <script src="{{ asset('assets/backend/js/main.js') }}"></script>
    <script src="{{ asset('assets/backend/js/dashboards-analytics.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>


    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <!-- Custom Js -->
    <script src="{{ asset('assets/backend/js/services.js')}}"></script>
    <script src="{{ asset('assets/backend/js/downloads.js')}}"></script>
    <script src="{{ asset('assets/backend/js/user.js')}}"></script>
    <script src="{{ asset('assets/backend/js/emails.js')}}"></script>
    <script src="{{ asset('assets/backend/js/work-permit.js')}}"></script>
    <script src="{{ asset('assets/backend/js/amenities.js')}}"></script>
    <script src="{{ asset('assets/backend/js/function-rooms.js')}}"></script>
    <script src="{{ asset('assets/backend/js/gallery.js')}}"></script>
    <script src="{{ asset('assets/backend/js/events.js')}}"></script>
    <script src="{{ asset('assets/backend/js/residence.js')}}"></script>
    <script src="{{ asset('assets/backend/js/function-room-booking.js')}}"></script>
    <script src="{{ asset('assets/backend/js/date-blocking.js')}}"></script>
    <script src="{{ asset('assets/backend/js/dashboard.js')}}"></script>
    <script src="{{ asset('assets/backend/js/addOns.js')}}"></script>
    <script src="{{ asset('assets/backend/js/records.js')}}"></script>



    @stack('scripts')

    @stack('js')

    <script>
        const currentUserRoleId = {{ auth()->user()->role_id }};

        const pusher = new Pusher('c82d0ff4baaca9650c6f', {
            cluster: 'ap1'
        });

        // 🔹 Existing listener (for Residence Requests)
        const residenceChannel = pusher.subscribe('residence-requests');
        residenceChannel.bind('ResidenceRequestSubmitted', function (data) {
            toastr.success(
                `Residence request from Unit ${data.unit_no}`,
                'New Residence Request',
                {
                    timeOut: 5000,
                    closeButton: true,
                    progressBar: true
                }
            );
            updateCounters();
            prependNotification(`New request for unit ${data.unit_no}`);
        });

        function incrementFunctionRoomCounter() {
            const counter = document.getElementById('function-room-counter');
            let count = parseInt(counter.textContent);

            if (isNaN(count)) {
                count = 0;
            }

            count++;
            counter.textContent = count;
            counter.classList.remove('d-none');
        }

        // 🔹 Function Room Booking Notification
        const functionRoomChannel = pusher.subscribe('function-room-bookings');
        functionRoomChannel.bind('FunctionRoomBookingCreated', function (data) {
            // ✅ FIXED: Proper role check (removed typo `|`)
            if ([1, 2, 5, 7].includes(currentUserRoleId)) {
                toastr.success(
                    `(Unit: ${data.unit_no})`,
                    `New Booking - ${data.function_room}`,
                    {
                        timeOut: 5000,
                        closeButton: true,
                        progressBar: true
                    }
                );

                incrementFunctionRoomCounter();
                prependNotification(`New Function Room booking for ${data.function_room}`);
            }
        });

        functionRoomChannel.bind('FunctionRoomBookingCancelled', function (data) {
            if ([1, 2, 5, 7].includes(currentUserRoleId)) {
                toastr.warning(
                    `(Unit: ${data.unit_no})`,
                    `Booking Cancelled - ${data.function_room}`,
                    {
                        timeOut: 5000,
                        closeButton: true,
                        progressBar: true
                    }
                );
                incrementFunctionRoomCounter();
                prependNotification(`Function Room booking cancelled for ${data.function_room}`);
            }
        });
    </script>


</body>

</html>