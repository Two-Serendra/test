$(document).ready(function () {
    loadServiceBookingDashboard();


    function loadServiceBookingDashboard() {

        let container = $('#serviceBookingDashboard');

        container.html(`
        <div class="col-12 text-center py-5">

            <div class="spinner-border text-success"
                 role="status"
                 style="width: 3rem; height: 3rem;">

                <span class="visually-hidden">
                    Loading...
                </span>

            </div>

            <p class="mt-3 fw-semibold">
                Loading Dashboard...
            </p>

        </div>
    `);


        $.ajax({

            url: '/admin/admin-get-service-booking-stats',

            type: 'GET',

            dataType: 'json',


            success: function (data) {

                let html = `

            <!-- ================================================= -->
            <!-- TOP KPI CARDS -->
            <!-- ================================================= -->

            <div class="row">

                <!-- TOTAL -->

                <div class="col-xl-3 col-md-6 mb-4">

                    <div class="card h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between">

                                <div>

                                    <span class="fw-semibold d-block mb-1">
                                        Total Bookings
                                    </span>

                                    <h3 class="card-title mb-2">
                                        ${data.current_bookings}
                                    </h3>

                                    <small class="text-muted">
                                        ${data.current_year}
                                    </small>

                                </div>

                                <div>

                                    <span class="badge bg-label-success p-3">

                                        <i class="bx bx-calendar-check fs-4 text-success"></i>

                                    </span>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- TODAY -->

                <div class="col-xl-3 col-md-6 mb-4">

                    <div class="card h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between">

                                <div>

                                    <span class="fw-semibold d-block mb-1">
                                        Today's Bookings
                                    </span>

                                    <h3 class="card-title mb-2">
                                        ${data.bookingToday}
                                    </h3>

                                    <small class="text-success">
                                        Today
                                    </small>

                                </div>

                                <div>

                                    <span class="badge bg-label-info p-3">

                                        <i class="bx bx-calendar-event fs-4 text-info"></i>

                                    </span>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- UPCOMING -->

                <div class="col-xl-3 col-md-6 mb-4">

                    <div class="card h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between">

                                <div>

                                    <span class="fw-semibold d-block mb-1">
                                        Upcoming
                                    </span>

                                    <h3 class="card-title mb-2">
                                        ${data.upcomingBookings}
                                    </h3>

                                    <small class="text-muted">
                                        Future bookings
                                    </small>

                                </div>

                                <div>

                                    <span class="badge bg-label-warning p-3">

                                        <i class="bx bx-time-five fs-4 text-warning"></i>

                                    </span>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- EMERGENCY -->

                <div class="col-xl-3 col-md-6 mb-4">

                    <div class="card h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between">

                                <div>

                                    <span class="fw-semibold d-block mb-1">
                                        Emergency
                                    </span>

                                    <h3 class="card-title mb-2">
                                        ${data.emergencyBookings}
                                    </h3>

                                    <small class="text-danger">
                                        Emergency bookings
                                    </small>

                                </div>

                                <div>

                                    <span class="badge bg-label-danger p-3">

                                        <i class="bx bx-error fs-4 text-danger"></i>

                                    </span>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- ================================================= -->
            <!-- MAIN CHART + STATUS -->
            <!-- ================================================= -->

            <div class="row">


                <!-- MONTHLY TREND -->

                <div class="col-lg-8 mb-4">

                    <div class="card h-100">

                        <div class="card-header d-flex
                                    justify-content-between
                                    align-items-center">

                            <div>

                                <h5 class="mb-1">
                                    Service Booking Trend
                                </h5>

                                <small class="text-muted">
                                    ${data.current_year} monthly bookings
                                </small>

                            </div>


                            <div class="dropdown">

                                <button
                                    class="btn btn-sm btn-outline-success dropdown-toggle"
                                    type="button"
                                    data-bs-toggle="dropdown">

                                    ${data.current_year}

                                </button>

                                <ul class="dropdown-menu dropdown-menu-end">

                                    ${data.years.map(year => `

                                        <li>

                                            <a class="dropdown-item"
                                               href="#">
                                                ${year}
                                            </a>

                                        </li>

                                    `).join('')}

                                </ul>

                            </div>

                        </div>


                        <div class="card-body">

                            <div id="serviceBookingChart"></div>

                        </div>

                    </div>

                </div>


                <!-- STATUS -->

                <div class="col-lg-4 mb-4">

                    <div class="card h-100">

                        <div class="card-header">

                            <h5 class="mb-1">
                                Booking Status
                            </h5>

                            <small class="text-muted">
                                All services
                            </small>

                        </div>


                        <div class="card-body">

                            <div id="statusChart"></div>


                            <div class="row text-center mt-3">

                                <div class="col-4">

                                    <span class="d-block
                                                 fw-semibold">

                                        ${data.scheduled}

                                    </span>

                                    <small class="text-warning">

                                        Scheduled

                                    </small>

                                </div>


                                <div class="col-4">

                                    <span class="d-block
                                                 fw-semibold">

                                        ${data.completed}

                                    </span>

                                    <small class="text-success">

                                        Completed

                                    </small>

                                </div>


                                <div class="col-4">

                                    <span class="d-block
                                                 fw-semibold">

                                        ${data.cancelled}

                                    </span>

                                    <small class="text-danger">

                                        Cancelled

                                    </small>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- ================================================= -->
            <!-- SERVICE BREAKDOWN -->
            <!-- ================================================= -->

            <div class="row">


                <!-- AUSI -->

                <div class="col-lg-4 mb-4">

                    <div class="card h-100">

                        <div class="card-header">

                            <h5 class="mb-0">
                                AUSI
                            </h5>

                        </div>

                        <div class="card-body">

                            <div class="d-flex
                                        justify-content-between
                                        align-items-center">

                                <div>

                                    <h2 class="mb-0">
                                        ${data.services.ausi}
                                    </h2>

                                    <small class="text-muted">
                                        ${data.current_year} bookings
                                    </small>

                                </div>


                                <span class="badge bg-label-success p-3">

                                    <i class="bx bx-building-house
                                              fs-4 text-success"></i>

                                </span>

                            </div>


                            <hr>


                            <div class="d-flex
                                        justify-content-between">

                                <span>
                                    Completion Rate
                                </span>

                                <strong class="text-success">
                                    ${data.completion_rate.ausi}%
                                </strong>

                            </div>


                            <div class="progress mt-2"
                                 style="height: 8px;">

                                <div
                                    class="progress-bar bg-success"
                                    style="width:
                                    ${data.completion_rate.ausi}%">
                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- PEST CONTROL -->

                <div class="col-lg-4 mb-4">

                    <div class="card h-100">

                        <div class="card-header">

                            <h5 class="mb-0">
                                Pest Control
                            </h5>

                        </div>

                        <div class="card-body">

                            <div class="d-flex
                                        justify-content-between
                                        align-items-center">

                                <div>

                                    <h2 class="mb-0">
                                        ${data.services.pest_control}
                                    </h2>

                                    <small class="text-muted">
                                        ${data.current_year} bookings
                                    </small>

                                </div>


                                <span class="badge bg-label-warning p-3">

                                    <i class="bx bx-bug
                                              fs-4 text-warning"></i>

                                </span>

                            </div>


                            <hr>


                            <div class="d-flex
                                        justify-content-between">

                                <span>
                                    Completion Rate
                                </span>

                                <strong class="text-warning">
                                    ${data.completion_rate.pest_control}%
                                </strong>

                            </div>


                            <div class="progress mt-2"
                                 style="height: 8px;">

                                <div
                                    class="progress-bar bg-warning"
                                    style="width:
                                    ${data.completion_rate.pest_control}%">
                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- GREASE TRAP -->

                <div class="col-lg-4 mb-4">

                    <div class="card h-100">

                        <div class="card-header">

                            <h5 class="mb-0">
                                Grease Trap
                            </h5>

                        </div>

                        <div class="card-body">

                            <div class="d-flex
                                        justify-content-between
                                        align-items-center">

                                <div>

                                    <h2 class="mb-0">
                                        ${data.services.grease_trap}
                                    </h2>

                                    <small class="text-muted">
                                        ${data.current_year} bookings
                                    </small>

                                </div>


                                <span class="badge bg-label-info p-3">

                                    <i class="bx bx-water
                                              fs-4 text-info"></i>

                                </span>

                            </div>


                            <hr>


                            <div class="d-flex
                                        justify-content-between">

                                <span>
                                    Completion Rate
                                </span>

                                <strong class="text-info">
                                    ${data.completion_rate.grease_trap}%
                                </strong>

                            </div>


                            <div class="progress mt-2"
                                 style="height: 8px;">

                                <div
                                    class="progress-bar bg-info"
                                    style="width:
                                    ${data.completion_rate.grease_trap}%">
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- ================================================= -->
            <!-- TODAY + UPCOMING -->
            <!-- ================================================= -->

            <div class="row">


                <!-- TODAY -->

                <div class="col-lg-7 mb-4">

                    <div class="card h-100">

                        <div class="card-header
                                    d-flex
                                    justify-content-between
                                    align-items-center">

                            <div>

                                <h5 class="mb-1">
                                    Today's Bookings
                                </h5>

                                <small class="text-muted">
                                    ${data.bookingToday} bookings today
                                </small>

                            </div>

                        </div>


                        <div class="table-responsive">

                            <table class="table table-hover mb-0">

                                <thead>

                                    <tr>

                                        <th>
                                            Service
                                        </th>

                                        <th>
                                            Time
                                        </th>

                                        <th>
                                            Unit
                                        </th>

                                        <th>
                                            Status
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                    ${data.today_bookings.length > 0

                        ?

                        data.today_bookings.map(booking => `

                                            <tr>

                                                <td>

                                                    <span class="fw-semibold">
                                                        ${booking.service}
                                                    </span>

                                                </td>

                                                <td>
                                                    ${booking.booking_time_slot}
                                                </td>

                                                <td>
                                                    ${booking.unit_no}
                                                </td>

                                                <td>
                                                    ${getStatusBadge(booking.booking_status)}
                                                </td>

                                            </tr>

                                        `).join('')

                        :

                        `

                                        <tr>

                                            <td colspan="4"
                                                class="text-center py-4">

                                                <span class="text-muted">

                                                    No bookings today

                                                </span>

                                            </td>

                                        </tr>

                                        `
                    }

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>


                <!-- UPCOMING -->

                <div class="col-lg-5 mb-4">

                    <div class="card h-100">

                        <div class="card-header">

                            <h5 class="mb-1">
                                Upcoming Bookings
                            </h5>

                            <small class="text-muted">
                                Next scheduled services
                            </small>

                        </div>


                        <div class="card-body p-0">

                            <ul class="list-group list-group-flush">

                                ${data.upcoming_bookings.length > 0

                        ?

                        data.upcoming_bookings.map(booking => `

                                        <li class="list-group-item">

                                            <div class="d-flex
                                                        justify-content-between">

                                                <div>

                                                    <span class="fw-semibold d-block">

                                                        ${booking.service}

                                                    </span>

                                                    <small class="text-muted">

                                                        Unit ${booking.unit_no}

                                                    </small>

                                                </div>


                                                <div class="text-end">

                                                    <span class="fw-semibold d-block">

                                                        ${formatBookingDate(booking.booking_date)}

                                                    </span>

                                                    <small class="text-muted">

                                                        ${booking.booking_time_slot}

                                                    </small>

                                                </div>

                                            </div>

                                        </li>

                                    `).join('')

                        :

                        `

                                    <li class="list-group-item
                                               text-center
                                               py-5">

                                        <span class="text-muted">

                                            No upcoming bookings

                                        </span>

                                    </li>

                                    `
                    }

                            </ul>

                        </div>

                    </div>

                </div>

            </div>


            <!-- ================================================= -->
            <!-- GROWTH + PENALTIES -->
            <!-- ================================================= -->

            <div class="row">


                <!-- GROWTH -->

                <div class="col-lg-6 mb-4">

                    <div class="card h-100">

                        <div class="card-header">

                            <h5 class="mb-1">
                                Year-over-Year Growth
                            </h5>

                            <small class="text-muted">
                                ${data.current_year} vs ${data.previous_year}
                            </small>

                        </div>


                        <div class="card-body">

                            <div class="row align-items-center">

                                <div class="col-md-6">

                                    <div id="growthChart"></div>

                                </div>


                                <div class="col-md-6">

                                    <div class="mb-4">

                                        <small class="text-muted d-block">
                                            ${data.current_year}
                                        </small>

                                        <h4 class="mb-0">
                                            ${data.current_bookings}
                                        </h4>

                                        <small>
                                            bookings
                                        </small>

                                    </div>


                                    <div>

                                        <small class="text-muted d-block">
                                            ${data.previous_year}
                                        </small>

                                        <h4 class="mb-0">
                                            ${data.previous_bookings}
                                        </h4>

                                        <small>
                                            bookings
                                        </small>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- PENALTIES -->

                <div class="col-lg-6 mb-4">

                    <div class="card h-100">

                        <div class="card-header">

                            <h5 class="mb-1">
                                Penalty Monitoring
                            </h5>

                            <small class="text-muted">
                                Pest Control & Grease Trap
                            </small>

                        </div>


                        <div class="card-body">

                            <div class="row text-center">


                                <div class="col-6">

                                    <span class="badge bg-label-danger p-3 mb-3">

                                        <i class="bx bx-error fs-4 text-danger"></i>

                                    </span>

                                    <h3 class="mb-0">

                                        ${data.penalties.count}

                                    </h3>

                                    <small class="text-muted">

                                        Penalty Cases

                                    </small>

                                </div>


                                <div class="col-6">

                                    <span class="badge bg-label-warning p-3 mb-3">

                                        <i class="bx bx-money fs-4 text-warning"></i>

                                    </span>

                                    <h3 class="mb-0">

                                        ₱${Number(data.penalties.amount).toLocaleString(
                        'en-PH',
                        {
                            minimumFractionDigits: 2
                        }
                    )}

                                    </h3>

                                    <small class="text-muted">

                                        Total Penalties

                                    </small>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            `;


                container.hide().html(html);


                setTimeout(function () {

                    renderServiceBookingChart(data.chart_data);

                    renderStatusChart(data.status_data);

                    renderGrowthChart(data.growth);

                    container.fadeIn(300);

                }, 200);

            },


            error: function (xhr) {

                container.html(`

                <div class="col-12">

                    <div class="alert alert-danger">

                        <i class="bx bx-error-circle me-2"></i>

                        Failed to load dashboard data.

                    </div>

                </div>

            `);

                console.error(xhr.responseText);

            }

        });

    }


    function renderStatusChart(statusData) {

        let chartEl = document.querySelector('#statusChart');

        if (!chartEl) return;


        let options = {

            chart: {
                type: 'donut',
                height: 260
            },


            series: [

                statusData.scheduled,
                statusData.completed,
                statusData.cancelled

            ],


            labels: [

                'Scheduled',
                'Completed',
                'Cancelled'

            ],


            colors: [

                '#FFAB00',
                '#28C76F',
                '#EA5455'

            ],


            legend: {
                position: 'bottom'
            },


            stroke: {
                width: 0
            },


            dataLabels: {
                enabled: false
            },


            plotOptions: {

                pie: {

                    donut: {

                        size: '70%',

                        labels: {

                            show: true,

                            total: {

                                show: true,

                                label: 'Total',

                                formatter: function (w) {

                                    return w.globals.seriesTotals
                                        .reduce((a, b) => a + b, 0);

                                }

                            }

                        }

                    }

                }

            }

        };


        let chart = new ApexCharts(chartEl, options);

        chart.render();
    }

    function renderServiceBookingChart(chartData) {

        let chartEl = document.querySelector('#serviceBookingChart');

        if (!chartEl) return;


        let options = {

            chart: {

                type: 'line',

                height: 320,

                toolbar: {
                    show: false
                },

                zoom: {
                    enabled: false
                }

            },


            series: [

                {
                    name: 'AUSI',
                    data: chartData.ausi
                },

                {
                    name: 'Pest Control',
                    data: chartData.pest_control
                },

                {
                    name: 'Grease Trap',
                    data: chartData.grease_trap
                },

                {
                    name: 'Total',
                    data: chartData.total
                }

            ],


            xaxis: {

                categories: chartData.labels,

                axisBorder: {
                    show: false
                }

            },


            stroke: {

                curve: 'smooth',

                width: [
                    3,
                    3,
                    3,
                    2
                ]

            },


            markers: {

                size: 4

            },


            legend: {

                position: 'top',

                horizontalAlign: 'right'

            },


            tooltip: {

                shared: true,

                intersect: false

            },


            colors: [

                '#008b26',
                '#FFAB00',
                '#03C3EC',
                '#696CFF'

            ],


            grid: {

                borderColor: '#f1f1f1',

                strokeDashArray: 4

            }

        };


        let chart = new ApexCharts(chartEl, options);

        chart.render();
    }


    function renderGrowthChart(growth) {

        let growthEl = document.querySelector('#growthChart');

        if (!growthEl) return;


        let value = parseFloat(growth) || 0;

        let positive = value >= 0;

        let chartValue = Math.min(Math.abs(value), 100);


        let options = {

            chart: {

                type: 'radialBar',

                height: 220,

                sparkline: {
                    enabled: true
                }

            },


            plotOptions: {

                radialBar: {

                    startAngle: -135,

                    endAngle: 135,

                    hollow: {
                        size: '65%'
                    },


                    track: {

                        background: '#f2f2f2',

                        strokeWidth: '100%'

                    },


                    dataLabels: {

                        name: {

                            show: true,

                            offsetY: 35,

                            color: '#6c757d',

                            fontSize: '13px',

                        },


                        value: {

                            show: true,

                            offsetY: -5,

                            fontSize: '24px',

                            fontWeight: 600,

                            formatter: function () {

                                return `${positive ? '+' : ''}${value}%`;

                            }

                        }

                    }

                }

            },


            series: [chartValue],


            labels: [

                positive
                    ? 'Growth'
                    : 'Decline'

            ],


            colors: [

                positive
                    ? '#28C76F'
                    : '#EA5455'

            ]

        };


        let chart = new ApexCharts(growthEl, options);

        chart.render();
    }
    function renderServiceBookingChart(chartData) {

        let chartEl = document.querySelector('#serviceBookingChart');

        if (!chartEl) return;


        let options = {

            chart: {

                type: 'line',

                height: 320,

                toolbar: {
                    show: false
                },

                zoom: {
                    enabled: false
                }

            },


            series: [

                {
                    name: 'AUSI',
                    data: chartData.ausi
                },

                {
                    name: 'Pest Control',
                    data: chartData.pest_control
                },

                {
                    name: 'Grease Trap',
                    data: chartData.grease_trap
                },

                {
                    name: 'Total',
                    data: chartData.total
                }

            ],


            xaxis: {

                categories: chartData.labels,

                axisBorder: {
                    show: false
                }

            },


            stroke: {

                curve: 'smooth',

                width: [
                    3,
                    3,
                    3,
                    2
                ]

            },


            markers: {

                size: 4

            },


            legend: {

                position: 'top',

                horizontalAlign: 'right'

            },


            tooltip: {

                shared: true,

                intersect: false

            },


            colors: [

                '#008b26',
                '#FFAB00',
                '#03C3EC',
                '#696CFF'

            ],


            grid: {

                borderColor: '#f1f1f1',

                strokeDashArray: 4

            }

        };


        let chart = new ApexCharts(chartEl, options);

        chart.render();
    }


    function getStatusBadge(status) {

        switch (parseInt(status)) {

            case 1:

                return `
                <span class="badge bg-label-warning">
                    Scheduled
                </span>
            `;


            case 2:

                return `
                <span class="badge bg-label-success">
                    Completed
                </span>
            `;


            case 0:

                return `
                <span class="badge bg-label-danger">
                    Cancelled
                </span>
            `;


            default:

                return `
                <span class="badge bg-label-secondary">
                    Unknown
                </span>
            `;

        }

    }


    function formatBookingDate(date) {

        if (!date) return '';

        let bookingDate = new Date(date);

        return bookingDate.toLocaleDateString('en-PH', {

            month: 'short',

            day: 'numeric'

        });

    }
});
