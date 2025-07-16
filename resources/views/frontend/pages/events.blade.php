@extends('layouts.frontend')
@section('content')
    <div class="container-fluid page-header pt-5 mb-6">
        <div class="container text-center pt-5">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="p-5 text-dark"
                        style="background-color: rgba(255, 255, 255, 0.8); border-radius: 10px 10px 0 0;">
                        <!-- Enhanced Title -->
                        <h1 class="display-6 text-uppercase mb-3 text-dark">EVENTS</h1>
                        <!-- Breadcrumb -->
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb justify-content-center mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('home') }}"
                                        style="color: #00c440 !important; font-weight: 600;">Home</a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page" style="color: #222; font-weight: 600;">
                                    Events
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-5">

        {{-- Upcoming Events --}}
        <div class="row g-4" id="upcoming-events">
            @forelse ($upcomingEvents as $event)
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <a href="{{ route('show.event.details', $event->id) }}" class="text-decoration-none text-dark">
                        <div class="card h-100 border-0 shadow-lg rounded-4 position-relative overflow-hidden">
                            {{-- Badge at top-left --}}
                            <span class="badge bg-primary position-absolute top-0 start-0 ms-2 mt-2">Upcoming</span>

                            <div class="card-img-top" style="height: 250px; overflow: hidden;">
                                <img src="{{ asset('assets/images/events/' . $event->event_image) }}" alt="Event Image"
                                    class="img-fluid w-100 h-100" style="object-fit: cover; object-position: top;">
                            </div>

                            <div class="card-body">
                                <h5 class="card-title text-uppercase fw-bold mb-2"
                                    style="font-size: 1.1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $event->event_title ?? 'No Title' }}
                                </h5>
                                {{-- Event Date under the image --}}
                                <p class="text-muted small mb-0">
                                    {{ \Carbon\Carbon::parse($event->event_date)->format('F j, Y') }}
                                </p>
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <p class="text-muted">No upcoming events available.</p>
            @endforelse
        </div>

        {{-- Past Events --}}
        <div class="mt-5">
            <hr class="mb-4">
            <div class="row g-4" id="past-events">
                @include('frontend.pages.partials.past-events-card', ['pastEvents' => $pastEvents])
            </div>

            @if ($totalPastCount > 8)
                <div class="text-center mt-4">
                    <button id="loadMoreBtn" class="btn btn-primary px-4">Load More</button>
                </div>
            @endif
        </div>

    </div>




    <style>
        .card:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            transform: translateY(-5px);
        }

        .card:hover img {
            transform: scale(1.05);
        }
    </style>

@endsection