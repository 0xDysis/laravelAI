<?php
namespace App\Services;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class PHPScriptRunnerService
{
    private $phpBinaryPath;
    private $scriptPath;

    public function __construct()
    {
        // These paths can be set via environment variables or configuration settings
        $this->phpBinaryPath = config('/Users/dysisx/Library/Application Support/Herd/bin/php');
        $this->scriptPath = config('/Users/dysisx/Documents/assistant/app/Http/Controllers/OpenaiAssistantController.php');
    }

    public function runScript($function, $args = [])
    {
        $process = new Process(array_merge([$this->phpBinaryPath, $this->scriptPath, $function], $args));
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
