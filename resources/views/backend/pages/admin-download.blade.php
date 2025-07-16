@extends('layouts.backend')
@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-lg-12 mx-auto">
                    <form id="fileUploadForm" enctype="multipart/form-data" class="d-flex align-items-center gap-2">
                        @csrf

                        <select name="category" id="categorySelect" class="form-select" style="max-width: 250px;" required>
                            <option value="" selected disabled>Select Category</option>
                            <option value="admin forms">Admin Forms</option>
                            <option value="engineering forms">Engineering Forms</option>
                            <option value="function and amenity form">Function & Amenity Forms</option>
                            <option value="service request form">Service Request Form</option>
                        </select>

                        <input type="file" name="file" id="fileInput" class="form-control" required
                            style="max-width: 300px;" disabled>

                        <div class="d-flex align-items-center gap-2" style="min-width: 200px;">
                            <button type="submit" class="btn btn-primary" id="uploadFile" disabled>
                                <i class='bx bx-upload'></i> Upload
                            </button>

                            <!-- Progress Bar -->
                            <div class="progress" style="height: 30px; width: 150px; display: none;">
                                <div class="progress-bar progress-bar-animated bg-info"
                                    role="progressbar" style="width: 0%">0%</div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class=" table-responsive">
                <!-- <table id="userTable" class="table table-bordered table-hover table-striped"></table> -->
                <table id="downloadTable" class="table">
                    <thead>
                        <tr>
                            <th class="text-dark">Category Name</th>
                            <th class="text-dark">File Name</th>
                            <th class="text-dark">Uploaded at</th>
                            <th class="text-dark">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($downloads->isEmpty())
                            <tr>
                                <td colspan="5" class="text-center">No Downloads Found</td>
                            </tr>
                        @else
                            @foreach ($downloads as $download)
                                <tr>
                                    <td class="text-uppercase">{{($download->category_name ?? 'N/A') }}</td>
                                    <td>{{($download->file_name ?? 'N/A') }}</td>
                                    <td>{{($download->created_at ?? 'N/A') }}</td>

                                    <td>
                                        <button type="button" class="btn btn-sm btn-icon btn-danger delete-file-btn"
                                            data-bs-toggle="tooltip" data-bs-placement="right" title="Delete"
                                            data-id="{{ $download->id }}">
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
        <div class="pagination-container">
            {{ $downloads->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
  
@endsection