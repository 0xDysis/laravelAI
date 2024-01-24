

import {
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
  } from './ajax.js';
  
  var intervalId = null;
  var currentThreadId = null;
  var currentRunId = null;
  
  function handleStatusChange(status) {
    const statusHandlers = {
      'completed': () => {
        clearInterval(intervalId);
        if (currentThreadId) {
          fetchAndDisplayMessages(currentThreadId, updateMessageArea, handleError);
        } else {
          console.log("No current thread selected.");
          updateMessageArea('<p>No thread selected. Please select a thread to view messages.</p>');
        }
      },
      'queued': () => console.log('Run is queued. Waiting for next check.'),
      'in_progress': () => {},
      'default': (status) => {
        clearInterval(intervalId);
        updateMessageArea('<p>Run ended with status: ' + status + '</p>');
      }
    };
  
    const handler = statusHandlers[status] || statusHandlers['default'];
    handler(status);
  }
  
  function initiateStatusCheck(runId) {
    intervalId && clearInterval(intervalId);
    intervalId = setInterval(() => {
      checkRunStatus(runId, currentThreadId, handleStatusChange, handleError);
    }, 1000);
  }
  
  function handleError(error, message) {
    console.error(message, error);
    updateMessageArea(`<p>${message} Please try again.</p>`, true);
  }
  
  function handleMessageSubmit(e) {
    e.preventDefault();
    const messageInput = $('#message');
    const message = messageInput.val();
    updateMessageArea(`<p><strong>User:</strong> ${message}</p>`, true);
    submitMessage(
      message,
      currentThreadId,
      $('input[name="_token"]').val(),
      () => {
        messageInput.val('');
        startAssistantRun(currentThreadId, (runId) => {
          currentRunId = runId;
          initiateStatusCheck(runId);
        }, (error) => handleError(error, 'Error starting assistant run:'));
      },
      (error) => handleError(error, 'Error submitting message:')
    );
  }
  
  function attachEventListeners() {
    const threadsArea = document.getElementById('threads');
    threadsArea.addEventListener('click', handleThreadAreaClick);
  
    const createThreadButton = document.getElementById('createThreadButton');
    createThreadButton && createThreadButton.addEventListener('click', () => {
      createNewThread(() => fetchAndDisplayThreads(updateThreadsArea, handleError), 
      (error) => handleError(error, 'Error creating new thread:'));
    });
  
    const createAssistantButton = document.getElementById('createAssistantButton');
    createAssistantButton && createAssistantButton.addEventListener('click', () => {
      createNewAssistant(() => console.log('New assistant created successfully'),
      (error) => handleError(error, 'Error creating new assistant:'));
    });
  
    const deleteAssistantButton = document.getElementById('deleteAssistantButton');
    deleteAssistantButton && deleteAssistantButton.addEventListener('click', () => {
      deleteAssistant(() => console.log('Assistant deleted successfully'),
      (error) => handleError(error, 'Error deleting assistant:'));
    });
  
    const messageForm = document.getElementById('messageForm');
    messageForm && messageForm.addEventListener('submit', handleMessageSubmit);
  }
  
  function handleThreadAreaClick(event) {
    const target = event.target;
    if (target.matches('.delete-thread-icon, .delete-thread-icon *')) {
      const threadId = target.closest('.thread-id-container').querySelector('.thread-id').textContent;
      deleteThread(threadId, () => {
        console.log('Thread deleted successfully');
        updateMessageArea('<p>Thread deleted. Select another thread to view messages.</p>');
        currentThreadId = null;
      }, (error) => handleError(error, 'Error deleting thread:'));
      event.stopPropagation();
    } else if (target.matches('.thread-id-container, .thread-id-container *')) {
      const threadId = target.closest('.thread-id-container').querySelector('.thread-id').textContent;
      currentThreadId = threadId;
      fetchAndDisplayMessages(threadId, updateMessageArea, handleError);
    }
  }
  
  function initialize() {
    attachEventListeners();
    fetchAndDisplayThreads(updateThreadsArea, handleError);
}



export { initialize };
  