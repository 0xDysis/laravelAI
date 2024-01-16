<!DOCTYPE html>
<html>
<head>
    <title>OpenAI Assistant</title>
    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('js/assistant.js') }}"></script> <!-- Include assistant.js -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        #messages, #threads {
            max-height: 400px; /* Adjust height as needed */
            overflow-y: auto; /* Makes the div scrollable */
            margin-bottom: 20px;
        }
        /* Additional styles for better UI */
        .form-container {
            margin-top: 20px;
        }
        .message-form {
            margin-bottom: 20px;
        }
    </style>
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

    <div id="threads">
        <!-- Threads will be populated here via AJAX -->
    </div>
    
    <div class="form-container">
        <div class="message-form">
            <form id="messageForm">
                @csrf
                <label for="message">Enter your message:</label><br>
                <input type="text" id="message" name="message"><br>
                <input type="submit" value="Submit">
            </form>
        </div>

        <!-- Flex container for creation and deletion forms -->
        <div style="display: flex; justify-content: space-around;">
            <form action="/create-new-thread" method="post">
                @csrf
                <input type="submit" value="Create New Thread">
            </form>

            <form action="/create-new-assistant" method="post">
                @csrf
                <input type="submit" value="Create New Assistant">
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
        </div>
    </div>
</body>
</html>