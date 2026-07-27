@extends('layouts.backend')

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap">

                    <form id="searchFormAddOns" class="d-flex align-items-center" style="max-width: 250px;">
                        <div class="input-group text-dark w-100">
                            <span class="input-group-text">
                                <i class='bx bx-search-alt text-dark'></i>
                            </span>
                            <input type="text" name="searchAddOns" id="searchInputAddOns" class="form-control"
                                placeholder="Item" autocomplete="off">
                        </div>
                        <button type="submit" hidden></button>
                    </form>

                    <div class="mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary badge FunctionRoomDiscounts">
                            <i class='bx bx-plus'></i> Discount
                        </button>
                    </div>

                </div>
            </div>

            <div class="table-container">
                <table id="functionRoomDiscountTable" class="display table">
                    <thead>
                        <tr>
                            <th class="table-custom">Function Room</th>
                            <th class="table-custom">Discount</th>
                            <th class="table-custom">Remarks</th>
                            <th class="table-custom">Start Date</th>
                            <th class="table-custom">End Date</th>
                            <th class="table-custom">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($functionRoomDiscounts->isEmpty())
                            <tr>
                                <td colspan="12" class="text-center">No Discount Found</td>
                            </tr>
                        @else
                            @foreach ($functionRoomDiscounts as $functionRoomDiscount)
                                <tr>
                                    <td>{{ $functionRoomDiscount->functionRoom->function_room_name ?: 'N/A' }}</td>
                                    <td>
                                        @if($functionRoomDiscount->discount > 0)
                                            <span
                                                class="badge bg-success">{{ rtrim(rtrim(number_format($functionRoomDiscount->discount, 2), '0'), '.') }}%</span>
                                        @else
                                            <span class="badge bg-secondary">0%</span>
                                        @endif
                                    </td>
                                    <td>{{ $functionRoomDiscount->remarks ?: 'N/A'}}</td>
                                    <td>{{ $functionRoomDiscount->start_date ?: 'N/A' }}</td>
                                    <td>{{ $functionRoomDiscount->end_date ?: 'N/A' }}</td>

                                    <td>
                                        {{-- Edit button --}}
                                        <button type="button" class="btn btn-sm btn-icon btn-secondary edit_function_room_discounts"
                                            data-bs-toggle="tooltip" data-bs-placement="left" title="Edit"
                                            data-id="{{ $functionRoomDiscount->id }}">
                                            <i class='bx bx-edit'></i>
                                        </button>


                                        {{-- Delete --}}
                                        <button type="button" class="btn btn-sm btn-icon btn-danger delete_function_room_discounts"
                                            data-bs-toggle="tooltip" data-bs-placement="right" title="Delete"
                                            data-id="{{ $functionRoomDiscount->id }}">
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

        <div class="pagination-container-function-room-discounts">
            {{ $functionRoomDiscounts->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>

    @include('backend.modal.admin-function-room-discount-modal')
    @push('scripts')
        <script src="{{ asset('assets/backend/js/function-room-discount.js')}}"></script>
    @endpush
@endsection