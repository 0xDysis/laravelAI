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
        // Create a new assistant and thread if they don't exist in the session
        if (!Session::has('assistantId') || !Session::has('threadId')) {
            $assistantId = $this->runPHPScript('createAssistant');
            $threadId = $this->runPHPScript('createThread');
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

    // Add the user's message to the thread and run the assistant
    $this->runPHPScript('addMessage', [$threadId, 'user', $userMessage]);
    $this->runPHPScript('runAssistant', [$threadId, $assistantId]);

    // Wait for the assistant to finish processing
    sleep(7);

    // Retrieve the messages from the thread
    $rawData = $this->runPHPScript('getMessages', [$threadId]);

    // Pass the raw data to the view
    return view('assistant', ['rawData' => $rawData]);
}

    
    

    private function runPHPScript($function, $args = [])
    {
        // Run the specified function from the PHP script with the provided arguments
        $process = new Process(array_merge(['php', '/Users/dysisx/Documents/assistant/app/Http/Controllers/OpenaiAssistantController.php', $function], $args));
        $process->setWorkingDirectory(base_path());  // Set the working directory to the Laravel project root
        $process->run();
    
        // Throw an exception if the process fails or if there is no output from the PHP script
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    
        $output = trim($process->getOutput());
        if (!$output) {
            throw new \Exception("No output from PHP script for function: $function");
        }
    
        // Return the output from the PHP script
        return $output;
    }
    public function deleteThread()
{
    // Call your PHP script to delete the thread
    $this->runPHPScript('deleteThread', [Session::get('threadId')]);
    Session::forget('threadId');
    return redirect('/');
}

public function deleteAssistant()
{
    // Call your PHP script to delete the assistant
    $this->runPHPScript('deleteAssistant', [Session::get('assistantId')]);
    Session::forget('assistantId');
    return redirect('/');
}

    
}
