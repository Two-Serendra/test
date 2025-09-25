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
                        <button type="button" class="btn btn-primary badge AddFunctionRoomDateBlocking"
                            id="addFunctionRoomDateBlocking">
                            <i class='bx bx-plus'></i> Date Blocking
                        </button>
                    </div>

                </div>
            </div>

            <div class="table-container">
                <table id="functionRoomDateBlockingTable" class="display table">
                    <thead>
                        <tr>
                            <th class="table-custom">Function Room</th>
                            <th class="table-custom">Remarks</th>
                            <th class="table-custom">FROM</th>
                            <th class="table-custom">TO</th>
                            <th class="table-custom">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($functionRoomDateBlockings->isEmpty())
                            <tr>
                                <td colspan="12" class="text-center">No Records Found</td>
                            </tr>
                        @else
                            @foreach ($functionRoomDateBlockings as $functionRoomDateBlocking)
                                <tr>
                                    <td>{{ strtoupper($functionRoomDateBlocking->functionRoom->function_room_name ?: 'N/A') }}</td>
                                    <td>{{ strtoupper($functionRoomDateBlocking->blocking_remarks ?: 'N/A') }}</td>
                                    <td>{{ strtoupper($functionRoomDateBlocking->date_blocking_start ?: 'N/A') }}</td>
                                    <td>{{ strtoupper($functionRoomDateBlocking->date_blocking_end ?: 'N/A') }}</td>
                                    <td>
                                        <button type="button"
                                            class="btn btn-danger delete_block_date btn-responsive btn-equal btn-sm"
                                            data-bs-toggle="tooltip" data-bs-placement="right" title="Delete"
                                            data-id="{{ $functionRoomDateBlocking->id }}">
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

        <div class="pagination-container-function-room-date-blocking">
            {{ $functionRoomDateBlockings->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>

    @include('backend.modal.admin-function-room-date-blocking-modal')

@endsection