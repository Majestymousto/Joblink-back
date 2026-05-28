<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification de votre compte</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .wrapper {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .header {
            background-color: #0f4f2e;
            padding: 32px 40px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            font-size: 24px;
            margin: 0;
            letter-spacing: 1px;
        }
        .body {
            padding: 40px;
            color: #333333;
        }
        .body p {
            font-size: 15px;
            line-height: 1.6;
            margin: 0 0 16px;
        }
        .otp-box {
            background-color: #f0f4ff;
            border: 2px dashed #0f4f2e;
            border-radius: 8px;
            text-align: center;
            padding: 24px;
            margin: 28px 0;
        }
        .otp-code {
            font-size: 42px;
            font-weight: bold;
            letter-spacing: 10px;
            color: #0f4f2e;
        }
        .notice {
            font-size: 13px;
            color: #888888;
            margin-top: 24px;
        }
        .footer {
            background-color: #f8f8f8;
            padding: 24px 40px;
            text-align: center;
            border-top: 1px solid #eeeeee;
        }
        .footer p {
            font-size: 13px;
            color: #999999;
            margin: 0;
        }
        .footer strong {
            color: #0f4f2e;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>JobLink Niger</h1>
        </div>
        <div class="body">
            <p>Bonjour <strong>{{ $name }}</strong>,</p>
            <p>Votre code de vérification est :</p>

            <div class="otp-box">
                <div class="otp-code">{{ $otpCode }}</div>
            </div>

            <p>Ce code est valable pendant <strong>10 minutes</strong>.</p>
            <p class="notice">Si vous n'avez pas créé de compte sur JobLink, ignorez cet email.</p>
        </div>
        <div class="footer">
            <p>Cordialement,<br><strong>L'équipe JobLink Niger</strong></p>
        </div>
    </div>
</body>
</html>
