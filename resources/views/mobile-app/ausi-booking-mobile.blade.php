@extends('layouts.app')

@section('title', 'Bridge Demo — Dashboard')

@section('content')
    <div class="" x-data="dashboardPage()">

        {{-- Loading ──────────────────────────────────────────────── --}}
        <div class="loading" x-show="$store.superapp.isLoading">
            <p>Waiting for shell context…</p>
        </div>

        {{-- Dev warning (not inside the shell) ──────────────────── --}}
        <div class="warning-banner" x-show="!inShell && !$store.superapp.isLoading">
            Running outside the shell — bridge data is unavailable.
            In production this page runs inside the shell iframe.
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="alert alert-info">
                    <div><strong>User:</strong> <span x-text="$store.superapp.user?.email ?? 'NO USER'"></span></div>
                    <div><strong>Residences:</strong> <span x-text="residences.length"></span></div>
                    <div><strong>Selected:</strong> <span x-text="selectedResidence ?? 'NONE'"></span></div>
                </div>

                <form method="POST" action="{{ route('ausi.booking.store') }}" enctype="multipart/form-data"
                    id="userAusiNewBooking" class="needs-validation" novalidate>
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label">Select Residence <span class="required">*</span></label>
                            <dl>
                                <dt>Name</dt>
                                <dd x-text="$store.superapp.user?.name  ?? '—'"></dd>
                                <dt>Email</dt>
                                <dd x-text="$store.superapp.user?.email ?? '—'"></dd>
                                <dt>Role</dt>
                                <dd x-text="$store.superapp.user?.role  ?? '—'"></dd>
                            </dl>

                            <select x-model="selectedResidence" name="residence_id">
                                <option value="">-- Select Residence --</option>

                                <template x-for="residence in residences" :key="residence.id">
                                    <option :value="residence.id"
                                        x-text="`${residence.resident_type} - Unit ${residence.unit_no}`">
                                    </option>
                                </template>
                            </select>

                            <template x-if="residences.length === 0">
                                <small class="text-muted">No units found for this account.</small>
                            </template>

                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date <span class="required">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class='bx bx-calendar'></i></span>
                                <input type="text" class="form-control bg-white text-dark" id="AusiBookingDate"
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
                                '8:00 AM - 8:30 AM',
                                '8:30 AM - 9:00 AM',
                                '9:00 AM - 9:30 AM',
                                '9:30 AM - 10:00 AM',
                                '10:00 AM - 10:30 AM',
                                '10:30 AM - 11:00 AM',
                                '11:00 AM - 11:30 AM',
                                '11:30 AM - 12:00 NN',

                                '1:00 PM - 1:30 PM',
                                '1:30 PM - 2:00 PM',
                                '2:00 PM - 2:30 PM',
                                '2:30 PM - 3:00 PM',
                                '3:00 PM - 3:30 PM',
                                '3:30 PM - 4:00 PM',
                                '4:00 PM - 4:30 PM',
                                '4:30 PM - 5:00 PM',
                            ]; 
                        @endphp
                        <div id="slotWrapper" class="position-relative">
                            <div id="slotLoading" class="slot-loading d-none">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>

                            <div class="row g-2">
                                @foreach ($slots as $slot)
                                    <div class="col-lg-3 col-md-4 col-6">
                                        <input type="radio" class="btn-check ausi-booking-slot" name="booking_time_slot"
                                            id="slot{{ $loop->index }}" value="{{ $slot }}" data-slot="{{ $slot }}" required>

                                        <label class="btn btn-outline-primary w-100 py-2" for="slot{{ $loop->index }}">
                                            {{ $slot }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" form="userAusiNewBooking" id="saveUserAusiBtn"
                            class="btn btn-primary d-flex align-items-center justify-content-center customBtn"
                            style="min-width: 100px; height: 38px;">
                            <span class="btn-text">SUBMIT</span>
                        </button>

                    </div>
                </form>
            </div>
        </div>

        <div class="alert alert-dark mt-2" style="white-space: pre-wrap; font-size: 12px;">
            <strong>DEBUG PANEL</strong><br>
            <div x-text="debugLog"></div>
        </div>

        <div class="alert alert-danger mt-2" x-show="debugLog.includes('ERROR')">
            Something went wrong. Check debug logs below.
        </div>
    </div>



    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('dashboardPage', () => ({
                residences: [],
                selectedResidence: null,
                debugLog: '',

                log(msg) {
                    console.log(msg);
                    this.debugLog += msg + "\n";
                },
                init() {
                    this.log("🚀 INIT STARTED");

                    this.setHeader();
                    console.log('🚀 SUPERAPP INIT CALLED');
                    console.log('inside shell:', isInsideShell());
                    // extra safety delay (VERY important for mobile shells)
                    setTimeout(() => {
                        this.startUserListener();
                    }, 500);
                },

                startUserListener() {
                    this.log("👀 Waiting for superapp user...");

                    let attempts = 0;

                    const interval = setInterval(() => {
                        const user = Alpine.store('superapp')?.user;

                        this.log("CHECK #" + attempts + " user = " + JSON.stringify(user));

                        attempts++;

                        const email = user?.email;

                        if (email && email !== 'undefined' && email !== 'null') {
                            const cleanEmail = email.trim().toLowerCase();

                            this.log("🔥 FINAL EMAIL READY: " + cleanEmail);

                            clearInterval(interval);

                            this.loadResidences(cleanEmail);
                        }

                        // safety stop (prevents infinite loop)
                        if (attempts > 50) {
                            clearInterval(interval);
                            this.log("❌ STOPPED: user never became available");
                        }

                    }, 300);
                },
                setHeader() {
                    Alpine.store('superapp')?.bridge?.setHeader({
                        mode: 'sticky-no-back',
                        title: 'Bridge Demo',
                        backgroundColor: '#1e3a5f',
                        textStyle: 'white',
                        showHome: false,
                    });
                },

                async loadResidences(email) {
                    this.log("Fetching residences for: " + email);

                    try {
                        const url = `/mobile/residences?email=${encodeURIComponent(email)}`;

                        this.log("Request URL: " + url);

                        const res = await fetch(url);

                        this.log("HTTP STATUS: " + res.status);

                        const text = await res.text();

                        this.log("RAW RESPONSE: " + text);

                        const data = JSON.parse(text);

                        this.residences = Array.isArray(data) ? data : [];

                        this.log("Residences loaded: " + this.residences.length);

                    } catch (err) {
                        this.log("❌ ERROR: " + err.message);
                        this.residences = [];
                    }
                }
            }));
        });
    </script>

@endsection