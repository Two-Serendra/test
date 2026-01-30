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
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <form action="{{ route('admin.search.activities') }}" method="GET" id="searchFormActivity"
                        class="d-flex align-items-center" style="max-width: 250px;">
                        <div class="input-group text-dark w-100">
                            <span class="input-group-text">
                                <i class='bx bx-search-alt text-dark'></i>
                            </span>
                            <input type="text" name="searchActivity" value="{{ $searchActivity ?? '' }}"
                                id="searchInputActivity" class="form-control" placeholder="Name" autocomplete="off">
                        </div>
                    </form>

                    <div class="mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary badge AddActivity" >
                            <i class='bx bx-plus'></i> New Activity
                        </button>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <table id="activityTable" class="display table">
                    <thead>
                        <tr>
                            <th class="table-custom">AMENITY</th>
                            <th class="table-custom">ACTIVITY</th>
                            <th class="table-custom">IMAGE</th>
                            <th class="table-custom">DESCRIPTION</th>
                            <th class="table-custom">STATUS</th>
                            <th class="table-custom">REMARKS</th>
                            <th class="table-custom">MAX BOOKING</th>
                            <th class="table-custom">SPACE</th>
                            <th class="table-custom">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($activities->isEmpty())
                            <tr>
                                <td colspan="11" class="text-center">No Records Found</td>
                            </tr>
                        @else
                            @foreach ($activities as $activity)
                                <tr>
                                    <td>{{ strtoupper($activity->amenity->amenity_name ?: 'N/A') }}</td>
                                    <td>{{ strtoupper($activity->activity_name ?: 'N/A') }}</td>
                                    <td>
                                        @if ($activity->activity_image)
                                            <img src="{{ asset('assets/images/activities/' . $activity->activity_image) }}"
                                                alt="Amenity Image" style="width: 100px; height: auto;">
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ strtoupper($activity->activity_description ?: 'N/A') }}
                                    </td>
                                    <td>
                                        @if ($activity->activity_status == 1)
                                            <span class="badge bg-success custom-badge">Active</span>
                                        @else
                                            <span class="badge bg-danger custom-badge">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ strtoupper($activity->activity_remarks ?: 'N/A') }}</td>
                                    <td>{{ strtoupper($activity->activity_max_booking ?: 'N/A') }}</td>
                                    <td>{{ strtoupper($activity->activity_space ?: 'N/A') }}</td>
                                    <td>
                                        <button type="button"
                                            class="btn btn-primary editInfo_id_activity btn-responsive btn-equal btn-sm"
                                            data-bs-toggle="tooltip" data-bs-placement="left" title="Edit"
                                            data-id="{{ $activity->id }}">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>

                                        @if ($activity->activity_status == 1)
                                            <button type="button"
                                                class="btn btn-danger deactivate_activity btn-responsive btn-equal btn-sm"
                                                data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-target="#activityRemarks"
                                                title="Deactivate" data-id="{{ $activity->id }}">
                                                <i class="fa-solid fa-ban"></i>
                                            </button>
                                        @else
                                            <button type="button"
                                                class="btn btn-success activate-activity btn-responsive btn-equal btn-sm"
                                                data-bs-toggle="tooltip" data-bs-placement="bottom" title="Activate"
                                                data-id="{{ $activity->id }}">
                                                <i class="fa-solid fa-check-circle"></i>
                                            </button>
                                        @endif

                                        <button type="button" class="btn btn-danger delete_activity btn-responsive btn-equal btn-sm"
                                            data-bs-toggle="tooltip" data-bs-placement="right" title="Delete"
                                            data-id="{{ $activity->id }}">
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
            {{ $activities->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
    @include('backend.modal.activities.activities-modal')
@endsection