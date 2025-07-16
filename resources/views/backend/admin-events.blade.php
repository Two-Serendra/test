@extends('layouts.backend')

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap">

                    <form id="searchFormEvents" class="d-flex align-items-center" style="max-width: 250px;">
                        <div class="input-group text-dark w-100">
                            <span class="input-group-text">
                                <i class='bx bx-search-alt text-dark'></i>
                            </span>
                            <input type="text" name="searchEvent" id="searchInputEvents" class="form-control"
                                placeholder="Title" autocomplete="off">
                        </div>

                        <!-- 🔽 Add this hidden button to allow Enter to trigger the form -->
                        <button type="submit" hidden></button>
                    </form>

                    <div class="mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary badge AdminAddEvent" id="addEvent">
                            <i class='bx bx-plus'></i> New Event
                        </button>
                    </div>

                </div>
            </div>

            <div class="table-container">
                <table id="eventTable" class="display table">
                    <thead>
                        <tr>
                            <th class="table-custom">Title</th>
                            <th class="table-custom">Details</th>
                            <th class="table-custom">Image</th>
                            <th class="table-custom">Date</th>
                            <th class="table-custom">Created at</th>
                            <th class="table-custom">Updated at</th>
                            <th class="table-custom">Action</th>

                        </tr>
                    </thead>
                    <tbody>
                        @if($events->isEmpty())
                            <tr>
                                <td colspan="12" class="text-center">No Events Found</td>
                            </tr>
                        @else
                            @foreach ($events as $event)
                                <tr>
                                    <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ strtoupper($event->event_title ?: 'N/A') }}
                                    </td>
                                    <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {!! Str::limit(strip_tags($event->event_details), 100, '...') !!}
                                    </td>

                                    <td style="vertical-align: middle;">
                                        @if ($event->event_image)
                                            <img src="{{ asset('assets/images/events/' . $event->event_image) }}" alt="Event Image"
                                                style="width: 100px; height: 100px; object-fit: contain;">
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>{{ strtoupper($event->event_date ?: 'N/A') }}</td>

                                    <td>{{ strtoupper($event->created_at ?: 'N/A') }}</td>
                                    <td>{{ strtoupper($event->updated_at ?: 'N/A') }}</td>

                                    <td>
                                        <button type="button" class="btn btn-sm btn-icon btn-primary admin_edit_event"
                                            data-bs-toggle="tooltip" data-bs-placement="left" title="Edit"
                                            data-id="{{ $event->id }}">
                                            <i class='bx bx-edit'></i>
                                        </button>

                                        <button type="button" class="btn btn-sm btn-icon btn-danger delete_event"
                                            data-bs-toggle="tooltip" data-bs-placement="right" title="Delete"
                                            data-id="{{ $event->id }}">
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

        <div class="pagination-container-events">
            {{ $events->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>

    @include('backend.modal.admin-events-modal')
    @push('scripts')
        <script>
            $(document).ready(function () {
                $('#event_details').summernote({
                    height: 300,
                    placeholder: 'Enter event details here...'
                });

                $('#update_event_details').summernote({
                    height: 300,
                    placeholder: 'Update event details here...'
                });
            });
        </script>
    @endpush
@endsection