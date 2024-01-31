<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class ThreadService
{
    protected $openAIService;

    public function __construct(MyOpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    public function deleteThread($threadId)
    {
        $user = Auth::user(); 
        $user->threads = array_filter($user->threads, function($tid) use ($threadId) {
            return $tid != $threadId;
        });
        $user->save();
        $this->openAIService->deleteThread($threadId);
        Session::forget('threadId');
    }

    public function createNewThread()
    {
        $threadId = $this->openAIService->createThread();
        Session::put('threadId', $threadId);

        $user = Auth::user(); 
        $user->threads = array_merge($user->threads ?? [], [$threadId]);
        $user->save();

        return $threadId;
    }
}

