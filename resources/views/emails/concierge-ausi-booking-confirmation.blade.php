<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>New Ausi Booking</title>
</head>

<body style="font-family: Arial, sans-serif; background:#f4f6f8; padding:20px; color:#333;">
    <div style="max-width:700px; margin:auto; background:#ffffff; border-radius:6px; border:1px solid #e5e5e5;">

        <!-- Header -->
        <div style="padding:15px; text-align:center;">
            <img src="https://twoserendra.com/assets/images/twoserendraemaillogo.png" alt="Two Serendra"
                style="max-width:160px;">
        </div>

        <!-- Body -->
        <div style="padding:25px;">
            <h2>📢 New Ausi Booking Received</h2>

            <p>A new ausi booking has been submitted by a resident.</p>

            <table style="width:100%; border-collapse:collapse; margin-top:15px; font-size:14px;">
                <tr>
                    <td style="padding:8px;"><strong>Resident Name:</strong></td>
                    <td>{{ $name }}</td>
                </tr>
                <tr>
                    <td style="padding:8px;"><strong>Transaction No:</strong></td>
                    <td>{{ $transaction_no }}</td>
                </tr>
                <tr>
                    <td style="padding:8px;"><strong>Unit No:</strong></td>
                    <td>{{ $unit_no }}</td>
                </tr>
                <tr>
                    <td style="padding:8px;"><strong>Date:</strong></td>
                    <td>{{ \Carbon\Carbon::parse($booking_date)->format('F d, Y') }}</td>
                </tr>
                <tr>
                    <td style="padding:8px;"><strong>Time Slot:</strong></td>
                    <td>{{ $booking_time_slot }}</td>
                </tr>
            </table>

            <p style="margin-top:20px;">
                Please assign this request to the ausi service provider and update the booking status
                accordingly.
            </p>

            <p style="margin-top:25px;">
                Regards,<br>
                <strong>Two Serendra</strong>
            </p>
        </div>

        <!-- Footer -->
        <div style="background:#f1f1f1; padding:15px; text-align:center; font-size:12px; color:#777;">
            © {{ date('Y') }} Two Serendra. All rights reserved.
        </div>

    </div>
</body>

</html>