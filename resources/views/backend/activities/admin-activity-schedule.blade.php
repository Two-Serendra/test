@extends('layouts.backend')
@section('content')
    <style>
        #resident-type-dropdown {
            inset: 36px auto auto 0px !important;
        }

        /* Additional styles for hiding the column */
        .hide-column {
            display: none;
        }
    </style>

    <div class="row mt-4">
        <div class="col-12">
            <h2>Activities</h2>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-6 d-flex justify-content-start align-items-center">

                </div>
                <div class="col-6 d-flex align-items-center justify-content-end">
                    <button type="button" class="btn btn-primary badge fs-5 px-2 py-2 AddSchedule" data-bs-toggle="modal"
                        data-bs-original-title="Add" id="addActivity">
                        <i class="fa-solid fa-plus fa-sm"></i> New Activity
                    </button>
                </div>
            </div>

            <div class="table-container">
                <table id="scheduleTable" class="display table">
                    <thead>
                        <tr>
                            <th class="table-custom">ACTIVITY</th>
                            <th class="table-custom">START</th>
                            <th class="table-custom">END</th>
                            <th class="table-custom">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($schedules->isEmpty())
                            <tr>
                                <td colspan="11" class="text-center">No Records Found</td>
                            </tr>
                        @else
                            @foreach ($schedules as $schedule)
                                <tr>
                                    <td>{{ strtoupper($schedule->activity->activity_name ?? 'N/A') }}</td>
                                    <td>{{ strtoupper($schedule->start_time ?? 'N/A') }}</td>
                                    <td>{{ strtoupper($schedule->start_time ?? 'N/A') }}</td>
                                    <td>{{ strtoupper($schedule->end_time ?? 'N/A') }}</td>
                                    <td>
                                        <button type="button"
                                            class="btn btn-primary editInfo_id_schedule btn-responsive btn-equal btn-sm"
                                            data-bs-toggle="tooltip" data-bs-placement="left" title="Edit"
                                            data-id="{{ $schedule->id }}">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>

                                        @if ($schedule->schedule_status == 1)
                                            <button type="button"
                                                class="btn btn-danger deactivate_schedule btn-responsive btn-equal btn-sm"
                                                data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-target="#scheduleRemarks"
                                                title="Deactivate" data-id="{{ $schedule->id }}">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        @else
                                            <button type="button"
                                                class="btn btn-success activate-schedule btn-responsive btn-equal btn-sm"
                                                data-bs-toggle="tooltip" data-bs-placement="bottom" title="Activate"
                                                data-id="{{ $schedule->id }}">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        @endif

                                        <button type="button" class="btn btn-danger delete_schedule btn-responsive btn-equal btn-sm"
                                            data-bs-toggle="tooltip" data-bs-placement="right" title="Delete"
                                            data-id="{{ $schedule->id }}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        <div class="pagination-container">
            {{ $schedules->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
    @include('backend.modal')
    @push('scripts')
        <script src="{{ asset('assets/backend/js/activity-schedule.js') }}"></script>
    @endpush
@endsection