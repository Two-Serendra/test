@extends('layouts.frontend')

@section('content')
    <div class="container py-5">
        {{-- Success alert for profile update --}}
        <div class="card mb-3 shadow mb-5 bg-body rounded">
            <h5 class="card-header">Profile Information</h5>
            <div class="card-body">
                @if (session('status') === 'profile-updated')
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Profile updated successfully.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- Profile Update Form --}}
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('patch')
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" name="email" type="email" class="form-control" value="{{ auth()->user()->email }}"
                            disabled>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', auth()->user()->name) }}" disabled>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !auth()->user()->hasVerifiedEmail())
                        <div class="mb-3 text-warning">
                            Your email address is unverified.
                            <button form="send-verification" class="btn btn-link p-0">Click here to resend verification
                                email.</button>
                        </div>
                    @endif

                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>

        <div class="card mb-3 shadow mb-5 bg-body rounded">
            <h5 class="card-header">Residence</h5>
            <div class="card-body">
                @if (auth()->user()->residentDetails->count())
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Section</th>
                                    <th>Resident Type</th>
                                    <th>Unit No</th>

                                </tr>
                            </thead>
                            <tbody>
                                @foreach (auth()->user()->residentDetails as $residence)
                                    <tr>
                                        <td>{{ $residence->section }}</td>
                                        <td>
                                            @if (strtolower($residence->resident_type) === 'owner')
                                                <span class="badge bg-success">Owner</span>
                                            @elseif (strtolower($residence->resident_type) === 'tenant')
                                                <span class="badge bg-danger">Tenant</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $residence->resident_type }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $residence->unit_no }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-muted">No residence records available.</div>
                @endif
            </div>
        </div>


        {{-- Password Update Section --}}
        <div class="card shadow mb-5 bg-body rounded">
            <h5 class="card-header">Update Password</h5>
            <div class="card-body">
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    @method('put')

                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input id="current_password" name="current_password" type="password"
                            class="form-control @error('current_password', 'updatePassword') is-invalid @enderror">
                        @error('current_password', 'updatePassword')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input id="password" name="password" type="password"
                            class="form-control @error('password', 'updatePassword') is-invalid @enderror">
                        @error('password', 'updatePassword')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password"
                            class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror">
                        @error('password_confirmation', 'updatePassword')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">Update Password</button>

                    @if (session('status') === 'password-updated')
                        <div class="alert alert-success mt-3 mb-0">
                            Password updated successfully.
                        </div>
                    @endif
                </form>
            </div>
        </div>

        {{-- Hidden form to resend email verification --}}
        <form id="send-verification" method="POST" action="{{ route('verification.send') }}">
            @csrf
        </form>
    </div>
@endsection