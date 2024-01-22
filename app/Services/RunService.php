<?php
namespace App\Services;

use Illuminate\Support\Facades\Session;
use App\Services\PHPScriptRunnerService;

class RunService
{
    protected $phpScriptRunnerService;

    public function __construct(PHPScriptRunnerService $phpScriptRunnerService)
    {
        $this->phpScriptRunnerService = $phpScriptRunnerService;
    }

    public function startRun($threadId, $assistantId)
    {
        return $this->phpScriptRunnerService->runScript('runAssistant', [$threadId, $assistantId]);
    }

    public function checkRunStatus($threadId, $runId)
    {
        return $this->phpScriptRunnerService->runScript('checkRunStatus', [$threadId, $runId]);
    }

    // Add the cancelRun method
    public function cancelRun($threadId, $runId)
    {
        return $this->phpScriptRunnerService->runScript('cancelRun', [$threadId, $runId]);
    }

    // ... additional methods if necessary ...
}
