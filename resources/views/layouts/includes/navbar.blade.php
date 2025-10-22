<div class="container-fluid bg-white sticky-top wow fadeIn" data-wow-delay="0.1s">
    <div class="container">
        <nav class="navbar navbar-expand-lg bg-white navbar-light p-lg-0" style="background-color: #008b26;">
            <a href="{{ route('home') }}" class="navbar-brand d-lg-none">
                <img src="{{ asset('assets/images/TWO SERENDRA LOGO PNG (White).png') }}"
                    style="height: 50px; width: auto; object-fit: contain;" alt="2serendra" />
            </a>
            <button type="button" class="navbar-toggler me-0" data-bs-toggle="collapse"
                data-bs-target="#navbarCollapse">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <div class="navbar-nav">
                    <a href="{{ route('home') }}"
                        class="nav-item nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                    <a href="{{ route('about') }}"
                        class="nav-item nav-link {{ request()->routeIs('about') ? 'active' : '' }}">About</a>
                    <a href="{{ route('services') }}"
                        class="nav-item nav-link {{ request()->routeIs('services') ? 'active' : '' }}">Services</a>

                    <!-- <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">Online Forms</a>
                        <div class="dropdown-menu bg-light rounded-0 rounded-bottom m-0">
                            <a href="{{ route('minor.work.permit') }}" class="dropdown-item">Minor Work Permit</a>
                            <a href="testimonial.html" class="dropdown-item">Pull out Permit</a>
                            <a href="appoinment.html" class="dropdown-item">Delivery Permit</a>
                        </div>
                    </div> -->

                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">Pages</a>
                        <div class="dropdown-menu bg-light rounded-0 rounded-bottom m-0">
                            <a href="{{ route('ourTeam') }}" class="dropdown-item">Our Team</a>
                            <a href="{{ route('gallery') }}" class="dropdown-item">Gallery</a>
                            <a href="{{ route('events') }}" class="dropdown-item">Events</a>
                            <a href="{{ route('downloads') }}" class="dropdown-item">Forms</a>
                        </div>
                    </div>
                    <a href="{{ route('contact') }}"
                        class="nav-item nav-link {{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a>
                </div>

                <!-- <div class="ms-auto">
                    <a href="{{ route('booking.list') }}" class="btn btn-primary custom-btn">Book Now</a>
                </div> -->

                <!-- @auth
                    <div class="nav-item dropdown ms-3 mt-2 mt-lg-0">
                        <a href="#"
                            class="nav-link dropdown-toggle d-flex align-items-center justify-content-center position-relative"
                            id="notifDropdown" role="button" data-bs-toggle="dropdown"
                            style="width: 40px; height: 40px; background-color: #008b26; border-radius: 50%; color: white;"
                            data-bs-display="static">
                            <i class='bx bx-bell' style="font-size: 1.4rem;"></i>
                            @if(auth()->user()->unreadNotifications->count() > 0)
                                <span class="position-absolute top-0 start-100 badge rounded-pill bg-danger"
                                    style="transform: translate(-60%, -35%);">
                                    {{ auth()->user()->unreadNotifications->count() }}
                                </span>
                            @endif
                        </a>

                        <div class="dropdown-menu bg-light rounded-0 rounded-bottom m-0"
                            style="min-width: 320px; max-width: 350px; max-height: 300px; overflow-y: auto; word-wrap: break-word; white-space: normal;"
                            id="notifDropdownMenu">



                            @forelse(auth()->user()->notifications->take(5) as $notification)
                                @php
                                    $bookingId = $notification->data['booking_id'] ?? null;
                                    $message = \Illuminate\Support\Str::limit($notification->data['message'] ?? 'New notification', 80);
                                    $url = $bookingId ? route('show.functionroom.booking.details', $bookingId) : '#';
                                @endphp

                                <a href="{{ $url }}"
                                    class="dropdown-item text-start mark-as-read {{ $notification->read_at ? 'notification-read' : 'fw-bold' }}"
                                    data-id="{{ $notification->id }}" data-url="{{ $url }}"
                                    style="white-space: normal; text-wrap: wrap;" data-bs-display="static">
                                    <i class="bx bx-bell me-2"></i> {{ $message }}
                                    <br>
                                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                </a>
                            @empty
                                @if(auth()->user()->notifications->count() === 0)
                                    <span class="dropdown-item text-muted" style="white-space: normal; text-wrap: wrap;">
                                        No notifications
                                    </span>
                                @endif
                            @endforelse

                        </div>
                    </div>
                @endauth -->

                <!-- <div class="nav-item dropdown ms-3 mt-2 mt-lg-0">
                    <a href="#" class="nav-link dropdown-toggle d-flex align-items-center justify-content-center"
                        id="userDropdown" role="button" data-bs-toggle="dropdown"
                        style="width: 40px; height: 40px; background-color: #008b26; border-radius: 50%; color: white;">
                        <i class='bx bx-user' style="font-size: 1.4rem;"></i>
                    </a>
                    <div class="dropdown-menu bg-light rounded-0 rounded-bottom m-0">
                        @auth
                            <a href="{{ route('profile.edit') }}" class="dropdown-item">
                                <i class='bx bx-user-circle me-2'></i> Profile
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class='bx bx-log-out me-2'></i> Logout
                                </button>
                            </form>

                        @else
                            <a href="{{ route('login') }}" class="dropdown-item">
                                <i class='bx bx-log-in me-2'></i> Login
                            </a>
                        @endauth
                    </div>
                </div> -->
            </div>
        </nav>
    </div>
</div>