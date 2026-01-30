@extends('layouts.backend')
@section('content')
    <style>
        * {
            text-decoration: none !important;
        }

        #calendar {
            max-width: 1100px;
            margin: 0 auto;
            position: relative;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        #calendar::after {
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

        /* .filter-container {
                    margin-left: -250px;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    width: 300px;

                }

                .filter-btn {
                    transition: background-color 0.3s, color 0.3s;
                    color: black;
                    width: auto;
                    min-width: 150px;
                    padding: 6px 10px;
                    text-align: center;
                    font-size: 13px;
                    white-space: nowrap;
                } */

        /* Active Button */
        .filter-btn.active {
            background-color: #28a745 !important;
            color: #fff !important;
            font-weight: bold;
        }

        /* Hover Effect */
        .filter-btn:hover,
        .filter-btn:focus {
            background-color: #28a745 !important;
            color: #fff !important;
            font-weight: bold;
        }
    </style>
    <div class="container">
        <div class="row">
            <div class="col-12 col-lg-3 order-1 order-lg-2 mb-3 mb-lg-0">
                <div class="p-3 bg-white rounded shadow-sm">
                    <h5 class="text-center fs-6 fw-bold">Filter Activity</h5>

                    <button class="btn btn-success w-100 mb-2 filter-btn active" data-activity="">All</button>

                    @php
                        $activities = \App\Models\Activity::whereIn('id', collect($events)->pluck('activity_id'))->get();
                    @endphp

                    @foreach ($activities as $activity)
                        <button class="btn btn-outline-success w-100 mb-2 filter-btn text-wrap"
                            data-activity="{{ $activity->id }}">
                            {{ $activity->activity_name }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Calendar Section -->
            <div class="col-12 col-lg-9 order-2 order-lg-1">
                <div id="calendar"></div>
            </div>
        </div>
    </div>

    @include('backend.calendar-modal')
@endsection
@push('js')
    <script>
        $(document).ready(function () {
            var schedule = @json($events);
            $('#calendar').fullCalendar({
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
                    $.get('/fetch/calendar-schedule/' + schedule_id, function (data) {
                        $('#calendarModal').modal('show');
                        $('#edit_id').val(data.id);
                        $('#calendar_activity_name').text(data.activity_name);
                        $('#calendar_unit').text(data.unit);
                        $('#calendar_name').text(data.name);
                        $('#calendar_contact_number').text(data.contact_number);
                        $('#calendar_booking_date').text(data.booking_date);
                        $('#calendar_booking_start_time').text(data.booking_start_time);
                        $('#calendar_booking_end_time').text(data.booking_end_time);
                    }).fail(function () {
                        alert("Failed to fetch data. Please try again.");
                    });
                }
            });

            let selectedActivities = [];

            $('.filter-btn').on('click', function () {
                var activityId = $(this).data('activity');
                if (activityId === "") {
                    selectedActivities = [];
                    $('.filter-btn').removeClass('active btn-success').addClass('btn-outline-success');
                    $(this).addClass('active btn-success');

                    $('#calendar').fullCalendar('removeEvents');
                    $('#calendar').fullCalendar('addEventSource', schedule);
                    return;
                }

                if (selectedActivities.includes(activityId)) {
                    selectedActivities = selectedActivities.filter(id => id !== activityId);
                    $(this).removeClass('active btn-success').addClass('btn-outline-success');
                } else {
                    selectedActivities.push(activityId);
                    $(this).addClass('active btn-success').removeClass('btn-outline-success');
                }

                $(this).blur();

                console.log("Filtering by Activity IDs:", selectedActivities);

                if (selectedActivities.length === 0) {
                    $('.filter-btn[data-activity=""]').addClass('active btn-success').removeClass('btn-outline-success');

                    $('#calendar').fullCalendar('removeEvents');
                    $('#calendar').fullCalendar('addEventSource', schedule);
                    return;
                } else {
                    $('.filter-btn[data-activity=""]').removeClass('active btn-success').addClass('btn-outline-success');
                }
                var filteredEvents = schedule.filter(function (event) {
                    return selectedActivities.includes(event.activity_id);
                });

                $('#calendar').fullCalendar('removeEvents');
                $('#calendar').fullCalendar('addEventSource', filteredEvents);
            });
        });



    </script>
@endpush