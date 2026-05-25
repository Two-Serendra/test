<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo display-flex align-items-center justify-content-center my-3">
        <a href="{{ route('admin.dashboard') }}" class="app-brand-link">
            <img src="{{ asset('assets/images/TWO SERENDRA LOGO PNG.png') }}"
                style="height: 100%; width: 100%; object-fit: contain;" alt="two serendra" class="brand-logo" />
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Dashboard -->
        @roles(1)
        <li class="menu-item">
            <a href="{{ route('admin.dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Analytics">Dashboard</div>
            </a>
        </li>
        @endroles

        @roles(1)
        <li class="menu-header small text-uppercase"><span class="menu-header-text">Booking</span></li>
        <!-- <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-file"></i>
                <div data-i18n="Form Elements">Work Permit</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('admin.show.minor.work.permit') ? 'active' : '' }}">
                    <a href="{{ route('admin.show.minor.work.permit') }}" class="menu-link">
                        <div data-i18n="Typography">Online</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('admin.show.walkin.work.permit') ? 'active' : '' }}">
                    <a href="{{ route('admin.show.walkin.work.permit') }}" class="menu-link">
                        <div data-i18n="Typography">Walk-in</div>
                    </a>
                </li>
            </ul>
        </li> -->
        @endroles


        @roles(1, 6, 7, 9)
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-building"></i>
                <div>Amenities</div>
                <span id="amenity-booking-counter" class="badge bg-danger d-none"
                    style="min-width: 22px; text-align: center; display: inline-flex; justify-content: center; align-items: center;">
                    0
                </span>
            </a>

            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('admin.booking.activities') ? 'active' : '' }}">
                    <a href="{{ route('admin.booking.activities') }}" class="menu-link">
                        <div>Booking</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('admin.activity.history') ? 'active' : '' }}">
                    <a href="{{ route('admin.activity.history') }}" class="menu-link">
                        <div>Reports</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('admin.activity.booking.calendar') ? 'active' : '' }}">
                    <a href="{{ route('admin.activity.booking.calendar') }}" class="menu-link">
                        <div>Calendar</div>
                    </a>
                </li>
                @endroles
                @roles(1, 7, 9)
                <li class="menu-item">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <div>Setup</div>
                    </a>
                    <ul class="menu-sub">
                        <li class="menu-item {{ request()->routeIs('admin.show.amenities') ? 'active' : '' }}">
                            <a href="{{ route('admin.show.amenities') }}" class="menu-link">
                                <div>Amenities</div>
                            </a>
                        </li>
                        <li class="menu-item {{ request()->routeIs('admin.show.activities') ? 'active' : '' }}">
                            <a href="{{ route('admin.show.activities') }}" class="menu-link">
                                <div>Activities</div>
                            </a>
                        </li>

                        <li class="menu-item {{ request()->routeIs('admin.show.schedule.blocking') ? 'active' : '' }}">
                            <a href="{{ route('admin.show.schedule.blocking') }}" class="menu-link">
                                <div>Schedule Blocking</div>
                            </a>
                        </li>

                        <li
                            class="menu-item {{ request()->routeIs('admin.show.date.blocking.activities') ? 'active' : '' }}">
                            <a href="{{ route('admin.show.date.blocking.activities') }}" class="menu-link">
                                <div>Date Blocking</div>
                            </a>
                        </li>
                    </ul>
                </li>
                @endroles

            </ul>
        </li>

        @roles(1, 6, 7, 9)
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-dumbbell"></i>
                <div>Fitness Hub</div>
                <span id="fitness-hub-booking-counter" class="badge bg-danger d-none"
                    style="min-width: 22px; text-align: center; display: inline-flex; justify-content: center; align-items: center;">
                    0
                </span>
            </a>

            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('admin.booking.fitness') ? 'active' : '' }}">
                    <a href="{{ route('admin.booking.fitness') }}" class="menu-link">
                        <div>Booking</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('admin.fitnessHub.history') ? 'active' : '' }}">
                    <a href="{{ route('admin.fitnessHub.history') }}" class="menu-link">
                        <div>Reports</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('admin.fitness.hub.booking.calendar') ? 'active' : '' }}">
                    <a href="{{ route('admin.fitness.hub.booking.calendar') }}" class="menu-link">
                        <div>Calendar</div>
                    </a>
                </li>

                <li class="menu-item">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <div>Setup</div>
                    </a>
                    <ul class="menu-sub">
                        <li class="menu-item {{ request()->routeIs('admin.show.fitness.hub') ? 'active' : '' }}">
                            <a href="{{ route('admin.show.fitness.hub') }}" class="menu-link">
                                <div>Fitness Hub</div>
                            </a>
                        </li>

                        <li
                            class="menu-item {{ request()->routeIs('admin.show.schedule.blocking.fitness.hub') ? 'active' : '' }}">
                            <a href="{{ route('admin.show.schedule.blocking.fitness.hub') }}" class="menu-link">
                                <div>Schedule Blocking</div>
                            </a>
                        </li>

                        <li
                            class="menu-item {{ request()->routeIs('admin.show.date.blocking.fitness.hub') ? 'active' : '' }}">
                            <a href="{{ route('admin.show.date.blocking.fitness.hub') }}" class="menu-link">
                                <div>Date Blocking</div>
                            </a>
                        </li>
                    </ul>
                </li>

            </ul>
        </li>
        @endroles

        @roles(1, 2, 3, 5, 6, 7, 8)
        <!-- <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-home-alt"></i>
                <div data-i18n="Form Elements">
                    Function Rooms
                    <span id="function-room-counter" class="badge bg-danger d-none"
                        style="min-width: 22px; text-align: center; display: inline-flex; justify-content: center; align-items: center;">
                        0
                    </span>

                </div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('admin.show.function.room.bookings') ? 'active' : '' }}">
                    <a href="{{ route('admin.show.function.room.bookings') }}" class="menu-link">
                        <div data-i18n="Typography">Booking</div>
                    </a>
                </li>

                @roles(1, 6, 7, 8)
                <li
                    class="menu-item {{ request()->routeIs('admin.show.function.room.booking.records') ? 'active' : '' }}">
                    <a href="{{ route('admin.show.function.room.booking.records') }}" class="menu-link">
                        <div data-i18n="Typography">Reports</div>
                    </a>
                </li>

                <li
                    class="menu-item {{ request()->routeIs('admin.show.function.rooms.date.blocking') ? 'active' : '' }}">
                    <a href="{{ route('admin.show.function.rooms.date.blocking') }}" class="menu-link">
                        <div data-i18n="Typography">Date Blocking</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('admin.show.function.room.discounts') ? 'active' : '' }}">
                    <a href="{{ route('admin.show.function.room.discounts') }}" class="menu-link">
                        <div data-i18n="Typography">Discounts</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('admin.show.add.ons') ? 'active' : '' }}">
                    <a href="{{ route('admin.show.add.ons') }}" class="menu-link">
                        <div data-i18n="Typography">Add Ons</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('admin.show.function.rooms') ? 'active' : '' }}">
                    <a href="{{ route('admin.show.function.rooms') }}" class="menu-link">
                        <div data-i18n="Typography">Setup</div>
                    </a>
                </li>

                @endroles
            </ul>
        </li> -->
        @endroles

        @roles(1, 6, 9)
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-wrench"></i>
                <div data-i18n="Form Elements">
                    Grease Trap
                    <span id="grease-trap-booking-counter" class="badge bg-danger d-none"
                        style="min-width: 22px; text-align: center; display: inline-flex; justify-content: center; align-items: center;">
                        0
                    </span>

                </div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('admin.booking.grease.trap') ? 'active' : '' }}">
                    <a href="{{ route('admin.booking.grease.trap') }}" class="menu-link">
                        <div data-i18n="Typography">Booking</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('admin.booking.grease.trap.calendar') ? 'active' : '' }}">
                    <a href="{{ route('admin.booking.grease.trap.calendar') }}" class="menu-link">
                        <div data-i18n="Typography">Calendar</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('admin.report.grease.trap') ? 'active' : '' }}">
                    <a href="{{ route('admin.report.grease.trap') }}" class="menu-link">
                        <div data-i18n="Typography">Reports</div>
                    </a>
                </li>
            </ul>
        </li>

        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-bug"></i>
                <div data-i18n="Form Elements">
                    Pest Control
                    <span id="pest-control-booking-counter" class="badge bg-danger d-none"
                        style="min-width: 22px; text-align: center; display: inline-flex; justify-content: center; align-items: center;">
                        0
                    </span>

                </div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('admin.booking.pest.control') ? 'active' : '' }}">
                    <a href="{{ route('admin.booking.pest.control') }}" class="menu-link">
                        <div data-i18n="Typography">Booking</div>
                    </a>
                </li>


                <li class="menu-item {{ request()->routeIs('admin.booking.pest.control.calendar') ? 'active' : '' }}">
                    <a href="{{ route('admin.booking.pest.control.calendar') }}" class="menu-link">
                        <div data-i18n="Typography">Calendar</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('admin.report.pest.control') ? 'active' : '' }}">
                    <a href="{{ route('admin.report.pest.control') }}" class="menu-link">
                        <div data-i18n="Typography">Reports</div>
                    </a>
                </li>
            </ul>
        </li>


        <!-- <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-shield-quarter"></i>
                <div data-i18n="Form Elements">
                    AUSI
                    <span id="ausi-booking-counter" class="badge bg-danger d-none"
                        style="min-width: 22px; text-align: center; display: inline-flex; justify-content: center; align-items: center;">
                        0
                    </span>

                </div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('admin.booking.ausi') ? 'active' : '' }}">
                    <a href="{{ route('admin.booking.ausi') }}" class="menu-link">
                        <div data-i18n="Typography">Booking</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('admin.booking.ausi.calendar') ? 'active' : '' }}">
                    <a href="{{ route('admin.booking.ausi.calendar') }}" class="menu-link">
                        <div data-i18n="Typography">Calendar</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('admin.report.ausi') ? 'active' : '' }}">
                    <a href="{{ route('admin.report.ausi') }}" class="menu-link">
                        <div data-i18n="Typography">Reports</div>
                    </a>
                </li>
            </ul>
        </li> -->
        @endroles

        <!-- Manage -->
        @roles(1)
        <li class="menu-header small text-uppercase"><span class="menu-header-text">Manage</span></li>
        <li class="menu-item {{ request()->routeIs('admin.show.user') ? 'active' : '' }}">
            <a href="{{ route('admin.show.user') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-user-circle"></i>
                <div data-i18n="Basic">User</div>
            </a>
        </li>
        @endroles

        @roles(1, 2)
        <li class="menu-item {{ request()->routeIs('admin.show.resident.details') ? 'active' : '' }}">
            <a href="{{ route('admin.show.resident.details') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-book-content"></i>
                <div data-i18n="Basic">Resident Details</div>
            </a>
        </li>
        @endroles

        @roles(1, 10)
        <li class="menu-item {{ request()->routeIs('admin.show.events', 'admin.search.events') ? 'active' : '' }}">
            <a href="{{ route('admin.show.events') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-calendar-star"></i>
                <div data-i18n="Basic">Events</div>
            </a>
        </li>
        @endroles

        @roles(1)
        <li class="menu-item">
            <a href="javascript:void(0)" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-bookmarks"></i>
                <div data-i18n="User interface">Pages</div>
            </a>
            <ul class="menu-sub">
                <!-- <li class="menu-item">
                    <a href="ui-toasts.html" class="menu-link">
                        <div data-i18n="Toasts">Our Team</div>
                    </a>
                </li> -->
                <li class="menu-item {{ request()->routeIs('admin.show.gallery') ? 'active' : '' }}">
                    <a href="{{ route('admin.show.gallery') }}" class="menu-link">
                        <div data-i18n="Tooltips & Popovers">Gallery</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('admin.downloads') ? 'active' : '' }}">
                    <a href="{{ route('admin.downloads') }}" class="menu-link">
                        <div data-i18n="Typography">Download</div>
                    </a>
                </li>
            </ul>
        </li>
        @endroles
        <!-- Misc -->
        <!-- <li class="menu-header small text-uppercase"><span class="menu-header-text">Misc</span></li>
        <li class="menu-item">
            <a href="https://github.com/themeselection/sneat-html-admin-template-free/issues" target="_blank"
                class="menu-link">
                <i class="menu-icon tf-icons bx bx-support"></i>
                <div data-i18n="Support">Support</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="https://themeselection.com/demo/sneat-bootstrap-html-admin-template/documentation/" target="_blank"
                class="menu-link">
                <i class="menu-icon tf-icons bx bx-file"></i>
                <div data-i18n="Documentation">Documentation</div>
            </a>
        </li> -->
    </ul>
</aside>