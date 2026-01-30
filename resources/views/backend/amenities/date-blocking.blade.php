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

<div class="col-12 d-flex justify-content-start align-items-center mt-4">
    <h2>Date Blocking</h2>
</div>

<div class="card">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-6 d-flex justify-content-start align-items-center">
                <!-- Search Form -->
                <form action="{{ route('search-block-date') }}" method="GET" id="searchFormBooking"
                    class="d-flex align-items-center">
                    <div class="input-group" style="width: 200px;">
                        <span class="input-group-text">
                            <i class="fa-solid fa-magnifying-glass fa-sm"></i>
                        </span>
                        <input type="text" name="searchBooking" value="{{ $searchBooking ?? '' }}"
                            id="searchInputBooking" class="form-control" placeholder="AMENITY" autocomplete="off">
                    </div>
                </form>
            </div>
            <div class="col-6 d-flex justify-content-end align-items-center">
                <button type="button" class="btn btn-primary AddDateBlocking badge fs-5 px-2 py-2 me-2">
                <i class="fa-solid fa-plus fa-sm me-1"></i> Date Blocking
                </button>
                <!-- <button type="button" class="btn btn-primary badge fs-5 px-2 py-2 AddBookingAdmin">
                    <i class="fa-solid fa-plus me-1"></i> New Booking
                </button> -->
            </div>
        </div>

        <div class="table-container">
            <table id="dateBlockingTable" class="display table">
                <thead>
                    <tr>
                        <th class="table-custom">AMENITY</th>
                        <th class="table-custom">REMARKS</th>
                        <!-- <th class="table-custom">STATUS</th> -->
                        <th class="table-custom">FROM</th>
                        <th class="table-custom">TO</th>
                        <th class="table-custom">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @if($dateBlockings->isEmpty())
                        <tr>
                            <td colspan="11" class="text-center">No Blockings Found</td>
                        </tr>
                    @else
                        @foreach ($dateBlockings as $dateBlocking) 
                            <tr>
                                <td>{{ strtoupper($dateBlocking->amenity->amenity_name ?? 'N/A') }}</td>
                                <td>{{ strtoupper($dateBlocking->blocking_remarks ?? 'N/A') }}</td>
                                <!-- <td>
                                            @if ($dateBlocking->blocking_status == 1)
                                                <span class="badge bg-success custom-badge">Active</span>
                                            @else
                                                <span class="badge bg-danger custom-badge">Inactive</span>
                                            @endif
                                        </td> -->
                                <td>{{ strtoupper($dateBlocking->date_blocking_start ?? 'N/A') }}</td>
                                <td>{{ strtoupper($dateBlocking->date_blocking_end ?? 'N/A') }}</td>

                                <td>
                                    <button type="button" class="btn btn-danger delete_block_date btn-responsive btn-equal btn-sm"
                                    data-bs-toggle="tooltip" data-bs-placement="right" title="Delete"
                                    data-id="{{ $dateBlocking->id }}">
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
        {{ $dateBlockings->links('vendor.pagination.bootstrap-5') }}
    </div>
</div>
@include('backend.modal.dateBlocking-modal')
@endsection