<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Grease Trap Booking Cancelled</title>
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
            <h2 style="color:#dc3545;">Pest Control Booking Cancelled</h2>

            <p>A resident pest control booking has been <strong>cancelled</strong>. Please review the details below:</p>

            <table style="width:100%; border-collapse:collapse; margin-top:15px; font-size:14px;">
                <tr>
                    <td style="padding:8px; font-weight:bold;">Resident Name:</td>
                    <td>{{ $name }}</td>
                </tr>
                <tr>
                    <td style="padding:8px; font-weight:bold;">Transaction No:</td>
                    <td>{{ $transaction_no }}</td>
                </tr>
                <tr>
                    <td style="padding:8px; font-weight:bold;">Unit No:</td>
                    <td>{{ $unit_no }}</td>
                </tr>
                <tr>
                    <td style="padding:8px; font-weight:bold;">Date:</td>
                    <td>{{ \Carbon\Carbon::parse($booking_date)->format('F d, Y') }}</td>
                </tr>
                <tr>
                    <td style="padding:8px; font-weight:bold;">Time Slot:</td>
                    <td>{{ $booking_time_slot }}</td>
                </tr>
            </table>

            <!-- Cancellation Penalty -->
            <!-- @if($has_penalty)
                <div style="margin-top:15px; padding:12px; background:#ffecec; border-left:5px solid #dc3545;">
                    <strong>Cancellation Penalty Applied:</strong> ₱{{ number_format($penalty_amount, 2) }}
                    <br>This penalty is applied because the booking was cancelled within 24 hours.
                </div>
            @else
                <div style="margin-top:15px; padding:12px; background:#e6fffa; border-left:5px solid #28a745;">
                    <strong>No Cancellation Penalty</strong>
                    <br>This booking was cancelled outside the 24-hour window.
                </div>
            @endif -->

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