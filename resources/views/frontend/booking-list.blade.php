@extends('layouts.frontend')

@section('content')
    <div class="container">
        <div class="row mt-5">
            <!-- Filter Sidebar (No Card) -->
            <div class="col-md-3 mb-4">
                <h5 class="mb-3">Filter By</h5>

                <form method="GET" action="{{ route('booking.list') }}">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="category" id="category_function_room"
                            value="function_room" {{ $category == 'function_room' ? 'checked' : '' }}
                            onchange="this.form.submit()">
                        <label class="form-check-label" for="category_function_room">Function Rooms</label>
                    </div>

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="category" id="category_amenity" value="amenity"
                            {{ $category == 'amenity' ? 'checked' : '' }} onchange="this.form.submit()">
                        <label class="form-check-label" for="category_amenity">Amenities</label>
                    </div>

                    <!-- <div class="form-check">
                        <input class="form-check-input" type="radio" name="category" id="category_grease_trap"
                            value="grease_trap" {{ $category == 'grease_trap' ? 'checked' : '' }}
                            onchange="this.form.submit()">
                        <label class="form-check-label" for="category_grease_trap">Grease Trap</label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="category" id="category_pest_control"
                            value="pest_control" {{ $category == 'pest_control' ? 'checked' : '' }}
                            onchange="this.form.submit()">
                        <label class="form-check-label" for="category_pest_control">Pest Control</label>
                    </div> -->
                </form>
            </div>

            <!-- Items Display -->
            <div class="col-md-9">
                <h5 class="mb-4">
                    @php
                        $category = $category ?? 'function_room';
                    @endphp

                    @if($category == 'function_room')
                        Function Rooms
                    @elseif($category == 'amenity')
                        Amenities

                    @elseif($category == 'grease_trap')
                        Grease Trap

                    @elseif($category == 'pest_control')
                      Pest Control

                    @else
                        All (Function Rooms & Amenities)
                    @endif
                </h5>

                <div class="row">

                    @if($category === 'grease_trap')
                        @auth
                            <div class="col-12">
                                @include('frontend.grease-trap-booking')
                            </div>
                        @else
                            <script>
                                window.location.href = "{{ route('login', ['redirect' => route('grease.trap.booking')]) }}";
                            </script>
                        @endauth
                    @endif

                    @if($category === 'pest_control')
                        @auth
                            <div class="col-12">
                                @include('frontend.pest-control-booking')
                            </div>
                        @else
                            <script>
                                window.location.href = "{{ route('login', ['redirect' => route('pest.control.booking')]) }}";
                            </script>
                        @endauth
                    @endif

                    {{-- FUNCTION ROOMS --}}
                    @if($category === 'function_room')
                        @foreach($items->where('type', 'function_room') as $item)
                            <div class="col-md-4 mb-4">
                                <a href="{{ route('booking.full.details', ['type' => 'function_room', 'id' => $item->id]) }}"
                                    class="text-decoration-none text-dark">
                                    <div class="card h-100 shadow-lg border-0 hover-card position-relative">

                                        @if ($item->firstImage)
                                            <img src="{{ asset('assets/images/uploads/function-rooms/images/' . $item->firstImage->image) }}"
                                                class="card-img-top">
                                        @else
                                            <img src="{{ asset('assets/images/no-image.jpg') }}" class="card-img-top">
                                        @endif

                                        @if($item->function_room_status == 0)
                                            <span class="badge bg-danger position-absolute top-0 start-0 m-2">
                                                Unavailable
                                            </span>
                                        @endif

                                        @if($item->discount > 0)
                                            <span class="badge bg-danger position-absolute top-0 end-0 m-2">
                                                {{ $item->discount }}% OFF
                                            </span>
                                        @endif

                                        <div class="card-body">
                                            <h5 class="card-title">{{ $item->function_room_name }}</h5>

                                            @if($item->discount > 0)
                                                <div class="d-flex justify-content-between">
                                                    <span class="text-muted text-decoration-line-through">
                                                        ₱{{ number_format($item->function_room_rate, 2) }}
                                                    </span>
                                                    <span class="fw-bold text-danger">
                                                        ₱{{ number_format($item->discounted_rate, 2) }}
                                                    </span>
                                                </div>
                                            @else
                                                <p class="fw-bold mb-0">
                                                    ₱{{ number_format($item->function_room_rate, 2) }}/hr
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    @endif

                    {{-- ACTIVITIES (AMENITIES) --}}
                    @if($category === 'amenity')
                        @forelse($items as $activity)
                            <div class="col-md-4 mb-4">
                                <a
                                    href="{{ route('booking.full.details.activity', ['type' => 'amenity', 'activity_id' => $activity->id]) }}">
                                    <div class="card h-100 shadow-lg border-0 hover-card position-relative">
                                        <img src="{{ asset('assets/images/activities/' . $activity->activity_image) }}"
                                            class="card-img-top" style="height: 200px; object-fit: cover;"
                                            alt="{{ $activity->activity_name }}">
                                        <div class="card-body d-flex flex-column justify-content-between">
                                            <h5 class="card-title text-black">
                                                {{ strtoupper($activity->activity_name) }}
                                            </h5>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @empty
                            <div class="col-12 text-center">
                                <p>No Active Amenities Found</p>
                            </div>
                        @endforelse

                    @endif
                </div>

            </div>
        </div>
    </div>
@endsection
{{-- Styles for Card Emphasis --}}
<style>
    .hover-card {
        transition: all 0.3s ease;
        border-radius: 10px;
        overflow: hidden;
    }

    .hover-card img {
        transition: transform 0.3s ease;
    }

    .hover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0px 6px 20px rgba(0, 0, 0, 0.15);
    }

    .hover-card:hover img {
        transform: scale(1.05);
    }
</style>