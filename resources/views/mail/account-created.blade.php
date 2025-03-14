<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Created</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            width: 80%;
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h1 {
            font-size: 2.5rem;
            color: #1a73e8;
            margin-bottom: 1.5rem;
        }

        p {
            font-size: 1.1rem;
            line-height: 1.6;
            color: #555;
            margin-bottom: 1.5rem;
        }

        ul {
            list-style: none;
            padding: 0;
            margin: 0;
            text-align: left;
            margin-bottom: 2rem;
        }

        li {
            font-size: 1rem;
            margin-bottom: 0.8rem;
        }

        li strong {
            color: #1a73e8;
        }

        a {
            color: #1a73e8;
            text-decoration: none;
            font-weight: bold;
        }

        .footer {
            font-size: 1rem;
            color: #999;
        }

        .btn {
            background-color: #1a73e8;
            color: white;
            padding: 10px 20px;
            font-size: 1.1rem;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            margin-top: 1.5rem;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #155ab5;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Welcome to Opticaller!</h1>

        <p>Hi {{ $user->name }},</p>

        <p>We're excited to let you know that your account has been successfully created. Below are your account
            details:</p>

        <ul>
            <li><strong>Email:</strong> {{ $user->email }}</li>
            <li><strong>One-Time Password (OTP):</strong> {{ $otp }}</li>
            <li><strong>Opticaller Portal:</strong> <a href="https://opticaller.vim-solution.com/">Click here to log
                    in</a></li>
        </ul>

        <p>To access your account, simply use the provided credentials. Should you have any questions or require
            assistance, feel free to reach out to us.</p>

        <a href="https://opticaller.vim-solution.com/" class="btn">Visit Opticaller</a>

        <p class="footer">Thank you for being a part of the Opticaller community. We look forward to working with you!
        </p>

        <p class="footer">Best Regards,<br> The Opticaller Team</p>
    </div>
</body>

</html>
