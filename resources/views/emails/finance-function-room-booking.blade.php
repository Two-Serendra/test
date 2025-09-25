<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>New Function Room Booking</title>
</head>

<body style="font-family: Arial, sans-serif; color: #333;">
    <div style="max-width: 600px; margin: auto; border: 1px solid #eee; padding: 30px; background-color: #f9f9f9;">
        <h2 style="color: #0056b3;">📢 New Function Room Booking Received</h2>

        <p>A new booking has been made by <strong>{{ $booking->user->name }}</strong>.</p>
        <p><strong>Transaction No:</strong>{{ $booking->transaction_no }}.</p>
        <p><strong>Unit No:</strong>{{ $booking->unit_no }}</p>
        <p><strong>Function Room:</strong> {{ $booking->functionRoom->function_room_name ?? 'N/A' }}</p>
        <p><strong>Event Purpose:</strong> {{ $booking->purpose_of_event }}</p>
        <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($booking->function_room_booking_date)->format('F d, Y') }}
        </p>
        <p><strong>Time:</strong> {{ \Carbon\Carbon::parse($booking->event_start_time)->format('h:i A') }} -
            {{ \Carbon\Carbon::parse($booking->event_end_time)->format('h:i A') }}
        </p>
        <p><strong>Pax:</strong> {{ $booking->pax }}</p>
        <p><strong>Contact Number:</strong> {{ $booking->contact_number }}</p>
        <p><strong>Payment Mode:</strong> {{ $booking->payment_mode }}</p>

        @if($booking->suppliers->count())
            <p><strong>Suppliers:</strong></p>
            <ul>
                @foreach($booking->suppliers as $supplier)
                    <li>{{ $supplier->name }} @if($supplier->attachment) (Attachment: {{ $supplier->attachment }}) @endif</li>
                @endforeach
            </ul>
        @endif

        <!-- @if($booking->authorization_file)
            <p><strong>Authorization File:</strong> {{ $booking->authorization_file }}</p>
        @endif -->

        <br>
        <p style="margin-top: 30px;">Please log in to the system to review and process this booking.</p>
    </div>
</body>

</html>