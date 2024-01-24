<?php
namespace App\Services;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use App\Services\PHPScriptRunnerService;

class MessageService
{
    protected $phpScriptRunnerService;

    public function __construct(PHPScriptRunnerService $phpScriptRunnerService)
    {
        $this->phpScriptRunnerService = $phpScriptRunnerService;
    }

    public function submitMessage($threadId, $assistantId, $userMessage)
    {
        $this->phpScriptRunnerService->runScript('addMessage', [$threadId, 'user', $userMessage]);
    }

    public function getMessages($threadId)
    {
        return $this->fetchAndProcessMessages($threadId);
    }
   
    private function fetchAndProcessMessages($threadId) 
{
    $messagesJson = $this->phpScriptRunnerService->runScript('getMessages', [$threadId]);
    $messagesData = json_decode($messagesJson, true);
    $processedMessages = Cache::get('processedMessages', []);

    foreach ($messagesData as $key => &$message) {
        if (isset($processedMessages[$message['id']])) {
            
            $message['fileId'] = $processedMessages[$message['id']]['fileId'] ?? null;
            continue;
        }

        // Process new messages to find file IDs
        $fileId = $this->processMessageForFileId($threadId, $message);
        $message['fileId'] = $fileId;

        // Store the message status and fileId in the cache
        $processedMessages[$message['id']] = ['processed' => 1, 'fileId' => $fileId];
    }

    Cache::put('processedMessages', $processedMessages, 3600); // Update the cache with processed messages
    return $messagesData;
}

    
    

    private function processMessageForFileId($threadId, $message)
{
    // Check if file ID is already cached for this message
    $processedMessages = Cache::get('processedMessages', []);
    if (isset($processedMessages[$message['id']]['fileId'])) {
        // Return the cached fileId
        return $processedMessages[$message['id']]['fileId'];
    }

    $fileIdsJson = $this->phpScriptRunnerService->runScript('listMessageFiles', [$threadId, $message['id']]);
    $fileIds = json_decode($fileIdsJson, true);

    if (!empty($fileIds)) {
        $fileId = $fileIds[0];
        $this->storeFileMetadataInSession($fileId, $threadId, $message['id'], $message['content']);

        // Update the cache with the new fileId
        $processedMessages[$message['id']] = ['fileId' => $fileId];
        Cache::put('processedMessages', $processedMessages, 3600);

        return $fileId;
    }

    return null;
}


    private function storeFileMetadataInSession($fileId, $threadId, $messageId, $content)
    {
        $fileName = $this->extractFileNameFromContent($content);
        Session::put($fileId . '-threadId', $threadId);
        Session::put($fileId . '-messageId', $messageId);
        Session::put($fileId . '-fileName', $fileName);
    }

    private function extractFileNameFromContent($content)
    {
        if (preg_match('/\[Download (.*?)\]\(sandbox:/', $content, $matches)) {
            return $matches[1];
        }
        return 'defaultFileName.txt';
    }
}
