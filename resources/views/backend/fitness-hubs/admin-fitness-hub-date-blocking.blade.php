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
                    <form action="{{ route('admin.search.block.dates') }}" method="GET" id="searchFormFitnessHubBlocking"
                        class="d-flex align-items-center" style="max-width: 250px;">
                        <div class="input-group text-dark w-100">
                            <span class="input-group-text">
                                <i class='bx bx-search-alt text-dark'></i>
                            </span>
                            <input type="text" name="searchFitnessHubBlocking" value="{{ $searchFitnessHubBlocking ?? '' }}"
                                id="searchInputFitnessHubBlocking" class="form-control" placeholder="Name" autocomplete="off">
                        </div>
                    </form>

                    <div class="mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary NewDateBlockingFitnessHubBtn badge">
                            <i class='bx bx-plus'></i> Date Blocking
                        </button>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <table id="fitnessHubDateBlockingTable" class="display table">
                    <thead>
                        <tr>
                            <th class="table-custom">Fitness Hub</th>
                            <th class="table-custom">REMARKS</th>
                            <!-- <th class="table-custom">STATUS</th> -->
                            <th class="table-custom">FROM</th>
                            <th class="table-custom">TO</th>
                            <th class="table-custom">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($fitnessHubDateBlockings->isEmpty())
                            <tr>
                                <td colspan="11" class="text-center">No Blockings Found</td>
                            </tr>
                        @else
                            @foreach ($fitnessHubDateBlockings as $fitnessHubDateBlocking)
                                <tr>
                                    <td>{{ strtoupper($fitnessHubDateBlocking->fitnessHub->fitness_hub_name ?? 'N/A') }}</td>
                                    <td>{{ strtoupper($fitnessHubDateBlocking->blocking_remarks ?? 'N/A') }}</td>
                                    <td>{{ strtoupper($fitnessHubDateBlocking->date_blocking_start ?? 'N/A') }}</td>
                                    <td>{{ strtoupper($fitnessHubDateBlocking->date_blocking_end ?? 'N/A') }}</td>

                                    <td>
                                        <button type="button"
                                            class="btn btn-danger delete_block_date_fitness-hub btn-responsive btn-equal btn-sm"
                                            data-bs-toggle="tooltip" data-bs-placement="right" title="Delete"
                                            data-id="{{ $fitnessHubDateBlocking->id }}">
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
            {{ $fitnessHubDateBlockings->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
    @include('backend.modal.fitness-hubs.fitness-hubs-modal')

@endsection