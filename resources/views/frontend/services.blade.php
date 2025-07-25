@extends('layouts.frontend')
@section('content')
    <style>
        .service .service-inner::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(rgba(4, 61, 19, 0.5), rgba(8, 131, 41, 0.5)),
                url("{{ asset('assets/images/2S DAHON.png') }}");
            background-size: contain;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0;
            transform: scale(1.2);
            transition: opacity 0.5s ease, transform 0.5s ease;
            z-index: 0;
        }

        .service .service-inner:hover::before {
            opacity: 1;
            transform: scale(1);
        }
    </style>
    <div class="container-fluid page-header pt-5 mb-6">
        <div class="container text-center pt-5">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="p-5 text-dark"
                        style="background-color: rgba(255, 255, 255, 0.8); border-radius: 10px 10px 0 0;">

                        <!-- Enhanced Title -->
                        <h1 class="display-6 text-uppercase mb-3 text-dark">SERVICES</h1>
                        <!-- Breadcrumb -->
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb justify-content-center mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('home') }}"
                                        style="color: #00c440 !important; font-weight: 600;">Home</a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page" style="color: #222; font-weight: 600;">
                                    Services
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid service pt-6 pb-6">
        <div class="container">
            <div class="text-center mx-auto" style="max-width: 600px;">
                <h1 class="display-6 text-uppercase mb-5">Reliable & High-Quality Services</h1>
            </div>

            <!-- First row: 3 cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-4 d-flex">
                    <div class="card card services border-0 shadow flex-fill d-flex flex-column h-100">
                        <div
                            class="card-header services-header bg-primary text-white text-uppercase fw-bold d-flex align-items-center gap-2">
                            Pest Control Services
                        </div>
                        <div class="card-body services-body">
                            <i class="fa-solid fa-bug-slash background-icon"></i>
                            <p class="text-justify">
                                Two Serendra offers complimentary pest control services once a month, including spraying,
                                misting, baiting, rat trapping and caging, and larviciding. Additional cleaning services may
                                be requested for PHP 300. Contact your lobby concierge to schedule.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 d-flex">
                    <div class="card card services border-0 shadow flex-fill d-flex flex-column h-100">
                        <div
                            class="card-header services-header bg-primary text-white text-uppercase fw-bold d-flex align-items-center gap-2">
                            Grease Trap Cleaning
                        </div>
                        <div class="card-body services-body">
                            <i class="fa-solid fa-sink background-icon"></i>
                            <p class="text-justify">
                                Available daily from 9:00 AM to 4:00 PM. Each unit gets two free cleanings per year.
                                Additional cleanings are PHP 448. Book via your lobby concierge.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 d-flex">
                    <div class="card card services border-0 shadow flex-fill d-flex flex-column h-100">
                        <div
                            class="card-header services-header bg-primary text-white text-uppercase fw-bold d-flex align-items-center gap-2">
                            Shuttle Services
                        </div>
                        <div class="card-body services-body">
                            <i class="fa-solid fa-shuttle-van background-icon"></i>

                            <p class="text-justify">
                                Daily shuttle service to key locations in BGC. For schedules and seat reservations, please
                                contact your lobby concierge.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Second row: 2 centered cards -->
            <div class="row g-4 justify-content-center">
                <div class="col-md-4 d-flex">
                    <div class="card card services border-0 shadow flex-fill d-flex flex-column h-100">
                        <div
                            class="card-header services-header bg-primary text-white text-uppercase fw-bold d-flex align-items-center gap-2">
                            In-Unit Services (IUS)
                        </div>
                        <div class="card-body services-body">
                            <i class="fa-solid fa-tools background-icon"></i>
                            <p class="text-justify">
                                In partnership with WilServ MPC, this service runs Mon–Sat, 8:00 AM–5:00 PM. Contact the IUS
                                Office for assistance.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 d-flex">
                    <div class="card card services border-0 shadow flex-fill d-flex flex-column h-100">
                        <div
                            class="card-header services-header bg-primary text-white text-uppercase fw-bold d-flex align-items-center gap-2">
                            Annual Unit Safety Inspection (AUSI)
                        </div>
                        <div class="card-body services-body">
                            <i class="fa-solid fa-helmet-safety background-icon"></i>
                            <p class="text-justify">
                                Inspections are Mon–Sat, 8:30 AM–4:30 PM. Scheduling is required to avoid penalties.
                                Coordinate with the lobby concierge or Engineering Office.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>




    <div class="container-fluid service pt-6 pb-6" style="background-color: #f2f2f2;">
        <div class="container">
            <div class="text-center mx-auto" style="max-width: 600px;">
                <h1 class="display-6 text-uppercase mb-5">Concessionaires</h1>
            </div>

            <!-- First Row: 3 Images -->
            <div class="row g-4 justify-content-center">
                <div class="col-lg-4 col-md-6 d-flex">
                    <div class="card border-0 shadow w-100 overflow-hidden" style="height: 500px;">
                        <img src="{{ asset('assets/images/concessionaire/soybueno.webp') }}" class="img-fluid w-100 h-100"
                            style="width:100%, height: auto, object-fit: contain;" alt="Soybueno">
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 d-flex">
                    <div class="card border-0 shadow w-100 overflow-hidden" style="height: 500px;">
                        <img src="{{ asset('assets/images/concessionaire/farmersmarket.webp') }}"
                            class="img-fluid w-100 h-100" style="width:100%, height: auto, object-fit: contain;"
                            alt="Farmers Market">
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 d-flex">
                    <div class="card border-0 shadow w-100 overflow-hidden" style="height: 500px;">
                        <img src="{{ asset('assets/images/concessionaire/commune.webp') }}" class="img-fluid w-100 h-100"
                            style="width:100%, height: auto, object-fit: contain;" alt="Commune">
                    </div>
                </div>
            </div>

            <!-- Second Row: 2 Images -->
            <div class="row g-4 justify-content-center mt-1">
                <div class="col-lg-4 col-md-6 d-flex">
                    <div class="card border-0 shadow w-100 overflow-hidden" style="height: 500px;">
                        <img src="{{ asset('assets/images/concessionaire/wilserv.webp') }}" class="img-fluid w-100 h-100"
                            style="width:100%, height: auto, object-fit: contain;" alt="Wilserv">
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 d-flex">
                    <div class="card border-0 shadow w-100 overflow-hidden" style="height: 500px;">
                        <img src="{{ asset('assets/images/concessionaire/pilates.webp') }}" class="img-fluid w-100 h-100"
                            style="width:100%, height: auto, object-fit: contain;" alt="Pilates">
                    </div>
                </div>
            </div>
        </div>
    </div>





@endsection