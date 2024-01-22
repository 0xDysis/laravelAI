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
        $this->sessionValidationService->validate(['assistantId']);
        
        $userMessage = $request->input('message');
        $threadId = $request->input('threadId'); // Get threadId from the request
        $assistantId = Session::get('assistantId');
    
        $this->messageService->submitMessage($threadId, $assistantId, $userMessage);
    }
    
    
    public function startRun(Request $request)
    {
        $this->sessionValidationService->validate(['assistantId']);
        $threadId = $request->input('threadId') ?? Session::get('threadId');
        $assistantId = Session::get('assistantId');
    
        $runId = $this->runService->startRun($threadId, $assistantId);
        
        return response()->json(['runId' => $runId]);
    }

    public function cancelRun(Request $request)
    {
        $this->sessionValidationService->validate(['assistantId']);
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
public function modifyMessage(Request $request)
    {
        $this->sessionValidationService->validate(['assistantId']);

        $threadId = $request->input('threadId');
        $messageId = $request->input('messageId');
        $newName = $request->input('newName');

        if (!$threadId || !$messageId || !$newName) {
            return response()->json(['error' => 'Thread ID, Message ID, and New Name are required'], 400);
        }

        try {
            $this->messageService->modifyMessage($threadId, $messageId, $newName);
            return response()->json(['message' => 'Message modified successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    
    public function downloadMessageFile($fileId)
    {
        return $this->fileDownloadService->downloadMessageFile($fileId);
    }
    
    public function createNewAssistantWithCsv()
    {
        return $this->databaseExportService->createNewAssistantWithCsv();
    }
    
    public function deleteThread($threadId)
{
    $this->threadService->deleteThread($threadId);
    return redirect('/');
}


    public function deleteAssistant()
    {
        $assistantId = Session::get('assistantId');
        $this->assistantService->deleteAssistant($assistantId);
        return redirect('/');
    }

    public function createNewThread()
    {
        $this->threadService->createNewThread();
        return redirect('/');
    }
    public function getThreads()
{
    $user = auth()->user(); // Get the authenticated user
    $threads = $user->threads; // Get the threads from the user model

    return response()->json($threads);
}
    
}
