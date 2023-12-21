<!DOCTYPE html>
<html>
<head>
    <title>OpenAI Assistant</title>
</head>
<body>
    @if(session('message'))
        <p>{{ session('message') }}</p>
    @endif

    @if(isset($rawData))
        <p><strong>Messages:</strong></p>
        @foreach(json_decode($rawData, true) as $message)
            <p><strong>{{ $message['role'] }}:</strong> {{ $message['content'] }}</p>
        @endforeach
    @else
        <p>No messages to display. Start by creating a new thread and assistant.</p>
    @endif

    <form action="/submit-message" method="post">
        @csrf
        <label for="message">Enter your message:</label><br>
        <input type="text" id="message" name="message"><br>
        <input type="submit" value="Submit">
    </form>

    <form action="/delete-thread" method="post">
        @csrf
        <input type="hidden" name="_method" value="DELETE">
        <input type="submit" value="Delete Thread">
    </form>
    
    <form action="/delete-assistant" method="post">
        @csrf
        <input type="hidden" name="_method" value="DELETE">
        <input type="submit" value="Delete Assistant">
    </form>

    <form action="/create-new-thread" method="post">
        @csrf
        <input type="submit" value="Create New Thread">
    </form>

    <form action="/create-new-assistant" method="post">
        @csrf
        <input type="submit" value="Create New Assistant">
    </form>
    
</body>
</html>