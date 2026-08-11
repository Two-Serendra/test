@extends('layouts.app')

<style>
    body {
        background: #f7f8fa;
    }

    /* Accordion Container */
    .faq-accordion .accordion-item {
        border: 0;
        border-radius: 14px;
        margin-bottom: 12px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
    }

    /* Remove Bootstrap arrow */
    .faq-accordion .accordion-button::after {
        display: none;
    }

    /* Accordion Header */
    .faq-accordion .accordion-button {
        background: #fff;
        color: #222;
        font-size: 15px;
        font-weight: 600;
        padding: 18px;
        border: 0;
        box-shadow: none;
    }

    /* Active Header */
    .faq-accordion .accordion-button:not(.collapsed) {
        background: #008b26;
        color: #fff;
    }

    /* Focus */
    .faq-accordion .accordion-button:focus {
        box-shadow: none;
    }

    /* Body */
    .faq-accordion .accordion-body {
        background: #fff !important;
        color: #555 !important;
        font-size: 14px;
        line-height: 1.7;
        padding: 18px;

        /* Force repaint */
        opacity: 1 !important;
        visibility: visible !important;
        -webkit-text-fill-color: #555 !important;

        transform: translateZ(0);
        -webkit-transform: translateZ(0);
        backface-visibility: hidden;
        -webkit-backface-visibility: hidden;
    }

    /* Icons */
    .faq-icon,
    .faq-chevron {
        color: #008b26;
    }

    .faq-accordion .accordion-button:not(.collapsed) .faq-icon,
    .faq-accordion .accordion-button:not(.collapsed) .faq-chevron {
        color: #fff;
    }

    .faq-chevron {
        font-size: 22px;
    }

    .faq-accordion .accordion-button:not(.collapsed) .faq-chevron {
        transform: rotate(90deg);
    }
