<?php
namespace App\Services;

use App\Services\PHPScriptRunnerService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use App\Models\User; // Import the User model

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

        // Delete the thread ID from the user's threads in the database
        $user = auth()->user(); // Get the authenticated user
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

        // Add the new thread ID to the user's threads in the database
        $user = auth()->user(); // Get the authenticated user
        $user->threads = array_merge($user->threads ?? [], [$threadId]);
        $user->save();

        return $threadId;
    }

    // ... any other thread-related methods ...
}

