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
                    <form action="{{ route('admin.search.amenities') }}" method="GET" id="searchFormAmenity"
                        class="d-flex align-items-center" style="max-width: 250px;">
                        <div class="input-group text-dark w-100">
                            <span class="input-group-text">
                                <i class='bx bx-search-alt text-dark'></i>
                            </span>
                            <input type="text" name="searchAmenity" value="{{ $searchAmenity ?? '' }}"
                                id="searchInputAmenity" class="form-control" placeholder="Name" autocomplete="off">
                        </div>
                    </form>

                    <div class="mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary badge addAmenity" id="addAmenity">
                             <i class='bx bx-plus'></i> New Amenity
                        </button>
                    </div>

                </div>
            </div>

            <div class="table-container">
                <table id="amenityTable" class="display table">
                    <thead>
                        <tr>
                            <th class="table-custom">AMENITY</th>
                            <th class="table-custom">IMAGE</th>
                            <th class="table-custom">DESCRIPTION</th>
                            <th class="table-custom">REMARKS</th>
                            <th class="table-custom">STATUS</th>

                            <th class="table-custom">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($amenities->isEmpty())
                            <tr>
                                <td colspan="11" class="text-center">No Records Found</td>
                            </tr>
                        @else
                            @foreach ($amenities as $amenity)
                                <tr>
                                    <td>{{ strtoupper($amenity->amenity_name ?: 'N/A') }}</td>
                                    <td>
                                        @if ($amenity->amenity_image)
                                            <img src="{{ asset('assets/images/amenities/' . $amenity->amenity_image) }}"
                                                alt="Amenity Image" style="width: 100px; height: auto;">
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ strtoupper($amenity->amenity_description ?: 'N/A') }}
                                    </td>

                                    <td>{{ strtoupper($amenity->amenity_remarks ?: 'N/A') }}</td>
                                    <td>
                                        @if ($amenity->amenity_status == 1)
                                            <span class="badge bg-success custom-badge">Active</span>
                                        @else
                                            <span class="badge bg-danger custom-badge">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button"
                                            class="btn btn-primary btn-equal editInfo_id_amenity btn-responsive btn-sm"
                                            data-bs-toggle="tooltip" data-bs-placement="left" title="Edit"
                                            data-id="{{ $amenity->id }}">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        @if ($amenity->amenity_status == 1)
                                            <button type="button"
                                                class="btn btn-danger btn-equal add_remarks_amenity btn-responsive btn-sm"
                                                data-bs-toggle="tooltip" data-bs-placement="right" data-bs-target="#amenityRemarks"
                                                title="Deactivate" data-id="{{ $amenity->id }}">
                                                <i class="fa-solid fa-ban"></i>
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-success btn-equal show-amenities btn-responsive btn-sm"
                                                data-bs-toggle="tooltip" data-bs-placement="right" title="Activate"
                                                data-id="{{ $amenity->id }}">
                                                <i class="fa-solid fa-check-circle"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        <div class="pagination-container">
            {{ $amenities->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
    @include('backend.modal.amenities.amenities-modal')
@endsection