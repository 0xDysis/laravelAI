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
        // Retrieve the file content using the PHPScriptRunnerService
        $fileContent = $this->phpScriptRunnerService->runScript('retrieveMessageFile', [$fileId]);
        if (!$fileContent) {
            // Handle error response if file content is not found
            return response()->json(['error' => 'File not found or unable to retrieve.'], 404);
        }

        // Retrieve file name and content type from session
        $fileName = Session::get($fileId . '-fileName', 'defaultFile.csv');
        $contentType = $this->getContentTypeByExtension(pathinfo($fileName, PATHINFO_EXTENSION));

        // Create and return the file download response
        return $this->createFileDownloadResponse($fileContent, $fileName, $contentType);
    }

    private function getContentTypeByExtension($extension)
    {
        // Mapping of file extensions to MIME types
        $mimeTypes = [
           
            // add more mappings as needed
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
