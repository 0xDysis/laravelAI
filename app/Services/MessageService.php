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
        $messagesJson = $this->phpScriptRunnerService->runScript('getMessages', [$threadId]);
        $messagesData = json_decode($messagesJson, true);
        $processedMessages = Cache::get('processedMessages', []);

        foreach ($messagesData as $key => $message) {
            if (isset($processedMessages[$message['id']])) {
                continue;
            }

            $fileId = $this->processMessageForFileId($threadId, $message);
            if ($fileId) {
                $messagesData[$key]['fileId'] = $fileId;
                $processedMessages[$message['id']] = 1;
            }
        }

        Cache::put('processedMessages', $processedMessages, 3600);
        return $messagesData;
    }

    private function processMessageForFileId($threadId, $message)
    {
        $fileIdsJson = $this->phpScriptRunnerService->runScript('listMessageFiles', [$threadId, $message['id']]);
        $fileIds = json_decode($fileIdsJson, true);

        if (!empty($fileIds) && is_array($fileIds)) {
            $fileId = $fileIds[0];
            $this->storeFileMetadataInSession($fileId, $threadId, $message['id'], $message['content']);
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
