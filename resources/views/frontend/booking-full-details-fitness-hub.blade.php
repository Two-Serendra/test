@extends('layouts.frontend')

<style>
    .text-justified {
        text-align: justify !important;
        text-justify: inter-word !important;
    }

    input.flatpickr-input[readonly] {
        background-color: #fff !important;
        cursor: pointer;
    }
</style>

@section('content')


    <div class="container my-5">

        <div class="mb-3">
            <a href="{{ url()->previous() ?? route('booking.list') }}"
                class="text-decoration-none text-primary fw-semibold">
                ← Back
            </a>
        </div>

        <div class="card border rounded-4 shadow-sm" style="background-color: #f4faff52;">
            <div class="card-body p-4">
                @php
                    $isDisabled = isset($fitness_hub->fitness_hub_status) && $fitness_hub->fitness_hub_status == 0;
                @endphp

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="rounded-4 overflow-hidden shadow-sm border">
                            @if(!empty($fitness_hub->fitness_hub_image))
                                <img src="{{ asset('assets/images/fitness-hubs/' . $fitness_hub->fitness_hub_image) }}"
                                    class="img-fluid w-100" alt="{{ $fitness_hub->fitness_hub_name }}">
                            @else
                                <img src="{{ asset('assets/images/no-image.jpg') }}" class="img-fluid w-100" alt="No Image">
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6 d-flex flex-column justify-content-between">
                        <div>
                            <h3 class="fw-bold mb-1">{{ strtoupper($fitness_hub->fitness_hub_name) }}</h3>

                            @if($isDisabled)
                                                <span class="badge bg-danger mb-2">
                                                    {{ $isDisabled
                                ? ($fitness_hub->fitness_hub_remarks ?? 'Unavailable')
                                : 'Unavailable' 
                                                                                                                                                                                                        }}
                                                </span>
                            @endif

                            @if(!empty($fitness_hub->fitness_hub_description))
                                <div class="mt-3 mb-3 p-3 fitness_hub-description editor-content">
                                    {!! $fitness_hub->fitness_hub_description !!}
                                </div>
                            @endif

                        </div>
                        <div>
                            @auth
                            <button type="button"
                                class="btn customBtn AddNewBookingFitnessHub {{ $isDisabled ? 'btn-secondary' : 'btn-outline-primary' }}"
                                style="{{ $isDisabled
                                    ? 'cursor: not-allowed; opacity: 0.6;'
                                    : 'background-color: #008b26; border-color: #008b26; color: white; font-weight: bold;' }}"
                                {{ $isDisabled ? 'disabled' : '' }}
                                data-fitness-hub-id="{{ $fitness_hub->id }}">
                                BOOK NOW
                            </button>
                            @else
                                @if($isDisabled)
                                    <a class="btn btn-secondary customBtn disabled" style="pointer-events: none; opacity: 0.6;">
                                        Book Now
                                    </a>
                                @else
                                    <a href="{{ route('login', ['redirect' => url()->current()]) }}"
                                        class="btn btn-primary customBtn">
                                        Book Now
                                    </a>
                                @endif
                            @endauth
                            <button type="button" class="btn customBtn SlotCheckingModalUserbBtn btn-secondary"
                                style="color: white;">
                                Check Slots
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Suggested Items -->
    <div class="container-fluid py-5 px-3 px-md-5 position-relative">
        @if($suggestions->count())
            <div class="">
                <h5 class="fw-bold mb-4">You may also like</h5>
                <div class="row z-1 position-relative">
                    @foreach($suggestions as $suggestion)


                        <div class="col-sm-6 col-md-4 col-lg-3 mb-4">
                            <a href="{{ route('booking.full.details.fitness_hub', ['type' => 'amenity', 'fitness_hub_id' => $suggestion->id]) }}"
                                class="text-decoration-none text-dark">
                                <div class="card shadow featured-card h-100 position-relative" style="border-radius: 5px;">

                                    {{-- 🔴 Overlay (same as your amenity cards) --}}
                                    @if($suggestion->fitness_hub_status == 0)
                                        <div class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-center align-items-center"
                                            style="background: rgba(0,0,0,0.6); z-index: 2; border-radius: 5px;">

                                            <span class="badge bg-danger mb-2">Unavailable</span>

                                            <small class="text-white text-center px-3">
                                                {{ $suggestion->fitness_hub_remarks ?? 'Not Available' }}
                                            </small>
                                        </div>
                                    @endif
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            @if(!empty($suggestion->fitness_hub_image))
                                                <img src="{{ asset('assets/images/fitness_hubs/' . $suggestion->fitness_hub_image) }}"
                                                    alt="{{ $suggestion->fitness_hub_name }} Image" class="featured-img" loading="lazy">
                                            @else
                                                <p class="text-muted">No Image</p>
                                            @endif
                                        </div>

                                        <h5 class="card-title fw-bold text-start mb-1 text-dark" style="font-size: 1.1rem;">
                                            {{ strtoupper($suggestion->fitness_hub_name) }}
                                        </h5>
                                    </div>

                                </div>
                            </a>
                        </div>

                    @endforeach
                </div>
            </div>
        @endif
    </div>


    <style>
        .thumb-img:hover {
            opacity: 0.85;
            border: 2px solid #007bff;
        }
    </style>
    @include('frontend.modal.fitness-hub-booking-modal')
@endsection