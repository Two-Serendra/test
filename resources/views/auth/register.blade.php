<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Two Serendra</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Boxicons CDN -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- CSRF Token for Laravel -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        .text-justified {
            text-align: justify;
        }

        @media (max-width: 767.98px) {
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            table {
                min-width: 600px;
                /* Adjust this depending on how wide your table needs to be */
            }
        }
    </style>
</head>

<body class="bg-light">
    <div class="container py-5">
        <!-- Registration Form -->
        <div class="card shadow-sm mx-auto">
            <!-- Logo -->
            <div class="text-center my-3">
                <img src="{{ asset('assets/images/TWO SERENDRA LOGO PNG.png') }}" class="img-fluid"
                    style="max-width: 200px;" alt="2serendra" />
            </div>

            <!-- Session Status -->
            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif
            <div class="card-body">
                <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data"
                    class="needs-validation" novalidate>
                    @csrf
                    <!-- Basic Info -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}"
                                class="form-control @error('email') is-invalid @enderror" required
                                autocomplete="username">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="name" class="form-label">Name</label>
                            <input id="name" type="text" name="name" value="{{ old('name') }}"
                                class="form-control @error('name') is-invalid @enderror" required autocomplete="name">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="password" class="form-label">Password</label>
                            <input id="password" type="password" name="password"
                                class="form-control @error('password') is-invalid @enderror" required
                                autocomplete="new-password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input id="password_confirmation" type="password" name="password_confirmation"
                                class="form-control" required autocomplete="new-password">
                        </div>
                    </div>

                    <!-- Residences -->
                    <div class="mt-3">
                        <label class="form-label fw-semibold mb-0">
                            Residences
                            <i class="bx bx-info-circle text-primary ms-2" data-bs-toggle="tooltip"
                                data-bs-placement="right" title="Add only the unit that is under the registered email.">
                            </i>
                        </label>
                        <small class="text-muted d-md-none mb-2 d-block">Swipe left/right to view all columns</small>
                        <div class="table-responsive">
                            <div class="table-responsive" style="overflow-x: auto;">
                                <table class="table table-bordered align-middle text-center" id="residences-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 30%">Resident Type</th>
                                            <th style="width: 30%">Section</th>
                                            <th style="width: 30%">Unit No</th>
                                            <th style="width: 10%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="residences-container">
                                        <tr class="residence-row">
                                            <td>
                                                <select name="resident_type[]" required class="form-select">
                                                    <option value="" disabled selected>Select</option>
                                                    <option value="Owner">Owner</option>
                                                    <option value="Tenant">Tenant</option>
                                                </select>
                                                <div class="invalid-feedback">Required</div>
                                            </td>
                                            <td>
                                                <select name="section[]" required class="form-select">
                                                    <option value="" disabled selected>Select</option>
                                                    <option value="Almond">Almond</option>
                                                    <option value="Belize">Belize</option>
                                                    <option value="Callery">Callery</option>
                                                    <option value="Dolce">Dolce</option>
                                                    <option value="Aston">Aston</option>
                                                    <option value="Red Oak">Red Oak</option>
                                                    <option value="Meranti">Meranti</option>
                                                    <option value="Sequoia">Sequoia</option>
                                                </select>
                                                <div class="invalid-feedback">Required</div>
                                            </td>
                                            <td>
                                                <input type="text" name="unit_no[]" class="form-control" required>
                                                <div class="invalid-feedback">Required</div>
                                            </td>
                                            <td>
                                                <button type="button"
                                                    class="btn btn-danger btn-sm remove-residence d-none">Delete</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Add Row Button -->
                            <div class="d-flex justify-content-end">
                                <button type="button" id="add-residence" class="btn btn-primary btn-sm">+ Add
                                    row</button>
                            </div>
                        </div>


                        <!-- Data Privacy Agreement -->
                        <div class="mt-4">
                            <div class="card">
                                <div class="card-body" style="padding:20px; background-color: #f2f5f9;">
                                    <p class="m-0 text-justified">
                                        As part of our commitment to upholding your privacy, Two Serendra will only
                                        utilize
                                        the personal data you submit to manage your account and deliver the goods and
                                        services you have
                                        requested. We would want to stay in touch with you from time to time on our
                                        offerings, both goods
                                        and services, and other content that might catch your attention.
                                    </p>

                                    <div class="form-check mt-3">
                                        <input class="form-check-input" type="checkbox" id="dataprivacy" required>
                                        <label class="form-check-label text-justified" for="dataprivacy">
                                            I consent to receive more correspondence from Two Serendra.
                                        </label>
                                    </div>

                                    <p class="mt-3 mb-0 text-justified">
                                        By clicking submit below, you consent to allow Two Serendra to store and
                                        process the personal information submitted above to provide you the content
                                        requested.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="d-grid mt-4">
                            <button type="submit" id="register-btn" class="btn"
                                style="background-color: #008b26; color:#FFFFFF" disabled>
                                SUBMIT
                            </button>
                        </div>


                        <!-- Already Registered -->
                        <div class="text-center mt-3">
                            <a class="text-decoration-underline small text-muted" href="{{ route('login') }}">
                                Already registered?
                            </a>
                        </div>
                </form>
            </div>
        </div>
    </div>



    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>

        $('#dataprivacy').on('change', function () {
            $('#register-btn').prop('disabled', !$(this).is(':checked'));
        });

        // Bootstrap validation before form submission
        $('form.needs-validation').on('submit', function (e) {
            let form = this;
            let isValid = true;

            // Loop through all required fields and apply Bootstrap validation styles
            $(form).find(':input[required]').each(function () {
                if (!this.checkValidity()) {
                    $(this).addClass('is-invalid');
                    isValid = false;
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            // If any field is invalid, prevent form submission
            if (!isValid) {
                e.preventDefault();
                e.stopPropagation();
            }

            $(form).addClass('was-validated'); // optional: for Bootstrap 5 styling
        });

        // Live validation feedback on input
        $(document).on('input change', ':input[required]', function () {
            if (this.checkValidity()) {
                $(this).removeClass('is-invalid');
            }
        });
        $(function () {
            $('[data-bs-toggle="tooltip"]').tooltip();
        });
        const maxRows = 5;

        $('#add-residence').on('click', function () {
            const rowCount = $('#residences-container tr').length;

            if (rowCount >= maxRows) {
                alert("Maximum of 5 residence rows allowed.");
                return;
            }

            const newRow = `
                <tr class="residence-row">
                    <td>
                        <select name="resident_type[]" required class="form-select">
                            <option value="" disabled selected>Select</option>
                            <option value="Owner">Owner</option>
                            <option value="Tenant">Tenant</option>
                        </select>
                        <div class="invalid-feedback">Required</div>
                    </td>
                    <td>
                        <select name="section[]" required class="form-select">
                            <option value="" disabled selected>Select</option>
                            <option value="Almond">Almond</option>
                            <option value="Belize">Belize</option>
                            <option value="Callery">Callery</option>
                            <option value="Dolce">Dolce</option>
                            <option value="Aston">Aston</option>
                            <option value="Red Oak">Red Oak</option>
                            <option value="Meranti">Meranti</option>
                            <option value="Sequoia">Sequoia</option>
                        </select>
                        <div class="invalid-feedback">Required</div>
                    </td>
                    <td>
                        <input type="text" name="unit_no[]" class="form-control" required>
                        <div class="invalid-feedback">Required</div>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove-residence"><i class='bx bx-trash'></i></button>
                    </td>
                </tr>
            `;

            $('#residences-container').append(newRow);


            $('.residence-row').each(function (i) {
                $(this).find('.remove-residence').toggleClass('d-none', i === 0);
            });
        });

        $(document).on('click', '.remove-residence', function () {
            $(this).closest('tr').remove();
            $('.residence-row').each(function (i) {
                $(this).find('.remove-residence').toggleClass('d-none', i === 0);
            });
        });




        $('#dataprivacy').on('change', function () {
            if ($(this).is(':checked')) {
                $('#register-btn').prop('disabled', false);
            } else {
                $('#register-btn').prop('disabled', true);
            }
        });

    </script>
</body>

</html>