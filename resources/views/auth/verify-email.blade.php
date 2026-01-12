@extends('layouts.frontend')

@section('content')
<div class="container py-5 d-flex justify-content-center">

    <!-- Compact verification card -->
    <div class="text-center p-4" style="max-width: 320px;">

        <!-- Icon wrapper -->
        <div class="icon-wrapper mb-1" style="height: 160px; display: flex; align-items: center; justify-content: center;">
            <i class="fa-solid fa-envelope" style="font-size: 100px;"></i>
        </div>

        <!-- Description -->
        <p class="text-dark mb-2" style="font-size: 0.95rem; line-height:1.4;">
            Thanks for signing up! A verification link has been sent to:  
            <br>
            <strong>{{ auth()->user()->email }}</strong>
        </p>

        <!-- Reminder -->
        <p class="text-danger mb-3" style="font-size: 0.9rem; line-height:1.2;">
            You must verify your email before you can access all features of the website.
        </p>

        <!-- Success message -->
        @if (session('status') == 'verification-link-sent')
            <div class="alert alert-success py-2 mb-3" style="font-size: 0.9rem;">
                A new verification link has been sent to your email.
            </div>
        @endif

        <!-- Buttons -->
        <div class="d-flex flex-column gap-2">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn btn-primary w-100 rounded-pill py-2">
                    Resend Email Verification
                </button>
            </form>
        </div>

    </div>
</div>
@endsection
