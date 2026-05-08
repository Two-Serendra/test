@extends('layouts.frontend')
@section('content')
    <div class="container-fluid page-header pt-5 mb-6">
        <div class="container text-center pt-5">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="p-5 text-dark"
                        style="background-color: rgba(255, 255, 255, 0.8); border-radius: 10px 10px 0 0;">

                        <!-- Enhanced Title -->
                        <h1 class="display-6 text-uppercase mb-3 text-dark">Quick Links</h1>
                        <!-- Breadcrumb -->
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb justify-content-center mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('home') }}"
                                        style="color: #00c440 !important; font-weight: 600;">Home</a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page" style="color: #222; font-weight: 600;">
                                    Links
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-5">
        <div class="row g-4 mt-4">
            <!-- Admin Forms -->
            <div class="col-md-12">
                <div class="card shadow mb-3 bg-body rounded">
                    <div class="card-header bg-light text-dark">
                        <h5 class="mb-0 text-uppercase">Quick Links</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">

                            <li class="list-group-item">
                                <a href="https://drive.google.com/drive/folders/1omzrm6qWH6ImQ86yfaRVDBmLzUly39Fb?usp=sharing" target="_blank"
                                    class="d-flex align-items-center fs-6 text-dark online-form-link">
                                    <i class='bx bx-file-blank me-2 fs-4'></i>
                                    Circulars
                                </a>
                            </li>
                            <li class="list-group-item">
                                <a href="https://drive.google.com/file/d/1YL_9lqzPETjW2gxe1d13maXOsP1wh7zE/view" target="_blank"
                                    class="d-flex align-items-center fs-6 text-dark online-form-link">
                                    <i class='bx bx-file-blank me-2 fs-4'></i>
                                    House Rules and Regulations
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection