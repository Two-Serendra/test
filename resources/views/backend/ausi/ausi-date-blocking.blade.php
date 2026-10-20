@extends('layouts.backend')

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-end align-items-center">

                    <button type="button" class="btn btn-primary badge" data-bs-toggle="modal"
                        data-bs-target="#addAusiDateBlockModal">
                        <i class="bx bx-plus"></i> Add Date Block
                    </button>

                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Date Range</th>
                            <th>Reason</th>
                            <th>Created</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($blocks as $block)
                            <tr>
                                <td>
                                    <div class="fw-semibold">
                                        {{ $block->start_date->format('M d, Y') }}
                                    </div>

                                    @if ($block->start_date->toDateString() !== $block->end_date->toDateString())
                                        <small class="text-muted">
                                            through {{ $block->end_date->format('M d, Y') }}
                                        </small>
                                    @else
                                        <small class="text-muted">Single day</small>
                                    @endif
                                </td>

                                <td>{{ $block->reason }}</td>

                                <td>{{ $block->created_at?->format('M d, Y') ?? '—' }}</td>

                                <td class="text-center">
                                    <form method="POST" action="{{ route('admin.ausi-date-blocking.destroy', $block) }}"
                                        class="deleteAusiDateBlockForm">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No AUSI date blocks have been created.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $blocks->links('vendor.pagination.bootstrap-5') }}
            </div>
        </div>
    </div>

    {{-- Add Date Block Modal --}}
    <div class="modal fade" id="addAusiDateBlockModal" tabindex="-1" aria-labelledby="addAusiDateBlockModalLabel"
        aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <form action="{{ route('admin.ausi-date-blocking.store') }}" id="addAusiDateBlockForm" method="POST"
                    class="needs-validation" novalidate>

                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title" id="addAusiDateBlockModalLabel">
                            Add AUSI Date Block
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">

                        <div class="alert alert-warning">
                            <i class="bx bx-info-circle me-1"></i>
                            Bookings will be unavailable for all units within the selected date range.
                        </div>

                        {{-- Start Date and End Date: Same Row --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="add_start_date" class="form-label">
                                    Start Date <span class="text-danger">*</span>
                                </label>

                                <input type="text" class="form-control flatpickr-date" id="add_start_date" name="start_date"
                                    placeholder="Select start date" autocomplete="off" required>

                                <div class="invalid-feedback">
                                    Please select a start date.
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="add_end_date" class="form-label">
                                    End Date <span class="text-danger">*</span>
                                </label>

                                <input type="text" class="form-control flatpickr-date" id="add_end_date" name="end_date"
                                    placeholder="Select end date" autocomplete="off" required>

                                <div class="invalid-feedback">
                                    Please select an end date.
                                </div>
                            </div>
                        </div>

                        {{-- Reason: Next Row --}}
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="add_reason" class="form-label">
                                    Reason <span class="text-danger">*</span>
                                </label>

                                <input type="text" class="form-control" id="add_reason" name="reason" maxlength="255"
                                    placeholder="e.g. Holiday or maintenance" required>

                                <div class="invalid-feedback">
                                    Please enter the reason for blocking these dates.
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>

                        <button type="submit" class="btn btn-primary">
                            Save
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function () {

                // Initialize Flatpickr for Start Date
                const endPicker = flatpickr('#add_end_date', {
                    dateFormat: 'Y-m-d',
                    allowInput: false,
                    disableMobile: true
                });

                flatpickr('#add_start_date', {
                    dateFormat: 'Y-m-d',
                    allowInput: false,
                    disableMobile: true,

                    onChange: function (selectedDates, dateStr) {
                        endPicker.set('minDate', dateStr || null);

                        // Clear End Date if it is earlier than Start Date
                        if (
                            endPicker.selectedDates.length &&
                            selectedDates.length &&
                            endPicker.selectedDates[0] < selectedDates[0]
                        ) {
                            endPicker.clear();
                        }
                    }
                });

                // Submit Date Block
                $('#addAusiDateBlockForm').on('submit', function (event) {
                    event.preventDefault();

                    const form = this;
                    const $form = $(form);
                    const $submitBtn = $form.find('button[type="submit"]');
                    const originalHtml = $submitBtn.html();
                    const originalWidth = $submitBtn.outerWidth();

                    if (!form.checkValidity()) {
                        form.classList.add('was-validated');

                        Swal.fire({
                            icon: 'warning',
                            title: 'Required Fields',
                            text: 'Please select both dates and enter a reason.',
                            confirmButtonColor: '#008b26'
                        });

                        return;
                    }

                    form.classList.remove('was-validated');

                    // Prevent End Date from being earlier than Start Date
                    const startDate = $('#add_start_date').val();
                    const endDate = $('#add_end_date').val();

                    if (endDate < startDate) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Invalid Date Range',
                            text: 'The end date cannot be earlier than the start date.',
                            confirmButtonColor: '#008b26'
                        });

                        return;
                    }

                    $submitBtn
                        .prop('disabled', true)
                        .html('<span class="spinner-border spinner-border-sm text-light"></span>')
                        .css('width', originalWidth + 'px');

                    $.ajax({
                        url: $form.attr('action'),
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'Accept': 'application/json'
                        },
                        data: $form.serialize(),

                        success: function (response) {
                            $('#addAusiDateBlockModal').modal('hide');

                            Swal.fire({
                                icon: 'success',
                                title: 'Date Block Created!',
                                text: response.message || 'AUSI date block saved successfully.',
                                timer: 2000,
                                showConfirmButton: false
                            });

                            setTimeout(function () {
                                window.location.reload();
                            }, 2000);
                        },

                        error: function (xhr) {
                            if (xhr.status === 422) {
                                let msg = 'Please check the form fields.';

                                if (xhr.responseJSON?.errors) {
                                    msg = Object.values(xhr.responseJSON.errors)
                                        .map(errors => errors[0])
                                        .join('\n');
                                } else if (xhr.responseJSON?.message) {
                                    msg = xhr.responseJSON.message;
                                }

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Validation Error',
                                    text: msg,
                                    confirmButtonColor: '#d33'
                                });

                                return;
                            }

                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'error',
                                title: 'Something went wrong. Please try again later.',
                                timer: 3000,
                                showConfirmButton: false
                            });
                        },

                        complete: function () {
                            $submitBtn
                                .prop('disabled', false)
                                .html(originalHtml)
                                .css('width', '');
                        }
                    });
                });

                // Delete Date Block
                $(document).on('submit', '.deleteAusiDateBlockForm', function (event) {
                    event.preventDefault();

                    const form = this;

                    Swal.fire({
                        icon: 'warning',
                        title: 'Delete Date Block?',
                        text: 'This action cannot be undone.',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, delete it',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d'
                    }).then(function (result) {
                        if (!result.isConfirmed) {
                            return;
                        }

                        const $button = $(form).find('button[type="submit"]');

                        $button.prop('disabled', true);

                        $.ajax({
                            url: $(form).attr('action'),
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                                'Accept': 'application/json'
                            },
                            data: $(form).serialize(),

                            success: function (response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: response.message || 'Date block deleted successfully.',
                                    timer: 2000,
                                    showConfirmButton: false
                                });

                                setTimeout(function () {
                                    window.location.reload();
                                }, 2000);
                            },

                            error: function () {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'error',
                                    title: 'Unable to delete the date block.',
                                    timer: 3000,
                                    showConfirmButton: false
                                });
                            },

                            complete: function () {
                                $button.prop('disabled', false);
                            }
                        });
                    });
                });

            });
        </script>
    @endpush
@endsection