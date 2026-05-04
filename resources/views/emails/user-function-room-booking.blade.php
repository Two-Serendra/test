<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Your Function Room Booking</title>
</head>

<body style="font-family: Arial, sans-serif; color: #333;">
    <div style="max-width: 600px; margin: auto; border: 1px solid #eee; padding: 30px; background-color: #f9f9f9;">
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="https://twoserendra.com/assets/images/twoserendraemaillogo.png" alt="Two Serendra Logo"
                style="max-width: 180px;">
        </div>

        <h2 style="color: #0056b3;">Hi {{ $name }},</h2>

        <p>Thank you for booking with <strong>Two Serendra</strong>. We have received your request, and it is now being
            processed.</p>

        <p><strong>Transaction No:</strong> {{ $transaction_no }}</p>
        <p><strong>Function Room(s):</strong> {{ $rooms }}</p>
        <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}</p>
        <p><strong>Time:</strong> {{ \Carbon\Carbon::parse($start_time)->format('h:i A') }} - {{
    \Carbon\Carbon::parse($end_time)->format('h:i A') }}</p>

        <br>
        <p style="margin-top: 30px;">We will review your booking and confirm with you shortly.</p>

        <p style="margin-top:30px;">
            Regards,<br>
            <strong>Two Serendra</strong>
        </p>

        <!-- Footer -->
        <div style="background:#f1f1f1; padding:15px; text-align:center; font-size:12px; color:#777;">
            © {{ date('Y') }} Two Serendra. All rights reserved.
        </div>
    </div>
</body>

</html>