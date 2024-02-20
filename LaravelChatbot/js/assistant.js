var intervalId = null;
var currentThreadId = null;
var currentRunId = null; 

function startAssistantRun() {
    $.ajax({
        url: '/start-run',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: { 
            threadId: currentThreadId 
        },
        success: function(response) {
            currentRunId = response.runId;
            initiateStatusCheck(response.runId);
    
          
            $('#cancelRunButton').removeClass('opacity-0 transition-opacity duration-500 ease-in-out hidden');
    
        
            setTimeout(function() {
                $('#cancelRunButton').addClass('transition-opacity duration-500 ease-in-out opacity-100');
            }, 500);
        },
        error: function(error) {
            console.error('Error starting assistant run:', error);
        }
    });
}
function hideCancelButton() {

    $('#cancelRunButton').removeClass('transition-opacity duration-500 ease-in-out opacity-100');
    setTimeout(function() {
        $('#cancelRunButton').addClass('hidden');
    }, 500);
}
const statusHandlers = {
    'completed': function() {
        clearInterval(intervalId);
       
        if (currentThreadId) {
            fetchAndDisplayMessages(currentThreadId);
        } else {
            console.log("No current thread selected.");
            updateMessageArea('<p>No thread selected. Please select a thread to view messages.</p>');
        }
        hideCancelButton(); 

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
            threadId: currentThreadId 
        },
        success: function(response) {
            console.log('Run status:', response.status); 
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
    console.log('Cancel Run button clicked');

    if (!currentThreadId || !currentRunId) {
        console.error('No active thread or run to cancel');
        return;
    }

    console.log('Attempting to cancel run with thread ID:', currentThreadId, 'and run ID:', currentRunId); 

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
            clearInterval(intervalId); 
            intervalId = null;
            currentRunId = null; 
            updateMessageArea('<p>Run cancelled.</p>');
            hideCancelButton(); 

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
    var userMessageElement = `
    <div class="mb-4 flex items-end justify-end">
    <div class="px-4 py-3 bottom-right-radius max-w-xs lg:max-w-md" 
         style="background-color: #EBF0FF; border: 1px solid #B9CAFF; color: #00165A;">
        ${message}
    </div>
</div>
    `;


    updateMessageArea(userMessageElement, true);
    
    
    var processingMessage = `
        <div class="text-center text-sm text-gray-500">
            Processing your request...
        </div>
    `;
    updateMessageArea(processingMessage, true);

    $.ajax({
        url: '/submit-message',
        type: 'POST',
        data: { 
            message: message,
            threadId: currentThreadId,
            _token: $('input[name="_token"]').val()
        },
        success: function() {
            messageInput.val('');
            startAssistantRun();
        },
        error: handleErrorOnSubmit
    });
}


function createAndRunNewThread(message) {
    $.ajax({
        url: '/create-and-run-thread',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            message: message
        },
        success: function(response) {
            console.log('New thread created and run started:', response);
         
        },
        error: function(error) {
            console.error('Error creating a new thread:', error);
            updateMessageArea('<p>Error starting a new thread. Please try again.</p>');
        }
    });
}
function displayCreateThreadInput() {
    var inputFieldHtml = `
        <div class="text-center p-4">
            <input type="text" id="newThreadMessage" placeholder="Type your message to start a new thread" class="w-full p-2 border rounded">
            <button onclick="submitNewThreadMessage()" class="mt-2 p-2 bg-blue-500 text-white rounded">Start New Thread</button>
        </div>
    `;
    updateMessageArea(inputFieldHtml, false);
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
            
            updateMessageArea('', false);
            var assistantMessage = `
                <div class="mb-4 flex items-end justify-start">
                    <div class="px-4 py-3 bottom-left-radius max-w-xs lg:max-w-md" 
                         style="background-color: white; border: 1px solid #D4D4D4; color: #414141; overflow-wrap: break-word; word-break: break-all;">
                        Hallo! ik ben de Hotel Casa data analyst. hoe kan ik u helpen?
                    </div>
                </div>
            `;
            updateMessageArea(assistantMessage, true);

            response.reverse().forEach(function(message) {
                var formattedContent = message.fileId ? 
                    formatMessageWithFile(message) : 
                    formatMessageWithoutFile(message);

                var messageElement;
                if(message.role === 'assistant') {
                    // Style for assistant messages
                    messageElement = `
                        <div class="mb-4 flex items-end justify-start">
                            <div class="px-4 py-3 bottom-left-radius max-w-xs lg:max-w-md" 
                                 style="background-color: white; border: 1px solid #D4D4D4; color: #414141; overflow-wrap: break-word; word-break: break-all;">
                                ${formattedContent}
                            </div>
                        </div>
                    `;
                } else {
                    // Style for user messages
                    messageElement = `
                    <div class="mb-4 flex items-end justify-end">
                    <div class="px-4 py-3 bottom-right-radius max-w-xs lg:max-w-md" 
                         style="background-color: #EBF0FF; border: 1px solid #B9CAFF; color: #00165A; overflow-wrap: break-word; word-break: break-all;">
                        ${formattedContent}
                    </div>
                </div>
                    `;
                }
                
                updateMessageArea(messageElement, true);
            });

           
            var messageArea = document.getElementById('messages');
            messageArea.scrollTop = messageArea.scrollHeight;
        },
        error: function(error) {
            console.error('Error fetching messages:', error);
            var errorMessage = '<p>Error fetching messages. Please try again.</p>';
            updateMessageArea(errorMessage, false);
        }
    });
}





