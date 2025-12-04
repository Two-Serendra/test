@extends('layouts.frontend')
@section('content')

    <div class="container">
        <div class="container">
            <div class="d-flex justify-content-center align-items-center" style="height:50vh;">
                <div class="card shadow" style="width: 350px; border-radius: 10px;">
                    <div class="card-body">
                        <h4 class="text-center mb-4">Email Test </h4>

                        <form action="{{ route('send.email.test') }}" method="POST" id="sentTestEmail"
                            enctype="multipart/form-data" class="needs-validation" novalidate>
                            @csrf
                            <div class="mb-3">
                                <label for="emailInput" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="emailInput" name="email"
                                    placeholder="Enter your email" required>
                            </div>

                            <button id="submitBtn" form="sentTestEmail"  class="btn btn-primary w-100 rounded-pill">
                                Send
                            </button>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection