$(document).ready(function () {
    loadFunctionRoomDashboard();

    function loadFunctionRoomDashboard() {
        let container = $('#functionRoomBookingDashboard');

        // 🔥 Show loading spinner
        container.html(`
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3 fw-semibold">Loading Dashboard...</p>
            </div>
        `);

        $.ajax({
            url: '/admin/admin-get-function-room-booking-stats',
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                // 🔥 Build entire layout in memory
                let html = `
                    <div class="row align-items-stretch">
                        <!-- Left Column -->
                        <div class="col-lg-8 mb-4 d-flex">
                            <div class="card flex-fill">
                                <div class="row row-bordered g-0">
                                    <div class="col-md-8">
                                        <h5 class="card-header m-0 me-2 pb-3">Function Room Bookings</h5>
                                        <div id="functionRoomChart" class="px-2"></div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card-body text-center">
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="bookingYearDropdown" data-bs-toggle="dropdown">
                                                    ${data.current_year}
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                    ${data.years.map(year => `<a class="dropdown-item" href="#">${year}</a>`).join('')}
                                                    </div>
                                                </div>
                                                </div>
                                                <div id="growthChart"></div>
                                                <div class="text-center fw-semibold pt-3 mb-2">${data.growth}% Growth</div>
                                                <div class="d-flex px-xxl-4 px-lg-2 p-4 gap-3 justify-content-between">
                                                <div class="d-flex">
                                                    <div class="me-2">
                                                    <span class="badge bg-label-primary p-2"><i class="bx bx-calendar text-primary"></i></span>
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                    <small>${data.current_year}</small>
                                                    <h6 class="mb-0">${data.current_bookings} Bookings</h6>
                                                    </div>
                                                </div>
                                                <div class="d-flex">
                                                    <div class="me-2">
                                                         <span class="badge bg-label-info p-2"><i class="bx bx-calendar text-info"></i></span>
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                     <small>${data.previous_year}</small>
                                                    <h6 class="mb-0">${data.previous_bookings} Bookings</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                    <!-- Right Column -->
                            <div class="col-lg-4 mb-4 d-flex">
                                <div class="row flex-fill">
                                    <div class="col-6 mb-4 d-flex">
                                    <div class="card flex-fill">
                                        <div class="card-body d-flex flex-column justify-content-center align-items-center text-center p-4">
                                        <h2 class="card-title text-nowrap mb-2">${data.pending}</h2>
                                        <small class="text-warning fw-semibold"><i class="bx bx-time"></i> Pending</small>
                                        </div>
                                    </div>
                                    </div>
                                    <div class="col-6 mb-4 d-flex">
                                    <div class="card flex-fill">
                                        <div class="card-body d-flex flex-column justify-content-center align-items-center text-center p-4">
                                        <h2 class="card-title mb-2">${data.approved}</h2>
                                        <small class="text-success fw-semibold"><i class="bx bx-check-circle"></i> Approved</small>
                                        </div>
                                    </div>
                                    </div>
                                    <div class="col-6 d-flex">
                                    <div class="card flex-fill">
                                        <div class="card-body d-flex flex-column justify-content-center align-items-center text-center p-4">
                                        <h2 class="card-title mb-2">${data.cancelled}</h2>
                                        <small class="text-danger fw-semibold"><i class="bx bx-x-circle"></i> Cancelled</small>
                                        </div>
                                    </div>
                                    </div>
                                    <div class="col-6 d-flex">
                                    <div class="card flex-fill">
                                        <div class="card-body d-flex flex-column justify-content-center align-items-center text-center p-4">
                                        <h2 class="card-title mb-2">${data.bookingToday}</h2>
                                        <small class="text-success fw-semibold"><i class='bx bx-book-open'></i> Today's Booking</small>
                                        </div>
                                    </div>
                                    </div>
                                </div>
                            </div>
                        `;
                container.hide().html(html);

                setTimeout(() => {
                    renderFunctionRoomChart(data.chart_data);
                    renderGrowthChart(data.growth);
                    container.fadeIn();
                }, 300);
            },
            error: function (xhr) {
                container.html(`
                    <div class="col-12 text-center py-5">
                        <p class="text-danger fw-semibold">⚠️ Failed to load dashboard data.</p>
                    </div>
                `);
                console.error(xhr.responseText);
            }
        });
    }

    function renderFunctionRoomChart(chartData) {
        let chartEl = document.querySelector('#functionRoomChart');
        if (!chartEl) return;

        let options = {
            chart: { type: 'line', height: 250, toolbar: { show: false } },
            series: [{ name: 'Bookings', data: chartData.values }],
            xaxis: { categories: chartData.labels },
            stroke: { curve: 'smooth' },
            colors: ['#696CFF'],
            fill: { opacity: 0.3 }
        };

        let chart = new ApexCharts(chartEl, options);
        chart.render();
    }

    function renderGrowthChart(growth) {
        let growthEl = document.querySelector('#growthChart');
        if (!growthEl) return;

        let options = {
            chart: { type: 'radialBar', height: 180, sparkline: { enabled: true } },
            plotOptions: {
                radialBar: {
                    hollow: { size: '70%' },
                    dataLabels: {
                        showOn: 'always',
                        name: { show: false },
                        value: {
                            fontSize: '20px',
                            formatter: function (val) {
                                return val + "%";
                            }
                        }
                    }
                }
            },
            series: [parseFloat(growth)],
            colors: [growth >= 0 ? '#28C76F' : '#EA5455']
        };

        let chart = new ApexCharts(growthEl, options);
        chart.render();
    }
});
