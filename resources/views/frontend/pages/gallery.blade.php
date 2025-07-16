@extends('layouts.frontend')
@section('content')
    <div class="container-fluid page-header pt-5 mb-6">
        <div class="container text-center pt-5">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="p-5 text-dark"
                        style="background-color: rgba(255, 255, 255, 0.8); border-radius: 10px 10px 0 0;">
                        <!-- Enhanced Title -->
                        <h1 class="display-6 text-uppercase mb-3 text-dark">GALLERY</h1>
                        <!-- Breadcrumb -->
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb justify-content-center mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('home') }}"
                                        style="color: #00c440 !important; font-weight: 600;">Home</a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page" style="color: #222; font-weight: 600;">
                                    Gallery
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container py-5">
        <!-- <div class="text-center mb-4">
                    <h3 class="fw-bold" style="
                            font-family: 'Poppins', sans-serif;
                            font-size: 2rem;
                            letter-spacing: 1px;
                            color: #008b26;
                            position: relative;
                            display: inline-block;
                        ">
                        Inside Two Serendra
                        <span style="
                                display: block;
                                width: 60px;
                                height: 3px;
                                background-color: #008b26;
                                margin: 8px auto 0;
                                border-radius: 5px;">
                            </span>
                    </h3>
                </div> -->

        <div class="row g-4" id="gallery">
            @foreach ($images as $index => $image)
                <div class="col-12 col-sm-6 col-lg-4 mb-4"> {{-- 1 on XS, 2 on SM, 3 on LG+ --}}
                    <div class="card h-100 overflow-hidden" style="border-radius: 5px;">
                        @if ($image->file_name)
                            <button type="button" class="btn p-0 border-0 w-100 h-100 open-gallery-modal" data-index="{{ $index }}"
                                data-img="{{ asset('assets/images/gallery/' . $image->file_name) }}">
                                <img src="{{ asset('assets/images/gallery/' . $image->file_name) }}" alt="Gallery Image"
                                    class="img-fluid w-100 h-100 gallery-img" style="object-fit: cover;">
                            </button>
                        @else
                            <div class="card-body text-center">
                                <p class="text-muted">No Image</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

    </div>
    @include('frontend.modal.image-modal')

    <script>
        window.galleryImageList = @json($images->pluck('file_name'));
    </script>
@endsection