<?php
namespace App\Services;

use App\Services\PHPScriptRunnerService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;

class AssistantService
{
    protected $phpScriptRunnerService;

    public function __construct(PHPScriptRunnerService $phpScriptRunnerService)
    {
        $this->phpScriptRunnerService = $phpScriptRunnerService;
    }

    public function deleteAssistant($assistantId)
    {
        $this->phpScriptRunnerService->runScript('deleteAssistant', [$assistantId]);
        Session::forget('assistantId');
        Cache::forget('processedMessages');
    }

    // ... any other assistant-related methods ...
}

