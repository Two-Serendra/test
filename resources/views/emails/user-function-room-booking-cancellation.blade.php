<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Booking Cancelled</title>
</head>

<body style="font-family: Arial, sans-serif; color: #333;">
    <div style="max-width: 600px; margin: auto; border: 1px solid #eee; padding: 30px; background-color: #f9f9f9;">
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="https://twoserendra.com/assets/images/twoserendraemaillogo.png" alt="Two Serendra Logo"
                style="max-width: 180px;">
        </div>

        <h2 style="color:#c0392b;">Your Function Room Booking Has Been Cancelled</h2>
        <p>Hi {{ $name }},</p>
        <p>We regret to inform you that your booking has been cancelled.</p>

        <h4>Booking Details:</h4>
        <ul>
            <li><strong>Transaction No:</strong> {{ $transaction_no }}</li>
            <li><strong>Function Room:</strong> {{ $function_room }}</li>
            <li><strong>Date:</strong> {{ \Carbon\Carbon::parse($date)->format('F j, Y') }}</li>
            <li><strong>Time:</strong> {{ \Carbon\Carbon::parse($start_time)->format('h:i A') }} - {{
                \Carbon\Carbon::parse($end_time)->format('h:i A') }}</li>
        </ul>

        @if(!empty($booking->penalty_fee) && $booking->penalty_fee > 0)
        <p style="color:#e74c3c;"><strong>Note:</strong> Since this cancellation was made within 24 hours of your event,
            a penalty fee of ₱{{ number_format($booking->penalty_fee, 2) }} has been applied.</p>
        @endif

        <p style="margin-top: 30px;">Best regards,</p>
        <p><strong>Two Serendra IT Team</strong></p>
    </div>
</body>

</html>