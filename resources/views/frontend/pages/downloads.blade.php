@extends('layouts.frontend')
@section('content')
    <div class="container-fluid page-header pt-5 mb-6">
        <div class="container text-center pt-5">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="p-5 text-dark"
                        style="background-color: rgba(255, 255, 255, 0.8); border-radius: 10px 10px 0 0;">

                        <!-- Enhanced Title -->
                        <h1 class="display-6 text-uppercase mb-3 text-dark">DOWNLOADS</h1>
                        <!-- Breadcrumb -->
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb justify-content-center mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('home') }}"
                                        style="color: #00c440 !important; font-weight: 600;">Home</a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page" style="color: #222; font-weight: 600;">
                                    Downloads
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container py-5">
        <div class="row g-4">
            @forelse($groupedDownloads as $category => $downloads)
                <div class="col-md-6">
                    <div class="card shadow mb-3 bg-body rounded">
                        <div class="card-header bg-light text-dark ">
                            <h5 class="mb-0 text-uppercase">{{ $category ?? 'Uncategorized' }}</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
                                @foreach($downloads as $download)
                                    <li class="list-group-item d-flex align-items-center">
                                        <a href="{{ asset('assets/files/downloads/' . $download->file_name) }}" download
                                            class="download-link d-flex align-items-center">
                                            <i class='bx bx-file-blank'></i>
                                            <span>{{ $download->file_name }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <p class="text-center text-muted">No downloads available.</p>
                </div>
            @endforelse
        </div>
    </div>

@endsection