</style>
@section('content')

    <div class="container py-3">
        <div class="" x-data="faqsMobile()">
            <div class="text-center mb-4">
                <!-- <h3 class="fw-bold text-success mb-2">
                                                            Metro Manila Subway FAQs
                                                        </h3> -->

                <p class="text-success mb-0">
                    Information for Two Serendra Residents
                </p>

                <small class="text-muted">
                    Last Updated: August 2026
                </small>

            </div>
            <div class="card advisory-card border-0 shadow-sm mb-4">
                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <i class='bx bx-info-circle text-success me-3' style="font-size:38px;"></i>

                        <div>
                            <h6 class="fw-bold mb-1">
                                Important Advisory
                            </h6>

                            <small class="text-muted">
                                Metro Manila Subway construction may temporarily affect
                                traffic and access around Two Serendra.
                                Please review the FAQs below for more information.
                            </small>
                        </div>

                    </div>

                </div>
            </div>

            <div class="accordion faq-accordion mt-4" id="faqAccordion">

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">

                            <i class='bx bx-help-circle me-2 faq-icon'></i>

                            <span class="flex-grow-1">
                                Why is construction taking place near Two Serendra?
                            </span>

                            <i class='bx bx-chevron-right faq-chevron'></i>

                        </button>
                    </h2>

                    <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                        <div class="accordion-body" style="color: #666 !important;">
                            The construction is part of the Metro Manila Subway Project, which aims to improve public
                            transportation by connecting key areas of Metro Manila through an underground railway system.

                            The Bonifacio Global City (BGC) Station will be constructed under Mckinley Parkway between
                            Serendra and Market Market.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faq2">

                            <i class='bx bx-help-circle me-2 faq-icon'></i>

                            <span class="flex-grow-1">
                                How will the subway construction affect traffic around Two Serendra?
                            </span>

                            <i class='bx bx-chevron-right faq-chevron'></i>

                        </button>
                    </h2>

                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            During the initial Advanced Works stage of construction (involving tree cutting/relocation,
                            utility relocation, etc) residents can expect temporary closures of vehicular lanes and sidewalk
                            of Mckinley Parkway. Once the main construction works (excavation, diaphragm wall construction,
                            etc) begin, Mckinley Parkway between 32nd and 26th streets will be closed to both vehicular and
                            pedestrian traffic.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faq3">

                            <i class='bx bx-help-circle me-2 faq-icon'></i>

                            <span class="flex-grow-1">
                                How will residents access Two Serendra?
                            </span>

                            <i class='bx bx-chevron-right faq-chevron'></i>

                        </button>
                    </h2>

                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Once Mckinley Parkway is fully closed, the Almond pedestrian gate and the Belize/Callery Gate
                            will be closed until the street is reopened towards the end of construction works. During this
                            time all pedestrian and vehicular ingress and egress to and from Two Serendra Low Rise will go
                            through the Almond, Dolce/Encino Gates along 11th Avenue as well as the lobby at the retail
                            area. The gate along 26th Street under the pedestrian footbridge will be opened for pedestrians
                            only.

                            The PMO is currently coordinating with BGCEA and the Contractor on their proposed Traffic
                            Management Plan around Two Serendra. Details on this will be disseminated to the Community once
                            they are available.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faq4">

                            <i class='bx bx-help-circle me-2 faq-icon'></i>

                            <span class="flex-grow-1">
                                Should residents expect construction noise?
                            </span>

                            <i class='bx bx-chevron-right faq-chevron'></i>

                        </button>
                    </h2>

                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Some construction activities may produce noise during approved
                            working hours. Contractors are expected to comply with government
                            regulations and implement measures to minimize disruption.
                        </div>
                    </div>
                </div>

                <!-- <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#faq5">

                                <i class='bx bx-help-circle me-2 faq-icon'></i>

                                <span class="flex-grow-1">
                                    Will pedestrian access around the property change?
                                </span>

                                <i class='bx bx-chevron-right faq-chevron'></i>

                            </button>
                        </h2>

                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Pedestrian access will be maintained whenever possible. If a
                                walkway needs to be temporarily closed, alternative routes will
                                be clearly marked.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#faq6">

                                <i class='bx bx-help-circle me-2 faq-icon'></i>

                                <span class="flex-grow-1">
                                    Will vehicle access, drop-off areas, or parking be affected?
                                </span>

                                <i class='bx bx-chevron-right faq-chevron'></i>

                            </button>
                        </h2>

                        <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Some parking spaces, loading zones, or drop-off points near the
                                construction area may be temporarily relocated.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#faq7">

                                <i class='bx bx-help-circle me-2 faq-icon'></i>

                                <span class="flex-grow-1">
                                    Why is the Metro Manila Subway being built?
                                </span>

                                <i class='bx bx-chevron-right faq-chevron'></i>

                            </button>
                        </h2>

                        <div id="faq7" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Once completed, the Metro Manila Subway is expected to provide
                                faster travel, improve connectivity, and reduce road congestion.
                            </div>
                        </div>
                    </div> -->

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faq8">

                            <i class='bx bx-help-circle me-2 faq-icon'></i>

                            <span class="flex-grow-1">
                                Where can residents receive official updates?
                            </span>

                            <i class='bx bx-chevron-right faq-chevron'></i>

                        </button>
                    </h2>

                    <div id="faq8" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            The PMO is in regular coordination with the DOTR and its Contractor and will release updates via
                            circular as they become available. Residents may also check this page for updates as well as to
                            review previously released information. This FAQ will also be updated as more information
                            becomes available.
                        </div>
                    </div>
                </div>

                <div class="card contact-card border-0 mt-4" style="">
                    <div class="card-body text-center">
                        <i class='bx bx-building-house text-success fs-2'></i>
                        <h6 class="fw-bold mt-2">
                            Need More Information?
                        </h6>
                        <p class="small text-dark mb-0">
                            Residents are encouraged to monitor official advisories from the
                            Property Management Office (PMO). Updates regarding access,
                            traffic management, and construction activities will be shared
                            through the appropriate communication channels whenever available.
                        </p>
                    </div>
                </div>

                <div class="text-center mt-4 mb-2">

                    <small class="text-muted">

                        This information is provided as a guide and may change as
                        construction progresses.

                    </small>

                </div>
            </div>
        </div>
    </div>


    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('faqsMobile', () => ({
                residences: [],
                selectedResidence: null,
                debugLog: '',
                debugEmail: null,

                log(msg) {
                    console.log(msg);
                    this.debugLog += msg + "\n";
                },
                init() {
                    this.log("🚀 INIT STARTED");
                    this.setHeader();
                    const store = Alpine.store('superapp');
                    $('#mobile_email').val(store?.user?.email || '');
                    $('#mobile_user_id').val(store?.user?.id || '');
                },
                setHeader() {
                    Alpine.store('superapp')?.bridge?.setHeader({
                        mode: 'sticky-no-back',
                        title: 'Subway FAQs',
                        backgroundColor: '#fff',
                        textStyle: 'black',
                        showHome: false,
                    });
                },

            }));
        });
    </script>
@endsection