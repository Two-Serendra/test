<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Grease Trap Booking Confirmation</title>
</head>

<body style="font-family: Arial, sans-serif; background-color:#f4f6f8; padding:20px; color:#333;">
    <div
        style="max-width: 650px; margin:auto; background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 0 10px rgba(0,0,0,0.05);">

        <!-- Header -->
        <div style="padding:20px; text-align:center;">
            <img src="https://twoserendra.com/assets/images/twoserendraemaillogo.png" alt="Two Serendra"
                style="max-width:160px;">
        </div>

        <!-- Body -->
        <div style="padding:30px;">
            <h2>Hello {{ $name }},</h2>

            <p>Your grease trap booking has been <strong>successfully confirmed</strong>. Below are your booking
                details:</p>

            <table style="width:100%; border-collapse:collapse; margin-top:15px;">
                <tr>
                    <td style="padding:8px 0;"><strong>Transaction No:</strong></td>
                    <td>{{ $transaction_no }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0;"><strong>Unit No:</strong></td>
                    <td>{{ $unit_no }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0;"><strong>Date:</strong></td>
                    <td>{{ \Carbon\Carbon::parse($booking_date)->format('F d, Y') }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0;"><strong>Time:</strong></td>
                    <td>{{ $booking_time_slot }}</td>
                </tr>
            </table>

            {{-- Payment Notice --}}
            @if($charged_type == 2)
                <div
                    style="margin-top:20px; padding:15px; background:#fff3cd; border-left:5px solid #ffc107; border-radius:4px;">
                    <strong>Payment Notice:</strong><br>
                    You have already used your free yearly grease trap service.
                    This booking will be charged <strong>₱{{ number_format($fee, 2) }}</strong> and will be billed
                    accordingly.
                </div>
            @else
                <div
                    style="margin-top:20px; padding:15px; background:#e6fffa; border-left:5px solid #28a745; border-radius:4px;">
                    <strong>This booking is FREE.</strong> You used your complimentary yearly grease trap service.
                </div>
            @endif

            <p style="margin-top:25px;">For any further assistance, please contact the Concierge Team.</p>

            <p style="margin-top:30px;">
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