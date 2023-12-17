<!DOCTYPE html>
<html>
<head>
    <title>OpenAI Assistant</title>
</head>
<body>
    @foreach ($messages as $message)
        <p><strong>{{ $message['role'] }}:</strong> {{ $message['content'] }}</p>
    @endforeach
</body>
</html>