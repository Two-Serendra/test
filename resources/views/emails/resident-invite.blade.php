<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Registration Token</title>
</head>

<body style="font-family: Arial, sans-serif; color: #333;">
    <div style="max-width: 600px; margin: auto; border: 1px solid #eee; padding: 30px; background-color: #f9f9f9;">
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="https://twoserendra.com/assets/images/twoserendraemaillogo.png" alt="Two Serendra Logo"
                style="max-width: 180px;">
        </div>

        <p>Welcome!</p>

        <p>Your registration token is:</p>

        <h2>{{ $token }}</h2>

        <p>Use this together with your email to register.</p>
        <p style="color: #d9534f; font-weight: bold;">
            This token will expire in 10 minutes.
        </p>
        <p style="margin-top: 30px;">Best regards,</p>
        <p><strong>Two Serendra</strong></p>
    </div>
</body>

</html>