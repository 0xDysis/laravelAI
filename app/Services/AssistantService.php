<?php
namespace App\Services;

use App\Services\PHPScriptRunnerService;
use Illuminate\Support\Facades\Auth;
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
        // Run the script to delete the assistant
        $this->phpScriptRunnerService->runScript('deleteAssistant', [$assistantId]);

        // Get the currently authenticated user
        $user = Auth::user();

        // Remove the assistantId from the user's assistant_ids array
        if (($key = array_search($assistantId, $user->assistant_ids)) !== false) {
            unset($user->assistant_ids[$key]);
        }

        // Save the updated user record
        $user->save();

        // Clear any cached data related to the assistant
        Cache::forget('processedMessages');
    }

    // ... any other assistant-related methods ...
}

