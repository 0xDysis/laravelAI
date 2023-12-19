<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class OpenAIController extends Controller
{
    public function index()
    {
        // Check if the assistant and thread IDs are already stored in the session
        if (!Session::has('assistantId') || !Session::has('threadId')) {
            $assistantId = $this->runPythonScript('create_assistant');
            $threadId = $this->runPythonScript('create_thread');
            Session::put('assistantId', $assistantId);
            Session::put('threadId', $threadId);
        }

        return view('assistant');
    }

    public function submitMessage(Request $request)
    {
        $userMessage = $request->input('message');
        $assistantId = Session::get('assistantId');
        $threadId = Session::get('threadId');
    
        if (!$assistantId || !$threadId) {
            throw new \Exception('Assistant or thread ID not found in session.');
        }
    
        $this->runPythonScript('add_message', [$threadId, 'user', $userMessage]);
        $this->runPythonScript('run_assistant', [$threadId, $assistantId]);
    
        // Wait for the assistant to finish processing
        sleep(5);  // Wait for 5 seconds
    
        $messages = $this->runPythonScript('get_messages', [$threadId]);
    
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