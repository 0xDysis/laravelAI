<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AssistantService
{
    protected $openAIService;

    public function __construct(MyOpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    public function deleteAssistant($assistantId)
    {
        
        $this->openAIService->deleteAssistant($assistantId);

        
        $user = Auth::user();

        
        if (($key = array_search($assistantId, $user->assistant_ids)) !== false) {
            unset($user->assistant_ids[$key]);
        }

        
        $user->save();

        
        Cache::forget('processedMessages');
    }
}

