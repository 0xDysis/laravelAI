<?php
namespace App\Services;

use App\Services\PHPScriptRunnerService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use App\Models\User; 

class ThreadService
{
    protected $phpScriptRunnerService;

    public function __construct(PHPScriptRunnerService $phpScriptRunnerService)
    {
        $this->phpScriptRunnerService = $phpScriptRunnerService;
    }

    public function deleteThread($threadId)
    {
        $this->phpScriptRunnerService->runScript('deleteThread', [$threadId]);
        Session::forget('threadId');
        Cache::forget('processedMessages');

        
        $user = auth()->user(); 
        $user->threads = array_filter($user->threads, function($tid) use ($threadId) {
            return $tid != $threadId;
        });
        $user->save();
    }

    public function createNewThread()
    {
        $threadId = $this->phpScriptRunnerService->runScript('createThread');
        Session::put('threadId', $threadId);
        Cache::forget('processedMessages');

        
        $user = auth()->user(); 
        $user->threads = array_merge($user->threads ?? [], [$threadId]);
        $user->save();

        return $threadId;
    }
    public function createAndRunThreadWithMessage($assistantId, $userMessage)
    {
        // Call the PHP script and pass parameters
        $response = $this->phpScriptRunnerService->runScript('createAndRunThreadWithMessage', [$assistantId, $userMessage]);

        // Process the response as needed, e.g., store in session, cache, or update user model
        Session::put('threadId', $response['id']);
        // ... other processing ...

        return $response;
    }

}

