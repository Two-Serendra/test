@extends('layouts.frontend')
@section('content')

    <style>
        #submitBtn {
            min-width: 100px;
            /* adjust to your desired width */
        }
    </style>
    <div class="container my-5">
        <div class="d-flex justify-content-center">
            <div class="card shadow-lg w-100" style="max-width: 900px; border-radius: 12px;">
                <div class="card-body p-4">
                    <h4 class="text-center mb-4 fw-bold">Email Test</h4>

                    <div class="row g-4">
                        <!-- LEFT COLUMN: EMAIL FORM -->
                        <div class="col-md-6 d-flex flex-column justify-content-center">
                            <form action="{{ route('send.email.test') }}" method="POST" id="sentTestEmail"
                                class="needs-validation" novalidate>
                                @csrf

                                <div class="mb-3">
                                    <label for="fromEmail" class="form-label fw-semibold">
                                        Send test email from
                                    </label>
                                    <select class="form-select form-select-lg" id="fromEmail" name="from_email" required>
                                        <option value="" selected disabled>Choose sender</option>
                                        <option value="circulars">
                                            circulars@twoserendra.com
                                        </option>
                                        <option value="finance">
                                            finance@twoserendra.com
                                        </option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Please select a sender email.
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <input type="email" class="form-control form-control-lg" id="emailInput" name="email"
                                        placeholder="Enter your email" required>
                                    <div class="invalid-feedback">
                                        Please enter a valid email address.
                                    </div>
                                </div>

                                <button id="submitBtnTestMail" type="submit" 
                                    class="btn btn-primary w-100 d-flex align-items-center justify-content-center"  style="min-width: 100px; height: 38px;">
                                    <span>Send</span> 
                                </button>

                            </form>
                        </div>

                        <!-- RIGHT COLUMN: EMAIL TIPS -->
                        <div class="col-md-6">
                            <h6 class="fw-semibold mb-3">
                                📩 Having trouble receiving the email?
                            </h6>

                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    ✅ <strong>Check Spam Folder:</strong> Look in Spam or Junk.
                                </li>
                                <li class="list-group-item">
                                    ✅ <strong>Search Everywhere:</strong> Search for "Two Serendra Test Email".
                                </li>
                                <li class="list-group-item">
                                    ✅ <strong>Corporate Email Filters:</strong> Ask IT to check quarantine.
                                </li>
                                <li class="list-group-item">
                                    ✅ <strong>Try a Different Email:</strong> Gmail/Yahoo to rule out filters.
                                </li>
                            </ul>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection