<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=\, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <h2>Hello {{ $name }},</h2>

<p>This is to confirm that you submitted a follow-up note for Ticket ID <strong>#{{ $ticketId }}</strong>.</p>

<p><strong>Your Note:</strong></p>
<p>{{ $note }}</p>

<p>Thank you,<br>Support Team</p>

</body>
</html>
