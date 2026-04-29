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

            <div class="col-12 col-lg-9 order-2 order-lg-1">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
    <div id="loadingOverlay" class="loading-overlay">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    @include('backend.modal.activities.activities-booking-modal')
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
            $('#calendar').fullCalendar({
                timezone: 'local',
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },
                contentHeight: 750,
                events: schedule,

                // ✅ AUTO spinner when calendar loads
                loading: function (isLoading) {
                    if (isLoading) {
                        showLoading();
                    } else {
                        hideLoading();
                    }
                },

                eventRender: function (event, element) {
                    element.find('.fc-time').remove();
                },

                eventClick: function (event) {
                    if (!event.id) {
                        alert("No event ID found.");
                        return;
                    }

                    var schedule_id = event.id;

                    showLoading();

                    $.ajax({
                        url: '/admin/fetch/activity-calendar-schedule/' + schedule_id,
                        method: 'GET',
                        success: function (data) {
                            $('#activityCalendarModal').modal('show');
                            $('#edit_id').val(data.id);

                            $('#calendar_activity_name').text(data.activity_name ?? 'N/A');
                            $('#calendar_unit').text(data.unit ?? 'N/A');
                            $('#calendar_name').text(data.name ?? 'N/A');

                            $('#calendar_contact_number').text(
                                data.contact_number ? data.contact_number : 'N/A'
                            );

                            const startTime = data.booking_start_time;
                            const endTime = data.booking_end_time;

                            $('#calendar_booking_date').text(data.booking_date ?? 'N/A');
                            $('#calendar_booking_start_time').text(`${startTime} - ${endTime}` ?? 'N/A');
                            $('#calendar_slot_count').text(
                                data.slot_count > 1
                                    ? data.slot_count + ' slots booked'
                                    : '1 slot booked'
                            );
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

            let selectedActivities = [];

            $('.filter-btn').on('click', function () {
                var activityId = $(this).data('activity');
                if (activityId === "") {
                    selectedActivities = [];
                    $('.filter-btn').removeClass('active btn-primary').addClass('btn-outline-primary');
                    $(this).addClass('active btn-primary');

                    $('#calendar').fullCalendar('removeEvents');
                    $('#calendar').fullCalendar('addEventSource', schedule);
                    return;
                }

                if (selectedActivities.includes(activityId)) {
                    selectedActivities = selectedActivities.filter(id => id !== activityId);
                    $(this).removeClass('active btn-primary').addClass('btn-outline-primary');
                } else {
                    selectedActivities.push(activityId);
                    $(this).addClass('active btn-primary').removeClass('btn-outline-primary');
                }

                $(this).blur();

                console.log("Filtering by Activity IDs:", selectedActivities);

                if (selectedActivities.length === 0) {
                    $('.filter-btn[data-activity=""]').addClass('active btn-primary').removeClass('btn-outline-primary');

                    $('#calendar').fullCalendar('removeEvents');
                    $('#calendar').fullCalendar('addEventSource', schedule);
                    return;
                } else {
                    $('.filter-btn[data-activity=""]').removeClass('active btn-primary').addClass('btn-outline-primary');
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