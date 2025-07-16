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




    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

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
    <script src="{{ asset('assets/backend/vendor/libs/apex-charts/apexcharts.js') }}"></script>
    <script src="{{ asset('assets/backend/js/main.js') }}"></script>
    <script src="{{ asset('assets/backend/js/dashboards-analytics.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js"></script>


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
    @stack('scripts')

</body>

</html>