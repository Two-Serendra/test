@extends('layouts.backend')
@section('content')
    <style>
        * {
            text-decoration: none !important;
        }

        #calendar-grease-trap-booking {
            max-width: 1100px;
            margin: 0 auto;
            position: relative;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        #calendar-grease-trap-booking::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url("{{ asset('assets/images/2S DAHON.png') }}");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0.1;
            pointer-events: none;
        }

        /* Adjust this value for desired transparency */
        .fc-header-toolbar {
            margin-bottom: 15px;
        }

        .fc-toolbar-title {
            color: white !important;
            font-weight: bold !important;
        }

        .fc-day-today {
            background-color: rgba(0, 123, 255, 0.1) !important;
        }

        .fc-event {
            background-color: #28a745 !important;
            border: none !important;
            font-size: 14px;
            padding: 3px 5px;
            border-radius: 4px !important;
            color: #fff !important;
            cursor: pointer !important;

        }

        .fc-day-header,
        .fc-day-top {
            color: #008b26 !important;
            font-weight: bold;
            text-decoration: none !important;


        }


        .fc-button {
            background-color: #f8f9fa !important;
            color: #6c757d !important;
            border: 1px solid #ddd !important;
            font-size: 14px;
            margin-right: 5px !important;

        }

        .fc-button.fc-state-active {
            background-color: #28a745 !important;
            color: white !important;
            border: 1px solid #218838 !important;
            opacity: 1 !important;
        }

        .filter-btn.active {
            background-color: #28a745 !important;
            color: #fff !important;
            font-weight: bold;
        }


        .filter-btn:hover,
        .filter-btn:focus {
            background-color: #28a745 !important;
            color: #fff !important;
            font-weight: bold;
        }
    </style>

    <div class="container">
        <div class="row">


            <div id="calendar-grease-trap-booking"></div>

        </div>
    </div>

    @include('backend.modal.grease-trap.grease-trap-booking-modal')
@endsection
@push('js')
    <script>
        $(document).ready(function () {

            window.showLoading = function () {
                $('#loadingOverlay').css('display', 'flex').hide().fadeIn(150);
            }

            window.hideLoading = function () {
                $('#loadingOverlay').fadeOut(150);
            }

            var schedule = @json($events);

            $('#calendar-grease-trap-booking').fullCalendar({
                timezone: 'local',
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },
                contentHeight: 750,
                events: schedule,

                loading: function (isLoading) {
                    isLoading ? showLoading() : hideLoading();
                },

                eventRender: function (event, element) {
                    element.find('.fc-time').remove();

                    if (event.emergency == 1) {
                        element.find('.fc-title').css({
                            'color': 'red',
                            'font-weight': 'bold'
                        });
                    }
                },

                eventClick: function (event) {

                    if (!event.id) {
                        alert("No event ID found.");
                        return;
                    }

                    var schedule_id = event.id;

                    showLoading();

                    $.ajax({
                        url: '/admin/fetch/grease-trap-calendar-details/' + schedule_id,
                        method: 'GET',

                        success: function (data) {
                            $('#calendarModalGreaseTrap').modal('show');

                            // ⚠️ safer targeting (avoid conflicts if reused elsewhere)
                            $('#calendarModalGreaseTrap #edit_id').val(data.id);

                            $('#calendarModalGreaseTrap #calendar_unit').text(data.unit_no ?? 'N/A');
                            $('#calendarModalGreaseTrap #calendar_name').text(data.name ?? 'N/A');
                            $('#calendarModalGreaseTrap #calendar_booking_date').text(data.booking_date ?? 'N/A');
                            $('#calendarModalGreaseTrap #calendar_time_slot').text(data.booking_time_slot ?? 'N/A');
                            $('#calendarModalGreaseTrap #calendar_transaction_no').text(data.transaction_no ?? 'N/A');
                            $('#calendarModalGreaseTrap #calendar_srf_no').text(data.srf_no ?? 'N/A');

                            // Resident Type
                            let residentType = (data.resident_type || 'N/A').toUpperCase();
                            let residentBadge = `<span class="badge bg-secondary">${residentType}</span>`;

                            if (residentType.includes('TENANT')) {
                                residentBadge = `<span class="badge bg-danger">${residentType}</span>`;
                            } else if (residentType.includes('OWNER')) {
                                residentBadge = `<span class="badge bg-primary">${residentType}</span>`;
                            }

                            $('#calendarModalGreaseTrap #display_resident_type_calendar').html(residentBadge);

                            // Charged Type
                            let chargedType = (data.charged_type || 'N/A').toString().toUpperCase();
                            let chargedBadge = `<span class="badge bg-secondary">${chargedType}</span>`;

                            if (chargedType === '1') {
                                chargedBadge = `<span class="badge bg-primary">FREE</span>`;
                            } else if (chargedType === '2') {
                                chargedBadge = `<span class="badge bg-danger">BILLABLE</span>`;
                            }

                            $('#calendarModalGreaseTrap #display_charged_type_calendar').html(chargedBadge);
                        },

                        error: function () {
                            alert("Failed to fetch data. Please try again.");
                        },

                        complete: function () {
                            hideLoading();
                        }
                    });
                }
            });

        });
    </script>
@endpush