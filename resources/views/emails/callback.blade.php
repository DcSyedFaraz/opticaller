<!DOCTYPE html>
<html>

<head>
    <title>New Callback Request</title>
</head>

<body>

    <p>You have a new callback request from {{ $details['senderName'] }} (ID: {{ $details['senderId'] }}):</p>

    <ul>
        <li><strong>Salutation:</strong> {{ $details['salutation'] }}</li>
        <li><strong>First Name:</strong> {{ $details['firstName'] }}</li>
        <li><strong>Last Name:</strong> {{ $details['lastName'] }}</li>
        <li><strong>Phone Number:</strong> {{ $details['phoneNumber'] }}</li>
        <li><strong>Notes:</strong> {{ $details['notes'] }}</li>
    </ul>

    <p>Thank you!</p>
</body>

</html>
