<!DOCTYPE html>
<html>

<head>
    <title>New Callback Request</title>

</head>

<body style="background: linear-gradient(to right, #ebf8ff, #dbeafe); min-height: 100vh; display: flex; align-items: center; justify-content: center;">

    <div style="max-width: 28rem; margin: 0 auto; padding: 1.5rem; background-color: white; box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1); border-radius: 0.75rem; border: 1px solid #e5e7eb;">
        <h2 style="font-size: 1.875rem; font-weight: 800; color: #1d4ed8; margin-bottom: 1.5rem; border-bottom: 1px solid #e5e7eb; padding-bottom: 1rem;">
            <i class="fas fa-phone-alt" style="color: #3b82f6; margin-right: 0.5rem;"></i> New Callback Request
        </h2>
        <p style="color: #4b5563; margin-bottom: 1.5rem;">You have a new callback request from
            <span style="color: #1e40af; font-weight: 500;">{{ $details['senderName'] }}</span>
            (ID: <span style="color: #1e40af; font-weight: 500;">{{ $details['senderId'] }}</span>):
        </p>

        <ul style="list-style: none; margin-bottom: 1.5rem; padding: 0; margin-left: 0;">
            <li style="display: flex; align-items: center; margin-bottom: 0.5rem;">
                <strong style="width: 8rem; color: #6b7280;">Salutation:</strong>
                <span style="color: #1f2937; font-weight: 600;">{{ $details['salutation'] }}</span>
            </li>
            <li style="display: flex; align-items: center; margin-bottom: 0.5rem;">
                <strong style="width: 8rem; color: #6b7280;">First Name:</strong>
                <span style="color: #1f2937; font-weight: 600;">{{ $details['firstName'] }}</span>
            </li>
            <li style="display: flex; align-items: center; margin-bottom: 0.5rem;">
                <strong style="width: 8rem; color: #6b7280;">Last Name:</strong>
                <span style="color: #1f2937; font-weight: 600;">{{ $details['lastName'] }}</span>
            </li>
            <li style="display: flex; align-items: center; margin-bottom: 0.5rem;">
                <strong style="width: 8rem; color: #6b7280;">Phone Number:</strong>
                <span style="color: #1f2937; font-weight: 600;">{{ $details['phoneNumber'] }}</span>
            </li>
            <li style="display: flex; align-items: center; margin-bottom: 0.5rem;">
                <strong style="width: 8rem; color: #6b7280;">Notes:</strong>
                <span style="color: #1f2937; font-weight: 600;">{{ $details['notes'] }}</span>
            </li>
        </ul>

        <p style="color: #6b7280; font-size: 0.875rem; margin-top: 1rem; text-align: center;">Thank you for using our service!</p>

        </div>

    </div>

    <!-- Font Awesome for icons -->

</body>



</html>
