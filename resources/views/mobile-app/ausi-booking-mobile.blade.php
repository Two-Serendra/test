@extends('layouts.app')

@section('title', 'Bridge Demo — Dashboard')

@section('content')
    <div class="" x-data="dashboardPage">

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

                            <select x-model="selectedResidence">
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
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('headerControls', () => ({
                mode: 'sticky-no-back',
                title: 'Bridge Demo',
                subtitle: '',
                backgroundColor: '#1e3a5f',
                textStyle: 'white',
                showHome: false,

                applyHeader() {
                    const payload = { mode: this.mode, textStyle: this.textStyle };
                    if (this.title) payload.title = this.title;
                    if (this.subtitle) payload.subtitle = this.subtitle;
                    if (this.backgroundColor) payload.backgroundColor = this.backgroundColor;
                    payload.showHome = this.showHome;
                    Alpine.store('superapp').bridge?.setHeader(payload);
                },
            }));
        });

        document.addEventListener('alpine:init', () => {
            Alpine.data('dashboardPage', () => ({
                residences: [],
                selectedResidence: null,

                init() {
                    this.waitForUserThenLoad();
                    this.setHeader();
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

                waitForUserThenLoad() {
                    const interval = setInterval(() => {
                        const user = Alpine.store('superapp')?.user;

                        if (user?.email) {
                            clearInterval(interval);
                            this.loadResidences(user.email);
                        }
                    }, 200);
                },

                async loadResidences(email) {
                    console.log('🔥 loadResidences CALLED with:', email);

                    try {
                        const url = `/mobile/residences?email=${encodeURIComponent(email)}`;
                        console.log('🌐 Fetching:', url);

                        const res = await fetch(url);

                        console.log('📡 Response status:', res.status);

                        const data = await res.json();
                        console.log('📦 Data received:', data);

                        this.residences = data;

                    } catch (err) {
                        console.error('❌ Failed to load residences:', err);
                    }
                }
            }));
        });
    </script>

@endsection