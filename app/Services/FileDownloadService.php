<?php


namespace App\Services;

use Illuminate\Support\Facades\Session;
use App\Services\PHPScriptRunnerService;

class FileDownloadService
{
    protected $phpScriptRunnerService;

    public function __construct(PHPScriptRunnerService $phpScriptRunnerService)
    {
        $this->phpScriptRunnerService = $phpScriptRunnerService;
    }

    public function downloadMessageFile($fileId)
    {
        // Retrieve file metadata from the session
        $fileName = Session::get($fileId . '-fileName', 'defaultFile.csv');
        $threadId = Session::get($fileId . '-threadId');
        $messageId = Session::get($fileId . '-messageId');

        // If any of the required metadata is missing, return an error
        if (!$fileName || !$threadId || !$messageId) {
            return response()->json(['error' => 'File metadata not found.'], 404);
        }

        // Retrieve the file content using the PHPScriptRunnerService
        $fileContent = $this->phpScriptRunnerService->runScript('retrieveMessageFile', [$fileId]);
        if (!$fileContent) {
            return response()->json(['error' => 'File not found or unable to retrieve.'], 404);
        }

        // Determine the content type
        $contentType = $this->getContentTypeByExtension(pathinfo($fileName, PATHINFO_EXTENSION));

        // Create and return the file download response
        return $this->createFileDownloadResponse($fileContent, $fileName, $contentType);
    }

    private function getContentTypeByExtension($extension)
    {
        // Mapping of file extensions to MIME types
        $mimeTypes = [
            'csv' => 'text/csv',
            'txt' => 'text/plain',
            // Add more mappings as needed
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }

    private function createFileDownloadResponse($fileContent, $fileName, $contentType)
    {
        // Stream the file content for download
        return response()->streamDownload(function () use ($fileContent) {
            echo $fileContent;
        }, $fileName, ['Content-Type' => $contentType]);
    }
}
