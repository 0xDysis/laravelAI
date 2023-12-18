<?php

namespace App\Http\Controllers;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class OpenAIController extends Controller
{
    public function runAssistant()
    {
        $assistantId = $this->runPythonScript('create_assistant');
        $threadId = $this->runPythonScript('create_thread');
        $this->runPythonScript('add_message', [$threadId, 'user', 'What is the capital of France?']);
        $this->runPythonScript('run_assistant', [$threadId, $assistantId]);
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

        return $process->getOutput();
    }
}