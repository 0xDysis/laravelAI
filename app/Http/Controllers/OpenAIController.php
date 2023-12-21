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
    
        
        $this->runPHPScript('addMessage', [$threadId, 'user', $userMessage]);
    
        
        $runId = $this->runPHPScript('runAssistant', [$threadId, $assistantId]);
    
        
        $rawData = $this->runPHPScript('getMessages', [$threadId]);
    
        return view('assistant', ['rawData' => $rawData]);
    }

    private function runPHPScript($function, $args = [])
    {
       
        $process = new Process(array_merge(['php', '/Users/dysisx/Documents/assistant/app/Http/Controllers/OpenaiAssistantController.php', $function], $args));
        $process->setWorkingDirectory(base_path());  
        $process->run();
    
        
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    
        $output = trim($process->getOutput());
        if (!$output) {
            throw new \Exception("No output from PHP script for function: $function");
        }
    
        
        return $output;
    }
    public function deleteThread()
    {
        
        $this->runPHPScript('deleteThread', [Session::get('threadId')]);
        Session::forget('threadId');
        return redirect('/');
    }
    
    public function deleteAssistant()
    {
       
        $this->runPHPScript('deleteAssistant', [Session::get('assistantId')]);
        Session::forget('assistantId');
        return redirect('/');
    }
    public function createNewThread()
{
    $threadId = $this->runPHPScript('createThread');
    Session::put('threadId', $threadId);
    return redirect('/');
}

public function createNewAssistant()
{
    $assistantId = $this->runPHPScript('createAssistant');
    Session::put('assistantId', $assistantId);
    return redirect('/');
}


    
}
