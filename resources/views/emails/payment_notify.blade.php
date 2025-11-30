<!DOCTYPE html>
<html>
<head>
    <title>Payment Notification</title>
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
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #eeeeee;
        }
        .header h1 {
            color: #333333;
        }
        .content {
            padding: 20px 0;
        }
        .content p {
            color: #555555;
            line-height: 1.6;
        }
        .details {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
        }
        .details p {
            margin: 5px 0;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #eeeeee;
            color: #999999;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Payment Notification</h1>
        </div>
        <div class="content">
            <p>Hello,</p>
            <p>We have received a new transaction on your account. Below are the details:</p>
            
            <div class="details">
                <p>Transaction Type: {{ $mail_data['type'] }}</p>
                <p>Amount: ₦{{ $mail_data['amount'] }}</p>
                <p>Reference No: {{ $mail_data['ref'] }}</p>
                <p>Bank/Gateway: {{ $mail_data['bankName'] }}</p>
            </div>

            <p>Thank you for using our service.</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Arewa Smart. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
