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
                ← Back to list
            </a>
        </div>

        <div class="card border rounded-4 shadow-sm" style="background-color: #f4faff52;">
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="rounded-4 overflow-hidden shadow-sm border">
                            @if(!empty($activity->activity_image))
                                <img src="{{ asset('assets/images/activities/' . $activity->activity_image) }}"
                                    class="img-fluid w-100" alt="{{ $activity->activity_name }}">
                            @else
                                <img src="{{ asset('assets/images/no-image.jpg') }}" class="img-fluid w-100" alt="No Image">
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6 d-flex flex-column justify-content-between">
                        <div>
                            <h3 class="fw-bold mb-1">{{ strtoupper($activity->activity_name) }}</h3>

                            @if(isset($activity->activity_status) && $activity->activity_status == 0)
                                <span class="badge bg-danger mb-2">Unavailable</span>
                            @endif

                            @if(!empty($activity->activity_description))
                                <div class="mt-3 mb-3 p-3 activity-description editor-content">
                                    {!! $activity->activity_description !!}
                                </div>
                            @endif

                        </div>
                        <div>
                            @auth
                                <button type="button"
                                    class="btn customBtn AddNewBooking 
                                                                                                    @if ($activity->activity_status == 0) btn-secondary @else btn-outline-primary @endif"
                                    style="@if ($activity->activity_status == 0) cursor: not-allowed; opacity: 0.6; @else background-color: #008b26; border-color: #008b26; color: white; font-weight: bold; @endif"
                                    @if ($activity->activity_status == 0) disabled @endif
                                    data-bs-target="#modalActivity{{ $activity->id }}" data-activity-id="{{ $activity->id }}">
                                    BOOK NOW
                                </button>
                            @else
                                @if($activity->activity_status == 0)
                                    <a class="btn btn-secondary customBtn disabled" style="pointer-events: none; opacity: 0.6;">
                                        Book Now
                                    </a>
                                @else
                                    <a href="{{ route('login', ['redirect' => url()->current()]) }}"
                                        class="btn btn-primary customBtn" >
                                        Book Now
                                    </a>
                                @endif
                            @endauth
                            <button type="button"
                                class="btn customBtn SlotCheckingModalUserbBtn btn-secondary" style="color: white;">
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
                            <div class="card shadow featured-card h-100 position-relative" style="border-radius: 5px;">
                                <a href="{{ route('booking.full.details.activity', ['type' => 'amenity', 'activity_id' => $suggestion->id]) }}"
                                    class="text-decoration-none text-dark">

                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            @if(!empty($suggestion->activity_image))
                                                <img src="{{ asset('assets/images/activities/' . $suggestion->activity_image) }}"
                                                    alt="{{ $suggestion->activity_name }} Image" class="featured-img" loading="lazy">
                                            @else
                                                <p class="text-muted">No Image</p>
                                            @endif
                                        </div>

                                        <h5 class="card-title fw-bold text-start mb-1 text-dark" style="font-size: 1.1rem;">
                                            {{ strtoupper($suggestion->activity_name) }}
                                        </h5>
                                    </div>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- <div id="loadingOverlay"
                        style="
                            display: none; /* 🔥 Keep this as default */
                            position: fixed;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 100%;
                            background: rgba(255, 255, 255, 0.7);
                            z-index: 2000;
                            justify-content: center;
                            align-items: center;
                                                                                                                                            ">
                        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                 -->


    <style>
        .thumb-img:hover {
            opacity: 0.85;
            border: 2px solid #007bff;
        }

        /* #loadingOverlay {
                            display: none;
                            position: fixed;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 100%;
                            background: rgba(255, 255, 255, 0.7);
                            z-index: 2000;
                            display: flex;

                            justify-content: center;
                            align-items: center;
                        } */
    </style>

    @include('frontend.modal.slot-checking-user-modal')
    @auth
        @include('frontend.modal.activity-booking-modal')
    @endauth
@endsection