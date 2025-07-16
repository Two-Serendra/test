@extends('layouts.backend')
@section('content')

    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap">

                    <form action="{{ route('search.admin.minor.work.permit') }}" method="GET" id="searchFormMinorWorkPermit"
                        class="d-flex align-items-center" style="max-width: 250px;">
                        <div class="input-group text-dark w-100">
                            <span class="input-group-text">
                                <i class='bx bx-search-alt text-dark'></i>
                            </span>
                            <input type="text" name="searchMinorWorkPermit" value="{{ $searchMinorWorkPermi ?? '' }}"
                                id="searchInputMinorWorkPermit" class="form-control" placeholder="Name/Unit"
                                autocomplete="off">
                            

                        </div>
                    </form>
                </div>
            </div>
            <div class=" table-responsive">
                <!-- <table id="userTable" class="table table-bordered table-hover table-striped"></table> -->
                <table id="minorWorkPermitsTable" class="table">
                    <thead>
                        <tr>
                            <th class="text-dark">Full Name</th>
                            <th class="text-dark">Unit No</th>
                            <th class="text-dark">Resident Type</th>
                            <th class="text-dark">Section</th>
                            <th class="text-dark">Date</th>
                            <th class="text-dark">Work Type</th>
                            <th class="text-dark">Contractor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($minorWorkPermits->isEmpty())
                            <tr>
                                <td colspan="12" class="text-center">No Record Found</td>
                            </tr>
                        @else
                            @foreach ($minorWorkPermits as $minorWorkPermit)
                                <tr>
                                    <td>{{ $minorWorkPermit->user->name ?? 'N/A' }}</td>
                                    <td>{{ $minorWorkPermit->user->unit_no ?? 'N/A' }}</td>
                                    <td>
                                        @php
                                            $resType = strtolower($minorWorkPermit->user->res_type ?? '');
                                        @endphp

                                        @if ($resType === 'tenant')
                                            <span class="badge bg-danger text-uppercase">{{ $minorWorkPermit->user->res_type }}</span>
                                        @elseif ($resType === 'owner')
                                            <span class="badge bg-success text-uppercase">{{ $minorWorkPermit->user->res_type }}</span>
                                        @else
                                            <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </td>

                                    <td>{{ $minorWorkPermit->user->section ?? 'N/A' }}</td>

                                    <td>{{ $minorWorkPermit->work_permit_booking_date ?? 'N/A' }}</td>
                                    <td>{{ $minorWorkPermit->work_type ?? 'N/A' }}</td>
                                    <td>{{ $minorWorkPermit->contractor ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        <div class="pagination-container">
            {{ $minorWorkPermits->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>

    @include('backend.modal.admin-work-permit-modal')
@endsection