@extends('layouts.backend')

@section('content')

    @php
        $roles = [
            0 => ['label' => 'User', 'class' => 'bg-secondary border-secondary'],
            1 => ['label' => 'Super Admin', 'class' => 'bg-dark border-dark'],
            2 => ['label' => 'Admin', 'class' => 'bg-success border-success'],
            3 => ['label' => 'Engineering', 'class' => 'bg-warning border-warning text-dark'],
            4 => ['label' => 'Security', 'class' => 'bg-danger border-danger'],
        ];
    @endphp

    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap">

                    <form action="{{ route('admin.search.user') }}" method="GET" id="searchFormUsers"
                        class="d-flex align-items-center" style="max-width: 250px;">
                        <div class="input-group text-dark w-100">
                            <span class="input-group-text">
                                <i class='bx bx-search-alt text-dark'></i>
                            </span>
                            <input type="text" name="searchUsers" value="{{ $searchUser ?? '' }}" id="searchInputUsers"
                                class="form-control" placeholder="Name" autocomplete="off">
                        </div>
                    </form>

                    <div class="mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary badge AdminAddUser" id="addUser">
                            <i class='bx bx-plus'></i> New User
                        </button>
                    </div>

                </div>
            </div>

            <div class="table-container">
                <table id="userTable" class="display table">
                    <thead>
                        <tr>
                            <th class="table-custom">Name</th>
                            <th class="table-custom">Email</th>
                            <th class="table-custom">Role</th>
                            <th class="table-custom">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($users->isEmpty())
                            <tr>
                                <td colspan="4" class="text-center">No Users Found</td>
                            </tr>
                        @else
                            @foreach ($users as $user)
                                <tr>
                                    <td>{{ strtoupper($user->name ?? 'N/A') }}</td>
                                    <td>{{ $user->email ?? 'N/A' }}</td>
                                    <td>
                                        @if (isset($roles[$user->role_id]))
                                            <span class="badge {{ $roles[$user->role_id]['class'] }} custom-badge">
                                                {{ $roles[$user->role_id]['label'] }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary custom-badge">Unknown</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-icon btn-primary edit_admin_user"
                                            data-bs-toggle="tooltip" data-bs-placement="left" title="Edit"
                                            data-id="{{ $user->id }}">
                                            <i class='bx bx-edit'></i>
                                        </button>

                                        <button type="button" class="btn btn-sm btn-icon btn-danger delete_user"
                                            data-bs-toggle="tooltip" data-bs-placement="right" title="Delete"
                                            data-id="{{ $user->id }}">
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
            {{ $users->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>

    @include('backend.modal.admin-user-creation-modal')

@endsection