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
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@3.10.5/dist/fullcalendar.min.css">

    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

</head>

<body>
    <div id="loadingOverlay" class="loading-overlay">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
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
                        @php
                            $routeName = request()->route()?->getName();
                            $breadcrumbs = config('breadcrumbs'); // full array
                            $crumbs = $breadcrumbs[$routeName] ?? null;

                            // Optional: map parent names to routes for clickable links
                            $breadcrumbLinks = [
                                'Function Rooms' => route('admin.show.function.rooms'),
                                'Amenities' => route('admin.show.amenities'),
                                'Grease Trap' => route('admin.booking.grease.trap'), // or main page for Grease Trap
                            ];
                        @endphp

                        @if($crumbs)
                            <nav aria-label="breadcrumb" class="mb-3">
                                <ol class="breadcrumb">
                                    @foreach($crumbs as $key => $crumb)
                                        @if($key < count($crumbs) - 1)
                                            <li class="breadcrumb-item">
                                                @if(isset($breadcrumbLinks[$crumb]))
                                                    <a href="{{ $breadcrumbLinks[$crumb] }}"
                                                        class="text-decoration-none">{{ $crumb }}</a>
                                                @else
                                                    {{ $crumb }}
                                                @endif
                                            </li>
                                        @else
                                            <li class="breadcrumb-item active fw-bold text-dark" aria-current="page">{{ $crumb }}
                                            </li>
                                        @endif
                                    @endforeach
                                </ol>
                            </nav>
                        @endif

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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@3.10.5/dist/fullcalendar.min.js"></script>




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
    <script src="{{ asset('assets/backend/js/function-room-discount.js')}}"></script>
    <!-- <script src="{{ asset('assets/backend/js/amenities.js') }}"></script> -->
    <script src="{{ asset('assets/backend/js/activities.js') }}"></script>
    <!-- <script src="{{ asset('assets/backend/js/amenity-booking.js') }}"></script> -->
    <script src="{{ asset('assets/backend/js/activity-schedule.js') }}"></script>
    <script src="{{ asset('assets/backend/js/history.js') }}"></script>
    <script src="{{ asset('assets/backend/js/calendar.js') }}"></script>
    <script src="{{ asset('assets/backend/js/blocking.js') }}"></script>
    <script src="{{ asset('assets/backend/js/activitiy-booking.js') }}"></script>
    <script src="{{ asset('assets/backend/js/grease-trap-booking.js') }}"></script>
    <script src="{{ asset('assets/backend/js/pest-control-booking.js') }}"></script>
    <script src="{{ asset('assets/backend/js/fitness-hub.js') }}"></script>
    <script src="{{ asset('assets/backend/js/fitness-hub-booking.js') }}"></script>
    <script src="{{ asset('assets/backend/js/fitness-hub-records.js') }}"></script>
    <script src="{{ asset('assets/backend/js/fitness-hub-schedule-blocking.js') }}"></script>
    <script src="{{ asset('assets/backend/js/ausi-booking.js') }}"></script>
    <script src="{{ asset('assets/backend/js/ausi-booking-report.js') }}"></script>

    @stack('scripts')

    @stack('js')

    <script>
        $(document).ready(function () {
            const currentUserRoleId = {{ auth()->user()->role_id }};

            const pusher = new Pusher('c82d0ff4baaca9650c6f', {
                cluster: 'ap1'
            });
            let refreshTimeout;


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

            function incrementGreaseTrapBookingCounter() {
                const counter = document.getElementById('grease-trap-booking-counter');
                let count = parseInt(counter.textContent);

                if (isNaN(count)) {
                    count = 0;
                }

                count++;
                counter.textContent = count;
                counter.classList.remove('d-none');
            }


            function incrementAmenityBookingCounter() {
                const counter = document.getElementById('amenity-booking-counter');
                let count = parseInt(counter.textContent);

                if (isNaN(count)) {
                    count = 0;
                }

                count++;
                counter.textContent = count;
                counter.classList.remove('d-none');
            }


            function incrementFitnessHubBookingCounter() {
                const counter = document.getElementById('fitness-hub-booking-counter');
                let count = parseInt(counter.textContent);

                if (isNaN(count)) {
                    count = 0;
                }

                count++;
                counter.textContent = count;
                counter.classList.remove('d-none');
            }


            const amenityBookingChannel = pusher.subscribe('amenity-booking');
            amenityBookingChannel.bind('amenity-booking-created', function (data) {
                if ([1, 6, 7].includes(currentUserRoleId)) {
                    toastr.success(`(Unit: ${data.unitNo})`, `New Amenity Booking`);
                    incrementAmenityBookingCounter();
                    refreshTableDebounced();
                }
            });

            const functionRoomChannel = pusher.subscribe('function-room-bookings');
            functionRoomChannel.bind('FunctionRoomBookingCreated', function (data) {
                if ([1, 2, 3, 5, 7, 6].includes(currentUserRoleId)) {
                    toastr.success(`(Unit: ${data.unit_no})`, `New Booking - ${data.function_room}`);
                    incrementFunctionRoomCounter();
                    refreshTableDebounced();
                }
            });

            functionRoomChannel.bind('FunctionRoomBookingCancelled', function (data) {
                if ([1, 2, 3, 5, 7, 6, 8].includes(currentUserRoleId)) {
                    toastr.warning(`(Unit: ${data.unit_no})`, `Booking Cancelled - ${data.function_room}`);
                    incrementFunctionRoomCounter();
                    refreshTableDebounced();
                }
            });

            const greaseTrapChannel = pusher.subscribe('grease-trap-bookings');
            greaseTrapChannel.bind('GreaseTrapBookingCreated', function (data) {
                if ([1, 6].includes(currentUserRoleId)) {
                    toastr.success(`(Unit: ${data.unit_no})`, `New Grease Trap Booking`);
                    incrementGreaseTrapBookingCounter();
                    refreshTableDebounced();
                }
            });

            greaseTrapChannel.bind('GreaseTrapBookingCancellation', function (data) {
                if ([1, 6].includes(currentUserRoleId)) {
                    toastr.success(`(Unit: ${data.unit_no})`, `Grease Trap Booking Cancelled`);
                    incrementGreaseTrapBookingCounter();
                    refreshTableDebounced();
                }
            });

            const fitnessHubChannel = pusher.subscribe('fitness-hub-bookings');
            fitnessHubChannel.bind('FitnessHubBookingCreated', function (data) {
                if ([1, 6, 7].includes(currentUserRoleId)) {
                    toastr.success(`(Unit: ${data.unit_no})`, `New Fitness Hub Booking`);
                    incrementFitnessHubBookingCounter();
                    refreshTableDebounced();
                }
            });

            fitnessHubChannel.bind('FitnessHubBookingCancellation', function (data) {
                if ([1, 6, 7].includes(currentUserRoleId)) {
                    toastr.success(`(Unit: ${data.unit_no})`, `Fitness Hub Booking Cancelled`);
                    incrementFitnessHubBookingCounter();
                    refreshTableDebounced();
                }
            });

            const pestControlChannel = pusher.subscribe('pest-control-bookings');
            pestControlChannel.bind('PestContrtolBookingCreated', function (data) {
                if ([1, 6].includes(currentUserRoleId)) {
                    toastr.success(`(Unit: ${data.unit_no})`, `New Pest Control Booking`);
                    incrementPestContrtolBookingCounter();
                    refreshTableDebounced();
                }
            });

            pestControlChannel.bind('PestContrtolBookingCancellation', function (data) {
                if ([1, 6].includes(currentUserRoleId)) {
                    toastr.success(`(Unit: ${data.unit_no})`, `Pest Control Booking Cancelled`);
                    incrementPestContrtolBookingCounter();
                    refreshTableDebounced();
                }
            });


            const ausiChannel = pusher.subscribe('ausi-bookings');
            ausiChannel.bind('AusiBookingCreated', function (data) {
                if ([1, 6].includes(currentUserRoleId)) {
                    toastr.success(`(Unit: ${data.unit_no})`, `New Ausi Booking`);
                    incrementAusiBookingCounter();
                    refreshTableDebounced();
                }
            });

            ausiChannel.bind('AusiBookingCancellation', function (data) {
                if ([1, 6].includes(currentUserRoleId)) {
                    toastr.success(`(Unit: ${data.unit_no})`, `Ausi Booking Cancelled`);
                    incrementAusiBookingCounter();
                    refreshTableDebounced();
                }
            });



            refreshTableDebounced = () => {
                clearTimeout(refreshTimeout);
                refreshTimeout = setTimeout(() => {
                    window.refreshFunctionRoomBookingsTable(window.currentFunctionRoomBookingPageUrl);
                }, 300);
            };
        });

        window.userRole = "{{ auth()->user()->role ?? '' }}";
    </script>


</body>

</html>