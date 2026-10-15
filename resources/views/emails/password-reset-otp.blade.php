<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="background-color: #ffffff; border-radius: 8px; padding: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h2 style="color: #2c3e50; margin-top: 0;">Password Reset OTP Code</h2>
            <a href="#">
                <img style="display:block; line-height:0px; font-size:0px; border:0px;" src="{{ asset('storage/'.$appSettings['logo']) }}" width="220" alt="img">
            </a>
            <p>Hello,</p>

            <p>You have requested to reset your password. Your verification code is:</p>

            <div style="text-align: center; margin: 30px 0; padding: 25px; background-color: #f8f9fa; border: 2px dashed #dee2e6; border-radius: 8px;">
                <div style="font-size: 36px; font-weight: bold; letter-spacing: 10px; color: #007bff; font-family: 'Courier New', monospace; padding: 10px;">
                    {{ $otp }}
                </div>
            </div>

            <p style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 12px; margin: 20px 0; border-radius: 4px;">
                <strong>Important:</strong> This code will expire in <strong>10 minutes</strong>.
            </p>

            <p>Please enter this code in the Auscultation World app to verify your identity and reset your password.</p>

            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6;">
                <p style="color: #6c757d; font-size: 14px; margin-bottom: 5px;">
                    If you did not request this code, please ignore this email.
                </p>
                <p style="color: #6c757d; font-size: 14px; margin-top: 10px;">
                    Best regards,<br>
                    <strong>MediRx Team</strong>
                </p>
            </div>
        </div>

        <div style="text-align: center; margin-top: 20px; color: #6c757d; font-size: 12px;">
            <p>This is an automated message. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>

