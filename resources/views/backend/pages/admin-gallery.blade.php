@extends('layouts.backend')
@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <form id="uploadGalleryForm" action="{{ route('admin.gallery.upload') }}" method="POST"
                        enctype="multipart/form-data" class="d-flex gap-2 align-items-center">
                        @csrf
                        <input type="file" name="images[]" id="galleryFileInput" class="form-control" multiple required>

                        <button type="submit" class="btn btn-primary badge" id="galleryUploadBtn">
                            <i class='bx bx-upload'></i> Upload
                        </button>

                        <div class="progress" style="height: 30px; width: 150px; display: none;">
                            <div class="progress-bar progress-bar-animated bg-info" role="progressbar" style="width: 0%">0%
                            </div>
                        </div>
                    </form>
                </div>

            </div>
            <div class="table-responsive">
                <!-- <table id="userTable" class="table table-bordered table-hover table-striped"></table> -->
                <table id="galleryTable" class="table">
                    <thead>
                        <tr>
                            <th class="text-dark">File Name</th>
                            <th class="text-dark">Image</th>
                            <th class="text-dark">Uploaded at</th>
                            <th class="text-dark">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($pictures->isEmpty())
                            <tr>
                                <td colspan="5" class="text-center">No Downloads Found</td>
                            </tr>
                        @else
                            @foreach ($pictures as $picture)
                                <tr>
                                    <td class="text-uppercase">{{($picture->file_name ?? 'N/A') }}</td>
                                    <td style="vertical-align: middle;">
                                        @if ($picture->file_name )
                                            <img src="{{ asset('assets/images/gallery/' . $picture->file_name) }}" alt="Gallery Image"
                                                style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px;">
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>{{($picture->created_at ?? 'N/A') }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-icon btn-danger delete-file-btn"
                                            data-bs-toggle="tooltip" data-bs-placement="right" title="Delete"
                                            data-id="{{ $picture->id }}">
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
            {{ $pictures->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>

@endsection