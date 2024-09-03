<!DOCTYPE html>
<html>

<head>
    <title>New Callback Request</title>

</head>

<body>

    <div>
        <h2>New Callback Request</h2>
        <p>You have a new callback request from <strong>{{ $details['senderName'] }}</strong> (ID:
            <strong>{{ $details['senderId'] }}</strong>):</p>

        <ul>
            <li><strong>Salutation:</strong> {{ $details['salutation'] }}</li>
            <li><strong>First Name:</strong> {{ $details['firstName'] }}</li>
            <li><strong>Last Name:</strong> {{ $details['lastName'] }}</li>
            <li><strong>Phone Number:</strong> {{ $details['phoneNumber'] }}</li>
            <li><strong>Notes:</strong> {{ $details['notes'] }}</li>
        </ul>

        <p>Thank you for using our service!</p>
    </div>

</body>




</html>
