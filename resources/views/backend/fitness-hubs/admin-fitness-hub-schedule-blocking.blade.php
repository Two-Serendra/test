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
                        <button type="button" class="btn btn-primary badge AddBlockingScheduleFitnessHub">
                            <i class='bx bx-plus'></i> New Blocking
                        </button>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table id="fitnessHubScheduleBlockingTable" class="display table">
                    <thead>
                        <tr>
                            <th class="table-custom sticky-th">FITNESS HUB</th>
                            <th class="table-custom sticky-th">DAY</th>
                            <th class="table-custom sticky-th">START TIME</th>
                            <th class="table-custom sticky-th">END TIME</th>
                            <th class="table-custom sticky-th">REMARKS</th>
                            <th class="table-custom sticky-th">REPEATED WEEKLY</th>
                            <th class="table-custom sticky-th">CREATED AT</th>
                            <th class="table-custom sticky-th">UPDATED AT</th>
                            <th class="table-custom sticky-th">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($fitness_hub_blockings->isEmpty())
                            <tr>
                                <td colspan="11" class="text-center">No Records Found</td>
                            </tr>
                        @else
                            @foreach ($fitness_hub_blockings as $fitness_hub_blocking)
                                <tr>
                                    <td>{{ strtoupper($fitness_hub_blocking->fitnessHub->fitness_hub_name ?? 'N/A') }}</td>
                                    <td>{{ strtoupper($fitness_hub_blocking->day ?? 'N/A') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($fitness_hub_blocking->start_time)->format('h:i A') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($fitness_hub_blocking->end_time)->format('h:i A') }}</td>
                                    <td>{{ strtoupper($fitness_hub_blocking->remarks ?? 'N/A') }}</td>
                                    <td>
                                        @if ($fitness_hub_blocking->repeat_weekly)
                                            <span class="badge bg-success">Yes</span>
                                        @else
                                            <span class="badge bg-danger">No</span>
                                        @endif
                                    </td>
                                    <td>{{ $fitness_hub_blocking->created_at }}</td>
                                    <td>{{ $fitness_hub_blocking->updated_at }}</td>
                                    <td>
                                        <button type="button"
                                            class="btn btn-danger delete_block_schedule_fitness-hub btn-responsive btn-equal btn-sm"
                                            data-bs-toggle="tooltip" data-bs-placement="right" title="Delete"
                                            data-id="{{ $fitness_hub_blocking->id }}">
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
            {{ $fitness_hub_blockings->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
    @include('backend.modal.fitness-hubs.fitness-hub-blocking-schedule-modal')
    @push('scripts')
        <script src="{{ asset('assets/backend/js/fitness-hub-schedule-blocking.js') }}"></script>
    @endpush
@endsection