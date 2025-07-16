@extends('layouts.backend')
@section('content')

    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap">

                    <form action="{{ route('search.services') }}" method="GET" id="searchFormServices"
                        class="d-flex align-items-center" style="max-width: 250px;">
                        <div class="input-group text-dark w-100">
                            <span class="input-group-text">
                                <i class='bx bx-search-alt text-dark'></i>
                            </span>
                            <input type="text" name="searchServices" value="{{ $searchService ?? '' }}"
                                id="searchInputServices" class="form-control" placeholder="Name" autocomplete="off">

                        </div>
                    </form>


                    <div class="mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary badge addService" id="addService">
                            <i class='bx bx-plus'></i> New Service
                        </button>
                    </div>

                </div>
            </div>


            <div class=" table-responsive">
                <!-- <table id="userTable" class="table table-bordered table-hover table-striped"></table> -->
                <table id="serviceTable" class="table">
                    <thead>
                        <tr>
                            <th class="text-dark">Name</th>
                            <th class="text-dark">Short Description</th>
                            <th class="text-dark">Long Description</th>
                            <th class="text-dark">Image</th>
                            <th class="text-dark">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($services->isEmpty())
                            <tr>
                                <td colspan="5" class="text-center">No Services Found</td>
                            </tr>
                        @else
                            @foreach ($services as $service)
                                <tr>
                                    <td>{{($service->service_name ?? 'N/A') }}</td>
                                    <td>{{ $service->service_short_description ?? 'N/A'}}</td>
                                    <td>{{ $service->service_long_description ?? 'N/A'}}</td>
                                    <td>
                                        @if ($service->service_image)
                                            <img src="{{ asset('assets/images/services/' . $service->service_image) }}"
                                                alt="Service Image" class="img-fluid" style="max-width: 100px;">
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-icon btn-primary edit_service"
                                            data-bs-toggle="tooltip" data-bs-placement="left" title="Edit"
                                            data-id="{{ $service->id }}">
                                            <i class='bx bx-edit'></i>
                                        </button>

                                        <button type="button" class="btn btn-sm btn-icon btn-danger delete_service"
                                            data-bs-toggle="tooltip" data-bs-placement="right" title="Delete"
                                            data-id="{{ $service->id }}">
                                            <i class='bx bx-trash'></i>
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
            {{ $services->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
    @include('backend.modal.admin-services-modal')

@endsection