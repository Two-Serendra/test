@extends('layouts.frontend')
@section('content')

    <style>
        .contact-bg {
            background: linear-gradient(to right, #e6f4e6, #f4faf4);
            /* light green gradient */
        }

        .contact-bg .card {
            background-color: #ffffff;
            /* keep cards white for contrast */
        }

        .contact-bg a {
            color: #0a6847;
        }

        .contact-bg a:hover {
            color: #075e3b;
            text-decoration: underline;
        }

        .bg-image-opacity {
            position: relative;
            background-color: #f5f8fa;
            border-top: 1px solid #dee2e6;
            border-bottom: 1px solid #dee2e6;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-image: url('{{ asset("assets/images/tower.jpeg") }}');
            overflow: hidden;
            color: #fff;

        }

        .bg-image-opacity::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: inherit;
            background-size: inherit;
            background-position: inherit;
            background-repeat: inherit;
            filter: brightness(0.5);
            opacity: 0.6;
            z-index: 0;
        }

        .bg-image-opacity::after {
            content: "";
            position: absolute;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.4);
            z-index: 1;
        }

        .bg-image-opacity>* {
            position: relative;
            z-index: 2;

        }
    </style>


    <div class="container-fluid page-header pt-5 mb-6">
        <div class="container text-center pt-5">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="p-5 text-dark"
                        style="background-color: rgba(255, 255, 255, 0.8); border-radius: 10px 10px 0 0;">

                        <!-- Enhanced Title -->
                        <h1 class="display-6 text-uppercase mb-3 text-dark">CONTACT</h1>
                        <!-- Breadcrumb -->
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb justify-content-center mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('home') }}"
                                        style="color: #00c440 !important; font-weight: 600;">Home</a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page" style="color: #222; font-weight: 600;">
                                    Contact
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Form and Map Section -->

    <div class="pt-6 pb-6 bg-image-opacity">
        <div class="container-fluid appoinment py-5">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 class="fw-bold text-light">
                        <i class="fas fa-envelope-open-text text-light me-2"></i>Get in Touch
                    </h2>
                    <p class="text-light">We’d love to hear from you. Please fill out the form below to send us a message.
                    </p>
                </div>

                <div class="row gx-5 d-flex align-items-stretch">
                    <!-- Map Column -->
                    <div class="col-lg-6 d-flex">
                        <div class="rounded shadow-sm overflow-hidden w-100 h-100">
                            <iframe src="https://maps.google.com/maps?q=Two%20Serendra&t=&z=15&ie=UTF8&iwloc=&output=embed"
                                class="w-100 h-100 border-0" style="min-height: 100%;" allowfullscreen="" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    </div>

                    <!-- Form Column -->
                    <div class="col-lg-6 d-flex">
                        <div class="bg-white p-5 shadow rounded border border-light-subtle w-100">
                            <h4 class="mb-4">Send Us a Message</h4>
                            <form class="contact-form" method="POST" action="{{ route('contact.send') }}">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="name" name="name"
                                                placeholder="Your Name" required>
                                            <label for="name">Your Name</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-floating">
                                            <input type="email" class="form-control" id="mail" name="email"
                                                placeholder="Your Email" required>
                                            <label for="mail">Your Email</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="mobile" name="mobile"
                                                placeholder="Your Mobile" required>
                                            <label for="mobile">Your Mobile</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="subject" name="subject"
                                                placeholder="Subject" required>
                                            <label for="subject">Subject</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <textarea class="form-control" placeholder="Leave a message here" id="message"
                                                name="inquiry" style="height: 130px" required></textarea>
                                            <label for="message">Message</label>
                                        </div>
                                    </div>
                                    <button id="submitBtn"
                                        class="btn btn-primary w-100 py-3 d-flex justify-content-center align-items-center"
                                        type="submit">
                                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"
                                            id="spinner" aria-hidden="true"></span>
                                        <span id="btnText">Submit Now</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div> <!-- end row -->
            </div>
        </div>
    </div>



    <section class="py-5 bg-light text-dark">
        <div class="container">
            <h2 class="text-uppercase text-center mb-5">Office Contacts & Information</h2>
            <div class="row g-4 align-items-stretch">
                <!-- Left Column (Single Tall Card) -->
                <div class="col-lg-6 d-flex">
                    <div class="card shadow-sm w-100">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0 text-light"><i class="bi bi-building me-2"></i>Department Emails</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li><strong>Low-Rise Residents:</strong><br>
                                    <a href="mailto:lowriseadmin@twoserendra.com">lowriseadmin@twoserendra.com</a>
                                </li>
                                <li><strong>High-Rise Residents:</strong><br>
                                    <a href="mailto:highriseadmin@twoserendra.com">highriseadmin@twoserendra.com</a>
                                </li>
                                <li><strong>Low-Rise Engineering:</strong><br>
                                    <a
                                        href="mailto:lowrise.engineering@twoserendra.com">lowrise.engineering@twoserendra.com</a>
                                </li>
                                <li><strong>High-Rise Engineering:</strong><br>
                                    <a
                                        href="mailto:highrise.engineering@twoserendra.com">highrise.engineering@twoserendra.com</a>
                                </li>
                                <li><strong>UMD Concerns:</strong><br>
                                    <a href="mailto:umd.office@twoserendra.com">umd.office@twoserendra.com</a>
                                </li>
                                <li><strong>Billing:</strong><br>
                                    <a href="mailto:finance@twoserendra.com">finance@twoserendra.com</a>
                                </li>
                                <li><strong>Payment Submission:</strong><br>
                                    <a href="mailto:payments@twoserendra.com">payments@twoserendra.com</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Right Column (Two Cards stacked, height-matched) -->
                <div class="col-lg-6 d-flex flex-column">
                    <!-- Top card that fills available space -->
                    <div class="card shadow-sm mb-4 flex-grow-1">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0 text-light"><i class="bi bi-telephone-fill me-2"></i>Office Phone Contacts</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li><strong>Low-Rise Admin:</strong> (02) 8252-5063</li>
                                <li><strong>High-Rise Admin:</strong> (02) 8256-2534</li>
                                <li><strong>Engineering:</strong> (02) 8551-1964</li>
                                <li><strong>Finance:</strong> (02) 8256-2971</li>
                                <li><strong>UMD Office:</strong> (02) 8715-5769</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Bottom card (General Emails) with normal height -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0 text-light"><i class="bi bi-envelope-fill me-2"></i>General Emails</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li><strong>Resident Services:</strong><br>
                                    <a href="mailto:resident.services@twoserendra.com">resident.services@twoserendra.com</a>
                                </li>
                                <li><strong>Circulars:</strong><br>
                                    <a href="mailto:circulars@twoserendra.com">circulars@twoserendra.com</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Your custom script -->
    <script>
        $(document).ready(function () {
            $('.contact-form').on('submit', function () {
                $('#submitBtn').prop('disabled', true);
                $('#spinner').removeClass('d-none');
                $('#btnText').text('Sending...');
            });

            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Message Sent!',
                    text: '{{ session('success') }}',
                    confirmButtonColor: '#3085d6'
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Oops!',
                    text: '{{ session('error') }}',
                    confirmButtonColor: '#d33'
                });
            @endif
                });
    </script>
@endsection