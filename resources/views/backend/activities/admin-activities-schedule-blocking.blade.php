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
    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-6 d-flex justify-content-start align-items-center">
                    <form action="{{ route('search-history') }}" method="GET" id="searchFormHistory"
                        class="d-flex align-items-center">
                        <div class="input-group" style="width: 200px;">
                            <span class="input-group-text">
                                <i class="fa-solid fa-magnifying-glass fa-sm"></i>
                            </span>
                            <input type="text" name="searchHistory" value="{{ $searchHistory ?? '' }}"
                                id="searchInputHistory" class="form-control" placeholder="Name/Unit" autocomplete="off">
                        </div>
                    </form>
                </div>
                <div class="col-6 d-flex justify-content-end align-items-center">
                    <div class="mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary badge AddBlockingSchedule">
                            <i class='bx bx-plus'></i> New Blocking
                        </button>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table id="activityScheduleBlockingTable" class="display table">
                    <thead>
                        <tr>
                            <th class="table-custom sticky-th">ACTIVITY</th>
                            <th class="table-custom sticky-th">DAY</th>
                            <th class="table-custom sticky-th">START TIME</th>
                            <th class="table-custom sticky-th">END TIME</th>
                            <th class="table-custom sticky-th">REMARKS</th>
                            <th class="table-custom sticky-th">REPEATED WEEKLY</th>
                            <th class="table-custom sticky-th">CREATED AT</th>
                            <th class="table-custom sticky-th">UPDATED AT</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($activity_blockings->isEmpty())
                            <tr>
                                <td colspan="11" class="text-center">No Records Found</td>
                            </tr>
                        @else
                            @foreach ($activity_blockings as $activity_blocking)
                                <tr>
                                    <td>{{ strtoupper($activity_blocking->activity->activity_name ?? 'N/A') }}</td>
                                    <td>{{ strtoupper($activity_blocking->day ?? 'N/A') }}</td>
                                    <td>{{ $activity_blocking->start_time }}</td>
                                    <td>{{ $activity_blocking->end_time }}</td>
                                    <td>{{ strtoupper($activity_blocking->remarks ?? 'N/A') }}</td>
                                    <td>
                                        @if ($activity_blocking->repeat_weekly)
                                            <span class="badge bg-success">Yes</span>
                                        @else
                                            <span class="badge bg-danger">No</span>
                                        @endif
                                    </td>
                                    <td>{{ $activity_blocking->created_at }}</td>
                                    <td>{{ $activity_blocking->updated_at }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        <div class="pagination-container">
            {{ $activity_blockings->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
    @include('backend.modal.activities.activities-blocking-schedule-modal')
@endsection