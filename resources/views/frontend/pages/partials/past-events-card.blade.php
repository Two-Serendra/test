@foreach ($pastEvents as $event)
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <a href="{{ route('show.event.details', $event->id) }}" class="text-decoration-none text-dark">
            <div class="card h-100 border shadow-sm rounded-4 position-relative overflow-hidden bg-light">
                <div class="card-img-top" style="height: 250px; overflow: hidden;">
                    <img src="{{ asset('assets/images/events/' . $event->event_image) }}" alt="Event Image"
                        class="img-fluid w-100 h-100" style="object-fit: cover; object-position: top;">
                </div>

                <div class="card-body">
                    <h5 class="card-title text-uppercase fw-bold mb-2"
                        style="font-size: 1.1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ $event->event_title ?? 'No Title' }}
                    </h5>
                    <p class="text-muted small mb-0">
                        {{ \Carbon\Carbon::parse($event->event_date)->format('F j, Y') }}
                    </p>
                </div>
            </div>
        </a>
    </div>
@endforeach