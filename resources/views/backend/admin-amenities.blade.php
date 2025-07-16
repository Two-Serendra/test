@extends('layouts.backend')

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap">

                    <form action="{{ route('admin.search.amenities') }}" method="GET" id="searchFormAmenities"
                        class="d-flex align-items-center" style="max-width: 250px;">
                        <div class="input-group text-dark w-100">
                            <span class="input-group-text">
                                <i class='bx bx-search-alt text-dark'></i>
                            </span>
                            <input type="text" name="searchAmenities" value="{{ $searchAmenity ?? '' }}"
                                id="searchInputAmenities" class="form-control" placeholder="Name" autocomplete="off">
                        </div>
                    </form>

                    <div class="mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary badge AdminAddAmenity" id="addAmenity">
                            <i class='bx bx-plus'></i> New Amenity
                        </button>
                    </div>

                </div>
            </div>

            <div class="table-container">
                <table id="amenityTable" class="display table">
                    <thead>
                        <tr>
                            <th class="table-custom">Amenity</th>
                            <th class="table-custom">Image</th>
                            <th class="table-custom">Description</th>
                            <th class="table-custom">Remarks</th>
                            <th class="table-custom">Status</th>
                            <th class="table-custom">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($amenities->isEmpty())
                            <tr>
                                <td colspan="12" class="text-center">No amenities Found</td>
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
                                        <button type="button" class="btn btn-sm btn-icon btn-primary edit_service"
                                            data-bs-toggle="tooltip" data-bs-placement="left" title="Edit"
                                            data-id="{{ $amenity->id }}">
                                            <i class='bx bx-edit'></i>
                                        </button>
                                        @if ($amenity->amenity_status == 1)
                                            <button type="button" class="btn btn-sm btn-icon btn-danger edit_service"
                                                data-bs-toggle="tooltip" data-bs-placement="right" title="Disable"
                                                data-id="{{ $amenity->id }}">
                                                <i class='bx bx-block'></i>
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-icon btn-activate edit_service"
                                                data-bs-toggle="tooltip" data-bs-placement="right" title="Enable"
                                                data-id="{{ $amenity->id }}">
                                                <i class='bx bx-check'></i>
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

    @include('backend.modal.admin-amenities-modal')

@endsection