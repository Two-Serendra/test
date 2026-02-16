<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Booking Confirmed</title>
</head>

<body style="font-family: Arial, sans-serif; color: #333;">
    <div style="max-width: 600px; margin: auto; border: 1px solid #eee; padding: 30px; background-color: #f9f9f9;">
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="https://twoserendra.com/assets/images/twoserendraemaillogo.png" alt="Two Serendra Logo"
                style="max-width: 180px;">
        </div>

        <h2 style="color:#c0392b;">Your Pest Control Booking Has Been Cancelled</h2>
        <p>Hi {{ $name }},</p>
        <p>We regret to inform you that your booking has been cancelled.</p>
        <h4>Booking Details:</h4>

        <p><strong>Transaction No:</strong> {{ $transaction_no }}</p>
        <p><strong>Unit No:</strong> {{ $unit_no }}</p>
        <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($booking_date)->format('F d, Y') }}</p>
        <p><strong>Time:</strong> {{ $booking_time_slot }}</p>
        <br>

        <p style="margin-top: 30px;">Best regards,</p>
        <p><strong>Two Serendra Concierge Team</strong></p>
    </div>
</body>

</html>