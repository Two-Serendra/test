@extends('layouts.backend')
@section('content')
    <style>
        * {
            text-decoration: none !important;
        }

        #calendar-pest-control-booking {
            max-width: 1100px;
            margin: 0 auto;
            position: relative;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        #calendar-pest-control-booking::after {
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

        .filter-btn-pc.active {
            background-color: #28a745 !important;
            color: #fff !important;
            font-weight: bold;
        }


        .filter-btn-pc:hover,
        .filter-btn-pc:focus {
            background-color: #28a745 !important;
            color: #fff !important;
            font-weight: bold;
        }
    </style>

    <div class="container">
        <div class="row">
            <div class="col-md-10">
                <div id="calendar-pest-control-booking"></div>
            </div>

            <div class="col-md-2">
                <div class="d-flex flex-column gap-2">
                    <button class="btn btn-outline-primary filter-btn-pc active" data-rise="">
                        All
                    </button>

                    <button class="btn btn-outline-primary filter-btn-pc filter-btn-rise" data-rise="lowrise">
                        Lowrise
                    </button>

                    <button class="btn btn-outline-primary filter-btn-pc filter-btn-rise" data-rise="highrise">
                        Highrise
                    </button>
                </div>
            </div>
        </div>
    </div>

    @include('backend.modal.pest-control.pest-control-booking-modal')
@endsection
@push('js')
    <script>
        var schedule = @json($events);
        $(document).ready(function () {

            $('#calendar-pest-control-booking').fullCalendar({
                timezone: 'local',
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },
                contentHeight: 750,
                events: schedule,
                eventRender: function (event, element) {
                    element.find('.fc-time').remove();
                },
                eventClick: function (event) {
                    if (!event.id) {
                        alert("No event ID found.");
                        return;
                    }
                    var schedule_id = event.id;
                    $.get('/admin/fetch/pest-control-calendar-details/' + schedule_id, function (data) {
                        $('#calendarModalPestControl').modal('show');
                        $('#edit_id').val(data.id);
                        $('#calendar_unit').text(data.unit_no);
                        $('#calendar_name').text(data.name);
                        $('#calendar_booking_date').text(data.booking_date);
                        $('#calendar_time_slot').text(data.booking_time_slot);
                        $('#calendar_transaction_no').text(data.transaction_no);
                        $('#calendar_srf_no').text(data.srf_no);

                        let residentType = (data.resident_type || 'N/A').toUpperCase();
                        let residentBadge = `<span class="badge bg-secondary">${residentType}</span>`;

                        if (residentType.includes('TENANT')) {
                            residentBadge = `<span class="badge bg-danger">${residentType}</span>`;
                        }

                        if (residentType.includes('OWNER')) {
                            residentBadge = `<span class="badge bg-primary">${residentType}</span>`;
                        }

                        $('#display_resident_type_calendar').html(residentBadge);


                        let chargedType = (data.charged_type || 'N/A').toString().toUpperCase();
                        let chargedBadge = `<span class="badge bg-secondary">${chargedType}</span>`;

                        // Handle numeric charged types first
                        if (chargedType === '1') {
                            chargedBadge = `<span class="badge bg-primary">FREE</span>`;
                        } else if (chargedType === '2') {
                            chargedBadge = `<span class="badge bg-danger">BILLABLE</span>`;
                        }

                        $('#display_charged_type_calendar').html(chargedBadge);

                    }).fail(function () {
                        alert("Failed to fetch data. Please try again.");
                    });
                }
            });
        });


        let selectedRise = "";

        $('.filter-btn-pc').on('click', function () {

            selectedRise = $(this).data('rise');

            $('.filter-btn-pc')
                .removeClass('active btn-success')
                .addClass('btn-outline-success');

            $(this)
                .addClass('active btn-success')
                .removeClass('btn-outline-success');

            if (selectedRise === "") {
                $('#calendar-pest-control-booking').fullCalendar('removeEvents');
                $('#calendar-pest-control-booking').fullCalendar('addEventSource', schedule);
                return;
            }

            var filteredEvents = schedule.filter(function (event) {

                let area = (event.unit_area || '').toUpperCase();

                if (selectedRise === "highrise") {
                    return ['F', 'G', 'H', 'I'].includes(area);
                }

                if (selectedRise === "lowrise") {
                    return ['A', 'B', 'C', 'D', 'E'].includes(area);
                }

            });

            $('#calendar-pest-control-booking').fullCalendar('removeEvents');
            $('#calendar-pest-control-booking').fullCalendar('addEventSource', filteredEvents);
        });


    </script>
@endpush