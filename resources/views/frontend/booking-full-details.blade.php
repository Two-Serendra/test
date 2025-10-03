@extends('layouts.frontend')

@section('content')
    <div class="container my-5">
        <!-- Go Back Button -->
        <div class="mb-3">
            <a href="{{ route('booking.list') }}" class="text-decoration-none text-primary">
                ← Back to List
            </a>
        </div>

        <!-- Card Container -->
        <div class="card border rounded-4 shadow-sm" style="background-color: #f4faff52;">
            <div class="card-body p-4">
                <div class="row">
                    <!-- LEFT COLUMN: Image Gallery -->
                    <div class="col-md-6">
                        <div class="border rounded shadow-sm mb-3">
                            <img id="mainImage" src="{{ $item->images->count()
        ? asset('assets/images/uploads/' . ($type === 'function_room' ? 'function-rooms' : 'amenities') . '/images/' . $item->images->first()->image)
        : asset('assets/images/no-image.jpg') }}" class="img-fluid rounded w-100"
                                style="max-height: 400px; object-fit: cover;" alt="Preview Image">
                        </div>

                        <div class="d-flex flex-wrap gap-2 justify-content-center mb-3 mb-md-0">
                            @if($item->images && $item->images->count())
                                @foreach($item->images as $image)
                                    <img src="{{ asset('assets/images/uploads/' . ($type === 'function_room' ? 'function-rooms' : 'amenities') . '/images/' . $image->image) }}"
                                        class="img-thumbnail thumb-img"
                                        style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;"
                                        onclick="document.getElementById('mainImage').src=this.src">
                                @endforeach
                            @else
                                <img src="{{ asset('assets/images/no-image.jpg') }}" class="img-thumbnail"
                                    style="width: 80px; height: 80px; object-fit: cover;">
                            @endif
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: Details -->
                    <div class="col-md-6 d-flex flex-column justify-content-between">
                        <div>
                            <h3 class="fw-bold">
                                {{ $type === 'function_room' ? $item->function_room_name : $item->amenity_name }}
                            </h3>

                            @if($item->function_room_status == 0)
                                <span class="badge bg-danger badge-forge mb-2">Unavailable</span>
                            @endif

                            @if(!empty($item->function_room_remarks))
                                <p class="text-muted"><strong>Remarks:</strong> {{ $item->function_room_remarks }}</p>
                            @endif

                            @if($type === 'function_room')
                                <p><strong>Capacity:</strong> {{ $item->function_room_capacity }}</p>
                                <div class="mb-2">
                                    <h6 class="fw-semibold">Description</h6>
                                    <p>{{ $item->function_room_description ?? 'No description available.' }}</p>
                                </div>
                                <div class="mb-2">
                                    <h6 class="fw-semibold">Policy</h6>
                                    <p>{{ $item->function_room_policy ?? 'No policy information available.' }}</p>
                                </div>
                            @else
                                <div class="mb-2">
                                    <h6 class="fw-semibold">Description</h6>
                                    <p>{{ $item->amenity_description ?? 'No description available.' }}</p>
                                </div>
                                <div class="mb-2">
                                    <h6 class="fw-semibold">Policy</h6>
                                    <p>{{ $item->amenity_policy ?? 'No policy information available.' }}</p>
                                </div>
                            @endif
                        </div>

                        <div>
                            @if($type === 'function_room' && $item->discount > 0)
                                <div class="mb-1 d-flex align-items-center gap-2">
                                    <span class="text-muted text-decoration-line-through fs-5">
                                        ₱{{ number_format($item->function_room_rate, 2) }}
                                    </span>
                                    <span class="badge bg-danger">
                                        {{ rtrim(rtrim(number_format($item->discount, 2), '0'), '.') }}% OFF
                                    </span>
                                </div>
                                <span class="text-danger fw-bold fs-4 d-block">
                                    ₱{{ number_format($item->discounted_rate, 2) }}/hr
                                </span>
                            @else
                                <p class="fw-bold fs-4 mb-3 text-dark">
                                    ₱{{ number_format($type === 'function_room' ? $item->function_room_rate : $item->amenity_rate, 2) }}/hour
                                </p>
                            @endif

                            @if($type === 'function_room' && !empty($item->function_room_360))
                                <button type="button" class="btn btn-secondary text-white me-2 360View customBtn"
                                    data-img="{{ asset('assets/images/uploads/function-rooms/360/' . $item->function_room_360) }}"
                                    data-name="{{ $item->function_room_name }}">
                                    View 360 <i class='bx bx-refresh'></i>
                                </button>
                            @endif

                            @auth
                                <button type="button" class="btn btn-primary text-white me-2 customBtn BookFunctionRoomBtn" data-id="{{ $item->id }}" 
                                    data-name="{{ $item->function_room_name }}" @if($item->function_room_status == 0) disabled @endif>
                                    Book Now
                                </button>
                            @else
                                @guest
                                    <a href="{{ route('login', ['redirect' => url()->current()]) }}"
                                        class="btn btn-primary text-white me-2 customBtn @if($item->function_room_status == 0) disabled @endif">
                                        Book Now
                                    </a>
                                @endguest
                            @endauth
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
                <h5 class="fw-bold mb-4">
                    You may also like
                    <!-- @if($type === 'function_room')
                                Function Rooms
                            @else
                                Amenities
                            @endif -->
                </h5>
                <div class="row z-1 position-relative">
                    @foreach($suggestions as $suggestion)
    <div class="col-sm-6 col-md-4 col-lg-3 mb-4">
        <div class="card shadow featured-card h-100 position-relative" style="border-radius: 5px;">
            <a href="{{ route('booking.full.details', ['type' => $type, 'id' => $suggestion->id]) }}"
               class="text-decoration-none text-dark">
               
                <!-- 🔹 Discount Badge -->
                @if($type === 'function_room' && $suggestion->discount > 0)
                    <span class="badge bg-danger position-absolute top-0 end-0 m-2">
    {{ rtrim(rtrim($suggestion->discount, '0'), '.') }}% OFF
