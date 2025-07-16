@extends('layouts.backend')
@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row mb-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <form action="{{ route('admin.search.email') }}" method="GET" id="searchFormEmails"
                        class="d-flex align-items-center" style="max-width: 250px;">
                        <div class="input-group text-dark w-100">
                            <span class="input-group-text">
                                <i class='bx bx-search-alt text-dark'></i>
                            </span>
                            <input type="text" name="searchEmails" value="{{ $searchService ?? '' }}" id="searchInputEmails"
                                class="form-control" placeholder="Unit/Email" autocomplete="off">

                        </div>
                    </form>


                    <div class="mb-2 mb-md-0">
                        <form id="emailUploadForm" enctype="multipart/form-data" class="d-flex align-items-center gap-2">
                            @csrf
                            <input type="file" name="email_file" id="emailInput" accept=".csv" class="form-control" required
                                style="max-width: 300px;">

                            <div class="d-flex align-items-center gap-2" style="min-width: 200px;">
                                <button type="submit" class="btn btn-primary" id="uploadFile">
                                    <i class='bx bx-upload'></i> Upload
                                </button>

                                <!-- Progress Bar -->
                                <div class="progress" style="height: 30px; width: 150px; display: none;">
                                    <div class="progress-bar progress-bar-animated bg-info" role="progressbar"
                                        style="width: 0%">0%</div>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
            </div>

            <div class=" table-responsive">
                <table id="emailTable" class="table">
                    <thead>
                        <tr>
                            <th class="text-dark">Unit No</th>
                            <th class="text-dark">Email</th>
                            <th class="text-dark">Created at</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($emailPaginationLinks->isEmpty())
                            <tr>
                                <td colspan="5" class="text-center">No Emails Found</td>
                            </tr>
                        @else
                            @foreach ($emailPaginationLinks as $emailPaginationLink)
                                <tr>
                                    <td class="text-uppercase">{{($emailPaginationLink->unit_no ?? 'N/A') }}</td>
                                    <td>{{($emailPaginationLink->email ?? 'N/A') }}</td>
                                    <td>{{($emailPaginationLink->created_at ?? 'N/A') }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-icon btn-primary edit_email"
                                            data-bs-toggle="tooltip" data-bs-placement="left" title="Edit"
                                            data-id="{{ $emailPaginationLink->id }}">
                                            <i class='bx bx-edit'></i>
                                        </button>

                                        <button type="button" class="btn btn-sm btn-icon btn-danger delete_email"
                                            data-bs-toggle="tooltip" data-bs-placement="right" title="Delete"
                                            data-id="{{ $emailPaginationLink->id }}">
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
        <div class="pagination-container-email">
            {{ $emailPaginationLinks->links('vendor.pagination.bootstrap-5') }}
        </div>

    </div>
    </div>
    @include('backend.modal.admin-email-modal')
@endsection