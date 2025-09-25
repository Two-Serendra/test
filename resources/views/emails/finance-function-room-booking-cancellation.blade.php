<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Function Room Booking Cancelled</title>
</head>

<body style="font-family: Arial, sans-serif; background:#f9f9f9; padding:20px;">
    <div style="max-width:600px; margin:0 auto; background:white; padding:20px; border-radius:8px;">
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="https://twoserendra.com/assets/images/twoserendraemaillogo.png" alt="Two Serendra Logo"
                style="max-width: 180px;">
        </div>

        <h2 style="color:#c0392b;">Function Room Booking Cancelled</h2>

        <h4>Booking Details:</h4>
        <ul>
            <li><strong>Transaction No:</strong> {{ $booking->transaction_no }}</li>
            <li><strong>Unit No:</strong> {{ $booking->unit_no }}</li>
            <li><strong>Resident:</strong> {{ $booking->user->name }}</li>
            <li><strong>Email:</strong> {{ $booking->user->email }}</li>
            <li><strong>Function Room:</strong> {{ $booking->functionRoom->function_room_name ?? 'Function Room' }}</li>
            <li><strong>Date:</strong> {{ \Carbon\Carbon::parse($booking->function_room_booking_date)->format('F j, Y')
                }}</li>
            <li><strong>Time:</strong> {{ \Carbon\Carbon::parse($booking->event_start_time)->format('h:i A') }} - {{
                \Carbon\Carbon::parse($booking->event_end_time)->format('h:i A') }}</li>
            <li><strong>Status:</strong> Cancelled</li>
        </ul>

        @if(!empty($booking->penalty_fee) && $booking->penalty_fee > 0)
        <p style="color:#e74c3c;"><strong>Penalty Applied:</strong> ₱{{ number_format($booking->penalty_fee, 2) }}</p>
        @endif

        <p>Please log in to the system to review and process this booking.</p>
    </div>
</body>

</html>