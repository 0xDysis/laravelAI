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
        $fileContent = $this->phpScriptRunnerService->runScript('retrieveMessageFile', [$fileId]);
        if (!$fileContent) {
            // Handle error response
            return response()->json(['error' => 'File not found or unable to retrieve.'], 404);
        }

        $fileName = Session::get($fileId . '-fileName', 'defaultFile.csv');
        $contentType = $this->getContentTypeByExtension(pathinfo($fileName, PATHINFO_EXTENSION));

        return $this->createFileDownloadResponse($fileContent, $fileName, $contentType);
    }

    private function createFileDownloadResponse($fileContent, $fileName, $contentType)
    {
        return response()->streamDownload(function () use ($fileContent) {
            echo $fileContent;
        }, $fileName, ['Content-Type' => $contentType]);
    }

    private function getContentTypeByExtension($extension)
    {
        $mimeTypes = [
            // Mime types mapping
            // Add your mime types here
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream'; 
    }
}
