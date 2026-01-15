@extends('layouts.frontend')
@section('content')

    <div class="container my-4">
        <div class="bg-white shadow-sm rounded p-3 mb-4">
            <form id="requestSoaForm" class="needs-validation" novalidate action="{{ route('generate.soa') }}"
                method="POST">
                @csrf

                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Select Residence <span class="required">*</span></label>
                        <select name="resident_id" class="form-select" required>
                            <option value="">-- Select Residence --</option>
                            @foreach ($residences as $residence)
                                <option value="{{ $residence->id }}">
                                    {{ ucfirst($residence->resident_type) }} - Unit {{ $residence->unit_no }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Year <span class="required">*</span></label>
                        <select name="year" class="form-select" required>
                            <option value="">-- Select Year --</option>
                            @for ($y = now()->year; $y >= now()->year - 10; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Month <span class="required">*</span></label>
                        <select name="month" class="form-select" required>
                            <option value="">-- Select Month --</option>
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}">
                                    {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Billing Type <span class="required">*</span></label>
                        <select name="billing_type" class="form-select" required>
                            <option value="">-- Select --</option>
                            <option value="Electricity">ELECTRICITY</option>
                            <option value="Soa">SOA</option>

                        </select>
                    </div>

                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-primary rounded-pill">
                            Generate
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Loading Spinner -->
        <div id="soaLoading" class="text-center my-4" style="display:none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Generating may take 30-60 seconds.</p>
        </div>


        <!-- Container for iframe -->
        <div id="soaContainer" class="bg-white shadow-sm rounded p-3 mt-4" style="display:none; min-height: 760px;">

            <h5 class="mb-3">Statement of Account</h5>

            <iframe id="soaFrame" width="100%" height="1000" style="border: none; background: #f8f9fa;">
            </iframe>
        </div>

        <div id="soaMobileLink" class="text-center mt-4" style="display:none;">
            <a id="soaOpenBtn" href="#" target="_blank" class="btn btn-primary btn-lg">
                Open Statement of Account
            </a>
        </div>

    </div>


@endsection