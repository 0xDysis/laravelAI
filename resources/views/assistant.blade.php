<!DOCTYPE html>
<html>
<head>
    <title>OpenAI Assistant</title>
</head>
<body>
    @if(isset($rawData))
    <p><strong>Raw Data:</strong> {{ $rawData }}</p>
@else
    <p>Error: Raw data is not available.</p>
@endif

    <form action="/submit-message" method="post">
        @csrf
        <label for="message">Enter your message:</label><br>
        <input type="text" id="message" name="message"><br>
        <input type="submit" value="Submit">
    </form>
    <form action="/delete-thread" method="post">
        @csrf
        <input type="submit" value="Delete Thread">
    </form>
    
    <form action="/delete-assistant" method="post">
        @csrf
        <input type="submit" value="Delete Assistant">
    </form>
    
</body>
</html>
