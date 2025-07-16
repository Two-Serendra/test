@extends('layouts.backend')
@section('content')

    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap">

                    <form action="{{ route('search.admin.walkin.work.permit') }}" method="GET"
                        id="searchFormWalkinWorkPermit" class="d-flex align-items-center" style="max-width: 250px;">
                        <div class="input-group text-dark w-100">
                            <span class="input-group-text">
                                <i class='bx bx-search-alt text-dark'></i>
                            </span>
                            <input type="text" name="searchWalkinWorkPermit" value="{{ $searchWalkinWorkPermit ?? '' }}"
                                id="searchInputWalkinWorkPermit" class="form-control" placeholder="Name/Unit"
                                autocomplete="off">


                        </div>
                    </form>

                    <div class="mb-2 mb-md-0">
                        <button type="button" class="btn btn-secondary custom-dl-btn  badge DownloadWalkinPermit"
                            id="addWorkPermit">
                            <i class='bx bx-download'></i> Download
                        </button>
                        <button type="button" class="btn btn-primary badge AdminAddWorkPermit" id="addWorkPermit">
                            <i class='bx bx-plus'></i> New Permit
                        </button>
                    </div>

                </div>
            </div>


            <div class="table-responsive">
                <!-- <table id="userTable" class="table table-bordered table-hover table-striped"></table> -->
                <table id="walkinWorkPermitsTable" class="table">
                    <thead>
                        <tr>
                            <th class="text-dark">Permit Type</th>
                            <th class="text-dark">Unit No</th>
                            <th class="text-dark">Section</th>
                            <th class="text-dark">Days No</th>
                            <th class="text-dark">Date</th>
                            <th class="text-dark">Under Renovation</th>
                            <th class="text-dark">Description</th>
                            <th class="text-dark">Contractor/Worker</th>
                            <th class="text-dark">Approved by</th>
                            <th class="text-dark">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($walkinWorkPermits->isEmpty())
                            <tr>
                                <td colspan="12" class="text-center">No Record Found</td>
                            </tr>
                        @else
                            @foreach ($walkinWorkPermits as $walkinWorkPermit)
                                <tr>
                                    <td>{{ strtoupper($walkinWorkPermit->permit_type ?? 'N/A') }}</td>
                                    <td>{{ strtoupper($walkinWorkPermit->unit_no ?? 'N/A') }}</td>
                                    <td>{{ strtoupper($walkinWorkPermit->section ?? 'N/A') }}</td>
                                    <td>{{ strtoupper($walkinWorkPermit->no_days ?? 'N/A') }}</td>
                                    <td>{{ strtoupper($walkinWorkPermit->work_permit_booking_date ?? 'N/A') }}</td>
                                    <td>{{ strtoupper($walkinWorkPermit->under_renovation ?? 'N/A') }}</td>
                                    <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ strtoupper($walkinWorkPermit->description ?? 'N/A') }}
                                    </td>
                                    <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ strtoupper($walkinWorkPermit->contractor ?? 'N/A') }}
                                    </td>
                                    <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ strtoupper($walkinWorkPermit->approved_by ?? 'N/A') }}
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-icon btn-primary edit_walkinWorkPermit"
                                            data-bs-toggle="tooltip" data-bs-placement="left" title="Edit"
                                            data-id="{{ $walkinWorkPermit->id }}">
                                            <i class='bx bx-edit'></i>
                                        </button>

                                        <button type="button" class="btn btn-sm btn-icon btn-danger  gvnj" data-bs-toggle="tooltip"
                                            data-bs-placement="right" title="Delete" data-id="{{ $walkinWorkPermit->id }}">
                                            <i class='bx  bx-trash'></i>
                                        </button>
                                    </td>
                                </tr>

                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        <div class="pagination-container-walkin">
            {{ $walkinWorkPermits->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>

    @include('backend.modal.admin-work-permit-modal')
@endsection