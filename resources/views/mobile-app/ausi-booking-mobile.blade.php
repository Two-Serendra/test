@extends('layouts.app')

@section('title', 'Bridge Demo — Dashboard')

@section('content')
    <div class="" x-data="ausiBookingPage()">
        <div class="loading" x-show="$store.superapp.isLoading">
            <p>Waiting for shell context…</p>
        </div>
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

        <div class="alert alert-info">
            <div><strong>User:</strong> <span x-text="$store.superapp.user?.email ?? 'NO USER'"></span></div>

            <!-- ADD THIS -->
            <div><strong>DEBUG EMAIL:</strong> <span x-text="debugEmail ?? 'NOT SET'"></span></div>
        </div>

        <div class="alert alert-warning mt-2">
            <div><strong>STORE USER:</strong></div>
            <pre x-text="JSON.stringify($store.superapp.user, null, 2)"></pre>

            <div><strong>EMAIL DETECTED:</strong></div>
            <pre x-text="debugEmail ?? 'NOT READY'"></pre>

            <div><strong>RESIDENCES COUNT:</strong></div>
            <pre x-text="residences.length"></pre>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('ausiBookingPage', () => ({
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

                    this.debugEmail = 'davidzul08@gmail.com';

                    this.loadResidences('davidzul08@gmail.com');

                    this.$watch(
                        () => Alpine.store('superapp')?.user,    
                        (user) => {
                            this.tryLoad(user);
                        }
                    ); 

                    // 2. fallback poller (important for mobile shell delay)
                    let attempts = 0;

                    const interval = setInterval(() => {
                        const user = Alpine.store('superapp')?.user;

                        attempts++;

                        if (user) {
                            this.tryLoad(user);
                            clearInterval(interval);
                        }

                        if (attempts > 50) {
                            clearInterval(interval);
                            this.log("❌ STOP: user never arrived");
                        }

                    }, 200);
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

                tryLoad(user) {
                    if (!user) return;

                    const email =
                        typeof user === 'string'
                            ? user
                            : user?.email;

                    if (!email) return;

                    const cleanEmail = email.trim().toLowerCase();

                    if (this.debugEmail === cleanEmail) return; // prevent duplicate calls

                    this.debugEmail = cleanEmail;

                    this.log("🔥 USER READY: " + cleanEmail);

                    this.loadResidences(cleanEmail);
                },

                async loadResidences(email) {
                    this.log("Fetching residences for: " + email);

                    try {
                        const url = `https://twoserendra.com/mobile/residences?email=${encodeURIComponent(email)}`;

                        this.log("Request URL: " + url);

                        const res = await fetch(url);

                        const data = await res.json();

                        console.log("API RESPONSE:", data);

                        this.residences = data;

                        this.log("Residences loaded: " + this.residences.length);

                    } catch (err) {
                        this.log("❌ ERROR: " + err.message);
                    }
                }
            }));
        });
    </script>

@endsection