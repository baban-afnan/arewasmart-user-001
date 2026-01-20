<!DOCTYPE html>
<html>
<head>
    <title>JAMB PIN Purchase Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background-color: #c2910aff;
            color: #ffffff;
            padding: 25px 20px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 22px;
            font-weight: 600;
        }
        .header .icon {
            font-size: 40px;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px 25px;
        }
        .content p {
            color: #555555;
            line-height: 1.6;
            margin: 10px 0;
        }
        .greeting {
            margin-bottom: 20px;
        }
        .pin-box {
            background-color: #ec6c02ff;
            color: #ffffff;
            padding: 25px 20px;
            border-radius: 8px;
            text-align: center;
            margin: 25px 0;
        }
        .pin-label {
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 12px;
            color: #ffffff;
            display: block;
        }
        .pin-value {
            background-color: #ffffff;
            color: #2d3748;
            font-size: 28px;
            font-weight: 700;
            padding: 18px 20px;
            border-radius: 6px;
            letter-spacing: 4px;
            font-family: 'Courier New', monospace;
            word-break: break-all;
            user-select: all;
            -webkit-user-select: all;
            -moz-user-select: all;
            -ms-user-select: all;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .details {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .details p {
            margin: 8px 0;
            font-weight: bold;
            color: #555555;
            font-size: 14px;
        }
        .details p span {
            font-weight: normal;
            color: #333333;
        }
        .important-note {
            background-color: #fffbeb;
            border-left: 4px solid #fbbf24;
            padding: 18px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .important-note p {
            margin: 0;
            color: #78350f;
            font-size: 14px;
        }
        .success-badge {
            background-color: #d1fae5;
            color: #065f46;
            padding: 12px 18px;
            border-radius: 6px;
            margin: 15px 0;
            font-size: 14px;
            font-weight: 600;
            display: inline-block;
        }
        .blessing {
            background-color: #f0f7ff;
            border-left: 4px solid #c2910aff;
            padding: 18px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .blessing p {
            margin: 0;
            color: #1e40af;
            font-size: 14px;
            font-style: italic;
        }
        .footer {
            text-align: center;
            padding: 25px 20px;
            border-top: 1px solid #eeeeee;
            color: #999999;
            font-size: 12px;
            background-color: #fafafa;
        }
        .footer p {
            margin: 5px 0;
        }
        @media only screen and (max-width: 600px) {
            .content {
                padding: 25px 20px;
            }
            .pin-value {
                font-size: 22px;
                letter-spacing: 3px;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">✓</div>
            <h1>JAMB PIN Purchase Successful</h1>
        </div>
        
        <div class="content">
            <!-- Success Message -->
            <div style="text-align: center; margin: 25px 0;">
                <span class="success-badge">✅ PIN Successfully Generated and Ready for use</span>
            </div>
            
            <!-- PIN Display Box -->
            <div class="pin-box">
                <span class="pin-label">Your JAMB PIN</span>
                <div class="pin-value">{{ $pin }}</div>
            </div>

            <!-- Purchase Details -->
            <div class="details">
                <p><strong>Service Type:</strong> <span>{{ $serviceType }}</span></p>
                <p><strong>Profile ID:</strong> <span>{{ $profileId }}</span></p>
                <p><strong>Amount Paid:</strong> <span>₦{{ number_format($amount, 2) }}</span></p>
                <p><strong>Transaction Reference:</strong> <span>{{ $reference }}</span></p>
                <p><strong>Transaction Date:</strong> <span>{{ $transactionDate }}</span></p>
            </div>

            <!-- Important Note -->
            <div class="important-note">
                <p><strong>⚠️ Important:</strong> Please keep this PIN safe and secure. You will need it to complete your JAMB registration process.</p>
            </div>

            <!-- Success Message -->
            <div style="text-align: center; margin: 25px 0;">
                <span class="success-badge">✅ PIN Successfully Generated</span>
            </div>

            <!-- Blessing -->
            <div class="blessing">
                <p>🙏 We wish you success in your JAMB examination. May God bless you and your family abundantly.</p>
            </div>

            <p style="margin-top: 25px; color: #555555;">Thank you for using our service.</p>
            <p style="color: #555555;">For any further assistance, please contact our support team.</p>
        </div>
        
        <div class="footer">
            <p><strong>Arewa Smart</strong></p>
            <p>&copy; {{ date('Y') }} Arewa Smart. All rights reserved.</p>
            <p style="margin-top: 10px; font-size: 11px;">This is an automated message, please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>