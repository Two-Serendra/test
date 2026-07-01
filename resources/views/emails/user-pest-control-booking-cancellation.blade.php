<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Pest Control Booking Cancelled</title>
</head>

<body style="font-family: Arial, sans-serif; color: #333;">
    <div style="max-width: 600px; margin: auto; border: 1px solid #eee; padding: 30px; background-color: #f9f9f9;">

        <!-- Logo -->
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="https://twoserendra.com/assets/images/twoserendraemaillogo.png" alt="Two Serendra Logo"
                style="max-width: 180px;">
        </div>

        <h2 style="color:#dc3545;">Hi {{ $name }},</h2>

        <p>Your Pest Control booking with <strong>Two Serendra</strong> has been <strong>cancelled</strong>.</p>

        <!-- Details -->
        <p><strong>Transaction No:</strong> {{ $transaction_no }}</p>
        <p><strong>Unit No:</strong> {{ $unit_no }}</p>
        <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($booking_date)->format('F d, Y') }}</p>
        <p><strong>Time:</strong> {{ $booking_time_slot }}</p>

        <!-- Cancellation Notice -->
        <!-- @if($has_penalty)
            <div style="margin-top:15px; padding:12px; background:#ffecec; border-left:5px solid #dc3545;">
                <strong>Cancellation Penalty Applied</strong><br>
                ₱{{ number_format($penalty_amount, 2) }} will be billed to your account.
                <br><br>
                This penalty was applied because the booking was cancelled within 24 hours.
            </div>

        @else
            <div style="margin-top:15px; padding:12px; background:#e6fffa; border-left:5px solid #28a745;">
                <strong>No Cancellation Penalty</strong><br>
                This booking was cancelled outside the 24-hour window.
            </div>
        @endif -->

        <!-- Closing -->
        <div style="margin-top:15px;">
            <p style="margin:0 0 10px 0;">
                For any further assistance, please contact the Concierge Team.
            </p>

            <p style="margin:0; line-height:1.5;">
                Regards,<br>
                <strong>Two Serendra</strong>
            </p>
        </div>

        <!-- Footer -->
        <hr style="border:none; border-top:1px solid #ddd; margin:25px 0 15px 0;">

        <div style="text-align:center; font-size:12px; color:#777;">
            © {{ date('Y') }} Two Serendra. All rights reserved.
        </div>

    </div>
</body>

</html>