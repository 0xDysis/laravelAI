// ajax.js - Refactored AJAX operations for a modular approach

// CSRF token retrieval function
function getCsrfToken() {
    return $('meta[name="csrf-token"]').attr('content');
}

// General AJAX request function
function ajaxRequest(url, type, data, successCallback, errorCallback) {
    $.ajax({
        url: url,
        type: type,
        dataType: 'json', // Assuming JSON responses for all AJAX calls
        headers: {
            'X-CSRF-TOKEN': getCsrfToken()
        },
        data: data,
        success: successCallback,
        error: errorCallback
    });
}

// Starting the assistant run
function startAssistantRun(threadId, onSuccess, onError) {
    ajaxRequest('/start-run', 'POST', { threadId }, onSuccess, onError);
}

// Checking the run status
function checkRunStatus(runId, threadId, onSuccess, onError) {
    ajaxRequest('/check-run-status', 'POST', { runId, threadId }, onSuccess, onError);
}

// Cancelling the assistant run
function cancelAssistantRun(threadId, runId, onSuccess, onError) {
    ajaxRequest('/cancel-run', 'POST', { threadId, runId }, onSuccess, onError);
}

// Submitting a message
function submitMessage(message, threadId, token, onSuccess, onError) {
    ajaxRequest('/submit-message', 'POST', { message, threadId, _token: token }, onSuccess, onError);
}

// Fetching messages
function fetchAndDisplayMessages(threadId, onSuccess, onError) {
    ajaxRequest('/get-messages', 'GET', { threadId }, onSuccess, onError);
}

// Fetching threads
function fetchAndDisplayThreads(onSuccess, onError) {
    ajaxRequest('/get-threads', 'GET', {}, onSuccess, onError);
}

// Deleting a thread
function deleteThread(threadId, onSuccess, onError) {
    ajaxRequest('/delete-thread/' + threadId, 'POST', {}, onSuccess, onError);
}

// Creating a new thread
function createNewThread(onSuccess, onError) {
    ajaxRequest('/create-new-thread', 'POST', {}, onSuccess, onError);
}

// Creating a new assistant
function createNewAssistant(onSuccess, onError) {
    ajaxRequest('/create-new-assistant', 'POST', {}, onSuccess, onError);
}

// Deleting an assistant
function deleteAssistant(onSuccess, onError) {
    ajaxRequest('/delete-assistant', 'POST', {}, onSuccess, onError);
}

// Exporting the functions to be used in other modules
export {
    startAssistantRun,
    checkRunStatus,
    cancelAssistantRun,
    submitMessage,
    fetchAndDisplayMessages,
    fetchAndDisplayThreads,
    deleteThread,
    createNewThread,
    createNewAssistant,
    deleteAssistant
};

