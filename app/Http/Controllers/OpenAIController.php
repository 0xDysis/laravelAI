<?php

namespace App\Http\Controllers;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;


class OpenAIController extends Controller
{
    public function runAssistant()
    {
        $assistantId = $this->runPythonScript('create_assistant');
        if (!$assistantId) {
            throw new \Exception('Failed to create assistant');
        }
    
        $threadId = $this->runPythonScript('create_thread');
        if (!$threadId) {
            throw new \Exception('Failed to create thread');
        }
    
        $this->runPythonScript('add_message', [$threadId, 'user', 'What is the capital of France?']);
        $runId = $this->runPythonScript('run_assistant', [$threadId, $assistantId]);  // Capture the run ID
    
        // Wait for the assistant to finish processing
        sleep(5);  // Wait for 5 seconds
    
        $messages = $this->runPythonScript('get_messages', [$threadId]);  // Pass the run ID to get_messages
    
        return view('assistant', ['messages' => $messages]);
    }

    private function runPythonScript($function, $args = [])
{
    $process = new Process(array_merge(['python3', base_path('app/PythonScripts/openai_assistant.py'), $function], $args));
    $process->run();

    if (!$process->isSuccessful()) {
        throw new ProcessFailedException($process);
    }

    $output = trim($process->getOutput());
    if (!$output) {
        throw new \Exception("No output from Python script for function: $function");
    }

    return $output;  // Return the JSON string directly
}
}