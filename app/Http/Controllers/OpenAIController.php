<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Models\Order;
use App\Services\MessageService;
use App\Services\RunService;
use App\Services\FileDownloadService;
use App\Services\DatabaseExportService;
use App\Services\SessionValidationService;
use App\Services\ThreadService;
use App\Services\AssistantService;

class OpenAIController extends Controller
{
    public function index()
{
    if (!Cache::has('processedMessages')) {
        Cache::put('processedMessages', [], 3600);
    }

    return view('assistant');
}
    protected $messageService;
    protected $runService;
    protected $fileDownloadService;
    protected $databaseExportService;
    protected $sessionValidationService;
    protected $threadService;
    protected $assistantService;

    public function __construct(
        MessageService $messageService, 
        RunService $runService, 
        FileDownloadService $fileDownloadService, 
        DatabaseExportService $databaseExportService,
        SessionValidationService $sessionValidationService,
        ThreadService $threadService,
        AssistantService $assistantService,
    ) {
        $this->messageService = $messageService;
        $this->runService = $runService;
        $this->fileDownloadService = $fileDownloadService;
        $this->databaseExportService = $databaseExportService;
        $this->sessionValidationService = $sessionValidationService;
        $this->threadService = $threadService;
        $this->assistantService = $assistantService;
    }
    public function submitMessage(Request $request)
    {
        $user = auth()->user(); // Get the authenticated user
    
        // Fetch the most recent assistantId from the user's assistant_ids array
        // Adjust the logic here if you need to select a different assistantId
        $assistantId = last($user->assistant_ids);
    
        // Get the user message and thread ID from the request
        $userMessage = $request->input('message');
        $threadId = $request->input('threadId');
    
        // Make sure we have an assistantId before attempting to submit the message
        if ($assistantId) {
            $this->messageService->submitMessage($threadId, $assistantId, $userMessage);
        } else {
            // Handle the case where there is no assistantId available
            // You might want to return an error response or take other appropriate action
            return response()->json(['error' => 'No assistant ID available'], 422);
        }
    }
    
    
    
    public function startRun(Request $request)
{
    // Ensure that there is a logged-in user before proceeding
    $user = auth()->user();
    if (!$user) {
        return response()->json(['error' => 'User not authenticated'], 401);
    }
    
    // Fetch the most recent assistantId from the user's assistant_ids array
    $assistantId = last($user->assistant_ids);

    // Get threadId from the request
    $threadId = $request->input('threadId');

    // Make sure we have an assistantId before attempting to start the run
    if ($assistantId) {
        $runId = $this->runService->startRun($threadId, $assistantId);
        return response()->json(['runId' => $runId]);
    } else {
        // Handle the case where there is no assistantId available
        return response()->json(['error' => 'No assistant ID available'], 422);
    }
}


    public function cancelRun(Request $request)
    {
        
        $threadId = $request->input('threadId');
        $runId = $request->input('runId');

        if (!$threadId || !$runId) {
            return response()->json(['error' => 'Thread ID and Run ID are required'], 400);
        }

        try {
            $cancelResponse = $this->runService->cancelRun($threadId, $runId);
            return response()->json($cancelResponse);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    
    public function checkRunStatus(Request $request)
{
    // Get threadId from the request, or fall back to session if not present
    $threadId = $request->input('threadId') ?? Session::get('threadId');
    $runId = $request->input('runId');
 
    $status = $this->runService->checkRunStatus($threadId, $runId);
 
    return response($status);
}

    
    public function getMessages(Request $request)
{
    $threadId = $request->input('threadId') ?? Session::get('threadId');
    $messagesData = $this->messageService->getMessages($threadId);
 
    return response()->json($messagesData);
}

    
    
    public function downloadMessageFile($fileId)
    {
        return $this->fileDownloadService->downloadMessageFile($fileId);
    }
    
    public function createNewAssistantWithCsv()
    {
        return $this->databaseExportService->createNewAssistantWithMultipleCsv();
    }
    
    public function deleteThread($threadId)
{
    $this->threadService->deleteThread($threadId);
    return redirect('/');
}


public function deleteAssistant()
{
    $user = auth()->user(); // Get the authenticated user

    // Clear the assistant_ids array
    $user->assistant_ids = [];
    $user->save(); // Save the user with the updated empty array

    return redirect('/');
}



public function createNewThread()
{
    // Assuming createNewThread() returns the ID of the new thread as a string
    $threadId = $this->threadService->createNewThread();

    // Return the thread ID as a JSON response
    return response()->json([
        'threadId' => $threadId
    ]);
}

public function createAndRunThreadWithMessage(Request $request)
    {
        $user = auth()->user(); // Ensure there's an authenticated user

        $assistantId = last($user->assistant_ids); // Get the most recent assistantId
        $userMessage = $request->input('message'); // Get the user message from the request

        // Ensure we have an assistantId and a user message
        if (!$assistantId || !$userMessage) {
            return response()->json(['error' => 'Assistant ID and user message are required'], 400);
        }

        try {
            $response = $this->threadService->createAndRunThreadWithMessage($assistantId, $userMessage);
            return response()->json($response); // Return the response from the service
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getThreads()
{
    $user = auth()->user(); // Get the authenticated user
    $threads = $user->threads; // Get the threads from the user model

    return response()->json($threads);
}
    
}