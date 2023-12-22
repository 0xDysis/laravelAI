var intervalId; // Declare intervalId at a higher scope

function startAssistantRun() {
    $.ajax({
        url: '/start-run', // Endpoint for starting the run
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Include CSRF token
        },
        success: function(response) {
            var runId = response.runId;
            initiateStatusCheck(runId);
        },
        error: function(error) {
            console.error('Error starting assistant run:', error);
        }
    });
}

function checkRunStatus(runId) {
    $.ajax({
        url: '/check-run-status',
        type: 'POST',
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: { runId: runId },
        success: function(response) {
            if (response.status !== 'in_progress') {
                clearInterval(intervalId);
                if (response.status === 'completed') {
                    fetchAndDisplayMessages(); // Fetch and display the final messages
                } else {
                    updateMessageArea('<p>Run ended with status: ' + response.status + '</p>');
                }
            }
        },
        error: function(error) {
            console.error('Error checking run status:', error);
            clearInterval(intervalId);
        }
    });
}




document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('messageForm').addEventListener('submit', function (e) {
        e.preventDefault(); // Prevent the default form submission
        submitMessage();
    });
});

function submitMessage() {
    var message = document.getElementById('message').value;
    updateMessageArea('<p>Processing your request...</p>'); // Display a temporary message

    $.ajax({
        url: '/submit-message', // URL for submitting the message
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

function updateMessageArea(message) {
    var messageArea = document.getElementById('messages');
    messageArea.innerHTML = message;
}

function initiateStatusCheck(runId) {
    intervalId = setInterval(function() {
        checkRunStatus(runId);
    }, 2000); // Check every 2 seconds
}
function fetchAndDisplayMessages() {
    $.ajax({
        url: '/get-messages', // URL for fetching messages
        type: 'GET',
        success: function(response) {
            // Assuming the response is an array of messages
            var messageContent = '<p><strong>Messages:</strong></p>';
            response.forEach(function(message) {
                messageContent += '<p><strong>' + message.role + ':</strong> ' + message.content + '</p>';
            });
            updateMessageArea(messageContent);
        },
        error: function(error) {
            console.error('Error fetching messages:', error);
            updateMessageArea('<p>Error fetching messages. Please try again.</p>');
        }
    });
}

