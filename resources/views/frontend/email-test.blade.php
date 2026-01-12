@extends('layouts.frontend')
@section('content')

    <style>
        #submitBtn {
            min-width: 100px;
            /* adjust to your desired width */
        }
    </style>
    <div class="container my-5">

        <!-- Email Test Card -->
        <div class="d-flex justify-content-center mb-4">
            <div class="card shadow-lg" style="max-width: 500px; width: 100%; border-radius: 12px;">
                <div class="card-body p-4">
                    <h4 class="text-center mb-4 fw-bold">Email Test</h4>

                    <!-- Inline Email Form -->
                    <form action="{{ route('send.email.test') }}" method="POST" id="sentTestEmail"
                        enctype="multipart/form-data" class="needs-validation" novalidate>
                        @csrf
                        <div class="input-group mb-3">
                            <input type="email" class="form-control form-control-lg" id="emailInput" name="email"
                                placeholder="Enter your email" required>
                            <button id="submitBtn" type="submit"
                                class="btn btn-primary rounded-end d-flex align-items-center justify-content-center">
                                <span class="spinner-border spinner-border-sm d-none" role="status"
                                    aria-hidden="true"></span>
                                <span class="btn-text ms-1">Send</span>
                            </button>

                            <div class="invalid-feedback">
                                Please enter a valid email address.
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Email Tips Container -->
        <div class="d-flex justify-content-center">
            <div class="card shadow-sm" style="max-width: 500px; width: 100%; border-radius: 12px;">
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-2">Having trouble receiving the email?</h6>
                    <div class="collapse show" id="emailTips">
                        <ul class="list-group list-group-flush medium">
                            <li class="list-group-item">✅ <strong>Check Spam Folder:</strong> Look in your Spam or Junk
                                folder. If you find it, mark it as Not spam.</li>
                            <!-- <li class="list-group-item">✅ <strong>Allow-list Our Sender:</strong> Add
                                <strong>hello@noreply.serendra.email.venyu.ph</strong> to your contacts or safe senders
                                list.
                            </li> -->
                            <li class="list-group-item">✅ <strong>Search Everywhere:</strong> Search your inbox for
                                "Two Serendra Test Email" and check All Mail, Archive, or Other tabs.</li>
                            <li class="list-group-item">✅ <strong>Corporate Email Filters:</strong> Work emails may block
                                unknown senders. Ask IT to check quarantine and allow our sender/domain.</li>
                            <li class="list-group-item">✅ <strong>Try a Different Email:</strong> Test with a personal email
                                (Gmail, Yahoo, etc.) to confirm if it's your corporate filter.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection