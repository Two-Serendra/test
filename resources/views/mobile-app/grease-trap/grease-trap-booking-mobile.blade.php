@extends('layouts.app')

@section('title', 'Bridge Demo — Dashboard')

@section('content')
    <div class="" x-data="greaseTrapBookingPage()">
        <!-- <div class="loading" x-show="$store.superapp.isLoading">
                                                        <p>Waiting for shell context…</p>
                                                    </div>
                                                    <div class="warning-banner" x-show="!inShell && !$store.superapp.isLoading">
                                                        Running outside the shell — bridge data is unavailable.
                                                        In production this page runs inside the shell iframe.
                                                    </div> -->
        <!-- <div class="bg-light rounded-3 py-3 px-3 border">
            <div class="d-flex align-items-center">
                <i class="bx bx-water text-primary fs-1 me-3"></i>

                <div>
                    <h5 class="fw-bold mb-0">
                        Grease Trap Cleaning
                    </h5>
                </div>
            </div>
        </div> -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="mb-3">
                    <a href="{{ route('grease.trap.booking.mobile.history') }}" class="history-shortcut">

                        <div class="history-shortcut-icon">
                            <i class="bx bx-history"></i>
                        </div>

                        <div>
                            <div class="fw-semibold">
                                My Bookings
                            </div>

                            <small class="text-muted">
                                View upcoming and past bookings
                            </small>
                        </div>

                        <i class="bx bx-chevron-right ms-auto"></i>

                    </a>

                </div>

                <form method="POST" action="{{ route('grease.trap.booking.mobile.store') }}" enctype="multipart/form-data"
                    id="userGtNewBookingMobile" class="needs-validation" novalidate>
                    @csrf

                    <div class="row mb-3">

                        <!-- <div class="">
                                                                                    <dt>Email</dt>
                                                                                    <dd x-text="$store.superapp.user?.email ?? '—'"></dd>
                                                                                </div> -->
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label">Select Residence <span class="required">*</span></label>
                            <input type="hidden" name="email" id="mobile_email">
                            <input type="hidden" name="mobile_unit_name" id="mobile_unit_name">
                            <input type="hidden" name="mobile_unit_role" id="mobile_unit_role">
                            <input type="hidden" name="email" :value="$store.superapp.user?.email || ''">


                            <select id="resident_id_gt" name="resident_id_gt" class="form-select"
                                x-model="$store.superapp.selectedUnit" required>
                                <option value="" disabled>-- Select Residence --</option>

                                <template x-for="(unit, index) in $store.superapp.units" :key="index">
                                    <option :value="unit.name" :data-name="unit.name" :data-role="unit.role"
                                        x-text="`${unit.role ?? ''} ${unit.name}`">
                                    </option>
                                </template>
                            </select>

                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date <span class="required">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class='bx bx-calendar'></i></span>
                                <input type="text" class="form-control bg-white text-dark" id="GtBookingDate"
                                    name="booking_date" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">
                            Select Time Slot <span class="required">*</span>
                        </label>

                        @php
                            $slots = [
                                '9:00AM - 10:00AM',
                                '10:00AM - 11:00AM',
                                '11:00AM - 12:00PM',
                                '1:00PM - 2:00PM',
                                '2:00PM - 3:00PM',
                                '3:00PM - 4:00PM',
                                '4:00PM - 5:00PM',
                            ]; 
                        @endphp
                        <div id="slotWrapper" class="position-relative">
                            <div id="slotLoadingGt" class="slot-loading d-none">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>

                            <div class="row g-3">
                                @foreach ($slots as $slot)
                                    <div class="col-12">
                                        <input type="radio" class="btn-check gt-booking-slot" name="booking_time_slot"
                                            id="slot{{ $loop->index }}" value="{{ $slot }}" data-slot="{{ $slot }}" disabled
                                            required>

                                        <label class="slot-card btn btn-outline-primary w-100 disabled"
                                            for="slot{{ $loop->index }}">
                                            {{ $slot }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" form="userGtNewBookingMobile" id="saveUserGtBtn"
                            class="btn btn-primary d-flex align-items-center justify-content-center customBtn"
                            style="min-width: 100px; height: 38px;">
                            <span class="btn-text" disabled>SUBMIT</span>
                        </button>

                    </div>
                </form>
            </div>
        </div>

        <div id="debugPanelGt" style="
                                                                                                                    position: fixed;
                                                                                                                    bottom: 0;
                                                                                                                    left: 0;
                                                                                                                    right: 0;
                                                                                                                    height: 140px;
                                                                                                                    overflow: auto;
                                                                                                                    background: black;
                                                                                                                    color: #00ff00;
                                                                                                                    font-size: 11px;
                                                                                                                    z-index: 99999;
                                                                                                                    padding: 10px;
                                                                                                                ">
                </div>

    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('greaseTrapBookingPage', () => ({
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
                        mode: 'sticky',
                        title: 'Grease Trap Booking',
                        backgroundColor: '#fff',
                        textStyle: 'black',
                        showHome: false,
                    });
                },

            }));
        });
    </script>


@endsection