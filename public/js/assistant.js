var intervalId = null;


function startAssistantRun() {
    $.ajax({
        url: '/start-run',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            initiateStatusCheck(response.runId);
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
    'in_progress': function() {},
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
            console.log('Run status:', response.status);  // Log the status
            var handler = statusHandlers[response.status] || statusHandlers['default'];
            handler(response.status);
            if (response.status === 'completed') {
                console.log('Run completed, clearing interval.');
                clearInterval(intervalId);
                intervalId = null;
            }
        },
        error: function(error) {
            console.error('Error checking run status:', error);
            clearInterval(intervalId);
            intervalId = null; 
            updateMessageArea('<p>Error checking run status. Please try again.</p>');
        }
    });
}

function initiateStatusCheck(runId) {
    
    if (intervalId) {
       
        clearInterval(intervalId);
    }

    intervalId = setInterval(function() {
        checkRunStatus(runId);
    }, 1000);
}

function handleErrorOnSubmit(error) {
    console.error('Error submitting message:', error);
    updateMessageArea('<p>Error submitting message. Please try again.</p>', true);
}

function submitMessage() {
    var messageInput = $('#message');
    var message = messageInput.val();
    updateMessageArea('<p><strong>User:</strong> ' + message + '</p>', true);
    updateMessageArea('<p>Processing your request...</p>', true);

    $.ajax({
        url: '/submit-message',
        type: 'POST',
        data: { 
            message: message,
            _token: $('input[name="_token"]').val()
        },
        success: function() {
            messageInput.val('');
            startAssistantRun();
        },
        error: handleErrorOnSubmit
    });
}


function appendMessage(messageArea, message) {
    var newMessage = document.createElement('div');
    newMessage.innerHTML = message;
    messageArea.appendChild(newMessage);
    messageArea.scrollTop = messageArea.scrollHeight;
}

function setMessage(messageArea, message) {
    messageArea.innerHTML = message;
}

function updateMessageArea(message, append = false) {
    var messageArea = document.getElementById('messages');
    const action = append ? appendMessage : setMessage;
    action(messageArea, message);
}


function formatMessageWithFile(message) {
    var downloadLink = '/download-file/' + message.fileId;
    return message.content.replace(
        /\[Download [^\]]+\]\(sandbox:\/mnt\/data\/[^\)]+\)/g, 
        '<a href="' + downloadLink + '" target="_blank">Download File</a>'
    );
}

function formatMessageWithoutFile(message) {
    return message.content;
}

function fetchAndDisplayMessages() {
    $.ajax({
        url: '/get-messages',
        type: 'GET',
        success: function(response) {
            var messageContent = '<p><strong>Messages:</strong></p>';
            response.reverse();
            response.forEach(function(message) {
                var formattedContent = message.fileId ? 
                    formatMessageWithFile(message) : 
                    formatMessageWithoutFile(message);
                messageContent += '<p><strong>' + message.role + ':</strong> ' + formattedContent + '</p>';
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
        e.preventDefault();
        submitMessage();
    });
});
