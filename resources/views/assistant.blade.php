<!DOCTYPE html>
<html>
<head>
    <title>OpenAI Assistant</title>
</head>
<body>
    @php
    $decodedMessages = json_decode($messages, true);
    @endphp

    @if(is_array($decodedMessages))
        @foreach ($decodedMessages as $message)
            <p><strong>{{ $message['role'] }}:</strong> {{ $message['content'] }}</p>
        @endforeach
    @else
        <p>Error: Messages are not in the correct format.</p>
    @endif
</body>
</html>