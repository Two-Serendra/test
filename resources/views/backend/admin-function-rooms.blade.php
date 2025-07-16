@extends('layouts.backend')

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap">

                    <form action="{{ route('admin.search.function.rooms') }}" method="GET" id="searchFormFunctionRooms"
                        class="d-flex align-items-center" style="max-width: 250px;">
                        <div class="input-group text-dark w-100">
                            <span class="input-group-text">
                                <i class='bx bx-search-alt text-dark'></i>
                            </span>
                            <input type="text" name="searchFunctionRooms" value="{{ $searchFunctionRoom ?? '' }}"
                                id="searchInputFunctionRooms" class="form-control" placeholder="Name" autocomplete="off">
                        </div>
                    </form>

                    <div class="mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary badge AdminAddFunctionRoom" id="addFunctionRoom">
                            <i class='bx bx-plus'></i> Function Room
                        </button>
                    </div>

                </div>
            </div>

            <div class="table-container">
                <table id="functionRoomTable" class="display table">
                    <thead>
                        <tr>
                            <th class="table-custom">Section</th>
                            <th class="table-custom">Function Room</th>
                            <th class="table-custom">Rate /hr</th>
                            <th class="table-custom">Capacity</th>
                            <th class="table-custom">Description</th>
                            <th class="table-custom">Policy</th>
                            <th class="table-custom">Image</th>
                            <th class="table-custom">360</th>

                            <th class="table-custom">Featured</th>
                            <th class="table-custom">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($functionRooms->isEmpty())
                            <tr>
                                <td colspan="12" class="text-center">No Function Rooms Found</td>
                            </tr>
                        @else
                            @foreach ($functionRooms as $functionRoom)
                                <tr>
                                    <td>{{ strtoupper($functionRoom->function_room_section ?: 'N/A') }}</td>
                                    <td>{{ strtoupper($functionRoom->function_room_name ?: 'N/A') }}</td>
                                    <td>{{ strtoupper($functionRoom->function_room_rate ?: 'N/A') }}</td>
                                    <td>{{ strtoupper($functionRoom->function_room_capacity ?: 'N/A') }}</td>
                                    <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ strtoupper($functionRoom->function_room_description ?: 'N/A') }}
                                    </td>
                                    <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ strtoupper($functionRoom->function_room_policy ?: 'N/A') }}
                                    </td>

                                    <td style="vertical-align: middle;">
                                        @if ($functionRoom->function_room_image)
                                            <img src="{{ asset('assets/images/function-rooms/' . $functionRoom->function_room_image) }}"
                                                alt="Amenity Image"
                                                style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px;">
                                        @else
                                            N/A
                                        @endif
                                    </td>

                                    <td style="vertical-align: middle;">
                                        @if ($functionRoom->function_room_360)
                                            <img src="{{ asset('assets/images/function-rooms-360/' . $functionRoom->function_room_360) }}"
                                                alt="Amenity Image 360"
                                                style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px;">
                                        @else
                                            N/A
                                        @endif
                                    </td>


                                    <td>
                                        @if ($functionRoom->featured == 1)
                                            <span class="badge bg-success">Yes</span>
                                        @else
                                            <span class="badge bg-danger">No</span>
                                        @endif
                                    </td>

                                    <td>
                                        <button type="button" class="btn btn-sm btn-icon btn-primary admin_edit_function_room"
                                            data-bs-toggle="tooltip" data-bs-placement="left" title="Edit"
                                            data-id="{{ $functionRoom->id }}">
                                            <i class='bx bx-edit'></i>
                                        </button>

                                        <button type="button" class="btn btn-sm btn-icon btn-danger delete_function_room"
                                            data-bs-toggle="tooltip" data-bs-placement="right" title="Delete"
                                            data-id="{{ $functionRoom->id }}">
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
            {{ $functionRooms->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>

    @include('backend.modal.admin-function-rooms-modal')

@endsection