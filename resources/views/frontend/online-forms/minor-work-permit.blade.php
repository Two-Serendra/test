@extends('layouts.frontend')
@section('content')
    <div class="container-fluid page-header pt-5 mb-6">
        <div class="container text-center pt-5">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="p-5 text-dark"
                        style="background-color: rgba(255, 255, 255, 0.8); border-radius: 10px 10px 0 0;">

                        <!-- Enhanced Title -->
                        <h1 class="display-6 text-uppercase mb-3 text-dark">ONLINE FORMS</h1>
                        <!-- Breadcrumb -->
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb justify-content-center mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('home') }}"
                                        style="color: #00c440 !important; font-weight: 600;">Home</a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page" style="color: #222; font-weight: 600;">
                                    Online Forms
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-center my-4">
        <div class="card shadow-lg p-4" style="max-width: 1000px; width: 100%;">
            <div class="card-body">
                <h2 class="text-primary fw-bold mb-3">2S Minor Work Permit Request</h2>

                <!-- Instructions -->
                <div class="mb-4 p-3 border rounded bg-light">
                    <h5 class="text-dark fw-bold">Instructions</h5>
                    <p>
                        Please complete this work permit request form for 3rd party service requests or for minor works
                        which take 3 days or less to complete.
                    </p>
                    <p>
                        After you submit this form, make sure to confirm the request by clicking on the link that will be
                        sent to your registered email.
                    </p>
                    <h5 class="text-danger fw-bold">Important</h5>
                    <p>
                        Only unit owners, tenants, and authorized representatives with registered emails can submit this
                        form.
                    </p>
                    <p class="mb-2 fw-semibold">Schedule your work permit only on working days and hours:</p>
                    <ul class="mb-3">
                        <li>Monday to Saturday: 8:00 AM to 6:00 PM</li>
                        <li>No working hours on Sundays or holidays.</li>
                    </ul>
                    <p>Workers will not be allowed entry outside of working hours.</p>
                    <p class="mb-2 fw-semibold">Noisy hours:</p>
                    <ul>
                        <li>Monday to Friday: 9:30 AM – 11:30 AM and 2:30 PM – 4:30 PM</li>
                        <li>No noisy hours during weekends or holidays.</li>
                    </ul>
                    <p class="mt-3"><strong>Engineering Department Contact:</strong> <a
                            href="mailto:engineering@twoserendraofficial.com">engineering@twoserendraofficial.com</a></p>
                </div>
                <form action="{{ route('submit.minor.work.permit') }}" id="workPermitForm" method="POST"
                    enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf
                    <div class="row">

                        <div class="mb-3 col-md-6 position-relative">
                            <label for="dateFieldWorkPermit" class="form-label">Date *</label>
                            <input type="date" id="dateFieldWorkPermit" name="work_permit_booking_date" class="form-control"
                                required>
                        </div>
                    </div>

                    <p class="mb-3 fw-semibold">
                        This form is for minor works that require a maximum of 1 day to complete.
                    </p>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="radio" name="work_type" id="radio1"
                                    value="Aircon cleaning" required>
                                <label class="form-check-label" for="radio1">Aircon cleaning</label>
                            </div>
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="radio" name="work_type" id="radio2"
                                    value="Aircon repair">
                                <label class="form-check-label" for="radio2">Aircon repair</label>
                            </div>
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="radio" name="work_type" id="radio3"
                                    value="Appliance installation (except split-type aircon)">
                                <label class="form-check-label" for="radio3">Appliance installation (except split-type
                                    aircon)</label>
                            </div>
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="radio" name="work_type" id="radio4"
                                    value="Appliance repair">
                                <label class="form-check-label" for="radio4">Appliance repair</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="radio" name="work_type" id="radio5"
                                    value="Grease trap cleaning">
                                <label class="form-check-label" for="radio5">Grease trap cleaning</label>
                            </div>
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="radio" name="work_type" id="radio6"
                                    value="Household cleaning">
                                <label class="form-check-label" for="radio6">Household cleaning</label>
                            </div>
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="radio" name="work_type" id="radio7"
                                    value="Telco installation or repair">
                                <label class="form-check-label" for="radio7">Telco installation or repair</label>
                            </div>
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="radio" name="work_type" id="radio8"
                                    value="Minor vehicle repair">
                                <label class="form-check-label" for="radio8">Minor vehicle repair</label>
                            </div>
                        </div>
                    </div>


                    <div class="mb-3">
                        <label for="contractor" class="form-label">* Name of Company/Contractor</label>
                        <textarea class="form-control" id="contractor" name="contractor" rows="3"
                            placeholder="Enter at least 3 characters" required></textarea>
                    </div>

                    <div class="text-end">
                        <button type="submit" form="workPermitForm" class="btn btn-primary" id="submitWorkPermitBtn"
                            style="border-radius: 10px;"><span class="spinner-border spinner-border-sm me-2 d-none"
                                role="status" aria-hidden="true"></span><span class="btn-text">SUBMIT</span></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection