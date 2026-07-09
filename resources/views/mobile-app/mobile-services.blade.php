@extends('layouts.app')
@section('content')

    <div class="container py-4" x-data="mobileServicesBookingPage()">

        <!-- <div class="text-center mb-4">
            <h3 class="fw-bold">Service Requests</h3>
        </div> -->

        <div class="row g-3">

            <!-- AUSI -->
            <div class="col-12 col-lg-4">
                <a href="{{ route('ausi.booking.mobile') }}" class="text-decoration-none service-link">
                    <div class="card service-card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center">
                            <div class="service-icon bg-primary-subtle text-primary">
                                <i class='bx bx-building-house'></i>
                            </div>

                            <div class="ms-3 flex-grow-1">
                                <h5 class="mb-1 text-dark fw-semibold">
                                    AUSI
                                </h5>
                                <small class="text-muted">
                                    Schedule maintenance and inspection services.
                                </small>
                            </div>

                            <i class='bx bx-chevron-right text-muted fs-4'></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Grease Trap Cleaning -->
            <div class="col-12 col-lg-4">
                <a href="{{ route('grease-trap.booking.mobile') }}" class="text-decoration-none service-link">
                    <div class="card service-card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center">
                            <div class="service-icon bg-success-subtle text-success">
                                <i class='bx bx-water'></i>
                            </div>

                            <div class="ms-3 flex-grow-1">
                                <h5 class="mb-1 text-dark fw-semibold">
                                    Grease Trap Cleaning
                                </h5>
                                <small class="text-muted">
                                    Request grease trap cleaning service.
                                </small>
                            </div>

                            <i class='bx bx-chevron-right text-muted fs-4'></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Pest Control -->
            <div class="col-12 col-lg-4">
                <a href="{{ route('pest.control.booking.mobile') }}" class="text-decoration-none service-link">
                    <div class="card service-card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center">
                            <div class="service-icon bg-warning-subtle text-warning">
                                <i class='bx bx-bug'></i>
                            </div>

                            <div class="ms-3 flex-grow-1">
                                <h5 class="mb-1 text-dark fw-semibold">
                                    Pest Control
                                </h5>
                                <small class="text-muted">
                                    Book pest treatment and inspection.
                                </small>
                            </div>

                            <i class='bx bx-chevron-right text-muted fs-4'></i>
                        </div>
                    </div>
                </a>
            </div>


            <!-- <div class="col-12 col-lg-4">
                <a href="{{ route('subway.faqs.mobile') }}" class="text-decoration-none service-link">
                    <div class="card service-card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center">
                            <div class="service-icon bg-warning-subtle text-warning">
                                <i class='bx bx-question-mark'></i>
                            </div>

                            <div class="ms-3 flex-grow-1">
                                <h5 class="mb-1 text-dark fw-semibold">
                                    Subway FAQs
                                </h5>
                                <small class="text-muted">
                                    Find answers to common questions.
                                </small>
                            </div>

                            <i class='bx bx-chevron-right text-muted fs-4'></i>
                        </div>
                    </div>
                </a>
            </div> -->

        </div>

    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('mobileServicesBookingPage', () => ({
                // residences: [],
                // selectedResidence: null,
                // debugLog: '',
                // debugEmail: null,

                // log(msg) {
                //     console.log(msg);
                //     this.debugLog += msg + "\n";
                // },
                // init() {
                //     this.log("🚀 INIT STARTED");
                //     this.setHeader();
                //     const store = Alpine.store('superapp');
                //     $('#mobile_email_pc').val(store?.user?.email || '');
                //     $('#mobile_user_id').val(store?.user?.id || '');
                // },
                setHeader() {
                    Alpine.store('superapp')?.bridge?.setHeader({
                        mode: 'sticky-no-back',
                        title: 'Service Requests',
                        backgroundColor: '#fff',
                        textStyle: 'black',
                        showHome: false,
                    });
                },


            }));
        });
    </script>
@endsection