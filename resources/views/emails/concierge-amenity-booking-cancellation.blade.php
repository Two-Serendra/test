<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Amenity Booking Cancelled</title>
</head>

<body style="font-family: Arial, sans-serif; color: #333;">
    <div style="max-width: 600px; margin: auto; border: 1px solid #eee; padding: 30px; background-color: #f9f9f9;">
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="https://twoserendra.com/assets/images/twoserendraemaillogo.png" alt="Two Serendra Logo"
                style="max-width: 180px;">
        </div>

        <h2 style="color: #dc3545;">Amenity Booking Cancelled</h2>

        <p>
            A resident amenity booking has been <strong>cancelled</strong>.
        </p>

        <p><strong>Status:</strong>
            @if(!empty($withPenalty) && $withPenalty)
                CANCELLED (WITH PENALTY)
            @else
                CANCELLED
            @endif
        </p>
        @if(!empty($withPenalty) && $withPenalty)
            <p style="color:#dc3545; font-weight:bold;">
                ⚠ Cancellation made less than the required hours prior to booking.
                A penalty of ₱{{ number_format($penaltyAmount) }} will be applied.
            </p>
        @endif
        <p><strong>Resident Name:</strong> {{ $name }}</p>
        <p><strong>Unit No:</strong> {{ $unit_no }}</p>
        <p><strong>Transaction No:</strong> {{ $transaction_no }}</p>
        <p><strong>Booking Type:</strong> {{ $booking_type }}</p>
        <p><strong>Amenity:</strong> {{ $activity_name ?? 'N/A' }}</p>
        <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($booking_date)->format('F d, Y') }}</p>
        <p><strong>Time:</strong>
            {{ \Carbon\Carbon::parse($booking_start_time)->format('g:i A') }}
            -
            {{ \Carbon\Carbon::parse($booking_end_time)->format('g:i A') }}
        </p>

        <br>

        <p style="margin-top: 30px;">Regards,</p>
        <p><strong>Two Serendra</strong></p>
    </div>
</body>

</html>