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

            Alpine.data('dashboardPage', () => ({
                inShell: window.self !== window.top,
                apiStatus: '',

                init() {
                    // Root page of the miniapp — sticky bar, no back button
                    Alpine.store('superapp').bridge?.setHeader({
                        mode: 'sticky-no-back',
                        title: 'Bridge Demo',
                        backgroundColor: '#1e3a5f',
                        textStyle: 'white',
                        showHome: false,
                    });
                },

                async fetchUnitData() {
                    const store = Alpine.store('superapp');
                    if (!store.unit || !store.token) {
                        this.apiStatus = 'No unit or token — not running inside the shell.';
                        return;
                    }
                    this.apiStatus = 'Fetching…';
                    try {
                        const res = await fetch(`/api/unit-data?unitId=${store.unit.id}`, {
                            headers: { Authorization: `Bearer ${store.token}` },
                        });
                        const body = await res.text();
                        console.log('[Bridge Demo] API response:', body);
                        this.apiStatus = `Response status: ${res.status}`;
                    } catch (err) {
                        this.apiStatus = `Error: ${err.message}`;
                    }
                },
            }));
        });
    </script>

@endsection