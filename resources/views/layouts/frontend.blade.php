<!DOCTYPE html>
<html lang="en">

<head>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta charset="utf-8">
    <title>Two Serendra</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

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
  






</head>

<body>
    @include('layouts.includes.topbar')
    @include('layouts.includes.navbar')
    <div class="wrapper">
        <div class="main">
            <div class="main-panel">
                <div id="app">
                    <div id="notifications-container">
                    </div>
                </div>
                @yield('content')
            </div>
        </div>
    </div>

    @include('layouts.includes.footer')
    @include('layouts.includes.copyright')

    <a href="#" class="btn btn-lg btn-lg-square back-to-top text-light" style="background-color: #004d1a;"><i
            class="bi bi-arrow-up"></i></a>


    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="{{ asset('assets/frontend/lib/wow/wow.min.js') }}"></script>
    <script src="{{ asset('assets/frontend/lib/easing/easing.min.js') }}"></script>
    <script src="{{ asset('assets/frontend/lib/waypoints/waypoints.min.js') }}"></script>
    <script src="{{ asset('assets/frontend/lib/counterup/counterup.min.js') }}"></script>
    <script src="{{ asset('assets/frontend/lib/owlcarousel/owl.carousel.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.js"></script>

    <!-- Template Javascript -->
    <script src="{{ asset('assets/frontend/js/main.js') }}"></script>
    <script src="{{ asset('assets/frontend/js/custom.js') }}"></script>
    <script src="{{ asset('assets/frontend/js/work-permit.js') }}"></script>


    <!-- Cookie Banner HTML -->
    <!-- <div id="cookieConsentBanner" class="position-fixed bottom-0 start-0 end-0 bg-light shadow-lg py-3 d-none"
        style="z-index: 1080;">
        <div class="container d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <img src="{{ asset('assets/images/dpo-dps.png') }}" alt="DPO/DPS" style="height: 80px;">
                <div>
                    <h6 class="fw-bold mb-1 text-dark">COOKIE POLICY</h6>
                    <p class="mb-0 text-dark small">
                        By using our site, you agree to Serendra Condominium Corporation's use of cookies to improve
                        your
                        browsing experience.
                    </p>
                </div>
            </div>
            <div class="d-flex align-items-start flex-column flex-md-row gap-2 ms-auto">
                <button id="acceptCookiesBtn" class="btn btn-primary rounded-pill px-4 py-2">ACCEPT COOKIES</button>
                <button class="btn btn-link text-dark p-0 m-0" id="closeCookieBanner"
                    style="font-size: 1.5rem; line-height: 1;">&times;</button>
            </div>
        </div>
    </div>


    <script>
        $(document).ready(function () {
            if (!localStorage.getItem('cookieConsent')) {
                $('#cookieConsentBanner').removeClass('d-none').hide().fadeIn();
            }

            $('#acceptCookiesBtn').click(function () {
                localStorage.setItem('cookieConsent', 'true');
                $('#cookieConsentBanner').fadeOut();
            });

            $('#closeCookieBanner').click(function () {
                $('#cookieConsentBanner').fadeOut(); 
            });
        });
    </script> -->

</body>

</html>