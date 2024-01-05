<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Models\Order; 

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

        // Note: We're not running the assistant here anymore, as it will be done asynchronously
        return view('assistant', ['threadId' => $threadId, 'assistantId' => $assistantId]);
    }

    public function startRun(Request $request)
    {
        $threadId = Session::get('threadId');
        $assistantId = Session::get('assistantId');

        if (!$assistantId || !$threadId) {
            return response()->json(['error' => 'Assistant or thread ID not found in session.'], 400);
        }

        $runId = $this->runPHPScript('runAssistant', [$threadId, $assistantId]);
        return response()->json(['runId' => $runId]);
    }

    public function checkRunStatus(Request $request)
    {
        $runId = $request->input('runId');
        $threadId = Session::get('threadId');

        if (!$threadId) {
            return response()->json(['error' => 'Thread ID not found in session.'], 400);
        }

        $status = $this->runPHPScript('checkRunStatus', [$threadId, $runId]);
        return response($status); // Assuming the status is a JSON string
    }
    public function getMessages()
    {
        $threadId = Session::get('threadId');
        
        if (!$threadId) {
            return response()->json(['error' => 'Thread ID not found in session.'], 400);
        }
        
        $messagesJson = $this->runPHPScript('getMessages', [$threadId]);
        $messagesData = json_decode($messagesJson, true);
    
        foreach ($messagesData as $key => $message) {
            $fileIdsJson = $this->runPHPScript('listMessageFiles', [$threadId, $message['id']]);
            $fileIds = json_decode($fileIdsJson, true);
    
            if (is_null($fileIds)) {
                // Special handling for non-array responses, possibly for image files
                // This logic needs to be refined based on the actual response structure for images
                $messagesData[$key]['fileId'] = 'image_handling_needed'; // Placeholder
            } elseif (!empty($fileIds) && is_array($fileIds)) {
                // Standard handling for regular files (CSV, HTML, etc.)
                $fileId = $fileIds[0];
                $messagesData[$key]['fileId'] = $fileId;
    
                $fileName = $this->extractFileNameFromContent($message['content']);
                Session::put($fileId . '-threadId', $threadId);
                Session::put($fileId . '-messageId', $message['id']);
                Session::put($fileId . '-fileName', $fileName);
            } else {
                $messagesData[$key]['fileId'] = null;
            }
        }
    
        return response()->json($messagesData);
    }
    


private function extractFileNameFromContent($content)
{
    // Regex to extract file name from the content
    if (preg_match('/\[Download (.*?)\]\(sandbox:/', $content, $matches)) {
        return $matches[1]; // Returns the file name with extension
    }

    return 'defaultFileName.txt'; // Default file name if not found
}



public function downloadMessageFile($fileId)
{
    $fileContent = $this->runPHPScript('retrieveMessageFile', [$fileId]);
    $fileName = Session::get($fileId . '-fileName', 'defaultFile.csv');
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
    $contentType = $this->getContentTypeByExtension($fileExtension);

    return response()->streamDownload(function () use ($fileContent) {
        // Ensure output buffering is turned off
        if (ob_get_level()) {
            ob_end_clean();
        }
        echo $fileContent;
    }, $fileName, [
        'Content-Type' => $contentType,
    ]);
}




private function getContentTypeByExtension($extension)
{
    $mimeTypes = [
        
    ];

    return $mimeTypes[$extension] ?? 'application/octet-stream'; 
}






    


    public function retrieveMessageFile($threadId, $messageId, $fileId)
    {
        return $this->runPHPScript('retrieveMessageFile', [$threadId, $messageId, $fileId]);
    }

    public function listMessageFiles($threadId, $messageId)
    {
        return $this->runPHPScript('listMessageFiles', [$threadId, $messageId]);
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

    public function createNewAssistantWithCsv()
    {
        $csvData = $this->convertOrdersToCsv();
    
        // Create a temporary file and write the CSV data to it
        $tempFilePath = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($tempFilePath, $csvData);
    
        // Pass the path of the temporary file to the script
        $assistantId = $this->runPHPScript('createAssistant', [$tempFilePath]);
    
        Session::put('assistantId', $assistantId);
        unlink($tempFilePath); // Remove the temporary file after use
    
        return redirect('/');
    }
    

    private function convertOrdersToCsv()
    {
        $orders = Order::all();

        // Convert orders to CSV format
        $csvData = "order_id,customer_name,order_total\n"; // Add your CSV headers
        foreach ($orders as $order) {
            $csvData .= "{$order->order_id},{$order->customer_name},{$order->order_total}\n"; // Format each order
        }

        return $csvData;
    }

    private function runPHPScript($function, $args = [])
{
    $scriptPath = '/Users/dysisx/Documents/assistant/app/Http/Controllers/OpenaiAssistantController.php';

    $process = new Process(array_merge(['php', $scriptPath, $function], $args));
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

}