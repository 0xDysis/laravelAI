<?php

namespace LaravelAI\LaravelChatbot\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use LaravelAI\LaravelChatbot\Models\User;
use LaravelAI\LaravelChatbot\Services\MessageService;
use LaravelAI\LaravelChatbot\Services\RunService;
use LaravelAI\LaravelChatbot\Services\FileDownloadService;
use LaravelAI\LaravelChatbot\Services\DatabaseExportService;
use LaravelAI\LaravelChatbot\Services\ThreadService;
use LaravelAI\LaravelChatbot\Services\AssistantService;
use App\Http\Controllers\Controller;

class OpenAIController extends Controller
{
    protected $messageService;
    protected $runService;
    protected $fileDownloadService;
    protected $databaseExportService;
    protected $threadService;
    protected $assistantService;

    public function __construct(
        MessageService $messageService, 
        RunService $runService, 
        FileDownloadService $fileDownloadService, 
        DatabaseExportService $databaseExportService,
        ThreadService $threadService,
        AssistantService $assistantService
    ) {
        $this->messageService = $messageService;
        $this->runService = $runService;
        $this->fileDownloadService = $fileDownloadService;
        $this->databaseExportService = $databaseExportService;
        $this->threadService = $threadService;
        $this->assistantService = $assistantService;
    }


    public function index()
    {
        if (!Cache::has('processedMessages')) {
            Cache::put('processedMessages', [], 3600);
        }
        return view('assistant');
    }

    public function submitMessage(Request $request)
    {
        $user = auth()->user();
        $assistantId = last($user->assistant_ids);
        $userMessage = $request->input('message');
        $threadId = $request->input('threadId');
        if ($assistantId) {
            $this->messageService->submitMessage($threadId, $assistantId, $userMessage);
        } else {
            return response()->json(['error' => 'No assistant ID available'], 422);
        }
    }
    
    public function startRun(Request $request)
    {
        $user = auth()->user();
        $assistantId = last($user->assistant_ids);
        $threadId = $request->input('threadId');
        if ($assistantId) {
            $runId = $this->runService->startRun($threadId, $assistantId);
            return response()->json(['runId' => $runId]);
        } else {
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
        $user = auth()->user();
        $user->assistant_ids = [];
        $user->save();
        return redirect('/');
    }

    public function createNewThread()
    {
        $threadId = $this->threadService->createNewThread();
        return response()->json(['threadId' => $threadId]);
    }

    public function getThreads()
    {
        $user = auth()->user();
        $threads = $user->threads;
        return response()->json($threads);
    }
}
