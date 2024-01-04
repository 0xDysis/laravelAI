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

const statusHandlers = {
    'completed': function() {
        clearInterval(intervalId);
        fetchAndDisplayMessages();
    },
    'queued': function() {
        console.log('Run is queued. Waiting for next check.');
    },
    'in_progress': function() {
        // No action needed, just waiting for the next interval
    },
    'default': function(status) {
        clearInterval(intervalId);
        updateMessageArea('<p>Run ended with status: ' + status + '</p>');
    }
};

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
            // Use the handler for the response status, or the default handler if no specific handler exists
            (statusHandlers[response.status] || statusHandlers['default'])(response.status);
        },
        error: function(error) {
            console.error('Error checking run status:', error);
            clearInterval(intervalId);
            updateMessageArea('<p>Error checking run status. Please try again.</p>');
        }
    });
}


function initiateStatusCheck(runId) {
    intervalId = setInterval(function() {
        checkRunStatus(runId);
    }, 2000); // Check every 2 seconds
}

function submitMessage() {
    var messageInput = $('#message');
    var message = messageInput.val();
    // Append user message to the message area
    updateMessageArea('<p><strong>User:</strong> ' + message + '</p>', true);
    updateMessageArea('<p>Processing your request...</p>', true); // Append processing message

    $.ajax({
        url: '/submit-message',
        type: 'POST',
        data: { 
            message: message,
            _token: $('input[name="_token"]').val() // CSRF token
        },
        success: function() {
            messageInput.val(''); // Clear the input field after submission
            startAssistantRun();
        },
        error: function(error) {
            console.error('Error submitting message:', error);
            updateMessageArea('<p>Error submitting message. Please try again.</p>', true);
        }
    });
}

function updateMessageArea(message, append = false) {
    var messageArea = document.getElementById('messages');
    if (append) {
        // Create a new div element for the new message
        var newMessage = document.createElement('div');
        newMessage.innerHTML = message;

        // Append the new message to the message area
        messageArea.appendChild(newMessage);

        // Scroll to the bottom
        messageArea.scrollTop = messageArea.scrollHeight;
    } else {
        messageArea.innerHTML = message; // Replace the content
    }
}

function fetchAndDisplayMessages() {
    $.ajax({
        url: '/get-messages', // URL for fetching messages
        type: 'GET',
        success: function(response) {
            var messageContent = '<p><strong>Messages:</strong></p>';

            response.reverse();

            response.forEach(function(message) {
                // Check if the message has a fileId and replace the link
                if (message.fileId) {
                    var downloadLink = '/download-file/' + message.fileId;
                    var updatedContent = message.content.replace(
                        /\[Download [^\]]+\]\(sandbox:\/mnt\/data\/[^\)]+\)/g, 
                        '<a href="' + downloadLink + '" target="_blank">Download File</a>'
                    );
                    messageContent += '<p><strong>' + message.role + ':</strong> ' + updatedContent + '</p>';
                } else {
                    messageContent += '<p><strong>' + message.role + ':</strong> ' + message.content + '</p>';
                }
            });

            updateMessageArea(messageContent);
        },
        error: function(error) {
            console.error('Error fetching messages:', error);
            updateMessageArea('<p>Error fetching messages. Please try again.</p>');
        }
    });
}


document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('messageForm').addEventListener('submit', function (e) {
        e.preventDefault(); // Prevent the default form submission
        submitMessage();
    });
});