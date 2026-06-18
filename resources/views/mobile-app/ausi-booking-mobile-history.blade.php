@extends('layouts.app')

@section('title', 'Bridge Demo — Dashboard')

@section('content')
    <div class="" x-data="ausiBookingPageHistory">
        <div class="loading" x-show="$store.superapp.isLoading">
            <p>Waiting for shell context…</p>
        </div>
        <div class="warning-banner" x-show="!inShell && !$store.superapp.isLoading">
            Running outside the shell — bridge data is unavailable.
            In production this page runs inside the shell iframe.
        </div>

        <div class="card shadow-sm mb-4">
            <H3>Booking History</H3>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="mb-3 mb-md-0">
                        <label class="form-label">Select Residence <span class="required">*</span></label>
                        <!-- <input type="text" name="email" id="mobile_email"> -->
                        <input type="hidden" name="mobile_unit_name" id="mobile_unit_name">
                        <input type="hidden" name="mobile_unit_role" id="mobile_unit_role">
                        <input type="hidden" id="history_mobile_email" name="email"
                            :value="$store.superapp.user?.email || ''">
                        <dl>
                            <dd>

                                <select id="resident_id_ausi_booking_history" name="resident_id_ausi_booking_history"
                                    class="form-select" x-model="$store.superapp.selectedUnit" required>
                                    <option value="" disabled>-- Select Residence --</option>

                                    <template x-for="(unit, index) in $store.superapp.units" :key="index">
                                        <option :value="unit.name" :data-name="unit.name" :data-role="unit.role"
                                            x-text="`${unit.role ?? ''} ${unit.name}`">
                                        </option>
                                    </template>
                                </select>
                            </dd>
                        </dl>
                    </div>

                    <div id="historyWrapper" class="position-relative">
                        <!-- Loading Overlay -->
                        <div id="historyLoading" class="history-loading d-none">
                            <div class="text-center">
                                <div class="spinner-border text-primary mb-2"></div>
                                <div class="text-muted">
                                    Loading bookings...
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="ausiHistoryTable" class="booking-history-list"></div>
                </div>
            </div>
        </div>

        <div id="debugPanelBookingHistory">
        </div>

    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('ausiBookingPageHistory', () => ({
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

                    $('#history_mobile_email').val(
                        store?.user?.email || ''
                    );
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

            }));
        });
    </script>

@endsection