@extends('layouts.frontend')
@section('content')

    <div class="container-fluid page-header pt-5 mb-6">
        <div class="container text-center pt-5">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="p-5 text-dark"
                        style="background-color: rgba(255, 255, 255, 0.8); border-radius: 10px 10px 0 0;">

                        <!-- Enhanced Title -->
                        <h1 class="display-6 text-uppercase mb-3 text-dark">SECTIONS</h1>
                        <!-- Breadcrumb -->
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb justify-content-center mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('home') }}"
                                        style="color: #00c440 !important; font-weight: 600;">Home</a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page" style="color: #222; font-weight: 600;">
                                    Sections
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>




    <div class="container-fluid px-0">

        <!-- Almond Section -->
        <div class="py-5 px-3 px-md-5 position-relative"
            style="background-color: #f4fdf3; border: 5px solid #0d8f28; font-family: 'Poppins', sans-serif; overflow: hidden;">

            <!-- Background Image -->
            <div
                style="
                                                                                                                                                                                                                    background: url('{{ asset('assets/images/2S DAHON.png') }}') no-repeat center center;
                                                                                                                                                                                                                    background-size: cover;
                                                                                                                                                                                                                    opacity: 0.05;
                                                                                                                                                                                                                    position: absolute;
                                                                                                                                                                                                                    top: 0;
                                                                                                                                                                                                                    left: 0;
                                                                                                                                                                                                                    width: 100%;
                                                                                                                                                                                                                    height: 100%;
                                                                                                                                                                                                                    z-index: 0;">
            </div>

            <!-- Foreground Content -->
            <div class="container position-relative" style="z-index: 1;">
                <div class="row align-items-stretch" data-wow-delay="0.1s">
                    <!-- Static Tower Image (Portrait) -->
                    <!-- Left Column: Main Image & Thumbnails -->
                    <div class="col-lg-6 mb-4 mb-lg-0 d-flex flex-column align-items-center">
                        <!-- Main Image (smaller but retains ratio) -->
                        <div class="shadow rounded overflow-hidden mb-3" style="max-width: 500px;">
                            <img id="mainAlmondImage" src="{{ asset('assets/images/section/a3.webp') }}"
                                alt="The Almond Tower" class="img-fluid" style="border-radius: 12px;">
                        </div>

                        <!-- Thumbnails -->

                        <div class="d-flex gap-2 flex-wrap justify-content-center mb-4">
                            <img src="{{ asset('assets/images/section/a3.webp') }}" class="thumbnail-gallery rounded border"
                                data-tower="almond" data-index="0"
                                style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;">
                            <img src="{{ asset('assets/images/section/a1.webp') }}" class="thumbnail-gallery rounded border"
                                data-tower="almond" data-index="1"
                                style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;">
                            <img src="{{ asset('assets/images/section/a4.webp') }}" class="thumbnail-gallery rounded border"
                                data-tower="almond" data-index="2"
                                style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;">
                        </div>
                    </div>

                    <!-- Right Column: Vertically and Horizontally Centered -->
                    <div class="col-lg-6 d-flex align-items-center justify-content-start">
                        <div>
                            <h2 class="mb-2"
                                style="font-family: 'Dancing Script', cursive; font-size: 3rem; color: #4e3f30;">
                                The Almond
                            </h2>
                            <h5 class="text-uppercase mb-2">Section A</h5>
                            <p class="mb-4 text-dark fs-5">350 units</p>

                            <h5 class="fw-bold text-dark mb-3">Section Officers</h5>
                            <div class="table-responsive">
                                <table class="table table-borderless mb-0">
                                    <tbody class="text-dark">
                                        <tr>
                                            <td class="text-start">Section Head</td>
                                            <td class="fw-bold">Vincer Viray</td>
                                        </tr>
                                        <tr>
                                            <td class="text-start">Asst. Section Head</td>
                                            <td class="fw-bold">Ma. Roberta Abad-Estacion</td>
                                        </tr>
                                        <tr>
                                            <td class="text-start">Secretary</td>
                                            <td class="fw-bold">Ma. Cecilia Magtuto</td>
                                        </tr>
                                        <tr>
                                            <td class="text-start">Member</td>
                                            <td class="fw-bold">Katherine Chua</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Belize Section -->
        <div class="py-5 px-3 px-md-5 position-relative bg-light"
            style="background-color: #f4fdf3; border: 5px solid #0d8f28; font-family: 'Poppins', sans-serif; overflow: hidden;">

            <!-- Background Image -->
            <div
                style="
                                                                                                                                                                                                                    background: url('{{ asset('assets/images/2S DAHON.png') }}') no-repeat center center;
                                                                                                                                                                                                                    background-size: cover;
                                                                                                                                                                                                                    opacity: 0.05;
                                                                                                                                                                                                                    position: absolute;
                                                                                                                                                                                                                    top: 0;
                                                                                                                                                                                                                    left: 0;
                                                                                                                                                                                                                    width: 100%;
                                                                                                                                                                                                                    height: 100%;
                                                                                                                                                                                                                    z-index: 0;">
            </div>

            <!-- Foreground Content -->
            <div class="container position-relative" style="z-index: 1;">
                <div class="row align-items-stretch" data-wow-delay="0.1s">
                    <!-- Static Tower Image (Portrait) -->
                    <!-- Left Column: Main Image & Thumbnails -->
                    <div class="col-lg-6 mb-4 mb-lg-0 d-flex flex-column align-items-center">
                        <!-- Main Image (smaller but retains ratio) -->
                        <div class="shadow rounded overflow-hidden mb-3" style="max-width: 500px;">
                            <img id="mainBelizeImage" src="{{ asset('assets/images/section/b1.webp') }}"
                                alt="The belize Tower" class="img-fluid" style="border-radius: 12px;">
                        </div>

                        <!-- Thumbnails -->
                        <div class="d-flex gap-2 flex-wrap justify-content-center">
                            <div class="d-flex gap-2 flex-wrap justify-content-center mb-4">
                                <img src="{{ asset('assets/images/section/b1.webp') }}"
                                    class="thumbnail-gallery rounded border" data-tower="belize" data-index="0"
                                    style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;">
                                <img src="{{ asset('assets/images/section/b2.webp') }}"
                                    class="thumbnail-gallery rounded border" data-tower="belize" data-index="1"
                                    style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;">
                                <img src="{{ asset('assets/images/section/b&c1.webp') }}"
                                    class="thumbnail-gallery rounded border" data-tower="belize" data-index="2"
                                    style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;">
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Vertically and Horizontally Centered -->
                    <div class="col-lg-6 d-flex align-items-center justify-content-start">
                        <div>
                            <h2 class="mb-2"
                                style="font-family: 'Dancing Script', cursive; font-size: 3rem; color: #4e3f30;">
                                The Belize
                            </h2>
                            <h5 class="text-uppercase mb-2">Section B</h5>
                            <p class="mb-4 text-dark fs-5">195 units</p>

                            <h5 class="fw-bold text-dark mb-3">Section Officers</h5>
                            <div class="table-responsive">
                                <table class="table table-borderless mb-0">
                                    <tbody class="text-dark">
                                        <tr>
                                            <td class="text-start">Section Head</td>
                                            <td class="fw-bold">Rizalino Antonio Pulumbarit</td>
                                        </tr>
                                        <tr>
                                            <td class="text-start">Asst. Section Head</td>
                                            <td class="fw-bold">Marietta Pecson</td>
                                        </tr>
                                        <tr>
                                            <td class="text-start">Secretary</td>
                                            <td class="fw-bold">Edward Estacion</td>
                                        </tr>
                                        <tr>
                                            <td class="text-start">Member</td>
                                            <td class="fw-bold">Patrick Richiardone</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Callery Section -->
        <div class="py-5 px-3 px-md-5 position-relative"
            style="background-color: #f4fdf3; border: 5px solid #0d8f28; font-family: 'Poppins', sans-serif; overflow: hidden;">

            <!-- Background Image -->
            <div
                style="
                                                                                                                                                                                                                    background: url('{{ asset('assets/images/2S DAHON.png') }}') no-repeat center center;
                                                                                                                                                                                                                    background-size: cover;
                                                                                                                                                                                                                    opacity: 0.05;
                                                                                                                                                                                                                    position: absolute;
                                                                                                                                                                                                                    top: 0;
                                                                                                                                                                                                                    left: 0;
                                                                                                                                                                                                                    width: 100%;
                                                                                                                                                                                                                    height: 100%;
                                                                                                                                                                                                                    z-index: 0;">
            </div>

            <!-- Foreground Content -->
            <div class="container position-relative" style="z-index: 1;">
                <div class="row align-items-stretch" data-wow-delay="0.1s">
                    <!-- Static Tower Image (Portrait) -->
                    <!-- Left Column: Main Image & Thumbnails -->
                    <div class="col-lg-6 mb-4 mb-lg-0 d-flex flex-column align-items-center">
                        <!-- Main Image (smaller but retains ratio) -->
                        <div class="shadow rounded overflow-hidden mb-3" style="max-width: 500px;">
                            <img id="mainCalleryImage" src="{{ asset('assets/images/section/c1.webp') }}"
                                alt="The Callery Tower" class="img-fluid" style="border-radius: 12px;">
                        </div>

                        <!-- Thumbnails -->
                        <div class="d-flex gap-2 flex-wrap justify-content-center">
                            <div class="d-flex gap-2 flex-wrap justify-content-center mb-4">
                                <img src="{{ asset('assets/images/section/c1.webp') }}"
                                    class="thumbnail-gallery rounded border" data-tower="callery" data-index="0"
                                    style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;">
                                <img src="{{ asset('assets/images/section/b2.webp') }}"
                                    class="thumbnail-gallery rounded border" data-tower="callery" data-index="1"
                                    style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;">
                                <img src="{{ asset('assets/images/section/b&c1.webp') }}"
                                    class="thumbnail-gallery rounded border" data-tower="callery" data-index="2"
                                    style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;">
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Vertically and Horizontally Centered -->
                    <div class="col-lg-6 d-flex align-items-center justify-content-start">
                        <div>
                            <h2 class="mb-2"
                                style="font-family: 'Dancing Script', cursive; font-size: 3rem; color: #4e3f30;">
                                The Callery
                            </h2>
                            <h5 class="text-uppercase mb-2">Section C</h5>
                            <p class="mb-4 text-dark fs-5">243 units</p>

                            <h5 class="fw-bold text-dark mb-3">Section Officers</h5>
                            <div class="table-responsive mb-4">
                                <table class="table table-borderless mb-0">
                                    <tbody class="text-dark">
                                        <tr>
                                            <td class="text-start">Section Head</td>
                                            <td class="fw-bold">Rene Janda</td>
                                        </tr>
                                        <tr>
                                            <td class="text-start">Asst. Section Head</td>
                                            <td class="fw-bold">Carlo Peterson</td>
                                        </tr>
                                        <tr>
                                            <td class="text-start">Secretary</td>
                                            <td class="fw-bold">Ana May Chua</td>
                                        </tr>
                                        <tr>
                                            <td class="text-start">Member</td>
                                            <td class="fw-bold">Jerimeco Dulalia</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Dolce Section -->
        <div class="py-5 px-3 px-md-5 position-relative bg-light"
            style="background-color: #f4fdf3; border: 5px solid #0d8f28; font-family: 'Poppins', sans-serif; overflow: hidden;">

            <!-- Background Image -->
            <div
                style="
                                                                                                                                                                                                                    background: url('{{ asset('assets/images/2S DAHON.png') }}') no-repeat center center;
                                                                                                                                                                                                                    background-size: cover;
                                                                                                                                                                                                                    opacity: 0.05;
                                                                                                                                                                                                                    position: absolute;
                                                                                                                                                                                                                    top: 0;
                                                                                                                                                                                                                    left: 0;
                                                                                                                                                                                                                    width: 100%;
                                                                                                                                                                                                                    height: 100%;
                                                                                                                                                                                                                    z-index: 0;">
            </div>

            <!-- Foreground Content -->
            <div class="container position-relative" style="z-index: 1;">
                <div class="row align-items-stretch" data-wow-delay="0.1s">
                    <!-- Static Tower Image (Portrait) -->
                    <!-- Left Column: Main Image & Thumbnails -->
                    <div class="col-lg-6 mb-4 mb-lg-0 d-flex flex-column align-items-center">
                        <!-- Main Image (smaller but retains ratio) -->
                        <div class="shadow rounded overflow-hidden mb-3" style="max-width: 500px;">
                            <img id="mainDolceImage" src="{{ asset('assets/images/section/d1.webp') }}"
                                alt="The Dolce Tower" class="img-fluid" style="border-radius: 12px;">
                        </div>

                        <!-- Thumbnails -->
                        <div class="d-flex gap-2 flex-wrap justify-content-center">
                            <div class="d-flex gap-2 flex-wrap justify-content-center mb-4">
                                <img src="{{ asset('assets/images/section/d1.webp') }}"
                                    class="thumbnail-gallery rounded border" data-tower="dolce" data-index="0"
                                    style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;">
                                <img src="{{ asset('assets/images/section/d&e4.webp') }}"
                                    class="thumbnail-gallery rounded border" data-tower="dolce" data-index="1"
                                    style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;">
                                <img src="{{ asset('assets/images/section/d&e3.webp') }}"
                                    class="thumbnail-gallery rounded border" data-tower="dolce" data-index="2"
                                    style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;">
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Vertically and Horizontally Centered -->
                    <div class="col-lg-6 d-flex align-items-center justify-content-start">
                        <div>
                            <h2 class="mb-2"
                                style="font-family: 'Dancing Script', cursive; font-size: 3rem; color: #4e3f30;">
                                The Dolce
                            </h2>
                            <h5 class="text-uppercase mb-2">Section D</h5>
                            <p class="mb-4 text-dark fs-5">138 units</p>
                            <h5 class="fw-bold text-dark mb-3">Section Officers</h5>
                            <div class="table-responsive mb-4">
                                <table class="table table-borderless mb-0">
                                    <tbody class="text-dark">
                                        <tr>
                                            <td class="text-start">Section Head</td>
                                            <td class="fw-bold">N/A</td>
                                        </tr>
                                        <tr>
                                            <td class="text-start">Asst. Section Head</td>
                                            <td class="fw-bold">N/A</td>
                                        </tr>
                                        <tr>
                                            <td class="text-start">Secretary</td>
                                            <td class="fw-bold">N/A</td>
                                        </tr>
                                        <tr>
                                            <td class="text-start">Member</td>
                                            <td class="fw-bold">N/A</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Encino Section -->
        <div class="py-5 px-3 px-md-5 position-relative"
            style="background-color: #f4fdf3; border: 5px solid #0d8f28; font-family: 'Poppins', sans-serif; overflow: hidden;">

            <!-- Background Image -->
            <div
                style="
                                                                                                                                                                                                                    background: url('{{ asset('assets/images/2S DAHON.png') }}') no-repeat center center;
                                                                                                                                                                                                                    background-size: cover;
                                                                                                                                                                                                                    opacity: 0.05;
                                                                                                                                                                                                                    position: absolute;
                                                                                                                                                                                                                    top: 0;
                                                                                                                                                                                                                    left: 0;
                                                                                                                                                                                                                    width: 100%;
                                                                                                                                                                                                                    height: 100%;
                                                                                                                                                                                                                    z-index: 0;">
            </div>

            <!-- Foreground Content -->
            <div class="container position-relative" style="z-index: 1;">
                <div class="row align-items-stretch" data-wow-delay="0.1s">
                    <!-- Static Tower Image (Portrait) -->
                    <!-- Left Column: Main Image & Thumbnails -->
                    <div class="col-lg-6 mb-4 mb-lg-0 d-flex flex-column align-items-center">
                        <!-- Main Image (smaller but retains ratio) -->
                        <div class="shadow rounded overflow-hidden mb-3" style="max-width: 500px;">
                            <img id="mainEncinoImage" src="{{ asset('assets/images/section/d&e1.webp') }}"
                                alt="The Encino Tower" class="img-fluid" style="border-radius: 12px;">
                        </div>

                        <!-- Thumbnails -->
                        <div class="d-flex gap-2 flex-wrap justify-content-center">
                            <div class="d-flex gap-2 flex-wrap justify-content-center mb-4">
                                <img src="{{ asset('assets/images/section/d&e1.webp') }}"
                                    class="thumbnail-gallery rounded border" data-tower="encino" data-index="0"
                                    style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;">
                                <img src="{{ asset('assets/images/section/d&e2.webp') }}"
                                    class="thumbnail-gallery rounded border" data-tower="encino" data-index="1"
                                    style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;">
                                <img src="{{ asset('assets/images/section/d&e3.webp') }}"
                                    class="thumbnail-gallery rounded border" data-tower="encino" data-index="2"
                                    style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;">
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Vertically and Horizontally Centered -->
                    <div class="col-lg-6 d-flex align-items-center justify-content-start">
                        <div>
                            <h2 class="mb-2"
                                style="font-family: 'Dancing Script', cursive; font-size: 3rem; color: #4e3f30;">
                                The Encino
                            </h2>
                            <h5 class="text-uppercase mb-2">Section E</h5>
                            <p class="mb-4 text-dark fs-5">274 units</p>

                            <h5 class="fw-bold text-dark mb-3">Section Officers</h5>
                            <div class="table-responsive mb-4">
                                <table class="table table-borderless mb-0">
                                    <tbody class="text-dark">
                                        <tr>
                                            <td class="text-start">Section Head</td>
                                            <td class="fw-bold">Raul Dimayuga</td>
                                        </tr>
                                        <tr>
                                            <td class="text-start">Asst. Section Head</td>
                                            <td class="fw-bold">N/A</td>
                                        </tr>
                                        <tr>
                                            <td class="text-start">Secretary</td>
                                            <td class="fw-bold">Eugenia Billones</td>
                                        </tr>
                                        <tr>
                                            <td class="text-start">Member</td>
                                            <td class="fw-bold">Nena Radoc</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aston Section -->
        <div class="py-5 px-3 px-md-5 position-relative bg-light"
            style="background-color: #f4fdf3; border: 5px solid #0d8f28; font-family: 'Poppins', sans-serif; overflow: hidden;">

            <!-- Background Image -->
            <div
                style="
                                                                                                                                                                                                                    background: url('{{ asset('assets/images/2S DAHON.png') }}') no-repeat center center;
                                                                                                                                                                                                                    background-size: cover;
                                                                                                                                                                                                                    opacity: 0.05;
                                                                                                                                                                                                                    position: absolute;
                                                                                                                                                                                                                    top: 0;
                                                                                                                                                                                                                    left: 0;
                                                                                                                                                                                                                    width: 100%;
                                                                                                                                                                                                                    height: 100%;
                                                                                                                                                                                                                    z-index: 0;">
            </div>

            <!-- Foreground Content -->
            <div class="container position-relative" style="z-index: 1;">
                <div class="row align-items-stretch" data-wow-delay="0.1s">
                    <!-- Static Tower Image (Portrait) -->
                    <!-- Left Column: Main Image & Thumbnails -->
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <div class="row h-100">

                            <!-- Portrait Image (f1) -->
                            <div class="col-6 d-flex align-items-center">
                                <div class="rounded overflow-hidden shadow w-100">
                                    <img src="{{ asset('assets/images/section/f1.webp') }}" alt="Aston Portrait"
                                        class="img-fluid w-100 thumbnail-gallery" data-tower="aston" data-index="0"
                                        style="height: 100%; object-fit: cover; border-radius: 12px; cursor: pointer;">
                                </div>
                            </div>

                            <div class="col-6 d-flex flex-column justify-content-between">
                                <div class="rounded overflow-hidden shadow mb-3">
                                    <img src="{{ asset('assets/images/section/f2.webp') }}" alt="RedOak Sub 1"
                                        class="img-fluid w-100 thumbnail-gallery" data-tower="aston" data-index="1"
                                        style="height: 180px; object-fit: cover; border-radius: 12px; cursor: pointer;">
                                </div>
                                <div class="rounded overflow-hidden shadow">
                                    <img src="{{ asset('assets/images/section/f3.webp') }}" alt="RedOak Sub 2"
                                        class="img-fluid w-100 thumbnail-gallery" data-tower="aston" data-index="2"
                                        style="height: 180px; object-fit: cover; border-radius: 12px; cursor: pointer;">
                                </div>
                            </div>



                        </div>
                    </div>

                    <!-- Right Column: Vertically and Horizontally Centered -->
                    <div class="col-lg-6 d-flex align-items-center justify-content-start">
                        <div>
                            <h2 class="mb-2"
                                style="font-family: 'Dancing Script', cursive; font-size: 3rem; color: #4e3f30;">
                                The Aston
                            </h2>
                            <h5 class="text-uppercase mb-2">Section F</h5>
                            <p class="mb-4 text-dark fs-5">400 units</p>

                            <h5 class="fw-bold text-dark mb-3">Section Officers</h5>
                            <div class="table-responsive mb-4">
                                <table class="table table-borderless mb-0">
                                    <tbody class="text-dark">
                                        <tr>
                                            <td class="text-start">Section Head</td>
                                            <td class="fw-bold">Jessica Chan</td>
                                        </tr>
                                        <tr>
                                            <td class="text-start">Asst. Section Head</td>
                                            <td class="fw-bold">Maria Joy Villanueva</td>
                                        </tr>
                                        <tr>
                                            <td class="text-start">Secretary</td>
                                            <td class="fw-bold">Matt Alistair Austria</td>
                                        </tr>
                                        <tr>
                                            <td class="text-start">Member</td>
                                            <td class="fw-bold">Jerimeco Dulalia</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- RedOak Section -->
        <div class="py-5 px-3 px-md-5 position-relative"
            style="background-color: #f4fdf3; border: 5px solid #0d8f28; font-family: 'Poppins', sans-serif; overflow: hidden;">

            <!-- Background Image -->
            <div
                style="
                                                                                                                                                                                                                    background: url('{{ asset('assets/images/2S DAHON.png') }}') no-repeat center center;
                                                                                                                                                                                                                    background-size: cover;
                                                                                                                                                                                                                    opacity: 0.05;
                                                                                                                                                                                                                    position: absolute;
                                                                                                                                                                                                                    top: 0;
                                                                                                                                                                                                                    left: 0;
                                                                                                                                                                                                                    width: 100%;
                                                                                                                                                                                                                    height: 100%;
                                                                                                                                                                                                                    z-index: 0;">
            </div>


            <div class="container position-relative" style="z-index: 1;">
                <div class="row align-items-stretch" data-wow-delay="0.1s">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <div class="row h-100">

                            <div class="col-6 d-flex align-items-center">
                                <div class="rounded overflow-hidden shadow w-100">
                                    <img src="{{ asset('assets/images/section/g1.webp') }}" alt="Main RedOak"
                                        class="img-fluid w-100 thumbnail-gallery" data-tower="redoak" data-index="0"
                                        style="height: 100%; object-fit: cover; border-radius: 12px; cursor: pointer;">
                                </div>
                            </div>


                            <div class="col-6 d-flex flex-column justify-content-between">
                                <div class="rounded overflow-hidden shadow mb-3">
                                    <img src="{{ asset('assets/images/section/g2.webp') }}" alt="RedOak Sub 1"
                                        class="img-fluid w-100 thumbnail-gallery" data-tower="redoak" data-index="1"
                                        style="height: 180px; object-fit: cover; border-radius: 12px;cursor: pointer;">
                                </div>
                                <div class="rounded overflow-hidden shadow">
                                    <img src="{{ asset('assets/images/section/g3.webp') }}" alt="RedOak Sub 2"
                                        class="img-fluid w-100 thumbnail-gallery" data-tower="redoak" data-index="2"
                                        style="height: 180px; object-fit: cover; border-radius: 12px; cursor: pointer;">
                                </div>
                            </div>

                        </div>
                    </div>


                    <div class="col-lg-6 d-flex align-items-center justify-content-start">
                        <div>
                            <h2 class="mb-2"
                                style="font-family: 'Dancing Script', cursive; font-size: 3rem; color: #4e3f30;">
                                The Red Oak
                            </h2>
                            <h5 class="text-uppercase mb-2">Section G</h5>
                            <p class="mb-4 text-dark fs-5">520 units</p>

                            <h5 class="fw-bold text-dark mb-3">Section Officers</h5>
                            <div class="table-responsive mb-4">
                                <table class="table table-borderless mb-0">
                                    <tbody class="text-dark">
                                        <tr>
                                            <td class="text-start">Section Head</td>
                                            <td class="fw-bold">Paul McDonald</td>
                                        </tr>
                                        <tr>
                                            <td class="text-start">Asst. Section Head</td>
                                            <td class="fw-bold">Cindy Sta. Maria</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Meranti Section -->
        <div class="py-5 px-3 px-md-5 position-relative bg-light"
            style="background-color: #f4fdf3; border: 5px solid #0d8f28; font-family: 'Poppins', sans-serif; overflow: hidden;">

            <!-- Background Image -->
            <div
                style="
                                                                                                                                                                                                                    background: url('{{ asset('assets/images/2S DAHON.png') }}') no-repeat center center;
                                                                                                                                                                                                                    background-size: cover;
                                                                                                                                                                                                                    opacity: 0.05;
                                                                                                                                                                                                                    position: absolute;
                                                                                                                                                                                                                    top: 0;
                                                                                                                                                                                                                    left: 0;
                                                                                                                                                                                                                    width: 100%;
                                                                                                                                                                                                                    height: 100%;
                                                                                                                                                                                                                    z-index: 0;">
            </div>


            <div class="container position-relative" style="z-index: 1;">
                <div class="row align-items-stretch" data-wow-delay="0.1s">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <div class="row h-100">

                            <div class="col-6 d-flex align-items-center">
                                <div class="rounded overflow-hidden shadow w-100">
                                    <img src="{{ asset('assets/images/section/h1.webp') }}" alt="Main Meranti"
                                        class="img-fluid w-100 thumbnail-gallery" data-tower="meranti" data-index="0"
                                        style="height: 100%; object-fit: cover; border-radius: 12px;cursor: pointer;">
                                </div>
                            </div>


                            <div class="col-6 d-flex flex-column justify-content-between">
                                <div class="rounded overflow-hidden shadow mb-3">
                                    <img src="{{ asset('assets/images/section/h2.webp') }}" alt="Meranti Sub 1"
                                        class="img-fluid w-100 thumbnail-gallery" data-tower="meranti" data-index="1"
                                        style="height: 180px; object-fit: cover; border-radius: 12px;cursor: pointer;">
                                </div>
                                <div class="rounded overflow-hidden shadow">
                                    <img src="{{ asset('assets/images/section/h3.webp') }}" alt="Meranti Sub 2"
                                        class="img-fluid w-100 thumbnail-gallery" data-tower="meranti" data-index="2"
                                        style="height: 180px; object-fit: cover; border-radius: 12px;cursor: pointer;">
                                </div>
                            </div>

                        </div>
                    </div>


                    <div class="col-lg-6 d-flex align-items-center justify-content-start">
                        <div>
                            <h2 class="mb-2"
                                style="font-family: 'Dancing Script', cursive; font-size: 3rem; color: #4e3f30;">
                                The Meranti
                            </h2>
                            <h5 class="text-uppercase mb-2">Section H</h5>
                            <p class="mb-4 text-dark fs-5">717 units</p>

                            <h5 class="fw-bold text-dark mb-3">Section Officers</h5>
                            <div class="table-responsive mb-4">
                                <table class="table table-borderless mb-0">
                                    <tbody class="text-dark">
                                        <tr>
                                            <td class="text-start">Section Head</td>
                                            <td class="fw-bold">Alexandra Garcia</td>
                                        </tr>
                                        <tr>
                                            <td class="text-start">Asst. Section Head</td>
                                            <td class="fw-bold">Jerimeco Dulalia</td>
                                        </tr>
                                        <tr>
                                            <td class="text-start">Secretary</td>
                                            <td class="fw-bold">Eugenia Billones</td>
                                        </tr>
                                        <tr>
                                            <td class="text-start">Member</td>
                                            <td class="fw-bold">Nelia Sarcol</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <!-- Sequoia Section -->
        <div class="py-5 px-3 px-md-5 position-relative"
            style="background-color: #f4fdf3; border: 5px solid #0d8f28; font-family: 'Poppins', sans-serif; overflow: hidden;">

            <!-- Background Image -->
            <div
                style="
                                                                                                                                                                                                                    background: url('{{ asset('assets/images/2S DAHON.png') }}') no-repeat center center;
                                                                                                                                                                                                                    background-size: cover;
                                                                                                                                                                                                                    opacity: 0.05;
                                                                                                                                                                                                                    position: absolute;
                                                                                                                                                                                                                    top: 0;
                                                                                                                                                                                                                    left: 0;
                                                                                                                                                                                                                    width: 100%;
                                                                                                                                                                                                                    height: 100%;
                                                                                                                                                                                                                    z-index: 0;">
            </div>


            <div class="container position-relative" style="z-index: 1;">
                <div class="row align-items-stretch" data-wow-delay="0.1s">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <div class="row h-100">

                            <div class="col-6 d-flex align-items-center">
                                <div class="rounded overflow-hidden shadow w-100">
                                    <img src="{{ asset('assets/images/section/i1.webp') }}" alt="Main Sequoia"
                                        class="img-fluid w-100 thumbnail-gallery" data-tower="sequoia" data-index="0"
                                        style="height: 100%; object-fit: cover; border-radius: 12px;cursor: pointer;">
                                </div>
                            </div>


                            <div class="col-6 d-flex flex-column justify-content-between">
                                <div class="rounded overflow-hidden shadow mb-3">
                                    <img src="{{ asset('assets/images/section/i2.webp') }}" alt="Sequoia Sub 1"
                                        class="img-fluid w-100 thumbnail-gallery" data-tower="sequoia" data-index="1"
                                        style="height: 180px; object-fit: cover; border-radius: 12px;cursor: pointer;">
                                </div>
                                <div class="rounded overflow-hidden shadow"> 
                                    <img src="{{ asset('assets/images/section/i3.webp') }}" alt="Sequoia Sub 2"
                                        class="img-fluid w-100 thumbnail-gallery" data-tower="sequoia" data-index="2"
                                        style="height: 180px; object-fit: cover; border-radius: 12px;cursor: pointer;">
                                </div>
                            </div>

                        </div>
                    </div>


                    <div class="col-lg-6 d-flex align-items-center justify-content-start">
                        <div>
                            <h2 class="mb-2"
                                style="font-family: 'Dancing Script', cursive; font-size: 3rem; color: #4e3f30;">
                                The Sequoia
                            </h2>
                            <h5 class="text-uppercase mb-2">Section I</h5>
                            <p class="mb-4 text-dark fs-5">615 units</p>

                            <h5 class="fw-bold text-dark mb-3">Section Officers</h5>
                            <div class="table-responsive mb-4">
                                <table class="table table-borderless mb-0">
                                    <tbody class="text-dark">
                                        <tr>
                                            <td class="text-start">Section Head</td>
                                            <td class="fw-bold">Mary Rose Macapagal</td>
                                        </tr>
                                        <tr>
                                            <td class="text-start">Asst. Section Head</td>
                                            <td class="fw-bold">Eleanor Martha Buensuceso</td>
                                        </tr>
                                        <tr>
                                            <td class="text-start">Secretary</td>
                                            <td class="fw-bold">Ma. Cecilia Magtuto</td>
                                        </tr>
                                        <tr>
                                            <td class="text-start">Member</td>
                                            <td class="fw-bold">Nena Radoc</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>


    @include('frontend.modal.thumbnail-modal')
@endsection