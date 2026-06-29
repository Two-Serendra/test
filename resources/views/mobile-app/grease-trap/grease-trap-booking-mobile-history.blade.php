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

    <div class="" x-data="gtBookingPageHistory">
        <div class="bg-light rounded-3 py-3 px-3 border">
            <div class="d-flex align-items-center">
                <i class="bx bx-water text-primary fs-1 me-3"></i>

                <div>
                    <h5 class="fw-bold mb-0">
                        Grease Trap Cleaning History
                    </h5>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="row mb-3">
                <div class="mb-3 mb-md-0">
                    <label class="form-label">Select Residence <span class="required">*</span></label>
                    <!-- <input type="text" name="email" id="mobile_email"> -->
                    <input type="hidden" name="mobile_unit_name" id="mobile_unit_name">
                    <input type="hidden" name="mobile_unit_role" id="mobile_unit_role">
                    <input type="hidden" id="history_mobile_email_gt" name="email"
                        :value="$store.superapp.user?.email || ''">
                    <dl>
                        <dd>

                            <select id="resident_id_gt_booking_history" name="resident_id_gt_booking_history"
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

                <div id="historyWrapperGt" class="position-relative">
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
                <div id="gtHistoryTable" class="booking-history-list"></div>
                <div id="gtHistoryPagination" class="mt-3"></div>
            </div>
        </div>
    </div>

    <div id="debugPanelBookingHistoryGt" style="position: fixed;
                                    bottom: 0;
                                    left: 0;
                                    right: 0;
                                    height: 140px;
                                    overflow: auto;
                                    background: black;
                                    color: #00ff00;
                                    font-size: 11px;
                                    z-index: 99999;
                                    padding: 10px;"></div>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('gtBookingPageHistory', () => ({
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

                    $('#history_mobile_email_gt').val(
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