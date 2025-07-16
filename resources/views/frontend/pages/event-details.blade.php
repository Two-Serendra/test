@extends('layouts.frontend')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">

                {{-- Event Title --}}
                <h2 class="text-uppercase fw-bold text-center mb-4" style="font-size: 2rem;">
                    {{ $event->event_title }}
                </h2>

                {{-- Description --}}
                <div class="mt-4" style="text-align: justify !important;">
                    {!! $event->event_details !!}
                </div>

                {{-- Image --}}
                <div style="
                                        margin-top: 10px;
                                        overflow: hidden;
                                        width: 100%;
                                        background-color: transparent;
                                        display: flex;
                                        justify-content: center;
                                        align-items: center;">
                    <img src="{{ asset('assets/images/events/' . $event->event_image) }}" alt="Event Image"
                        style="width: 100%; height: auto; display: block; object-fit: contain;">
                </div>



                {{-- Created Date --}}
                <p class="text-muted mt-3" style="font-size: 0.85rem;">
                    <strong>Created:</strong>
                    {{ \Carbon\Carbon::parse($event->created_at)->format('F d, Y h:i A') }}
                </p>

                {{-- Back Button --}}
                <div class="text-end mt-4">
                    <a href="{{ route('events') }}" class="btn btn-primary">
                        ← Back to Events
                    </a>
                </div>

            </div>
        </div>
    </div>





@endsection