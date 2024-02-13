<?php

namespace LaravelAI\LaravelChatbot\Services;


class RunService
{
    protected $openAIService;

    public function __construct(MyOpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    public function startRun($threadId, $assistantId)
    {
        return $this->openAIService->runAssistant($threadId, $assistantId);
    }

    public function checkRunStatus($threadId, $runId)
    {
        return $this->openAIService->checkRunStatus($threadId, $runId);
    }

    public function cancelRun($threadId, $runId)
    {
        return $this->openAIService->cancelRun($threadId, $runId);
    }

   
}

