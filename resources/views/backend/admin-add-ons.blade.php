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
                        <button type="button" class="btn btn-primary badge AdminAddaddOns" id="addaddOns">
                            <i class='bx bx-plus'></i> Add Ons
                        </button>
                    </div>

                </div>
            </div>

            <div class="table-container">
                <table id="addOnsTable" class="display table">
                    <thead>
                        <tr>
                            <th class="table-custom">Item</th>
                            <th class="table-custom">Qty</th>
                            <th class="table-custom">Price</th>
                            <th class="table-custom">Status</th>
                            <th class="table-custom">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($addOns->isEmpty())
                            <tr>
                                <td colspan="12" class="text-center">No amenities Found</td>
                            </tr>
                        @else
                            @foreach ($addOns as $addOn)
                                <tr>
                                    <td>{{ strtoupper($addOn->item ?: 'N/A') }}</td>
                                    <td>{{ strtoupper($addOn->qty ?: 'N/A') }}</td>
                                    <td>{{ strtoupper($addOn->price ?: 'N/A') }}</td>


                                    <td>
                                        @if ($addOn->status == 1)
                                            <span class="badge bg-success custom-badge">Active</span>
                                        @else
                                            <span class="badge bg-danger custom-badge">Disabled</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{-- Edit button --}}
                                        <button type="button" class="btn btn-sm btn-icon btn-secondary admin_edit_add_ons"
                                            data-bs-toggle="tooltip" data-bs-placement="left" title="Edit"
                                            data-id="{{ $addOn->id }}">
                                            <i class='bx bx-edit'></i>
                                        </button>

                                        {{-- Enable / Disable --}}
                                        @if ($addOn->status == 1)
                                            <button type="button" class="btn btn-sm btn-warning btn-icon disable_add_ons"
                                                data-id="{{ $addOn->id }}" data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="Disable">
                                                <i class='bx bx-block'></i>
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-primary btn-icon enable_add_ons"
                                                data-id="{{ $addOn->id }}" data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="Enable">
                                                <i class='bx bx-check'></i>
                                            </button>
                                        @endif

                                        {{-- Delete --}}
                                        <button type="button" class="btn btn-sm btn-icon btn-danger delete_add_ons"
                                            data-bs-toggle="tooltip" data-bs-placement="right" title="Delete"
                                            data-id="{{ $addOn->id }}">
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

        <div class="pagination-container-add-ons">
            {{ $addOns->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>

    @include('backend.modal.admin-addOns-modal')

@endsection