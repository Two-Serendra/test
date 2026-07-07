@extends('layouts.app')

@section('title', 'Bridge Demo — Dashboard')

@section('content')

    <div id="historyPageLoading" class="history-page-loading">
        <div class="text-center">
            <div class="spinner-border text-primary"></div>

            <div class="mt-2 text-muted">
                Loading bookings...
            </div>
        </div>
    </div>

    <div class="bg-light rounded-3 py-3 px-3 border">
        <div class="d-flex align-items-center">
            <i class="bx bx-building-house text-primary fs-1 me-3"></i>

            <div>
                <h5 class="fw-bold mb-0">
                    AUSI Booking History
                </h5>
            </div>
        </div>
    </div>

    <div class="container-fluid px-3 py-3">
        <div class="" x-data="ausiBookingPageHistory">
            <div class="row mb-3">
                <div class="col-12 mb-3 mb-md-0">
                    <label class="form-label">Select Residence <span class="required">*</span></label>
                    <!-- <input type="text" name="email" id="mobile_email"> -->
                    <input type="hidden" name="mobile_unit_name" id="mobile_unit_name">
                    <input type="hidden" name="mobile_unit_role" id="mobile_unit_role">
                    <input type="hidden" id="history_mobile_email" name="email" :value="$store.superapp.user?.email || ''">

                    <select id="resident_id_ausi_booking_history" name="resident_id_ausi_booking_history"
                        class="form-select" x-model="$store.superapp.selectedUnit" required>
                        <option value="" disabled>-- Select Residence --</option>

                        <template x-for="(unit, index) in $store.superapp.units" :key="index">
                            <option :value="unit.name" :data-name="unit.name" :data-role="unit.role"
                                x-text="`${unit.role ?? ''} ${unit.name}`">
                            </option>
                        </template>
                    </select>

                </div>

                <div class="col-12 mb-3 mb-md-0">
                    <div id="historyWrapperAusi" class="position-relative">
                        <!-- Loading Overlay -->
                        <div id="historyLoadingAusi" class="history-loading d-none">
                            <div class="text-center">
                                <div class="spinner-border text-primary mb-2"></div>
                                <div class="text-muted">
                                    Loading bookings...
                                </div>
                            </div>
                        </div>
                        <div id="ausiHistoryTable" class="booking-history-list"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- <div id="debugPanelBookingHistoryAusi">
                                                                </div> -->

    </div>
    @include('mobile-app.ausi.ausi-mobile-modal')
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