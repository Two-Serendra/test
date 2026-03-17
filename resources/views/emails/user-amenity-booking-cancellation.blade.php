<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Amenity Booking Cancelled</title>
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

            <h2 style="color:#dc3545;">Amenity Booking Cancelled</h2>

            <p>The following amenity booking has been <strong>cancelled</strong>.</p>

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
                    <td style="padding:8px;"><strong>Booking Type:</strong></td>
                    <td>{{ $booking_type }}</td>
                </tr>

                <tr>
                    <td style="padding:8px;"><strong>Amenity:</strong></td>
                    <td>{{ $activity_name ?? 'N/A' }}</td>
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
                    <td style="padding:8px;"><strong>Time:</strong></td>
                    <td>
                        {{ \Carbon\Carbon::parse($booking_start_time)->format('g:i A') }}
                        -
                        {{ \Carbon\Carbon::parse($booking_end_time)->format('g:i A') }}
                    </td>
                </tr>
            </table>


            <!-- Cancellation Result -->
            @if(!empty($withPenalty) && $withPenalty)

                <div style="margin-top:15px; padding:12px; background:#ffecec; border-left:5px solid #dc3545;">
                    <strong>Cancellation Penalty Applied</strong><br>
                    ₱{{ number_format($penaltyAmount, 2) }} will be billed to your account.
                    <br><br>
                    This penalty was applied because the booking was cancelled within 12 hours
                </div>

            @else

                <div style="margin-top:15px; padding:12px; background:#e6fffa; border-left:5px solid #28a745;">
                    <strong>No Cancellation Penalty</strong><br>
                    This booking was cancelled more than 12 hours before the scheduled time.
                    No penalty has been applied.
                </div>

            @endif


            <p style="margin-top:20px;">
                  For further assistance, please contact the concierge team.
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