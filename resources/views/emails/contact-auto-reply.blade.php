<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Thank you for contacting Two Serendra</title>
</head>

<body style="font-family: Arial, sans-serif; color: #333;">
    <div style="max-width: 600px; margin: auto; border: 1px solid #eee; padding: 30px; background-color: #f9f9f9;">
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="{{ asset('https://test.twoserendra.com/assets/images/twoserendraemaillogo.png') }}" alt="Two Serendra Logo" style="max-width: 180px;">
        </div>

        <h2 style="color: #0056b3;">Hi {{ $name }},</h2>

        <p>Thank you for contacting <strong>Two Serendra</strong>. We've received your message and one of our team
            members will get back to you shortly.</p>

        <p><strong>Subject:</strong> {{ $subject }}</p>
        <p><strong>Your Message:</strong><br>{{ $inquiry }}</p>

        <br>
        <p style="margin-top: 30px;">Best regards,</p>
        <p><strong>Two Serendra Admin Team</strong></p>
        <hr>
        <p style="font-size: 12px; color: #999;">This is an automated message. Please do not reply to this email.</p>
    </div>
</body>

</html>