function fetchAndDisplayThreads(callback) {
    $.ajax({
        url: '/get-threads',
        type: 'GET',
        success: function(threads) {
            threads.reverse();
            var threadsContent = '';
            threads.forEach(function(threadId) {
                threadsContent += `
                <div class="thread-id-container group p-2 my-2 flex justify-between items-center bg-white text-gray-800 hover:bg-light-blue focus:bg-light-blue active:bg-light-blue border-0 hover:text-dark-blue transition duration-300 ease-in-out rounded text-sm" data-thread-id="${threadId}">
                    <span class="thread-id truncate group-hover:text-dark-blue" style="flex-grow: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${threadId}</span>
                    <button class="delete-thread-icon ml-2 bg-transparent border-0 p-0 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                `;
            });
            updateThreadsArea(threadsContent);
            
           
            attachThreadStylingListeners();
            if (callback) callback(); 
        },
        error: function(error) {
            console.error('Error fetching threads:', error);
            updateThreadsArea('<p class="text-white">Error fetching threads. Please try again.</p>');
        }
    });
}

function attachThreadStylingListeners() {
    var threadsArea = document.getElementById('threads');
    threadsArea.addEventListener('click', function(event) {
        if (event.target.matches('.thread-id-container, .thread-id-container *')) {
          
            var allThreads = threadsArea.querySelectorAll('.thread-id-container');
            allThreads.forEach(function(thread) {
                thread.classList.remove('bg-clicked-blue');
            });

          
            var threadContainer = event.target.closest('.thread-id-container');
            if (threadContainer) {
                threadContainer.classList.add('bg-clicked-blue');
            }
        }
    });
}








function attachThreadClickListeners() {
    var threadsArea = document.getElementById('threads');
    threadsArea.addEventListener('click', function(event) {
        if (event.target.matches('.delete-thread-icon, .delete-thread-icon *')) {
            
            var threadId = event.target.closest('.thread-id-container').querySelector('.thread-id').textContent;
            deleteThread(threadId);
            event.stopPropagation(); 
        } else if (event.target.matches('.thread-id-container, .thread-id-container *')) {
            
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

   
    var threadElement = null;
    var threadElements = document.querySelectorAll('.thread-id-container');
    threadElements.forEach(function(el) {
        if (el.querySelector('.thread-id').textContent === threadId) {
            threadElement = el;
            el.remove();
        }
    });

    
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
        dataType: 'json', 
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            console.log('New thread created successfully');
            var newThreadId = response.threadId;
           
            fetchAndDisplayThreads(function() {
                
                var newThreadElement = document.querySelector(`.thread-id-container[data-thread-id="${newThreadId}"]`);
                if (newThreadElement) {
                    newThreadElement.click(); 
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
        currentThreadId = threadId;  
        fetchAndDisplayMessages(threadId);
    }
}