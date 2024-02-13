<?php

namespace LaravelAI\LaravelChatbot\Services;


use Illuminate\Support\Facades\Session;

class FileDownloadService
{
    protected $openAIService;

    public function __construct(MyOpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    public function downloadMessageFile($fileId)
    {
        $fileName = Session::get($fileId . '-fileName', 'defaultFile.csv');
        $threadId = Session::get($fileId . '-threadId');
        $messageId = Session::get($fileId . '-messageId');

        if (!$fileName || !$threadId || !$messageId) {
            return response()->json(['error' => 'File metadata not found.'], 404);
        }

        $fileContent = $this->openAIService->retrieveMessageFile($fileId);
        if (!$fileContent) {
            return response()->json(['error' => 'File not found or unable to retrieve.'], 404);
        }

        $contentType = $this->getContentTypeByExtension(pathinfo($fileName, PATHINFO_EXTENSION));

        return $this->createFileDownloadResponse($fileContent, $fileName, $contentType);
    }

    private function getContentTypeByExtension($extension)
    {
        $mimeTypes = [
            // ... your MIME type mappings ...
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }

    private function createFileDownloadResponse($fileContent, $fileName, $contentType)
    {
        return response()->streamDownload(function () use ($fileContent) {
            echo $fileContent;
        }, $fileName, ['Content-Type' => $contentType]);
    }
}
