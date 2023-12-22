<!DOCTYPE html>
<html>
<head>
    <title>OpenAI Assistant</title>
    <head>
        <title>OpenAI Assistant</title>
        <!-- Include jQuery -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        
    
    <script src="{{ asset('js/assistant.js') }}"></script> <!-- Include assistant.js -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    @if(session('message'))
        <p>{{ session('message') }}</p>
    @endif

    <div id="messages">
        @if(isset($rawData))
            <p><strong>Messages:</strong></p>
            @foreach(json_decode($rawData, true) as $message)
                <p><strong>{{ $message['role'] }}:</strong> {{ $message['content'] }}</p>
            @endforeach
        @else
            <p>No messages to display. Start by creating a new thread and assistant.</p>
        @endif
    </div>
    

    <form id="messageForm">
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

    <script>
       function updateMessageArea(message) {
    var messageArea = document.getElementById('messages');
    messageArea.innerHTML = message;
}

function submitMessage() {
    var message = $('#message').val();
    updateMessageArea('<p>Processing your request...</p>'); // Display a temporary message

    $.ajax({
        url: '/submit-message', // Adjust URL as per your routing
        type: 'POST',
        data: { 
            message: message,
            _token: $('input[name="_token"]').val() // CSRF token
        },
        success: function() {
            startAssistantRun();
        },
        error: function(error) {
            console.error('Error submitting message:', error);
            updateMessageArea('<p>Error submitting message. Please try again.</p>'); // Display error message
        }
    });
}

    </script>
</body>
</html>
