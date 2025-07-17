@extends('layouts.frontend')
@section('content')


    <div class="row m-0 p-0">
        <section class="hero-section position-relative d-flex align-items-center justify-content-center text-white"
            style="background: url('{{ asset('assets/images/twoserendra3.jpg') }}') center center / cover no-repeat; 
                                                                                                                                                                                                                                                                                                    height: 100vh; width: 100vw;">

            <!-- Dark overlay -->
            <div class="position-absolute top-0 start-0 w-100 h-100"
                style="background-color: rgba(0, 0, 0, 0.5); z-index: 1;"></div>

            <!-- Animated text -->
            <div class="text-center position-relative" style="z-index: 2;">
                <h1 class="display-5 fw-semibold text-light text-shadow animate__animated animate__fadeInDown mb-2"
                    style="font-family: 'Poppins', sans-serif; letter-spacing: 0.5px;">
                    Welcome to Two Serendra
                </h1>

                <div class="px-3 py-2 d-inline-block animate__animated animate__fadeInUp animate__delay-1s">
                    <p class="lead mb-0" style="font-family: 'Poppins', sans-serif; font-size: 1.2rem; color: #f8f9fa;">
                        Built for Comfort, Driven by Sustainability
                    </p>
                </div>
            </div>
    </div>
    </section>
    </div>

    <div class="container-fluid pt-6 pb-6">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-6">
                    <div class="about-img">
                        <img class="img-fluid w-100" src="{{ asset('assets/images/tower.jpeg') }}"
                            style="height: 520px; object-fit: cover;">
                    </div>
                </div>
                <div class="col-lg-6 d-flex align-items-center" style="min-height: 520px;">
                    <div class="w-100">
                        <h1 class="display-6 text-uppercase mb-4">Two Serendra -Alveo by Ayala Land</h1>
                        <p class="mb-4" style="text-align: justify;">
                            Nestled in the heart of Bonifacio Global City (BGC), Two Serendra offers the perfect blend of
                            modern urban living and serene residential comfort. Strategically located within one of Metro
                            Manila’s most vibrant and dynamic districts, it places residents at the center of a thriving
                            community known for its expansive green spaces, walkable avenues, diverse dining options, and a
                            deep appreciation for art and culture. BGC is also home to premier health and educational
                            institutions, making Two Serendra an ideal residence for those seeking a well-rounded,
                            connected, and inspiring lifestyle.
                        </p>

                        <!-- Amenities in Two Columns -->
                        <div class="row">
                            <div class="col-md-6">
                                <p><i class="fa-solid fa-cube"></i> Indoor and Outdoor Playground</p>
                                <p><i class="fa-solid fa-basketball"></i> Basketball, Badminton, Tennis, Pickleball and
                                    Futsal Courts
                                </p>
                                <p><i class="fa-solid fa-water-ladder"></i> Kiddie, Adult and Lap Pools</p>
                            </div>
                            <div class="col-md-6">
                                <p><i class="fa-solid fa-dumbbell"></i> Gym</p>
                                <p><i class="fa-solid fa-building-columns"></i> Function and Board Rooms</p>
                                <p><i class="fa-solid fa-leaf"></i> Lush Landscaped Gardens</p>
                            </div>
                        </div>

                        <div class="ms-auto mt-3">
                            <a href="{{ route('about') }}" class="btn btn-primary custom-btn">Learn More</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="container-fluid py-5 px-3 px-md-5 position-relative">
            <h4 class="fw-bold mb-4">Featured Function Rooms</h4>
            <div class="row z-1 position-relative">
                @foreach ($featuredFunctionRooms as $featuredFunctionRoom)
                    <div class="col-sm-6 col-md-4 col-lg-3 mb-4">
                        <div class="card shadow featured-card h-100" style="border-radius: 5px;">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    @if ($featuredFunctionRoom->function_room_image)
                                        <img src="{{ asset('assets/images/function-rooms/' . $featuredFunctionRoom->function_room_image) }}"
                                            alt="Amenity Image" class="featured-img" loading="lazy">
                                    @else
                                        <p class="text-muted">No Image</p>
                                    @endif
                                </div>

                                <h5 class="card-title fw-bold text-start mb-1 text-dark" style="font-size: 1.1rem;">
                                    {{ $featuredFunctionRoom->function_room_name ?? 'N/A' }}
                                </h5>

                                <p class="fw-semibold text-start mb-2 text-dark" style="font-size: 0.95rem;">
                                    <span class="text-uppercase">Section:</span>
                                    <span class="ms-1">{{ $featuredFunctionRoom->function_room_section ?? 'N/A' }}</span>
                                </p>

                                <div class="d-flex align-items-start justify-content-start mt-2">
                                    <button type="button" class="btn btn-sm btn-primary 360View" style="border-radius: 5px;"
                                        data-img="{{ asset('assets/images/function-rooms-360/' . $featuredFunctionRoom->function_room_360) }}"
                                        data-name="{{ $featuredFunctionRoom->function_room_name }}">
                                        View 360 <i class='bx bx-refresh'></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>



        <div class="container py-5">
            <div class="row g-4">
                <!-- Reusable Card -->
                @php
                    $items = [
                        ['img' => 'sections.avif', 'title' => 'SECTIONS', 'text' => 'Our belief is that communities are built when people work together. Two Serendra is formed of sections from low-rise to high-rise.', 'route' => route('sections')],
                        ['img' => 'facilities.avif', 'title' => 'Gallery', 'text' => 'Check here for a list of amenities, function spaces and community spaces.', 'route' => route('gallery')],
                        ['img' => 'events.avif', 'title' => 'EVENTS', 'text' => 'Community events bring people together. Check here for a list of upcoming events. Remember to RSVP with ADMIN.', 'route' => route('events')],
                        ['img' => 'maps of the area.avif', 'title' => 'MAPS OF THE AREA', 'text' => "Find out what's available in the local area.", 'route' => route('maps')],
                    ];
                @endphp

                @foreach ($items as $item)
                    <div class="col-md-6">
                        <div class="timeline-item">
                            <div class="timeline-content">
                                <img src="{{ asset('assets/images/' . $item['img']) }}" class="timeline-img"
                                    alt="{{ $item['title'] }}">
                                <div class="timeline-text">
                                    <h5>{{ $item['title'] }}</h5>
                                    <p class="text-justify">{{ $item['text'] }}</p>
                                    <a class="read-more-link text-decoration-none d-inline-flex align-items-center mt-2"
                                        href="{{ $item['route'] }}">
                                        Read More
                                        <i class="bi bi-arrow-right ms-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>


        @include('frontend.modal.360-modal-view')
@endsection