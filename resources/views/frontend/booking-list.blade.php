@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row mt-5">
        <!-- Filter Sidebar (No Card) -->
        <div class="col-md-3 mb-4">
            <h5 class="mb-3">Filter By</h5>
            <form method="GET" action="{{ route('booking.list') }}">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="category" id="category_all"
                           value="" {{ $category == '' ? 'checked' : '' }} onchange="this.form.submit()">
                    <label class="form-check-label" for="category_all">All</label>
                </div>

                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="category" id="category_function_room"
                           value="function_room" {{ $category == 'function_room' ? 'checked' : '' }} onchange="this.form.submit()">
                    <label class="form-check-label" for="category_function_room">Function Rooms</label>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="radio" name="category" id="category_amenity"
                           value="amenity" {{ $category == 'amenity' ? 'checked' : '' }} onchange="this.form.submit()">
                    <label class="form-check-label" for="category_amenity">Amenities</label>
                </div>
            </form>
        </div>

        <!-- Items Display -->
        <div class="col-md-9">
            <h5 class="mb-4">
                @if($category == 'function_room')
                    Function Rooms
                @elseif($category == 'amenity')
                    Amenities
                @else
                    All (Function Rooms & Amenities)
                @endif
            </h5>

            <div class="row">
                @forelse($items as $item)
                    <div class="col-md-4 mb-4">
                        <a href="{{ route('booking.full.details', ['type' => $item->type, 'id' => $item->id]) }}"
                           class="text-decoration-none text-dark">
                            <div class="card h-100 shadow-lg border-0 hover-card position-relative">
                                @if ($item->firstImage)
                                    <img src="{{ asset('assets/images/uploads/' . $item->imageFolder . '/images/' . $item->firstImage->image) }}" class="card-img-top">
                                @else
                                    <img src="{{ asset('assets/images/no-image.jpg') }}" class="card-img-top" alt="No Image">
                                @endif

                                @if($item->function_room_status == 0)
                                    <span class="badge bg-danger badge-forge position-absolute top-0 start-0 m-2">Unavailable</span>
                                @endif

                                {{-- ✅ Discount Badge --}}
                                @if($item->type === 'function_room' && $item->discount > 0)
                                    <span class="badge bg-danger position-absolute top-0 end-0 m-2">
                                       {{ rtrim(rtrim($item->discount, '0'), '.') }}% OFF
                                    </span>
                                @endif

                                <div class="card-body">
                                    <h5 class="card-title">{{ $item->function_room_name ?? $item->amenity_name }}</h5>

                                    @if($item->type === 'function_room' && $item->discount > 0)
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-muted text-decoration-line-through">
                                                ₱{{ number_format($item->function_room_rate, 2) }}/hr
                                            </span>
                                            <span class="text-danger fw-bold">
                                                ₱{{ number_format($item->discounted_rate, 2) }}/hr
                                            </span>
                                        </div>
                                    @else
                                        <p class="card-text fw-bold mb-0">
                                            ₱{{ number_format($item->function_room_rate ?? $item->amenity_rate, 2) }}/hr
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </a>
                    </div>
                @empty
                    <p class="text-muted">Coming soon.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>


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
        box-shadow: 0px 6px 20px rgba(0,0,0,0.15);
    }
    .hover-card:hover img {
        transform: scale(1.05);
    }
</style>
@endsection
