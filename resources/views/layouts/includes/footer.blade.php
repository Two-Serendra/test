<style>
    .footer-bg {
        background-color: #008b26;
        background-image: url('{{ asset('assets/images/2S DAHON.png') }}');
        background-repeat: no-repeat;
        background-size: contain;
        /* or cover */
        background-position: left bottom;
        background-blend-mode: overlay;
        /* Optional for contrast */
        position: relative;
        z-index: 1;
    }

    .footer-bg::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: #008b26;
        opacity: 0.6;
        /* Adjust for text visibility */
        z-index: -1;
    }
</style>





<div class="container-fluid footer footer-bg py-3 wow fadeIn" style="background-color: #008b26;" data-wow-delay="0.1s">
    <div class="container py-3">
        <div class="row g-5">
            <div class="col-lg-3 col-md-6 d-flex align-items-center justify-content-start">
                <img src="{{ asset('assets/images/TWO SERENDRA LOGO PNG (White).png') }}" alt="Two Serendra Logo"
                    class="img-fluid w-100">
            </div>
            <div class="col-lg-3 col-md-6">
                <h5 class="text-uppercase text-light mb-4">Our Office</h5>

                <div class="d-flex flex-column align-items-start text-start text-light mb-3">
                    <i class="fa fa-map-marker-alt mb-2" style="font-size: 1.5rem;"></i>
                    <a href="https://www.google.com/maps?q=Two+Serendra+11th+Avenue,+Bonifacio+Global+City+Taguig+City,+Philippines+1635"
                        target="_blank" class="text-light text-decoration-none">
                        <p class="mb-0">
                            Two Serendra 11th Avenue,<br>
                            Bonifacio Global City Taguig City,<br>
                            Philippines 1635
                        </p>
                    </a>
                </div>

                <div class="d-flex flex-column align-items-start text-start text-light mb-3">
                    <i class="fa fa-phone-alt mb-2" style="font-size: 1.5rem;"></i>
                    <a href="tel:+63282525063" class="text-light text-decoration-none">
                        <p class="mb-0">(02) 8252-5063</p>
                    </a>
                </div>

                <div class="d-flex flex-column align-items-start text-start text-light mb-3">
                    <i class="fa fa-envelope mb-2" style="font-size: 1.5rem;"></i>
                    <a href="mailto:lowriseadmin@twoserendraofficial.com" class="text-light text-decoration-none">
                        <p class="mb-0">lowriseadmin@twoserendraofficial.com</p>
                    </a>
                </div>


                <!-- Optional: Social Icons -->
                <!--
    <div class="d-flex justify-content-center pt-3 social-icons">
        <a class="btn btn-square btn-light me-2" href="#"><i class="fab fa-twitter"></i></a>
        <a class="btn btn-square btn-light me-2" href="#"><i class="fab fa-facebook-f"></i></a>
        <a class="btn btn-square btn-light me-2" href="#"><i class="fab fa-youtube"></i></a>
        <a class="btn btn-square btn-light me-2" href="#"><i class="fab fa-linkedin-in"></i></a>
    </div>
    -->
            </div>

            <div class="col-lg-2 col-md-6">
                <h5 class="text-uppercase text-light mb-4">Quick Links</h5>
                <a class="btn btn-link text-light" href="{{ route('home') }}">Home</a>
                <a class="btn btn-link text-light" href="{{ route('about') }}">About</a>
                <a class="btn btn-link text-light" href="{{ route('services') }}">Services</a>
                <a class="btn btn-link text-light" href="{{ route('contact') }}">Contact</a>
            </div>
            <div class="col-lg-4 col-md-6">
                <h5 class="text-uppercase text-light mb-4">Office Hours</h5>

                <!-- Admin -->
                <div class="mb-3">
                    <p class="text-uppercase text-light fw-bold mb-2">
                        <i class="fa fa-building me-2"></i>Admin
                    </p>
                    <div class="text-light small">
                        <div class="d-flex justify-content-between">
                            <span>Mon - Fri</span>
                            <span>8:00 AM - 5:00 PM</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Saturday</span>
                            <span>8:00 AM - 12:00 PM</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Sunday</span>
                            <span>Closed</span>
                        </div>
                    </div>
                </div>

                <!-- Engineering -->
                <div class="mb-3">
                    <p class="text-uppercase text-light fw-bold mb-2">
                        <i class="fa fa-cogs me-2"></i>Engineering
                    </p>
                    <div class="text-light small">
                        <div class="d-flex justify-content-between">
                            <span>Mon - Fri</span>
                            <span>8:00 AM - 5:00 PM</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Saturday</span>
                            <span>8:00 AM - 2:00 PM</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Sunday</span>
                            <span>Closed</span>
                        </div>
                    </div>
                </div>

                <!-- Payment -->
                <div>
                    <p class="text-uppercase text-light fw-bold mb-2">
                        <i class="fa fa-credit-card me-2"></i>Payment
                    </p>
                    <div class="text-light small">
                        <div class="d-flex justify-content-between">
                            <span>Mon - Fri</span>
                            <span>8:00 AM - 6:00 PM</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Saturday</span>
                            <span>8:00 AM - 3:00 PM</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Sunday</span>
                            <span>Closed</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>