</span>
                @endif

                <div class="card-body text-center">
                    <div class="mb-3">
                        @if($suggestion->images->first())
                            <img src="{{ asset('assets/images/uploads/' . ($type === 'function_room' ? 'function-rooms' : 'amenities') . '/images/' . $suggestion->images->first()->image) }}"
                                 alt="{{ $type === 'function_room' ? 'Function Room' : 'Amenity' }} Image"
                                 class="featured-img" loading="lazy">
                        @else
                            <p class="text-muted">No Image</p>
                        @endif
                    </div>

                    <h5 class="card-title fw-bold text-start mb-1 text-dark" style="font-size: 1.1rem;">
                        {{ $type === 'function_room' ? $suggestion->function_room_name : $suggestion->amenity_name }}
                    </h5>

                    <!-- 🔹 Price Layout -->
                    @if($type === 'function_room' && $suggestion->discount > 0)
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted text-decoration-line-through">
                                ₱{{ number_format($suggestion->function_room_rate, 2) }}/hr
                            </span>
                            <span class="text-danger fw-bold">
                                ₱{{ number_format($suggestion->discounted_rate, 2) }}/hr
                            </span>
                        </div>
                    @else
                        <p class="fw-semibold text-start mb-2 text-dark" style="font-size: 0.95rem;">
                            <span class="text-uppercase fw-bold">Rate:</span>
                            <span class="ms-1 fw-bold">
                                ₱{{ number_format($type === 'function_room' ? $suggestion->function_room_rate : $suggestion->amenity_rate, 2) }}/hr
                            </span>
                        </p>
                    @endif
                </div>
            </a>
        </div>
    </div>
@endforeach

                </div>
            </div>
        @endif
    </div>
    <div id="loadingOverlay" style="
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



    <style>
        .thumb-img:hover {
            opacity: 0.85;
            border: 2px solid #007bff;
        }

        #loadingOverlay {
            display: none;
            /* Hidden by default */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.7);
            z-index: 2000;
            display: flex;
            /* This centers it */
            justify-content: center;
            align-items: center;
        }
    </style>

    @include('frontend.modal.360-modal-view')
    @auth
        @include('frontend.modal.function-room-booking-modal')
    @endauth
@endsection