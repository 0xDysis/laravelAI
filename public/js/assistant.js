var intervalId = null;
var currentThreadId = null;


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
    intervalId && clearInterval(intervalId);
    intervalId = setInterval(() => checkRunStatus(runId), 1000);
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

    if (!currentThreadId) {
        // If no thread is currently selected, create a new one
        createThreadAndSubmitMessage(message);
    } else {
        // If a thread is selected, submit the message to the current thread
        sendExistingThreadMessage(message);
    }
}

function createThreadAndSubmitMessage(message) {
    console.log("Creating new thread before sending message");
    $.ajax({
        url: '/create-new-thread',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            // Assuming response contains the new thread ID
            currentThreadId = response.threadId; 
            console.log('New thread created successfully, Thread ID:', currentThreadId);
            sendExistingThreadMessage(message);
            fetchAndDisplayThreads();
        },
        error: function(error) {
            console.error('Error creating new thread:', error);
            updateMessageArea('<p>Error creating new thread. Please try again.</p>');
        }
    });
}

function sendExistingThreadMessage(message) {
    $.ajax({
        url: '/submit-message',
        type: 'POST',
        data: {
            message: message,
            threadId: currentThreadId,
            _token: $('input[name="_token"]').val()
        },
        success: function() {
            $('#message').val('');
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

function fetchAndDisplayMessages(threadId = null) {
    currentThreadId = threadId;

    $.ajax({
        url: '/get-messages',
        type: 'GET',
        data: { threadId: threadId },
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


function fetchAndDisplayThreads() {
    $.ajax({
        url: '/get-threads',
        type: 'GET',
        success: function(threads) {
            var threadsContent = '';
            threads.forEach(function(thread) {
                threadsContent += `
                    <div class="thread-id-container group p-2 border rounded my-2 flex justify-between items-center hover:bg-gray-300" style="overflow: hidden;">
                        <span class="thread-id" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${thread}</span>
                        <button class="delete-thread-icon text-red-500 hover:text-red-600" onclick="deleteThread('${thread}')" style="background: none; border: none; padding: 0; cursor: pointer;">
                            <!-- SVG icon here -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M6.707 4.707a1 1 0 00-1.414-1.414L4.5 4.5 4.5 6H4a1 1 0 000 2h12a1 1 0 100-2h-.5l-.5-1.5-.293-.293a1 1 0 00-1.414 1.414L13.5 6h-7l.207-.293z" clip-rule="evenodd" />
                                <path d="M4 7h12v10a2 2 0 002 2H4a2 2 0 002-2V7z" />
                            </svg>
                        </button>
                    </div>
                `;
            });
            updateThreadsArea(threadsContent);
            attachThreadClickListeners();
        },
        error: function(error) {
            console.error('Error fetching threads:', error);
            updateThreadsArea('<p>Error fetching threads. Please try again.</p>');
        }
    });
}



function attachThreadClickListeners() {
    document.querySelectorAll('.thread-id-container').forEach(item => {
        // Attach click listener for thread deletion
        item.querySelector('.delete-thread-icon').addEventListener('click', function(event) {
            event.stopPropagation();  // Prevent triggering the thread click event
            var threadId = this.closest('.thread-id-container').querySelector('.thread-id').textContent;
            deleteThread(threadId);
        });

        // Attach click listener for thread selection
        attachClickToThread(item);
    });
}

function attachClickToThread(threadElement) {
    threadElement.addEventListener('click', function() {
        var threadId = this.querySelector('.thread-id').textContent;
        fetchAndDisplayMessages(threadId);
    });
}



function updateThreadsArea(content) {
    var threadsArea = document.getElementById('threads'); 
    threadsArea.innerHTML = content;
}
function deleteThread(threadId) {
    console.log("Deleting thread with ID:", threadId); 
    $.ajax({
        url: '/delete-thread/' + threadId, // Ensure this is correctly concatenated
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function() {
            console.log('Thread deleted successfully');
            fetchAndDisplayThreads(); // Refresh the threads list
            updateMessageArea('<p>Thread deleted. Select another thread to view messages.</p>');
            currentThreadId = null; // Reset the current thread ID
        },
        error: function(error) {
            console.error('Error deleting thread:', error);
           
        }
    });
}
function createNewThread() {
    console.log("Creating new thread");
    $.ajax({
        url: '/create-new-thread',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function() {
            console.log('New thread created successfully');
            fetchAndDisplayThreads();
        },
        error: function(error) {
            console.error('Error creating new thread:', error);
        }
    });
}

function createNewAssistant() {
    console.log("Creating new assistant");
    $.ajax({
        url: '/create-new-assistant',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function() {
            console.log('New assistant created successfully');
        },
        error: function(error) {
            console.error('Error creating new assistant:', error);
        }
    });
}

function deleteAssistant() {
    console.log("Deleting assistant");
    $.ajax({
        url: '/delete-assistant',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function() {
            console.log('Assistant deleted successfully');
        },
        error: function(error) {
            console.error('Error deleting assistant:', error);
        }
    });
}



document.addEventListener('DOMContentLoaded', function () {
   
    var deleteThreadButton = document.getElementById('deleteThreadButton');
    if (deleteThreadButton) {
        deleteThreadButton.addEventListener('click', function () {
            if (currentThreadId) {
                deleteThread(currentThreadId);
            } else {
                console.error('No thread selected for deletion');
            }
        });
    }

    var createThreadButton = document.getElementById('createThreadButton');
    if (createThreadButton) {
        createThreadButton.addEventListener('click', createNewThread);
    }

    var createAssistantButton = document.getElementById('createAssistantButton');
    if (createAssistantButton) {
        createAssistantButton.addEventListener('click', createNewAssistant);
    }

    var deleteAssistantButton = document.getElementById('deleteAssistantButton');
    if (deleteAssistantButton) {
        deleteAssistantButton.addEventListener('click', deleteAssistant);
    }

    var messageForm = document.getElementById('messageForm');
    if (messageForm) {
        messageForm.addEventListener('submit', function (e) {
            e.preventDefault();
            submitMessage();
        });
    }

    fetchAndDisplayThreads();
});



