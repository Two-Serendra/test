@extends('layouts.frontend')
@section('content')


    <style>
        @media (min-width: 992px) {
            .about-section-fixed-height {
                height: 500px;
            }
        }
    </style>
    <div class="container-fluid page-header pt-5 mb-6">
        <div class="container text-center pt-5">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="p-5 text-dark"
                        style="background-color: rgba(255, 255, 255, 0.8); border-radius: 10px 10px 0 0;">

                        <!-- Enhanced Title -->
                        <h1 class="display-6 text-uppercase mb-3 text-dark">ABOUT</h1>
                        <!-- Breadcrumb -->
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb justify-content-center mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('home') }}"
                                        style="color: #00c440 !important; font-weight: 600;">Home</a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page" style="color: #222; font-weight: 600;">
                                    About
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <section class="py-5">
        <div class="container p-4">
            <div class="row align-items-stretch">
                <!-- Left Column: Image -->
                <div class="col-lg-6 d-flex">
                    <div class="w-100 rounded shadow overflow-hidden about-section-fixed-height">
                        <img src="{{ asset('assets/images/image22.webp') }}" alt="Two Serendra" class="w-100 h-100"
                            style="object-fit: cover;">
                    </div>
                </div>

                <!-- Right Column: Text -->
                <div class="col-lg-6 d-flex">
                    <div class="bg-white p-4 w-100 about-section-fixed-height overflow-auto">
                        <p style="text-align: justify;">
                            Welcome to <strong>Two Serendra</strong>, a signature development by Ayala Land Inc., where the
                            tranquility of
                            resort-style living meets the dynamism of Bonifacio Global City (BGC). Renowned for its
                            expansive
                            gardens, exceptional amenities, and a truly resort-like environment, Two Serendra offers a
                            unique
                            sanctuary designed for families. Here, lush landscapes and open spaces provide a refreshing
                            contrast
                            to the urban bustle, fostering a serene and family-friendly atmosphere that sets it apart within
                            BGC.
                        </p>
                        <p style="text-align: justify;">
                            Experience unparalleled comfort and convenience at Two Serendra, where every detail is
                            meticulously
                            managed to ensure a superior living experience. This premier condominium complex benefits from
                            the
                            expert oversight of Ayala Property Management Corporation (APMC), guaranteeing world-class
                            maintenance, security, and service. With APMC at the helm, residents can enjoy peace of mind,
                            knowing their homes and the surrounding environment are impeccably cared for, allowing them to
                            fully
                            embrace the vibrant lifestyle that Two Serendra and BGC have to offer.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <div class="container py-5">
        <div class="row g-4 align-items-stretch">

            <!-- ALVEO -->
            <div class="col-12 col-sm-6 col-md-4">
                <a href="https://www.alveoland.com.ph/" target="_blank" class="text-decoration-none">
                    <div class="card h-100 shadow d-flex justify-content-center align-items-center"
                        style="min-height: 200px;">
                        <img src="{{ asset('assets/images/ALVEO.png') }}" alt="ALVEO" class="img-fluid"
                            style="max-height: 100px; object-fit: contain;">
                    </div>
                </a>
            </div>

            <!-- Two Serendra -->
            <div class="col-12 col-sm-6 col-md-4">
                <a href="" target="_blank" class="text-decoration-none">
                    <div class="card h-100 shadow d-flex justify-content-center align-items-center"
                        style="min-height: 200px;">
                        <img src="{{ asset('assets/images/TWO SERENDRA LOGO PNG.png') }}" alt="Two Serendra"
                            class="img-fluid" style="max-height: 140px; object-fit: contain;">
                    </div>
                </a>
            </div>

            <!-- APMC -->
            <div class="col-12 col-sm-6 col-md-4">
                <a href="https://www.ayalaproperty.com.ph/" target="_blank" class="text-decoration-none">
                    <div class="card h-100 shadow d-flex justify-content-center align-items-center"
                        style="min-height: 200px;">
                        <img src="{{ asset('assets/images/APMC.png') }}" alt="APMC" class="img-fluid"
                            style="max-height: 100px; object-fit: contain;">
                    </div>
                </a>
            </div>

        </div>
    </div>


    <section class="py-5">
        <div class="container">
            <div class="row align-items-stretch g-4">
                <!-- Left: Image -->
                <div class="col-lg-6 d-flex">
                    <div class="rounded-4 shadow overflow-hidden w-100 h-100">
                        <img src="{{ asset('assets/images/2s.webp') }}" alt="Guiding Values" class="img-fluid w-100 h-100"
                            style="object-fit: cover;">
                    </div>
                </div>

                <!-- Right: Vision & Mission -->
                <div class="col-lg-6 d-flex">
                    <div class="d-flex flex-column justify-content-center w-100 h-100">
                        <div class="mb-4">
                            <h2 class="fw-bold text-primary">What Guides Our Community</h2>
                            <p class="text-muted">Our Vision and Mission reflect the heart of Two Serendra.</p>
                        </div>

                        <!-- Vision -->
                        <div class="bg-white rounded-4 shadow-sm p-4 border-start border-4 border-primary mb-4">
                            <h4 class="text-primary mb-3"><i class="fa fa-eye me-2"></i>Our Vision</h4>
                            <p class="mb-0" style="text-align: justify;">
                                Two Serendra shall be the premier residence of choice in Bonifacio Global City, thereby
                                increasing its value.
                            </p>
                        </div>

                        <!-- Mission -->
                        <div class="bg-white rounded-4 shadow-sm p-4 border-start border-4 border-success">
                            <h4 class="text-success mb-3"><i class="fa fa-bullseye me-2"></i>Our Mission</h4>
                            <p style="text-align: justify;">
                                In order to achieve the Vision, the Council shall pursue the following missions:
                            </p>
                            <ul class="ps-3" style="text-align: justify;">
                                <li>Fair, consistent and reasonable house rules and security regulations.</li>
                                <li>Efficient and customer-focused Administration Office.</li>
                                <li>Competent management of community funds and transparent decision-making.</li>
                                <li>Well-managed and maintained community facilities that residents can enjoy.</li>
                                <li>An inclusive community where everybody belongs.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>




@endsection