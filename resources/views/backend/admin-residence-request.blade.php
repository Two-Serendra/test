@extends('layouts.backend')
@section('content')

    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap">

                    <form action="{{ route('admin.search.residence.requests') }}" method="GET" id="searchFormResidence"
                        class="d-flex align-items-center" style="max-width: 250px;">
                        <div class="input-group text-dark w-100">
                            <span class="input-group-text">
                                <i class='bx bx-search-alt text-dark'></i>
                            </span>
                            <input type="text" name="searchResidenceRequest" value="{{ $searchResidenceRequest ?? '' }}"
                                id="searchInputResidence" class="form-control" placeholder="Email/Unit"
                                autocomplete="off">


                        </div>
                    </form>

                    <div class="mb-2 mb-md-0">
                        <!-- <button type="button" class="btn btn-secondary custom-dl-btn  badge DownloadWalkinPermit"
                                        id="addResidence">
                                        <i class='bx bx-download'></i> Download
                                    </button> -->
                        <button type="button" class="btn btn-primary badge AdminAddResidence" id="addResidence">
                            <i class='bx bx-plus'></i> Residence
                        </button>
                    </div>

                </div>
            </div>


            <div class="table-responsive">
                <table id="adminResidenceTable" class="table">
                    <thead>
                        <tr>
                            <th class="text-dark">Email</th>
                            <th class="text-dark">Resident Type</th>

                            <th class="text-dark">Section</th>
                            <th class="text-dark">Unit No</th>
                            <th class="text-dark">Status</th>
                            <th class="text-dark">Remarks</th>
                            <th class="text-dark">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($residenceRequests->isEmpty())
                            <tr>
                                <td colspan="12" class="text-center">No Record Found</td>
                            </tr>
                        @else
                            @foreach ($residenceRequests as $residenceRequest)
                                <tr>
                                    <td style="text-transform: none;">
                                        {{ $residenceRequest->user->email ?? 'N/A' }}
                                    </td>
                                    <td>
                                        @php
                                            $type = strtoupper($residenceRequest->resident_type ?? 'N/A');
                                        @endphp

                                        @if ($type === 'OWNER')
                                            <span class="badge badge-custom-success">{{ $type }}</span>
                                        @elseif ($type === 'TENANT')
                                            <span class="badge badge-custom-danger">{{ $type }}</span>
                                        @else
                                            <span class="badge badge-custom-secondary">{{ $type }}</span>
                                        @endif
                                    </td>
                                    <td>{{ strtoupper($residenceRequest->section ?? 'N/A') }}</td>

                                    <td>{{ strtoupper($residenceRequest->unit_no ?? 'N/A') }}</td>
                                    <td>
                                        @php
                                            $status = strtoupper($residenceRequest->status ?? 'N/A');
                                        @endphp

                                        @if ($status === 'PENDING')
                                            <span class="badge badge-custom-warning">{{ $status }}</span>
                                        @elseif ($status === 'ACTIVE')
                                            <span class="badge badge-custom-success">{{ $status }}</span>
                                        @elseif ($status === 'DENIED')
                                            <span class="badge badge-custom-danger">{{ $status }}</span>
                                        @else
                                            <span class="badge badge-custom-secondary">{{ $status }}</span>
                                        @endif
                                    </td>

                                    <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $residenceRequest->remarks ?? 'N/A'}}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-icon btn-secondary admin_edit_residence"
                                            data-bs-toggle="tooltip" data-bs-placement="left" title="Edit"
                                            data-id="{{ $residenceRequest->id }}">
                                            <i class='bx bx-edit'></i>
                                        </button>

                                        @php
                                            $status = strtoupper($residenceRequest->status ?? 'PENDING');
                                        @endphp

                                        {{-- Approve button --}}
                                        <button type="button" class="btn btn-sm btn-icon btn-primary update-status"
                                            data-bs-toggle="tooltip" data-bs-placement="right"
                                            title="{{ $status === 'ACTIVE' ? 'Already approved' : 'Approve' }}" data-status="ACTIVE"
                                            data-id="{{ $residenceRequest->id }}" @if($status === 'ACTIVE') disabled @endif>
                                            <i class='bx bxs-check-circle'></i>
                                        </button>

                                        {{-- Deny button --}}
                                        <button type="button" class="btn btn-sm btn-icon btn-danger update-status"
                                            data-bs-toggle="tooltip" data-bs-placement="right"
                                            title="{{ $status === 'DENIED' ? 'Already denied' : 'Deny' }}" data-status="DENIED"
                                            data-id="{{ $residenceRequest->id }}" @if($status === 'DENIED') disabled @endif>
                                            <i class='bx bx-x-circle'></i>
                                        </button>
                                    </td>

                                </tr>
                            @endforeach

                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        <div class="pagination-container-residence">
            {{ $residenceRequests->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>

    @include('backend.modal.admin-residence-modal')
@endsection