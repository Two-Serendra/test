@extends('layouts.backend')
@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-6  d-flex justify-content-between align-items-center flex-wrap">

                    <form action="{{ route('admin.search.ausi.inspection.item') }}" method="GET"
                        id="searctAusiInspectionItemForm" class="d-flex align-items-center" style="max-width: 250px;">
                        <div class="input-group text-dark w-100">
                            <span class="input-group-text">
                                <i class='bx bx-search-alt text-dark'></i>
                            </span>
                            <input type="text" name="searchAusiInpectionItem" value="{{ $searchBooking ?? '' }}"
                                id="searchInputAusiInspectionItem" class="form-control" placeholder="Name/Unit"
                                autocomplete="off">

                        </div>
                    </form>
                </div>

                <div class="col-6 d-flex justify-content-end align-items-center">
                    <button type="button" class="btn btn-primary badge AddAusiInspectionItem me-2">
                        <i class='bx bx-plus'></i> New item
                    </button>

                </div>
            </div>
            <div class="table-responsive">
                <table id="AusiInspectionItemTable" class="table">
                    <thead>
                        <tr>
                            <th class="text-dark">Item</th>
                            <th class="text-dark">Option 1</th>
                            <th class="text-dark">Option 2</th>
                            <th class="text-dark">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($AusiInspectionItems->isEmpty())
                            <tr>
                                <td colspan="4" class="text-center">No Record Found</td>
                            </tr>
                        @else
                            @foreach ($AusiInspectionItems as $AusiInspectionItem)
                                <tr>
                                    <td>{{ $AusiInspectionItem->item_name ?? 'N/A' }}</td>
                                    <td>{{ $AusiInspectionItem->option_1 ?? 'N/A' }}</td>
                                    <td>{{ $AusiInspectionItem->option_2 ?? 'N/A' }}</td>
                                    <td>

                                        <button type="button" class="btn btn-primary edit_inspection_item btn-sm"
                                            data-bs-toggle="tooltip" data-bs-placement="left" title="Edit"
                                            data-id="{{ $AusiInspectionItem->id }}">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>

                                        <button type="button"
                                            class="btn btn-danger delete_inspection_item btn-equal btn-responsive btn-sm"
                                            data-bs-toggle="tooltip" data-bs-placement="right" title="Delete"
                                            data-id="{{ $AusiInspectionItem->id }}">
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
            {{ $AusiInspectionItems->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>

    @include('backend.modal.ausi.ausi-booking-modal')

@endsection