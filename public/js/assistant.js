var intervalId = null;
var currentThreadId = null;
var currentRunId = null; // Add a global variable to keep track of the current run ID

function startAssistantRun() {
    $.ajax({
        url: '/start-run',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: { 
            threadId: currentThreadId // Add threadId to the request data
        },
        success: function(response) {
            currentRunId = response.runId; // Set the current run ID here
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
        // Pass the currentThreadId to fetch messages for the correct thread
        if (currentThreadId) {
            fetchAndDisplayMessages(currentThreadId);
        } else {
            console.log("No current thread selected.");
            updateMessageArea('<p>No thread selected. Please select a thread to view messages.</p>');
        }
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
        data: { 
            runId: runId, 
            threadId: currentThreadId // Add threadId to the request data
        },
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
function cancelAssistantRun() {
    console.log('Cancel Run button clicked'); // Log when the button is clicked

    if (!currentThreadId || !currentRunId) {
        console.error('No active thread or run to cancel');
        return;
    }

    console.log('Attempting to cancel run with thread ID:', currentThreadId, 'and run ID:', currentRunId); // Log the IDs being used

    $.ajax({
        url: '/cancel-run',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            threadId: currentThreadId,
            runId: currentRunId
        },
        success: function(response) {
            console.log('Run cancelled successfully:', response);
            clearInterval(intervalId); // Clear the interval checking the run status
            intervalId = null;
            currentRunId = null; // Reset the current run ID
            updateMessageArea('<p>Run cancelled.</p>');
        },
        error: function(error) {
            console.error('Error cancelling the run:', error);
            updateMessageArea('<p>Error cancelling the run. Please try again.</p>');
        }
    });
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
            threadId: currentThreadId,  // Include the current thread ID
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


function fetchAndDisplayThreads(callback) {
    $.ajax({
        url: '/get-threads',
        type: 'GET',
        success: function(threads) {
            var threadsContent = '';
            threads.forEach(function(threadId) {
                
                threadsContent += `
                    <div class="thread-id-container group p-2 my-2 flex justify-between items-center bg-black hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-500 border-0" data-thread-id="${threadId}">
                        <span class="thread-id truncate text-white" style="flex-grow: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${threadId}</span>
                        <button class="delete-thread-icon text-red-500 hover:text-red-600 ml-2 bg-transparent border-0" style="padding: 0; cursor: pointer;">
                            <!-- SVG icon here -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="#ffffff">
                                <path fill-rule="evenodd" d="M6.707 4.707a1 1 0 00-1.414-1.414L4.5 4.5 4.5 6H4a1 1 0 000 2h12a1 1 0 100-2h-.5l-.5-1.5-.293-.293a1 1 0 00-1.414 1.414L13.5 6h-7l.207-.293z" clip-rule="evenodd" />
                                <path d="M4 7h12v10a2 2 0 002 2H4a2 2 0 002-2V7z" />
                            </svg>
                        </button>
                    </div>
                `;
            });
            updateThreadsArea(threadsContent);
            if (callback) callback(); 
        },
        error: function(error) {
            console.error('Error fetching threads:', error);
            updateThreadsArea('<p class="text-white">Error fetching threads. Please try again.</p>');
        }
    });
}







function attachThreadClickListeners() {
    var threadsArea = document.getElementById('threads');
    threadsArea.addEventListener('click', function(event) {
        if (event.target.matches('.delete-thread-icon, .delete-thread-icon *')) {
            // Handle delete thread click
            var threadId = event.target.closest('.thread-id-container').querySelector('.thread-id').textContent;
            deleteThread(threadId);
            event.stopPropagation(); // Prevent triggering the thread click event
        } else if (event.target.matches('.thread-id-container, .thread-id-container *')) {
            // Handle thread selection click
            var threadId = event.target.closest('.thread-id-container').querySelector('.thread-id').textContent;
            fetchAndDisplayMessages(threadId);
        }
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

    // Optimistically remove the thread from the DOM
    var threadElement = null;
    var threadElements = document.querySelectorAll('.thread-id-container');
    threadElements.forEach(function(el) {
        if (el.querySelector('.thread-id').textContent === threadId) {
            threadElement = el;
            el.remove();
        }
    });

    // Perform the server request
    $.ajax({
        url: '/delete-thread/' + threadId,
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function() {
            console.log('Thread deleted successfully');
            updateMessageArea('<p>Thread deleted. Select another thread to view messages.</p>');
            currentThreadId = null;
        },
        error: function(error) {
            console.error('Error deleting thread:', error);

            // Revert the optimistic update on error
            if (threadElement) {
                var threadsArea = document.getElementById('threads');
                threadsArea.appendChild(threadElement);
            }
            updateMessageArea('<p>Error deleting thread. Please try again.</p>');
        }
    });
}





function createNewThread() {
    console.log("Creating new thread");
    $.ajax({
        url: '/create-new-thread',
        type: 'POST',
        dataType: 'json', // Expect a JSON response
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            console.log('New thread created successfully');
            var newThreadId = response.threadId;
            // Now trigger a click event on the new thread's element
            // We'll assume `fetchAndDisplayThreads` will add an element with the id `thread_{threadId}`
            fetchAndDisplayThreads(function() {
                // This callback ensures that we try to click the thread after the list is updated
                var newThreadElement = document.querySelector(`.thread-id-container[data-thread-id="${newThreadId}"]`);
                if (newThreadElement) {
                    newThreadElement.click(); // Simulate the click
                }
            });
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
    attachEventListeners();
    fetchAndDisplayThreads();
});

function attachEventListeners() {
    var threadsArea = document.getElementById('threads');
    threadsArea.addEventListener('click', handleThreadAreaClick);

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

    var cancelRunButton = document.getElementById('cancelRunButton');
    if (cancelRunButton) {
        cancelRunButton.addEventListener('click', cancelAssistantRun);
    }
}

function handleThreadAreaClick(event) {
    if (event.target.matches('.delete-thread-icon, .delete-thread-icon *')) {
        var threadId = event.target.closest('.thread-id-container').querySelector('.thread-id').textContent;
        deleteThread(threadId);
        event.stopPropagation();
    } else if (event.target.closest('.thread-id-container') && !event.target.closest('.delete-thread-icon')) {
        var threadId = event.target.closest('.thread-id-container').querySelector('.thread-id').textContent;
        currentThreadId = threadId;  // Set the currentThreadId here
        fetchAndDisplayMessages(threadId);
    }
}