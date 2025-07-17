@extends('layouts.frontend')
@section('content')
    <style>
        .img-frame {
            background-color: #008b26;
            border-radius: 15px !important;
            padding: 10px !important;
        }
    </style>
    <div class="container-fluid page-header py-5 my-5">
        <div class="container text-center">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="p-5 text-dark"
                        style="background-color: rgba(255, 255, 255, 0.8); border-radius: 10px 10px 0 0;">
                        <h1 class="display-6 text-uppercase mb-3 text-dark">OUR TEAM</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb justify-content-center mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('home') }}"
                                        style="color: #00c440 !important; font-weight: 600;">Home</a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page" style="color: #222; font-weight: 600;">
                                    Our Team
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="container py-5" style="font-family: 'Poppins', sans-serif;">

        <div class="w-100 mb-3">
            <img src="{{ asset('assets/images/our-team/2smanagers.jpg') }}" alt="Team Banner" class="img-fluid w-100"
                style="height: 100%; object-fit: cover;">
        </div>

        <!-- Department 1 -->
        <div class="row mb-5 align-items-center">
            <!-- Image -->
            <div class="col-md-6 mb-4 mb-md-0">
                <div class="overflow-hidden rounded-4 shadow-sm">
                    <img src="{{ asset('assets/images/our-team/concierge1.webp') }}" class="img-fluid w-100" alt="Concierge"
                        style="object-fit: cover; height: 350px; border: 2px solid #dee2e6; border-radius: 0.5rem; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);">

                </div>
            </div>
            <!-- Text -->
            <div class="col-md-6">
                <h2 class="mb-3" style="font-family: 'Dancing Script', cursive; font-size: 2.5rem; color: #4e3f30;">
                    Concierge
                </h2>
                <p class="text-dark fs-5 lh-lg" style="text-align: justify;">
                    The concierge serves as a professional and knowledgeable point of contact, providing personalized
                    assistance to the residents by managing a wide range of requests, including reservation bookings,
                    transportation arrangements, and tailored local recommendations. Committed to delivering exceptional
                    service, the concierge ensures each residents needs and preferences are met with efficiency, discretion,
                    and courtesy, contributing to a seamless and enjoyable experience.
                </p>
            </div>
        </div>

        <!-- Department 2 -->
        <div class="row mb-5 align-items-center flex-md-row-reverse">
            <!-- Image -->
            <div class="col-md-6 mb-4 mb-md-0">
                <div class="overflow-hidden rounded-4 shadow-sm">
                    <img src="{{ asset('assets/images/our-team/aa3.webp') }}" class="img-fluid w-100" alt="Admin Assistant"
                        style="object-fit: cover; height: 350px; border: 2px solid #dee2e6; border-radius: 0.5rem; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);">

                </div>
            </div>
            <!-- Text -->
            <div class="col-md-6">
                <h2 class="mb-3" style="font-family: 'Dancing Script', cursive; font-size: 2.5rem; color: #4e3f30;">
                    Admin Assistant
                </h2>
                <p class="text-dark fs-5 lh-lg" style="text-align: justify;">
                    The Administrative Assistant plays a key role in ensuring the smooth operation of daily activities by
                    effectively managing correspondence, organizing documentation, and maintaining accurate records. As the
                    primary point of contact for residents and visitors, the assistant upholds a high standard of
                    communication and professionalism, contributing to a well-organized and welcoming environment.
                </p>
            </div>
        </div>

        <!-- Department 3 -->
        <div class="row mb-5 align-items-center">
            <!-- Image -->
            <div class="col-md-6 mb-4 mb-md-0">
                <div class="overflow-hidden rounded-4 shadow-sm">
                    <img src="{{ asset('assets/images/our-team/engr2.webp') }}" class="img-fluid w-100" alt="IT Specialist"
                        style="object-fit: cover; height: 350px; border: 2px solid #dee2e6; border-radius: 0.5rem; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);">

                </div>
            </div>
            <!-- Text -->
            <div class="col-md-6">
                <h2 class="mb-3" style="font-family: 'Dancing Script', cursive; font-size: 2.5rem; color: #4e3f30;">
                    Engineering
                </h2>
                <p class="text-dark fs-5 lh-lg" style="text-align: justify;">
                    The Engineering Team is responsible for ensuring the safety, functionality, and efficiency of all
                    building systems within the condominium. Their scope of work includes the operation and maintenance of
                    electrical, mechanical, plumbing, and HVAC systems. They conduct routine inspections, manage preventive
                    and corrective maintenance activities, and respond promptly to technical concerns. At Two Serendra, the
                    Engineering Team plays a critical role in maintaining the overall quality and comfort of residential
                    living by ensuring that all facilities and equipment operate reliably and efficiently at all times.X
                </p>
            </div>
        </div>

        <!-- Department 4 -->
        <div class="row mb-5 align-items-center flex-md-row-reverse">
            <!-- Image -->
            <div class="col-md-6 mb-4 mb-md-0">
                <div class="overflow-hidden rounded-4 shadow-sm">
                    <img src="{{ asset('assets/images/our-team/it.webp') }}" class="img-fluid w-100" alt="IT Specialist"
                        style="object-fit: cover; height: 350px; border: 2px solid #dee2e6; border-radius: 0.5rem; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);">

                </div>
            </div>
            <!-- Text -->
            <div class="col-md-6">
                <h2 class="mb-3" style="font-family: 'Dancing Script', cursive; font-size: 2.5rem; color: #4e3f30;">
                    IT Specialist
                </h2>
                <p class="text-dark fs-5 lh-lg" style="text-align: justify;">
                    The IT Specialists are responsible for maintaining the security, stability, and optimal performance of
                    all IT systems at Two Serendra. They provide expert technical support, manage network infrastructure,
                    resolve technical issues, and ensure the reliable and secure delivery of digital services to both staff
                    and residents upholding the highest standards of operational efficiency and data integrity.
                </p>
            </div>
        </div>
    </div>

@endsection