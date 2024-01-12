<?php
namespace App\Services;

use App\Services\PHPScriptRunnerService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;

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
    }

    public function createNewThread()
    {
        $threadId = $this->phpScriptRunnerService->runScript('createThread');
        Session::put('threadId', $threadId);
        Cache::forget('processedMessages');
        return $threadId;
    }

    // ... any other thread-related methods ...
}
