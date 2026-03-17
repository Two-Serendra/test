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
            <h2 style="color:#dc3545;">Grease Trap Booking Cancelled</h2>
            
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

            <!-- Cancellation Penalty -->
            <!-- Cancellation Result -->
            @if($has_penalty)

                <div style="margin-top:15px; padding:12px; background:#ffecec; border-left:5px solid #dc3545;">
                    <strong>Cancellation Penalty Applied:</strong><br>
                    ₱{{ number_format($penalty_amount, 2) }} will be billed to your account.
                    <br><br>
                    This penalty was applied because the booking was cancelled within 24 hours
                    and the unit has already used its 2 free grease trap bookings for the year.
                </div>

            @elseif($booking->cancelled_within_24hrs)

                <div style="margin-top:15px; padding:12px; background:#fff3cd; border-left:5px solid #ffc107;">
                    <strong>Free Booking Forfeited</strong><br>
                    This cancellation was made within 24 hours of the scheduled booking.
                    One of your complimentary grease trap bookings for this year has been used.
                </div>

            @else

                <div style="margin-top:15px; padding:12px; background:#e6fffa; border-left:5px solid #28a745;">
                    <strong>No Cancellation Penalty</strong><br>
                    This booking was cancelled more than 24 hours before the scheduled time.
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