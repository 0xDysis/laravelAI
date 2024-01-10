<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class MessageService {
    private $phpBinaryPath;
    private $scriptPath;

    public function __construct() {
        $this->phpBinaryPath = '/Users/dysisx/Library/Application Support/Herd/bin/php';
        $this->scriptPath = '/Users/dysisx/Documents/assistant/app/Http/Controllers/OpenaiAssistantController.php';
    }

    public function submitMessage($request) {
        $this->validateSession(['assistantId', 'threadId']);
        $userMessage = $request->input('message');
        return $this->runPHPScript('addMessage', [Session::get('threadId'), 'user', $userMessage]);
    }

    public function getMessages($threadId) {
        return $this->fetchAndProcessMessages($threadId);
    }

    private function fetchAndProcessMessages($threadId) {
        // Implementation of fetchAndProcessMessages
    }

    private function runPHPScript($function, $args = []) {
        $process = new Process(array_merge([$this->phpBinaryPath, $this->scriptPath, $function], $args));
        $process->setWorkingDirectory(base_path());  
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return trim($process->getOutput());
    }

    private function validateSession(array $keys) {
        foreach ($keys as $key) {
            if (!Session::has($key)) {
                throw new \Exception("$key not found in session.");
            }
        }
    }

    // Other methods related to messages...
}
