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

        <h2 style="color: #0056b3;">Hi {{ $name }},</h2>

        <p>Good news! Your booking with <strong>Two Serendra</strong> has been <strong>confirmed</strong>.</p>

        <p><strong>Transaction No:</strong> {{ $transaction_no }}</p>
        <p><strong>Function Room:</strong> {{ $function_room }}</p>
        <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}</p>
        <p><strong>Time:</strong> {{ \Carbon\Carbon::parse($start_time)->format('h:i A') }} -
            {{ \Carbon\Carbon::parse($end_time)->format('h:i A') }}
        </p>

        <br>

        <p style="margin-top: 30px;">Best regards,</p>
        <p><strong>Two Serendra Admin Team</strong></p>
    </div>
</body>

</html>