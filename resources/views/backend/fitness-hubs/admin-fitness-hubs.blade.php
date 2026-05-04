@extends('layouts.backend')
@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <form action="{{ route('admin.search.fitness.hubs') }}" method="GET" id="searchFormActivity"
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
                        <button type="button" class="btn btn-primary badge addFitnessHubBtn">
                            <i class='bx bx-plus'></i> New Fitness Hub
                        </button>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <table id="fitnessHubsTable" class="display table">
                    <thead>
                        <tr>
                            <th class="table-custom"> FITNESS HUB</th>
                            <th class="table-custom">IMAGE</th>
                            <th class="table-custom">DESCRIPTION</th>
                            <th class="table-custom">REMARKS</th>
                            <th class="table-custom">STATUS</th>
                            <th class="table-custom">MAX BOOKING</th>
                            <th class="table-custom">START TIME</th>
                            <th class="table-custom">END TIME</th>
                            <th class="table-custom">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($fitnessHubs->isEmpty())
                            <tr>
                                <td colspan="11" class="text-center">Test No Records Found</td>
                            </tr>
                        @else
                            @foreach ($fitnessHubs as $fitnessHub)
                                <tr>
                                    <td>{{ strtoupper($fitnessHub->fitness_hub_name ?: 'N/A') }}</td>
                                    <td>
                                        @if ($fitnessHub->fitness_hub_image)
                                            <img src="{{ asset('assets/images/fitness-hubs/' . $fitnessHub->fitness_hub_image) }}"
                                                alt="Fitness Hub Image" style="width: 100px; height: auto;">
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {!! Str::limit(strip_tags($fitnessHub->fitness_hub_description ?: 'N/A'), 100, '...') !!}
                                    </td>
                                    <td>{{ strtoupper($fitnessHub->fitness_hub_remarks ?: 'N/A') }}</td>
                                    <td>
                                        @if ($fitnessHub->fitness_hub_status == 1)
                                            <span class="badge bg-primary custom-badge">Active</span>
                                        @else
                                            <span class="badge bg-danger custom-badge">Inactive</span>
                                        @endif
                                    </td>

                                    <td>{{ strtoupper($fitnessHub->fitness_hub_max_booking ?: 'N/A') }}</td>
                                    <td>{{ $fitnessHub->start_time_formatted ?? 'N/A' }}</td>
                                    <td>{{ $fitnessHub->end_time_formatted ?? 'N/A' }}</td>
                                    <td>
                                        <button type="button"
                                            class="btn btn-primary editInfo_id_fitnessHub btn-responsive btn-equal btn-sm"
                                            data-bs-toggle="tooltip" data-bs-placement="left" title="Edit"
                                            data-id="{{ $fitnessHub->id }}">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>

                                        @if ($fitnessHub->fitness_hub_status == 1)
                                            <button type="button"
                                                class="btn btn-danger deactivate_fitnessHub btn-responsive btn-equal btn-sm"
                                                data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-target="#fitnessHubRemarks"
                                                title="Deactivate" data-id="{{ $fitnessHub->id }}">
                                                <i class="fa-solid fa-ban"></i>
                                            </button>
                                        @else
                                            <button type="button"
                                                class="btn btn-success activate-fitnessHub btn-responsive btn-equal btn-sm"
                                                data-bs-toggle="tooltip" data-bs-placement="bottom" title="Activate"
                                                data-id="{{ $fitnessHub->id }}">
                                                <i class="fa-solid fa-check-circle"></i>
                                            </button>
                                        @endif

                                        <button type="button"
                                            class="btn btn-danger delete_fitnessHub btn-responsive btn-equal btn-sm"
                                            data-bs-toggle="tooltip" data-bs-placement="right" title="Delete"
                                            data-id="{{ $fitnessHub->id }}">
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
            {{ $fitnessHubs->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
    @include('backend.modal.fitness-hubs.fitness-hubs-modal')

    @push('scripts')
        <script>
            $(document).ready(function () {
                $('#fitnessHubDescription').summernote({
                    height: 300,
                    placeholder: 'Enter fitnessHub description here...'
                });
                $('#editFitnessHubDescription').summernote({
                    height: 300,
                    placeholder: 'Enter fitnessHub description here...'
                });
            });
        </script>
    @endpush

@endsection